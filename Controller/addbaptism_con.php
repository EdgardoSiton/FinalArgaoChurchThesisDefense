<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
session_start();
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';
require 'phpmailer/src/Exception.php';
require __DIR__ . '/twilio-php-main/src/Twilio/autoload.php';
require __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . '/../Model/staff_mod.php';
require_once __DIR__ . '/../Model/db_connection.php';
require_once __DIR__ . '/../Model/citizen_mod.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $staff = new Staff($conn);
    
    $Citizen = new Citizen($conn);
    $requestform_id = isset($_POST['requestform_id']) ? $_POST['requestform_id'] : null;
    $outsiderequestform_id = isset($_POST['outsiderequestform_id']) ? $_POST['outsiderequestform_id'] : null;
    $requestform_ids = isset($_POST['requestform_ids']) ? $_POST['requestform_ids'] : null;
    $baptismfill_id = isset($_POST['baptismfill_id']) ? $_POST['baptismfill_id'] : null;
     $confirmationfill_id = isset($_POST['confirmationfill_id']) ? $_POST['confirmationfill_id'] : null;
     $walkinconfirmationfill_id = isset($_POST['confirmation_id']) ? $_POST['confirmation_id'] : null;
    $massbaptismfillId = isset($_POST['massbaptismfill_id']) ? $_POST['massbaptismfill_id'] : null;
    $weddingffill_id = isset($_POST['marriage_id']) ? $_POST['marriage_id'] : null;
    $massweddingffill_id = isset($_POST['massmarriage_id']) ? $_POST['massmarriage_id'] : null;
    $defuctomfill_id = isset($_POST['defuctom_id']) ? $_POST['defuctom_id'] : null;
   // Handle Baptism
   



    if ($baptismfill_id) {
        $sunday = $start_time = $end_time = $priestId = $payableAmount = null;

        if (isset($_POST['sundays'])) {
            $selected_sunday = explode('|', $_POST['sundays']);
            if (count($selected_sunday) === 4) {
                list($schedule_id, $sunday, $start_time, $end_time) = $selected_sunday;
        
                // Convert start_time and end_time to 24-hour format
                $start_time = (new DateTime($start_time))->format('H:i:s');
                $end_time = (new DateTime($end_time))->format('H:i:s');
                
                // Now $start_time and $end_time are in 24-hour format
            } else {
                die('Error: Expected 4 values, but received fewer.');
            }
        }
        
 
        $payableAmount = $_POST['eventTitle'] ?? null;
        $eventspeaker = $_POST['eventspeaker'] ?? null;
        $citizen_id = isset($_SESSION['citizen_id']) ? $_SESSION['citizen_id'] : null;

        // Check that all required fields are present
        if (!$sunday || !$start_time || !$end_time || !$payableAmount|| !$eventspeaker) {
            die('Error: Missing required form data.');
        }

        $appointment = new Staff($conn);
        $scheduleId = $appointment->insertSchedule($sunday, $start_time, $end_time, 'Seminar');

        if ($scheduleId) {
            $result = $Citizen->insertAppointment($baptismfill_id, $payableAmount,$eventspeaker, $scheduleId);
            $result = $appointment->approveBaptism($baptismfill_id);
            if ($result) {
                $contactInfo = $appointment->getContactInfoAndTitle($baptismfill_id);
             
                $email = $contactInfo['email'];
                $citizen_name = $contactInfo['fullname'];
                $title = $contactInfo['event_name'];
                $phone = $contactInfo['phone'];  // Assuming this is the phone number
                $seminar_date = (new DateTime($contactInfo['seminar_date']))->format('F j, Y');
                    $seminar_start_time = (new DateTime($contactInfo['seminar_start_time']))->format('g:i A');
                    $seminar_end_time = (new DateTime($contactInfo['seminar_end_time']))->format('g:i A');
            
                // Send email
                try {
                    $mail = new PHPMailer(true);
                    $mail->isSMTP();
                    $mail->Host = "smtp.gmail.com";
                    $mail->SMTPAuth = true;
                    $mail->Username = "argaoparishchurch@gmail.com";
                    $mail->Password = "xomoabhlnrlzenur";
                    $mail->SMTPSecure = 'tls';
                    $mail->Port = 587;
            
                    $mail->setFrom('argaoparishchurch@gmail.com');
                    $mail->addAddress($email);
                    $mail->addEmbeddedImage('signature.png', 'signature_img');
                    $mail->addEmbeddedImage('logo.jpg', 'background_img');
                    $mail->isHTML(true);
                    $mail->Subject = "Appointment Schedule Confirmation";
                    $mail->Body = "
                    <div style='width: 100%; max-width: 400px; margin: auto; background: url(cid:background_img) no-repeat center center; background-size: cover; padding: 20px;'>
                            <div style='background: rgba(255, 255, 255, 0.8); padding: 20px; width:100%; height:auto;'>
                                Dear {$citizen_name},<br><br>
                                We are pleased to confirm your appointment for the event titled '<strong>{$title}</strong>'.<br><br>
                                <strong>Details of Your Seminar Schedule:</strong><br>
                                <ul>
                                    <li><strong>Date:</strong> {$seminar_date}</li>
                                    <li><strong>Time:</strong> {$seminar_start_time} to {$seminar_end_time}</li>
                                    <li><strong>Amount Due:</strong> ₱{$payableAmount}.00</li>
                                </ul>
                                Please ensure to complete the payment at the church office on or before the day of your appointment or your Seminar.<br><br>
                                We look forward to your participation and thank you for choosing Argao Parish Church.<br><br>
                                Best regards,<br>
                                <img src='cid:signature_img' style='width: 200px; height: auto;'>
                            </div>
                        </div>";
            
                    if ($mail->send()) {
                        echo "Email notification sent successfully.";
                    } else {
                        echo "Error sending email notification: " . $mail->ErrorInfo;
                    }
                } catch (Exception $e) {
                    echo "Error sending email notification: " . $e->getMessage();
                }
            
                // Send SMS via Twilio
                try {
                    $sid = 'AC7ef9e279eebfda753ed9f67ae1a77710';  // Your Twilio Account SID
                    $token = '40464522e94c2cdb6296d701bbe36a51';  // Your Twilio Auth Token
                    $twilio = new Twilio\Rest\Client($sid, $token);
                
                    $message = $twilio->messages->create(
                        $phone, // The recipient's phone number
                        [
                            'from' => '+17082776875', // Your Twilio phone number
                            'body' => "Dear {$citizen_name}, your appointment for '{$title}' on {$seminar_date} from {$seminar_start_time} to {$seminar_end_time} has been confirmed. ₱{$payableAmount}.00 is payable on the day of your appointment."
                        ]
                    );
                
                  
                
                    if ($message->sid) {
                        echo "SMS notification sent successfully.";
                    } else {
                        echo "Error sending SMS notification.";
                    }
                } catch (Exception $e) {
                    echo "Error sending SMS notification: " . $e->getMessage();
                }
            
                $_SESSION['status'] = "success";
                header('Location: ../View/PageStaff/StaffSoloSched.php');
                exit();
            
            } else {
                echo "Error updating status. Please try again.";
            }
        } else {
            echo "Error inserting schedule record. Please try again.";
        }
    }
    // Check if $massbaptismfillId is set, indicating a mass baptism
    if ($massbaptismfillId) {
        $payableAmount = $_POST['eventTitle'] ?? null;
        $citizen_id = isset($_SESSION['citizen_id']) ? $_SESSION['citizen_id'] : null;
     
        // Ensure required fields are present
        if (!$payableAmount) {
            die('Error: Missing required form data for mass baptism.');
        }
    
        $appointment = new Staff($conn);
    
        // Attempt to insert the mass appointment
        $insertResult = $appointment->insertMassAppointment($massbaptismfillId, NULL, $payableAmount);
    
        // If insert is successful, then attempt to approve the baptism
        if ($insertResult) {
            $approvalResult = $appointment->approveBaptism($massbaptismfillId);
    
            // Retrieve contact information
            $contactInfo = $appointment->getContactInfoAndTitle(null, $massbaptismfillId);
    
            // Check if contactInfo was successfully retrieved and contains contact details
            if ($contactInfo && !empty($contactInfo['email']) && !empty($contactInfo['phone'])) {
                $email = $contactInfo['email'];
                $citizen_name = $contactInfo['fullname'];
                $title = $contactInfo['event_name'];
                $phone = $contactInfo['phone'];
                $seminar_date = (new DateTime($contactInfo['seminar_date']))->format('F j, Y');
                $seminar_start_time = (new DateTime($contactInfo['seminar_start_time']))->format('g:i A');
                $seminar_end_time = (new DateTime($contactInfo['seminar_end_time']))->format('g:i A');
    
                try {
                    // Set up PHPMailer
                    $mail = new PHPMailer(true);
                    $mail->isSMTP();
                    $mail->Host = "smtp.gmail.com";
                    $mail->SMTPAuth = true;
                    $mail->Username = "argaoparishchurch@gmail.com";
                    $mail->Password = "xomoabhlnrlzenur";
                    $mail->SMTPSecure = 'tls';
                    $mail->Port = 587;
    
                    $mail->setFrom('argaoparishchurch@gmail.com');
                    $mail->addAddress($email);
                    $mail->addEmbeddedImage('signature.png', 'signature_img');
                    $mail->addEmbeddedImage('logo.jpg', 'background_img');
                    $mail->isHTML(true);
                    $mail->Subject = "Mass Baptism Schedule Confirmation";
                    $mail->Body = "
                    <div style='width: 100%; max-width: 400px; margin: auto; background: url(cid:background_img) no-repeat center center; background-size: cover; padding: 20px;'>
                            <div style='background: rgba(255, 255, 255, 0.8); padding: 20px; width:100%; height:auto;'>
                                Dear {$citizen_name},<br><br>
                                We are pleased to confirm your appointment for the event titled '<strong>{$title}</strong>'.<br><br>
                                <strong>Details of Your Seminar Schedule:</strong><br>
                                <ul>
                                    <li><strong>Date:</strong> {$seminar_date}</li>
                                    <li><strong>Time:</strong> {$seminar_start_time} to {$seminar_end_time}</li>
                                    <li><strong>Amount Due:</strong> ₱{$payableAmount}.00</li>
                                </ul>
                                Please ensure to complete the payment at the church office on or before the day of your appointment or your Seminar.<br><br>
                                We look forward to your participation and thank you for choosing Argao Parish Church.<br><br>
                                Best regards,<br>
                                <img src='cid:signature_img' style='width: 200px; height: auto;'>
                            </div>
                        </div>";
    
                    if ($mail->send()) {
                        echo "Email notification sent successfully.";
                    } else {
                        echo "Error sending email notification: " . $mail->ErrorInfo;
                    }
                } catch (Exception $e) {
                    echo "Error sending email notification: " . $e->getMessage();
                }
    
                // Send SMS via Twilio
                try {
                    $sid = 'AC7ef9e279eebfda753ed9f67ae1a77710';  // Your Twilio Account SID
                    $token = '40464522e94c2cdb6296d701bbe36a51';  // Your Twilio Auth Token
                    $twilio = new Twilio\Rest\Client($sid, $token);
    
                    $message = $twilio->messages->create(
                        $phone, // The recipient's phone number
                        [
                            'from' => '+17082776875', // Your Twilio phone number
                            'body' => "Dear {$citizen_name}, your appointment for '{$title}' on {$seminar_date} from {$seminar_start_time} to {$seminar_end_time} has been confirmed. ₱{$payableAmount}.00 is payable on the day of your appointment."
                        ]
                    );
    
                    if ($message->sid) {
                        echo "SMS notification sent successfully.";
                    } else {
                        echo "Error sending SMS notification.";
                    }
                } catch (Exception $e) {
                    echo "Error sending SMS notification: " . $e->getMessage();
                }
    
                $_SESSION['status'] = "success";
                header('Location: ../View/PageStaff/StaffMassSched.php');
            } else {
                $_SESSION['status'] = "success";
                header('Location: ../View/PageStaff/StaffMassSched.php');
            }
        } else {
            echo "Error inserting mass appointment. Please try again.";
        }
    }
    else if($weddingffill_id){
        if (isset($_POST['sundays'])) {
            $selected_sunday = explode('|', $_POST['sundays']);
            if (count($selected_sunday) === 4) {
                $schedule_id = $selected_sunday[0];
                $sunday = $selected_sunday[1];
                $start_time = $selected_sunday[2];
                $end_time = $selected_sunday[3];
        
                // Convert start_time and end_time to 24-hour (military) format
                $start_time = (new DateTime($start_time))->format('H:i:s');
                $end_time = (new DateTime($end_time))->format('H:i:s');
                
                // Now $start_time and $end_time are in 24-hour format
            } else {
                die("Error: Expected 4 values for sundays.");
            }
        } else {
            die("Error: No sundays data provided.");
        }
        
       
        $payableAmount = $_POST['eventTitle']; 
        $eventspeaker = $_POST['eventspeaker'] ?? null;
        $citizen_id = isset($_SESSION['citizen_id']) ? $_SESSION['citizen_id'] : null;

        if (!$sunday || !$start_time || !$end_time || !$payableAmount || !$weddingffill_id) {
            die('Error: Missing required form data.');
        }

        $appointment = new Staff($conn);
        $scheduleId = $appointment->insertSchedule($sunday, $start_time, $end_time, 'Seminar');

        if ($scheduleId) {
            $result = $appointment->insertwAppointment($weddingffill_id, $payableAmount,$eventspeaker, $scheduleId);
            $result = $appointment->approveWedding($weddingffill_id);
            
            if ($result) {
                $contactInfo = $appointment->getWeddingContactInfoAndTitles($weddingffill_id);

                if ($contactInfo) {
                    $email = $contactInfo['email'];
                    $citizen_name = $contactInfo['fullname'];
                    $title = $contactInfo['event_name'];
                    $phone = $contactInfo['phone'];
                    $seminar_date = (new DateTime($contactInfo['seminar_date']))->format('F j, Y');
                    $seminar_start_time = (new DateTime($contactInfo['seminar_start_time']))->format('g:i A');
                    $seminar_end_time = (new DateTime($contactInfo['seminar_end_time']))->format('g:i A');
                      // Assuming this is the phone number
                    try {
                        $mail = new PHPMailer(true);
                        $mail->isSMTP();
                        $mail->Host = "smtp.gmail.com";
                        $mail->SMTPAuth = true;
                        $mail->Username = "argaoparishchurch@gmail.com";
                        $mail->Password = "xomoabhlnrlzenur";
                        $mail->SMTPSecure = 'tls';
                        $mail->Port = 587;

                        $mail->setFrom('argaoparishchurch@gmail.com');
                        $mail->addAddress($email);
                        $mail->addEmbeddedImage('signature.png', 'signature_img');
                        $mail->addEmbeddedImage('logo.jpg', 'background_img');
                        $mail->isHTML(true);
                        $mail->Subject = "Appointment Schedule Confirmation";
                        $mail->Body = "
                        <div style='width: 100%; max-width: 400px; margin: auto; background: url(cid:background_img) no-repeat center center; background-size: cover; padding: 20px;'>
                            <div style='background: rgba(255, 255, 255, 0.8); padding: 20px; width:100%; height:auto;'>
                                Dear {$citizen_name},<br><br>
                                We are pleased to confirm your appointment for the event titled '<strong>{$title}</strong>'.<br><br>
                                <strong>Details of Your Seminar Schedule:</strong><br>
                                <ul>
                                    <li><strong>Date:</strong> {$seminar_date}</li>
                                    <li><strong>Time:</strong> {$seminar_start_time} to {$seminar_end_time}</li>
                                    <li><strong>Amount Due:</strong> ₱{$payableAmount}.00</li>
                                </ul>
                                Please ensure to complete the payment at the church office on or before the day of your appointment or your Seminar.<br><br>
                                We look forward to your participation and thank you for choosing Argao Parish Church.<br><br>
                                Best regards,<br>
                                <img src='cid:signature_img' style='width: 200px; height: auto;'>
                            </div>
                        </div>";

                        if ($mail->send()) {
                            echo "Email notification sent successfully.";
                        } else {
                            echo "Error sending email notification: " . $mail->ErrorInfo;
                        }
                    } catch (Exception $e) {
                        echo "Error sending email notification: " . $e->getMessage();
                    }
                    try {
                        $sid = 'AC7ef9e279eebfda753ed9f67ae1a77710';  // Your Twilio Account SID
                        $token = '40464522e94c2cdb6296d701bbe36a51';  // Your Twilio Auth Token
                        $twilio = new Twilio\Rest\Client($sid, $token);
                    
                        $message = $twilio->messages->create(
                            $phone, // The recipient's phone number
                            [
                                'from' => '+17082776875', // Your Twilio phone number
                                'body' => "Dear {$citizen_name}, your appointment for '{$title}' on {$seminar_date} from {$seminar_start_time} to {$seminar_end_time} has been confirmed. ₱{$payableAmount}.00 is payable on the day of your appointment."
                            ]
                        );
                    
                      
                    
                        if ($message->sid) {
                            echo "SMS notification sent successfully.";
                        } else {
                            echo "Error sending SMS notification.";
                        }
                    } catch (Exception $e) {
                        echo "Error sending SMS notification: " . $e->getMessage();
                    }

                    $_SESSION['status'] = "success";
                    header('Location: ../View/PageStaff/StaffSoloSched.php');
                    exit();
                } else {
                    echo "No contact information found for the given wedding ID.";
                }
            } else {
                echo "Error updating status. Please try again.";
            }
        } else {
            echo "Error inserting schedule record. Please try again.";
        }
    }else if($massweddingffill_id){
 // Handle mass wedding
 $payableAmount = $_POST['eventTitle'] ?? null;
 $citizen_id = isset($_SESSION['citizen_id']) ? $_SESSION['citizen_id'] : null;

 if (!$payableAmount) {
     die('Error: Missing required form data for mass wedding.');
 }

 $appointment = new Staff($conn);
 $insertResult = $appointment->insertMassAppointment(null,$massweddingffill_id, $payableAmount);

 if ($insertResult) {
    $approvalResult = $appointment->approveWedding($massweddingffill_id);

    if ($approvalResult) {
        // Retrieve contact info and skip if email or phone is empty
        $contactInfo = $appointment->getWeddingContactInfoAndTitles(null, $massweddingffill_id);

        if (!empty($contactInfo['email']) && !empty($contactInfo['phone'])) {
            $email = $contactInfo['email'];
            $citizen_name = $contactInfo['fullname'];
            $title = $contactInfo['event_name'];
            $phone = $contactInfo['phone'];
            $seminar_date = (new DateTime($contactInfo['seminar_date']))->format('F j, Y');
            $seminar_start_time = (new DateTime($contactInfo['seminar_start_time']))->format('g:i A');
            $seminar_end_time = (new DateTime($contactInfo['seminar_end_time']))->format('g:i A');

            // Send Email Notification
            try {
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host = "smtp.gmail.com";
                $mail->SMTPAuth = true;
                $mail->Username = "argaoparishchurch@gmail.com";
                $mail->Password = "xomoabhlnrlzenur";
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;

                $mail->setFrom('argaoparishchurch@gmail.com');
                $mail->addAddress($email);
                $mail->addEmbeddedImage('signature.png', 'signature_img');
                $mail->addEmbeddedImage('logo.jpg', 'background_img');
                $mail->isHTML(true);
                $mail->Subject = "Mass Wedding Schedule Confirmation";
                $mail->Body = "
                <div style='width: 100%; max-width: 400px; margin: auto; background: url(cid:background_img) no-repeat center center; background-size: cover; padding: 20px;'>
                            <div style='background: rgba(255, 255, 255, 0.8); padding: 20px; width:100%; height:auto;'>
                                Dear {$citizen_name},<br><br>
                                We are pleased to confirm your appointment for the event titled '<strong>{$title}</strong>'.<br><br>
                                <strong>Details of Your Seminar Schedule:</strong><br>
                                <ul>
                                    <li><strong>Date:</strong> {$seminar_date}</li>
                                    <li><strong>Time:</strong> {$seminar_start_time} to {$seminar_end_time}</li>
                                    <li><strong>Amount Due:</strong> ₱{$payableAmount}.00</li>
                                </ul>
                                Please ensure to complete the payment at the church office on or before the day of your appointment or your Seminar.<br><br>
                                We look forward to your participation and thank you for choosing Argao Parish Church.<br><br>
                                Best regards,<br>
                                <img src='cid:signature_img' style='width: 200px; height: auto;'>
                            </div>
                        </div>";

                $mail->send();
            } catch (Exception $e) {
                echo "Error sending email notification: " . $e->getMessage();
            }

            // Send SMS Notification
            try {
                $sid = 'AC7ef9e279eebfda753ed9f67ae1a77710';
                $token = '40464522e94c2cdb6296d701bbe36a51';
                $twilio = new Twilio\Rest\Client($sid, $token);

                $twilio->messages->create(
                    $phone,
                    [
                        'from' => '+17082776875',
                        'body' => "Dear {$citizen_name}, your appointment for '{$title}' on {$seminar_date} from {$seminar_start_time} to {$seminar_end_time} has been confirmed. ₱{$payableAmount}.00 is payable on the day of your appointment."
                    ]
                );
            } catch (Exception $e) {
                echo "Error sending SMS notification: " . $e->getMessage();
            }
        }

        $_SESSION['status'] = "success";
        header('Location: ../View/PageStaff/StaffMassSched.php');
        exit();
    } else {
        echo "Error updating status for mass wedding. Please try again.";
    }
} else {
    echo "Error inserting mass appointment. Please try again.";
}
    }else if($defuctomfill_id){
 // Capture form data

 $payableAmount = isset($_POST['eventTitle']) ? $_POST['eventTitle'] : null;
 
 $citizen_id = isset($_SESSION['citizen_id']) ? $_SESSION['citizen_id'] : null;
 
 // Check for missing required form data
 if (!$payableAmount || !$defuctomfill_id ) {
     die('Error: Missing required form data.');
 }

 // Initialize Staff object with connection
 $appointment = new Staff($conn);
 
 // Insert appointment without $newScheduleId
 $result = $appointment->insertfAppointment($defuctomfill_id, $payableAmount);

 if ($result) {
     // Approve the funeral appointment
     $result = $appointment->approveFuneral($defuctomfill_id);
     
     // Get contact information for the funeral
     $contactInfo = $appointment->getFuneralContactInfoAndTitles($defuctomfill_id);

     if ($contactInfo) {
         // Email details
         $email = $contactInfo['email'];
         $citizen_name = $contactInfo['fullname']; 
         $title = $contactInfo['event_name']; 
         $phone = $contactInfo['phone']; 
         try {
             // Set up PHPMailer
             $mail = new PHPMailer(true);
             $mail->isSMTP();
             $mail->Host = "smtp.gmail.com";
             $mail->SMTPAuth = true;
             $mail->Username = "argaoparishchurch@gmail.com";
             $mail->Password = "xomoabhlnrlzenur";
             $mail->SMTPSecure = 'tls';
             $mail->Port = 587;
 
             // Email settings
             $mail->setFrom('argaoparishchurch@gmail.com');
             $mail->addAddress($email);
             $mail->addEmbeddedImage('signature.png', 'signature_img');
             $mail->addEmbeddedImage('logo.jpg', 'background_img');
             $mail->isHTML(true);
             $mail->Subject = "Appointment Schedule Confirmation";
             $mail->Body = "
             <div style='width: 100%; max-width: 400px; margin: auto; background: url(cid:background_img) no-repeat center center; background-size: cover; padding: 20px;'>
                 <div style='background: rgba(255, 255, 255, 0.8); padding: 20px;width:100%;height:auto;'>
                     Dear {$citizen_name},<br><br>Your appointment schedule for '{$title}' has been confirmed. ₱{$payableAmount}.00<br>Please make sure to pay the said amount in the church office on the day of your appointment.<br><br>Thank you.<br>
                     <img src='cid:signature_img' style='width: 200px; height: auto;'>
                 </div>
             </div>";
         
             if ($mail->send()) {
                 echo "Email notification sent successfully.";
             } else {
                 echo "Error sending email notification: " . $mail->ErrorInfo;
             }
         } catch (Exception $e) {
             echo "Error sending email notification: " . $e->getMessage();
         }
         try {
            $sid = 'AC7ef9e279eebfda753ed9f67ae1a77710';  // Your Twilio Account SID
            $token = '40464522e94c2cdb6296d701bbe36a51';  // Your Twilio Auth Token
            $twilio = new Twilio\Rest\Client($sid, $token);
        
            $message = $twilio->messages->create(
                $phone, // The recipient's phone number
                [
                    'from' => '+17082776875', // Your Twilio phone number
                    'body' => "Dear {$citizen_name}, your appointment for '{$title}' has been confirmed. ₱{$payableAmount}.00 is payable on the day of your appointment."
                ]
            );
        
          
        
            if ($message->sid) {
                echo "SMS notification sent successfully.";
            } else {
                echo "Error sending SMS notification.";
            }
        } catch (Exception $e) {
            echo "Error sending SMS notification: " . $e->getMessage();
        }

        $_SESSION['status'] = "success";
         header('Location: ../View/PageStaff/StaffSoloSched.php');
         exit();
     } else {
         echo "No contact information found for the given defuctom fill ID.";
     }
 } else {
     echo "Error updating status. Please try again.";
 }
}else if($requestform_ids){
    // Capture form data
   
    $payableAmount = isset($_POST['eventTitle']) ? $_POST['eventTitle'] : null;
    
    $citizen_id = isset($_SESSION['citizen_id']) ? $_SESSION['citizen_id'] : null;
    
    // Check for missing required form data
    if (!$payableAmount || !$requestform_ids ) {
        die('Error: Missing required form data.');
    }
   
    // Initialize Staff object with connection
    $appointment = new Staff($conn);
    
    // Insert appointment without $newScheduleId
    $result = $appointment->insertrAppointment($requestform_ids, $payableAmount);
   
    if ($result) {
        // Approve the funeral appointment
        $result = $appointment->approverequestform($requestform_ids);
        
        // Get contact information for the funeral
        $contactInfo = $appointment->getRequestContactInfoAndTitles($requestform_ids);
   
       
            // Email details
            $email = $contactInfo['email'];
            $citizen_name = $contactInfo['fullname']; 
            $title = $contactInfo['req_category']; 
            $phone = $contactInfo['phone']; 
            try {
                // Set up PHPMailer
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host = "smtp.gmail.com";
                $mail->SMTPAuth = true;
                $mail->Username = "argaoparishchurch@gmail.com";
                $mail->Password = "xomoabhlnrlzenur";
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;
    
                // Email settings
                $mail->setFrom('argaoparishchurch@gmail.com');
                $mail->addAddress($email);
                $mail->addEmbeddedImage('signature.png', 'signature_img');
                $mail->addEmbeddedImage('logo.jpg', 'background_img');
                $mail->isHTML(true);
                $mail->Subject = "Appointment Schedule Confirmation";
                $mail->Body = "
                <div style='width: 100%; max-width: 400px; margin: auto; background: url(cid:background_img) no-repeat center center; background-size: cover; padding: 20px;'>
                    <div style='background: rgba(255, 255, 255, 0.8); padding: 20px;width:100%;height:auto;'>
                        Dear {$citizen_name},<br><br>Your appointment schedule for '{$title}' has been confirmed. ₱{$payableAmount}.00<br>Please make sure to pay the said amount in the church office in order to Approve the RequestForm.<br><br>Thank you.<br>
                        <img src='cid:signature_img' style='width: 200px; height: auto;'>
                    </div>
                </div>";
            
                if ($mail->send()) {
                    echo "Email notification sent successfully.";
                } else {
                    echo "Error sending email notification: " . $mail->ErrorInfo;
                }
            } catch (Exception $e) {
                echo "Error sending email notification: " . $e->getMessage();
            } 
            try {
                $sid = 'AC7ef9e279eebfda753ed9f67ae1a77710';  // Your Twilio Account SID
                $token = '40464522e94c2cdb6296d701bbe36a51';  // Your Twilio Auth Token
                $twilio = new Twilio\Rest\Client($sid, $token);
            
                $message = $twilio->messages->create(
                    $phone, // The recipient's phone number
                    [
                        'from' => '+17082776875', // Your Twilio phone number
                        'body' => "Dear {$citizen_name}, your appointment for '{$title}' has been confirmed. ₱{$payableAmount}.00 is payable on the day of your appointment."
                    ]
                );
            
              
            
                if ($message->sid) {
                    echo "SMS notification sent successfully.";
                } else {
                    echo "Error sending SMS notification.";
                }
            } catch (Exception $e) {
                echo "Error sending SMS notification: " . $e->getMessage();
            }
   
            $_SESSION['status'] = "success";
            header('Location: ../View/PageStaff/StaffRequestSchedule.php');
            exit();
       
    } else {
        echo "Error updating status. Please try again.";
    }
   }
   else if ($confirmationfill_id) {
    // Handle confirmation
    $payableAmount = $_POST['eventTitle'] ?? null;

    $appointment = new Staff($conn);
    $result = $appointment->insertcAppointment($confirmationfill_id, $payableAmount);
    $result = $appointment->approveConfirmation($confirmationfill_id);

    // Retrieve contact info and skip if email or phone is empty
    $contactInfo = $appointment->getContactInfoAndTitless($confirmationfill_id);

    if (!empty($contactInfo['email']) && !empty($contactInfo['phone'])) {
        $email = $contactInfo['email'];
        $citizen_name = $contactInfo['fullname'];
        $title = $contactInfo['event_name'];
        $phone = $contactInfo['phone'];
        $seminar_date = (new DateTime($contactInfo['seminar_date']))->format('F j, Y');
        $seminar_start_time = (new DateTime($contactInfo['seminar_start_time']))->format('g:i A');
        $seminar_end_time = (new DateTime($contactInfo['seminar_end_time']))->format('g:i A');


        // Send Email Notification
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = "smtp.gmail.com";
            $mail->SMTPAuth = true;
            $mail->Username = "argaoparishchurch@gmail.com";
            $mail->Password = "xomoabhlnrlzenur";
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('argaoparishchurch@gmail.com');
            $mail->addAddress($email);
            $mail->addEmbeddedImage('signature.png', 'signature_img');
            $mail->addEmbeddedImage('logo.jpg', 'background_img');
            $mail->isHTML(true);
            $mail->Subject = "Appointment Schedule Confirmation";
            $mail->Body = "
            <div style='width: 100%; max-width: 400px; margin: auto; background: url(cid:background_img) no-repeat center center; background-size: cover; padding: 20px;'>
            <div style='background: rgba(255, 255, 255, 0.8); padding: 20px; width:100%; height:auto;'>
                Dear {$citizen_name},<br><br>
                We are pleased to confirm your appointment for the event titled '<strong>{$title}</strong>'.<br><br>
                <strong>Details of Your Seminar Schedule:</strong><br>
                <ul>
                    <li><strong>Date:</strong> {$seminar_date}</li>
                    <li><strong>Time:</strong> {$seminar_start_time} to {$seminar_end_time}</li>
                    <li><strong>Amount Due:</strong> ₱{$payableAmount}.00</li>
                </ul>
                Please ensure to complete the payment at the church office on or before the day of your appointment or your Seminar.<br><br>
                We look forward to your participation and thank you for choosing Argao Parish Church.<br><br>
                Best regards,<br>
                <img src='cid:signature_img' style='width: 200px; height: auto;'>
            </div>
        </div>";

            $mail->send();
        } catch (Exception $e) {
            echo "Error sending email notification: " . $e->getMessage();
        }

        // Send SMS Notification
        try {
            $sid = 'AC7ef9e279eebfda753ed9f67ae1a77710';  // Your Twilio Account SID
            $token = '40464522e94c2cdb6296d701bbe36a51';  // Your Twilio Auth Token
            $twilio = new Twilio\Rest\Client($sid, $token);

            $message = $twilio->messages->create(
                $phone, // The recipient's phone number
                [
                    'from' => '+17082776875', // Your Twilio phone number
                    'body' => "Dear {$citizen_name}, your appointment for '{$title}' on {$seminar_date} from {$seminar_start_time} to {$seminar_end_time} has been confirmed. ₱{$payableAmount}.00 is payable on the day of your appointment."
                         ]
            );
        } catch (Exception $e) {
            echo "Error sending SMS notification: " . $e->getMessage();
        }
    }

    $_SESSION['status'] = "success";
    header('Location: ../View/PageStaff/StaffMassSched.php');
    exit();
}
else if($walkinconfirmationfill_id ){
    

   $payableAmount = null;



    $payableAmount = $_POST['eventTitle']; 

    $appointment = new Staff($conn);
    $result = $appointment->insertcAppointment($walkinconfirmationfill_id, $payableAmount);
    $result = $appointment->approveConfirmation($walkinconfirmationfill_id);
    if ($result) {
        $contactInfo = $appointment->getContactInfoAndTitles($confirmationfill_id);

      
            $email = $contactInfo['email'];
            $citizen_name = $contactInfo['fullname']; 
            $title = $contactInfo['event_name']; 
            $phone = $contactInfo['phone']; 
            try {
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host = "smtp.gmail.com";
                $mail->SMTPAuth = true;
                $mail->Username = "argaoparishchurch@gmail.com";
                $mail->Password = "xomoabhlnrlzenur";
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;
    
                $mail->setFrom('argaoparishchurch@gmail.com');
                $mail->addAddress($email);
                $mail->addEmbeddedImage('signature.png', 'signature_img');
                $mail->addEmbeddedImage('logo.jpg', 'background_img');
                $mail->isHTML(true);
                $mail->Subject = "Appointment Schedule Confirmation";
                $mail->Body = "z
                <div style='width: 100%; max-width: 400px; margin: auto; background: url(cid:background_img) no-repeat center center; background-size: cover; padding: 20px;'>
                    <div style='background: rgba(255, 255, 255, 0.8); padding: 20px;width:100%;height:auto;'>
                        Dear {$citizen_name},<br><br>Your appointment schedule for '{$title}' has been confirmed. ₱{$payableAmount}.00<br>Please make sure to pay the said amount in the church office on the day of your appointment.<br><br>Thank you.<br>
                        <img src='cid:signature_img' style='width: 200px; height: auto;'>
                    </div>
                </div>";
            
                if ($mail->send()) {
                    echo "Email notification sent successfully.";
                } else {
                    echo "Error sending email notification: " . $mail->ErrorInfo;
                }
            } catch (Exception $e) {
                echo "Error sending email notification: " . $e->getMessage();
            }
            try {
                $sid = 'AC7ef9e279eebfda753ed9f67ae1a77710';  // Your Twilio Account SID
                $token = '40464522e94c2cdb6296d701bbe36a51';  // Your Twilio Auth Token
                $twilio = new Twilio\Rest\Client($sid, $token);
            
                $message = $twilio->messages->create(
                    $phone, // The recipient's phone number
                    [
                        'from' => '+17082776875', // Your Twilio phone number
                        'body' => "Dear {$citizen_name}, your appointment for '{$title}' has been confirmed. ₱{$payableAmount}.00 is payable on the day of your appointment."
                    ]
                );
            
              
            
                if ($message->sid) {
                    echo "SMS notification sent successfully.";
                } else {
                    echo "Error sending SMS notification.";
                }
            } catch (Exception $e) {
                echo "Error sending SMS notification: " . $e->getMessage();
            }

            $_SESSION['status'] = "success";
            header('Location: ../View/PageStaff/StaffSoloSched.php');
            exit();
        
    } else {
        echo "Error updating status. Please try again.";
    }

}else if ($requestform_id) {
    $date = $_POST['date'] ?? '';
    $startTime = $_POST['start_time'] ?? '';
    $endTime = $_POST['end_time'] ?? '';
    
    // Function to validate military time format (HH:MM)
    function isValidMilitaryTime($time) {
        return preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $time);
    }
    
    // Validate start and end times
    if (!isValidMilitaryTime($startTime)) {
        // Handle invalid start time
        echo "Invalid start time format. Please use HH:MM (24-hour format).";
    }
    
    if (!isValidMilitaryTime($endTime)) {
        // Handle invalid end time
        echo "Invalid end time format. Please use HH:MM (24-hour format).";
    }
    
    // Additional logic to process the date and times...
    
    $datetofollowup = $_POST['datetofollowup'] ?? '';
    $priestId = $_POST['eventType'] ?? null;
    // Collecting the names and address
    $fullname = trim(implode(' ', [$_POST['firstname'] ?? '', $_POST['middlename'] ?? '', $_POST['lastname'] ?? '']));
    $fullnames = trim(implode(' ', [$_POST['firstnames'] ?? '', $_POST['middlenames'] ?? '', $_POST['lastnames'] ?? '']));
    $chapel = $_POST['chapel'] ?? '';
    $address = $_POST['address'] ?? ''; 
    $cpnumber = $_POST['cpnumber'] ?? '';
    $selectrequest = $_POST['selectrequest'] ?? '';
    $role = 'Walkin';
    $event_location = 'Inside';
    // Insert schedule and request form
    $scheduleId = $Citizen->insertSchedule(null, $date, $startTime, $endTime);
    $requestinside = $Citizen->insertoutsideRequestFormFill($scheduleId,$priestId, $selectrequest, $fullname, $datetofollowup, $address, $cpnumber, $fullnames, $chapel,$role,$event_location);
    
    // Insert appointment
   
    $payableAmount = $_POST['pay_amount'] ?? null;
    $appointmentResult = $Citizen->insertrequestAppointment($requestinside, $payableAmount);

    // Check if the insertions were successful
    if ($scheduleId && $appointmentResult) {
        $_SESSION['status'] = "success";
        header('Location: ../View/PageStaff/StaffDashboard.php');
        exit();
    } else {

    }
}
else if ($outsiderequestform_id) {
 
    $date = $_POST['date'] ?? '';
    $startTime = $_POST['start_time'] ?? '';
    $endTime = $_POST['end_time'] ?? '';
    
    // Function to validate military time format (HH:MM)
    function isValidMilitaryTime($time) {
        return preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $time);
    }
    
    // Validate start and end times
    if (!isValidMilitaryTime($startTime)) {
        // Handle invalid start time
        echo "Invalid start time format. Please use HH:MM (24-hour format).";
    }
    
    if (!isValidMilitaryTime($endTime)) {
        // Handle invalid end time
        echo "Invalid end time format. Please use HH:MM (24-hour format).";
    }
    
    // Additional logic to process the date and times...
    
    $datetofollowup = $_POST['datetofollowup'] ?? '';
    $priestId = $_POST['eventType'] ?? null;
    // Collecting the names and address
    $fullname = trim(implode(' ', [$_POST['firstname'] ?? '', $_POST['middlename'] ?? '', $_POST['lastname'] ?? '']));
    $fullnames = trim(implode(' ', [$_POST['firstnames'] ?? '', $_POST['middlenames'] ?? '', $_POST['lastnames'] ?? '']));
    $chapel = $_POST['chapel'] ?? '';
    $address = $_POST['address'] ?? ''; 
    $cpnumber = $_POST['cpnumber'] ?? '';
    $selectrequest = $_POST['selectrequest'] ?? '';
    $role = 'Walkin';
    $event_location = 'Outside';
    // Insert schedule and request form
    $scheduleId = $Citizen->insertSchedule(null, $date, $startTime, $endTime);
    $requestinside = $Citizen->insertoutsideRequestFormFill($scheduleId,$priestId, $selectrequest, $fullname, $datetofollowup, $address, $cpnumber, $fullnames, $chapel,$role,$event_location);
    
    // Insert appointment
   
    $payableAmount = $_POST['pay_amount'] ?? null;
    $appointmentResult = $Citizen->insertrequestAppointment($requestinside, $payableAmount);

    // Check if the insertions were successful
    if ($scheduleId && $appointmentResult) {
        $_SESSION['status'] = "success";
        header('Location: ../View/PageStaff/StaffDashboard.php');
        exit();
    } else {
    }
}
 
     
    else  {
        echo "Error: No valid form data provided.";
    }
    
}
ob_end_flush();

// Close the database connection
$conn->close();
?>
