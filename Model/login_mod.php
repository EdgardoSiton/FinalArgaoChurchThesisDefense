<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Twilio\Rest\Client;
require_once  __DIR__ . '/../Model/db_connection.php';
require_once  __DIR__ . '/../Model/staff_mod.php'; // Ensure this uses require_once
require_once __DIR__ . '/../Controller/phpmailer/src/PHPMailer.php'; // Ensure this uses require_once
require_once __DIR__ . '/../Controller/twilio-php-main/src/Twilio/autoload.php';
require_once  __DIR__ . '/../vendor/autoload.php';
class User {
    
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }
    public function archiveCitizen($citizen_id, $status) {
        // Use the status passed to either archive or unarchive
        $sql = "UPDATE citizen SET r_status = ? WHERE citizend_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('si', $status, $citizen_id);  // 's' for status, 'i' for citizen_id
        
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }
    
    
    
    public function getPriestAccount($statusFilter = '') {
        // Modify the SQL query to filter by r_status dynamically
        $sql = "SELECT `citizend_id`, `fullname`, `email`, `gender`, `phone`, `c_date_birth`, `age`, `address`, `valid_id`, `user_type`, `r_status`, `c_current_time`
                FROM `citizen` 
                WHERE `user_type` = 'Priest'";
        
        // Add condition based on the status filter
        if ($statusFilter === 'Unactive') {
            $sql .= " AND `r_status` = 'Unactive'";
        } else {
            $sql .= " AND `r_status` = 'Active'";
        }
        
        $sql .= " ORDER BY `c_current_time` ASC";
        
        // Prepare the statement
        $stmt = $this->conn->prepare($sql);
        
        if (!$stmt) {
            die('Prepare failed: ' . $this->conn->error);
        }
        
        // Execute the statement
        if (!$stmt->execute()) {
            die('Execute failed: ' . $stmt->error);
        }
        
        // Get the result set
        $result = $stmt->get_result();
        
        // Fetch all results as an associative array
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        
        // Close the statement
        $stmt->close();
        
        // Return the result
        return $data;
    }
    public function getStaffAccount($statusFilter = '') {
        // Modify the SQL query to filter by r_status dynamically
        $sql = "SELECT `citizend_id`, `fullname`, `email`, `gender`, `phone`, `c_date_birth`, `age`, `address`, `valid_id`, `user_type`, `r_status`, `c_current_time`
                FROM `citizen` 
                WHERE `user_type` = 'Staff'";
        
        // Add condition based on the status filter
        if ($statusFilter === 'Unactive') {
            $sql .= " AND `r_status` = 'Unactive'";
        } else {
            $sql .= " AND `r_status` = 'Active'";
        }
        
        $sql .= " ORDER BY `c_current_time` ASC";
        
        // Prepare the statement
        $stmt = $this->conn->prepare($sql);
        
        if (!$stmt) {
            die('Prepare failed: ' . $this->conn->error);
        }
        
        // Execute the statement
        if (!$stmt->execute()) {
            die('Execute failed: ' . $stmt->error);
        }
        
        // Get the result set
        $result = $stmt->get_result();
        
        // Fetch all results as an associative array
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        
        // Close the statement
        $stmt->close();
        
        // Return the result
        return $data;
    }
    
    public function getAccount() {
        // Prepare the SQL query with ORDER BY clause for descending order
        $sql = "SELECT `citizend_id`, `fullname`, `email`, `gender`, `phone`, `c_date_birth`, `age`, `address`, `valid_id`, `user_type`, `r_status`, `c_current_time`
                FROM `citizen` 
                WHERE `user_type` = 'Citizen' 
                AND `r_status` = 'Pending'
                ORDER BY `c_current_time` ASC";
    
        // Prepare the statement
        $stmt = $this->conn->prepare($sql);
    
        if (!$stmt) {
            die('Prepare failed: ' . $this->conn->error);
        }
    
        // Execute the statement
        if (!$stmt->execute()) {
            die('Execute failed: ' . $stmt->error);
        }
    
        // Get the result set
        $result = $stmt->get_result();
    
        // Fetch all results as an associative array
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    
        // Close the statement
        $stmt->close();
    
        // Return the result
        return $data;
    }
    public function getCitizenDetails($citizendId) {
        // Prepare the SQL query
        $sql = "SELECT `citizend_id`, `fullname`, `email`, `gender`, `phone`, `c_date_birth`, `age`, `address`, `valid_id`, `user_type`, `r_status`, `c_current_time`
                FROM `citizen` 
                WHERE `citizend_id` = ?";
    
        // Prepare the statement
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            die('Prepare failed: ' . $this->conn->error);
        }
    
        // Bind the parameter
        $stmt->bind_param('i', $citizendId);
        
        // Execute the statement
        if (!$stmt->execute()) {
            die('Execute failed: ' . $stmt->error);
        }
    
        // Get the result set
        $result = $stmt->get_result();
    
        // Fetch the details as an associative array
        $data = $result->fetch_assoc();
    
        // Close the statement
        $stmt->close();
    
        // Return the result
        return $data;
    }
    public function sendSms($toPhoneNumber, $messageBody) {
        // Your Twilio credentials (replace with your actual credentials)
        $accountSid = 'AC7ef9e279eebfda753ed9f67ae1a77710';
        $authToken = '40464522e94c2cdb6296d701bbe36a51';
        $twilioPhoneNumber = '+17082776875';
    
        try {
            // Initialize the Twilio client
            $client = new Client($accountSid, $authToken);
    
            // Send the SMS
            $client->messages->create(
                $toPhoneNumber, // Destination phone number
                [
                    'from' => $twilioPhoneNumber, // Twilio phone number
                    'body' => $messageBody // SMS body
                ]
            );
    
            echo "SMS sent successfully to {$toPhoneNumber}.";
    
        } catch (Exception $e) {
            // Log error and display failure message
            error_log("Failed to send SMS: " . $e->getMessage());
            echo "Error sending SMS: " . $e->getMessage();
        }
    }
    
    private function sendEmail($email, $citizen_name, $subject, $body) {
        
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
            $mail->addEmbeddedImage('../Controller/signature.png', 'signature_img');
            $mail->addEmbeddedImage('../Controller/logo.jpg', 'background_img');
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = "<div style='width: 100%; max-width: 400px; margin: auto; background: url(cid:background_img) no-repeat center center; background-size: cover; padding: 20px;'>
                           <div style='background: rgba(255, 255, 255, 0.8); padding: 20px;width:100%;height:auto;'>
                               {$body}
                               <img src='cid:signature_img' style='width: 200px; height: auto;'>
                           </div>
                       </div>";
    
            if (!$mail->send()) {
                error_log("Email failed: " . $mail->ErrorInfo); // Log error
                echo "Error sending email notification: " . $mail->ErrorInfo;
            } else {
                echo "Email notification sent successfully.";
            }
        } catch (Exception $e) {
            error_log("Error sending email notification: " . $e->getMessage()); // Log error
            echo "Error sending email notification: " . $e->getMessage();
        }
    }
    
    public function approveCitizen($citizenId) {
        $sql = "UPDATE citizen SET r_status = 'Approved' WHERE citizend_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $citizenId);
    
        if ($stmt->execute()) {
            // Fetch citizen details for sending email
            $contactInfo = $this->getCitizenDetails($citizenId);
            if ($contactInfo) {
                $this->sendEmail($contactInfo['email'], $contactInfo['fullname'], "Your account has been approved.", 
                    "Dear {$contactInfo['fullname']},<br><br>Your account has been successfully approved.<br>Thank you for your patience.<br>");
                
                // Send SMS notification with personalized message
                $smsMessage = "Dear {$contactInfo['fullname']}, Your account has been successfully approved. Thank you for your patience.";
                $this->sendSms($contactInfo['phone'], $smsMessage);
            }
            
            return true;
        } else {
            return false;
        }
    }
    
    public function deleteCitizen($citizenId) {
        // Fetch citizen details before deletion
        $contactInfo = $this->getCitizenDetails($citizenId);
        if ($contactInfo) {
            $email = $contactInfo['email'];
            $fullname = $contactInfo['fullname'];
            $phone = $contactInfo['phone']; // Assuming you have the phone number stored as 'phone'
        } else {
            return false; // Citizen not found
        }
    
        $sql = "DELETE FROM citizen WHERE citizend_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $citizenId);
    
        if ($stmt->execute()) {
            // Send email after successful deletion
            $this->sendEmail($email, $fullname, "Your account has been deleted.", 
                "Dear {$fullname},<br><br>Your account has been deleted.<br>If you have any questions, please contact us.<br>");
            
            // Send SMS after successful deletion
            $smsMessage = "Dear {$fullname}, Your account has been deleted. If you have any questions, please contact us.";
            $this->sendSms($phone, $smsMessage);
    
            return true;
        } else {
            return false;
        }
    }
    
    

    

    public function login($email, $password) {
        // Sanitize input
        $email = mysqli_real_escape_string($this->conn, $email);
    
        // Use prepared statements to prevent SQL injection
        $stmt = $this->conn->prepare("SELECT * FROM citizen WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result === false) {
            error_log("Database query error: " . mysqli_error($this->conn));
            return 'An error occurred. Please try again later.';
        }
    
        if (mysqli_num_rows($result) > 0) {
            // User found, verify password
            $user = $result->fetch_assoc();
            $hashedPassword = $user['password'];
    
            if (password_verify($password, $hashedPassword)) {
                // Check if the password needs to be rehashed
                if (password_needs_rehash($hashedPassword, PASSWORD_DEFAULT)) {
                    // Rehash the password and update it in the database
                    $newHashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $updateStmt = $this->conn->prepare("UPDATE citizen SET password = ? WHERE email = ?");
                    $updateStmt->bind_param("ss", $newHashedPassword, $email);
                    $updateResult = $updateStmt->execute();
    
                    if ($updateResult === false) {
                        error_log("Failed to update password hash: " . mysqli_error($this->conn));
                        return 'An error occurred. Please try again later.';
                    }
                }
    
                // Password is correct, return true
                return true;
            } else {
                // Password is incorrect
                error_log("Incorrect password for email: $email");
                return 'Incorrect credentials. Please try again.';
            }
        } else {
            // Email not found
            error_log("Email not found: $email");
            return 'Incorrect credentials. Please try again.';
        }
    }

    
    public function getUserInfo($email) {
        $email = mysqli_real_escape_string($this->conn, $email);
        $query = "SELECT citizend_id, user_type, r_status, fullname,gender FROM citizen WHERE email = '$email'";
        $result = mysqli_query($this->conn, $query);
        
        // Check if query was successful and fetch user info
        if ($result && mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            return [
                'citizend_id' => $user['citizend_id'] ?? null,
                'user_type' => $user['user_type'] ?? null,
                'r_status' => $user['r_status'] ?? null,
                'fullname' => $user['fullname'] ?? null,
                'gender' => $user['gender'] ?? null,
            ];
        } else {
            return null;
        }
    }
    
    public function registerUser($data) {
        // Automatically set user_type to 'Citizen'
        $data['user_type'] = 'Citizen';
    
        // Combine first name, last name, and middle name into a single fullname field
        $data['fullname'] = trim($data['first_name'] . ' ' . $data['middle_name'] . ' ' . $data['last_name']);
    
        // Ensure c_date_birth is set correctly before proceeding
        if (isset($data['year']) && isset($data['month']) && isset($data['day'])) {
            // Validate date components
            $year = intval($data['year']);
            $month = intval($data['month']);
            $day = intval($data['day']);
            
            if (checkdate($month, $day, $year)) {
                $data['c_date_birth'] = "$year-$month-$day";
            } else {
                return "Invalid date of birth";
            }
        } else {
            return "Date of birth is incomplete";
        }
    
        // Check for valid ID image upload
        if (isset($_FILES['valid_id'])) {
            $validIdError = $_FILES['valid_id']['error'];
            $validIdTmpName = $_FILES['valid_id']['tmp_name'];
            $validIdName = $_FILES['valid_id']['name'];
            $validIdUploadPath = 'img/' . $validIdName;
    
            // Proceed with file upload if no error
            if ($validIdError === 0) {
                $imageFileType = strtolower(pathinfo($validIdName, PATHINFO_EXTENSION));
                $allowedFileTypes = ['jpg', 'jpeg', 'png', 'gif'];
    
                if (in_array($imageFileType, $allowedFileTypes)) {
                    if (move_uploaded_file($validIdTmpName, $validIdUploadPath)) {
                        $data['valid_id'] = $validIdUploadPath; // Save the image path in data
                    } else {
                        return "Failed to upload valid ID image";
                    }
                } else {
                    return "Only JPG, JPEG, PNG, and GIF files are allowed for valid ID";
                }
            } else {
                return "Error uploading valid ID image";
            }
        } else {
            return "No valid ID image uploaded";
        }
    
        // Sanitize input data
        $sanitizedData = $this->sanitizeData($data);
    
        // Calculate age
        $sanitizedData['age'] = $this->calculateAge($sanitizedData['c_date_birth']);
    
        // Hash the password
        $sanitizedData['password'] = password_hash($sanitizedData['password'], PASSWORD_DEFAULT);
    
        // Prepare SQL query with placeholders
        $query = "INSERT INTO citizen (user_type, fullname, address, gender, c_date_birth, age, email, valid_id, phone, password, r_status) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')";
    
        // Use prepared statements to prevent SQL injection
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(
            'ssssssssss',
            $sanitizedData['user_type'],
            $sanitizedData['fullname'],
            $sanitizedData['address'],
            $sanitizedData['gender'],
            $sanitizedData['c_date_birth'],
            $sanitizedData['age'],
            $sanitizedData['email'],
            $sanitizedData['valid_id'],
            $sanitizedData['phone'],
            $sanitizedData['password']
        );
    
        if ($stmt->execute()) {
            // Insert notification record
            $notificationQuery = "INSERT INTO notifications (type, message) VALUES ('user_registration', ?)";
            $notificationStmt = $this->conn->prepare($notificationQuery);
            $notificationStmt->bind_param('s', $sanitizedData['fullname']);
    
            if ($notificationStmt->execute()) {
                return "Registration successful and notification sent";
            } else {
                return "Registration successful, but failed to send notification";
            }
        } else {
            return "Registration failed";
        }
    }
    
    public function registerUsers($data) {
        // Automatically set user_type to 'Priest'
        $data['user_type'] = 'Priest';
        $data['r_status'] = 'Active';
    
        // Combine first name, last name, and middle name into a single fullname field
        $data['fullname'] = trim($data['first_name'] . ' ' . $data['middle_name'] . ' ' . $data['last_name']);
    
      
            $year = intval($data['year']);
            $month = intval($data['month']);
            $day = intval($data['day']);
            
         
                $data['c_date_birth'] = "$year-$month-$day";
         
               
       
    
        // Sanitize input data
        $sanitizedData = $this->sanitizeData($data);
    
        // Calculate age
        $sanitizedData['age'] = $this->calculateAge($sanitizedData['c_date_birth']);
    
        // Hash the password
        $sanitizedData['password'] = password_hash($sanitizedData['password'], PASSWORD_DEFAULT);
    
        // Prepare SQL query with placeholders
        $query = "INSERT INTO citizen (user_type,r_status, fullname, address, gender, c_date_birth, age, email, phone, password) 
                  VALUES (?, ?, ?, ?,?, ?, ?, ?, ?, ?)";
    
        // Use prepared statements to prevent SQL injection
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(
            'ssssssssss',
            $sanitizedData['user_type'],
            $sanitizedData['r_status'],
            $sanitizedData['fullname'],
            $sanitizedData['address'],
            $sanitizedData['gender'],
            $sanitizedData['c_date_birth'],
            $sanitizedData['age'],
            $sanitizedData['email'],
            $sanitizedData['phone'],
            $sanitizedData['password']
        );
    
        if ($stmt->execute()) {
            // Insert notification record
            $notificationQuery = "INSERT INTO notifications (type, message) VALUES ('user_registration', ?)";
            $notificationStmt = $this->conn->prepare($notificationQuery);
            $notificationStmt->bind_param('s', $sanitizedData['fullname']);
    
            if ($notificationStmt->execute()) {
                return "Registration successful and notification sent";
            } else {
                return "Registration successful, but failed to send notification";
            }
        } else {
            return "Registration failed";
        }
    }
 
    public function registerUserss($data) {
        // Automatically set user_type to 'Priest'
        $data['user_type'] = 'Staff';
        $data['r_status'] = 'Active';
    
        // Combine first name, last name, and middle name into a single fullname field
        $data['fullname'] = trim($data['first_name'] . ' ' . $data['middle_name'] . ' ' . $data['last_name']);
    
      
            $year = intval($data['year']);
            $month = intval($data['month']);
            $day = intval($data['day']);
            
         
                $data['c_date_birth'] = "$year-$month-$day";
         
               
       
    
        // Sanitize input data
        $sanitizedData = $this->sanitizeData($data);
    
        // Calculate age
        $sanitizedData['age'] = $this->calculateAge($sanitizedData['c_date_birth']);
    
        // Hash the password
        $sanitizedData['password'] = password_hash($sanitizedData['password'], PASSWORD_DEFAULT);
    
        // Prepare SQL query with placeholders
        $query = "INSERT INTO citizen (user_type,r_status, fullname, address, gender, c_date_birth, age, email, phone, password) 
                  VALUES (?, ?, ?, ?,?, ?, ?, ?, ?, ?)";
    
        // Use prepared statements to prevent SQL injection
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(
            'ssssssssss',
            $sanitizedData['user_type'],
            $sanitizedData['r_status'],
            $sanitizedData['fullname'],
            $sanitizedData['address'],
            $sanitizedData['gender'],
            $sanitizedData['c_date_birth'],
            $sanitizedData['age'],
            $sanitizedData['email'],
            $sanitizedData['phone'],
            $sanitizedData['password']
        );
    
        if ($stmt->execute()) {
            // Insert notification record
            $notificationQuery = "INSERT INTO notifications (type, message) VALUES ('user_registration', ?)";
            $notificationStmt = $this->conn->prepare($notificationQuery);
            $notificationStmt->bind_param('s', $sanitizedData['fullname']);
    
            if ($notificationStmt->execute()) {
                return "Registration successful and notification sent";
            } else {
                return "Registration successful, but failed to send notification";
            }
        } else {
            return "Registration failed";
        }
    }
    
    
    private function calculateAge($birthday) {
        $birthDate = new DateTime($birthday);
        $today = new DateTime('today');
        return $birthDate->diff($today)->y;
    }

    private function sanitizeData($data) {
        foreach ($data as $key => $value) {
            $data[$key] = mysqli_real_escape_string($this->conn, $value);
        }
        return $data;
    }

    private function checkEmailExists($email) {
        $query = "SELECT * FROM citizen WHERE email = '$email'";
        $result = mysqli_query($this->conn, $query);
        return mysqli_num_rows($result) > 0;
    }
    public function checkEmailExistss($email) {
        $sql = "SELECT `citizend_id`, `fullname`, `email` FROM `citizen` WHERE `email` = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return true; // Email exists
        } else {
            return false; // Email does not exist
        }
    }
}
?>
