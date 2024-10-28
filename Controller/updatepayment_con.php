<?php
require_once '../Model/db_connection.php';
require_once '../Model/staff_mod.php';
require_once __DIR__ . '/../Model/citizen_mod.php';
require_once __DIR__ . '/../Model/login_mod.php';

$staff = new Staff($conn);
$Citizen = new Citizen($conn);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $baptismfill_id = isset($_POST['baptismfill_id']) ? $_POST['baptismfill_id'] : null;
    $baptismfillId = isset($_POST['baptismfillId']) ? intval($_POST['baptismfillId']) : null;
    $confirmationfill_ids = isset($_POST['confirmationfill_ids']) ? $_POST['confirmationfill_ids'] : null;
    $confirmationfill_id = isset($_POST['confirmationfill_id']) ? $_POST['confirmationfill_id'] : null;
    $weddingffill_id = isset($_POST['marriagefill_id']) ? $_POST['marriagefill_id'] : null;
    $weddingffill_ids = isset($_POST['weddingffill_ids']) ? $_POST['weddingffill_ids'] : null;
    $defuctom_id = isset($_POST['defuctomfill_id']) ? $_POST['defuctomfill_id'] : null;
    $defuctom_ids = isset($_POST['defuctom_ids']) ? $_POST['defuctom_ids'] : null;
    $massbaptismfillId = isset($_POST['baptism_id']) ? $_POST['baptism_id'] : null;
    $massweddingffill_id = isset($_POST['massmarriage_id']) ? $_POST['massmarriage_id'] : null;
    $bpriest_id = isset($_POST['bpriest_id']) ? $_POST['bpriest_id'] : null;
    $mpriest_id = isset($_POST['mpriest_id']) ? $_POST['mpriest_id'] : null;
    $fpriest_id = isset($_POST['fpriest_id']) ? $_POST['fpriest_id'] : null;
    $rpriest_id = isset($_POST['rpriest_id']) ? $_POST['rpriest_id'] : null;
    $cpriest_id = isset($_POST['cpriest_id']) ? $_POST['cpriest_id'] : null;
    $requestform_ids = isset($_POST['request_ids']) ? $_POST['request_ids'] : null;


    if ($baptismfill_id) {
        $decline = $staff->deleteBaptism($baptismfill_id);
        echo $decline; // Output the result for client-side handling
    } elseif ($confirmationfill_id) {
        $decline = $staff->deleteConfirmation($confirmationfill_id);
        echo $decline;
    } elseif ($weddingffill_id) {
        $declines = $staff->deleteWedding($weddingffill_id);
        echo $declines;
    } elseif ($defuctom_id) {
        $decline = $staff->deleteDefuctom($defuctom_id);
        echo $decline;
    } elseif ($massbaptismfillId) {
        $declines = $staff->deleteMassBaptism($massbaptismfillId);
        echo $declines;
    } elseif ($massweddingffill_id) {
        $declines = $staff->deleteMassWedding($massweddingffill_id);
        echo $declines;
    }else if($baptismfillId){
        $approve = $staff->approveBaptism($baptismfillId);  // Call the method to approve the request

    if ($approve) {
        // Approval successful
        $_SESSION['status'] = 'success';  // Set session to indicate success
        echo 'success';  // Send success response back to AJAX
    } else {
        // Approval failed
        echo 'error';  // Send error response back to AJAX
    }
    }else if ($confirmationfill_ids){
        $decline = $staff-> approveConfirmation($confirmationfill_ids);
        echo $decline;
    }else if($weddingffill_ids ){
        $decline = $staff-> approveWedding($weddingffill_ids);
        echo $decline;
    }else if($defuctom_ids){
        $decline = $staff-> approveFuneral($defuctom_ids);
        echo $decline; 
    }else if($requestform_ids){
        $decline = $staff-> approverequestform($requestform_ids);
        echo $decline; 
    }


    // Handle appointment deletions
    if (isset($_POST['appsched_ids'])) {
        $appsched_ids = $_POST['appsched_ids'];
        if ($staff->deleteAppointments($appsched_ids)) {
            header('Location: ../View/PageStaff/StaffAppointment.php');
            exit;
        } else {
            echo "Error deleting appointments.";
        }
    } elseif (isset($_POST['mappsched_ids'])) {
        $appsched_ids = $_POST['mappsched_ids'];
        if ($staff->deleteAppointments($appsched_ids)) {
            header('Location: ../View/PageStaff/StaffMassSeminar.php');
            exit;
        } else {
            echo "Error deleting appointments.";
        }
    } elseif (isset($_POST['rappsched_ids'])) {
        $appsched_ids = $_POST['rappsched_ids'];
        if ($staff->deleteAppointments($appsched_ids)) {
            header('Location: ../View/PageStaff/StaffRequestForm.php');
            exit;
        } else {
            echo "Error deleting appointments.";
        }
    }

    // Handle payment status updates
    if (isset($_POST['p_status']) && isset($_POST['appsched_id'])) {
        $appsched_id = $_POST['appsched_id'];
        $p_status = $_POST['p_status'];

        if ($staff->updatePaymentStatus($appsched_id, $p_status)) {
            header('Location: ../View/PageStaff/StaffAppointment.php');
            exit;
        } else {
            echo "Error updating payment status.";
        }
    } elseif (isset($_POST['mp_status']) && isset($_POST['mappsched_id'])) {
        $appsched_id = $_POST['mappsched_id'];
        $p_status = $_POST['mp_status'];

        if ($staff->updatePaymentStatus($appsched_id, $p_status)) {
            header('Location: ../View/PageStaff/StaffMassSeminar.php');
            exit;
        } else {
            echo "Error updating payment status.";
        }
    } elseif (isset($_POST['rp_status']) && isset($_POST['rappsched_id'])) {
        $appsched_id = $_POST['rappsched_id'];
        $p_status = $_POST['rp_status'];

        if ($staff->updatePaymentStatus($appsched_id, $p_status)) {
            header('Location: ../View/PageStaff/StaffRequestForm.php');
            exit;
        } else {
            echo "Error updating payment status.";
        }
    }

    // Handle event status updates
    if (isset($_POST['c_status']) && isset($_POST['cappsched_id'])) {
        $cappsched_id = $_POST['cappsched_id'];
        $c_status = $_POST['c_status'];

        if ($staff->updateEventStatus($cappsched_id, $c_status)) {
            header('Location: ../View/PageStaff/StaffAppointment.php');
            exit;
        } else {
            echo "Error updating event status.";
        }
    } elseif (isset($_POST['mc_status']) && isset($_POST['mcappsched_id'])) {
        $cappsched_id = $_POST['mcappsched_id'];
        $c_status = $_POST['mc_status'];

        if ($staff->updateEventStatus($cappsched_id, $c_status)) {
            header('Location: ../View/PageStaff/StaffMassSeminar.php');
            exit;
        } else {
            echo "Error updating event status.";
        }
    } elseif (isset($_POST['rc_status']) && isset($_POST['rcappsched_id'])) {
        $cappsched_id = $_POST['rcappsched_id'];
        $c_status = $_POST['rc_status'];

        if ($staff->updateEventStatus($cappsched_id, $c_status)) {
            header('Location: ../View/PageStaff/StaffRequestForm.php');
            exit;
        } else {
            echo "Error updating event status.";
        }
    }

    // Handle priest update for baptism or marriage
    if ($bpriest_id) {
        $priestId = $_POST['eventType'] ?? null;

        $result = $Citizen->updateBaptismStatus($bpriest_id, $priestId);
        if ($result) {
            header('Location: ../View/PageStaff/FillBaptismForm.php?id=' . $bpriest_id);
            exit;
        } else {
            echo "Failed to update baptism status.";
        }
    } elseif ($mpriest_id) {
        $priestId = $_POST['eventType'] ?? null;

        $result = $Citizen->updatemarriageStatus($mpriest_id, $priestId);
        if ($result) {
            header('Location: ../View/PageStaff/FillWeddingForm.php?id=' . $mpriest_id);
            exit;
        } else {
            echo "Failed to update marriage status.";
        }
    }   elseif ($fpriest_id) {
        $priestId = $_POST['eventType'] ?? null;

        $result = $Citizen->updatdefuctomStatus($fpriest_id, $priestId);
        if ($result) {
            header('Location: ../View/PageStaff/FillFuneralForm.php?id=' . $fpriest_id);
            exit;
        } else {
            echo "Failed to update marriage status.";
        }
    } elseif ($cpriest_id) {
        $priestId = $_POST['eventType'] ?? null;

        $result = $Citizen->updateconfirmationStatus($cpriest_id, $priestId);
        if ($result) {
            header('Location: ../View/PageStaff/FillConfirmationForm.php?id=' . $cpriest_id);
            exit;
        } else {
            echo "Failed to update marriage status.";
        }
    }elseif ($rpriest_id) {
        $priestId = $_POST['eventType'] ?? null;

        $result = $Citizen->updaterequestformStatus($rpriest_id, $priestId);
        if ($result) {
            header('Location: ../View/PageStaff/FillInsideRequestForm.php?req_id=' . $rpriest_id);
            exit;   
        } else {
            echo "Failed to update marriage status.";
        }
    }  else {
        echo "Invalid priest ID.";
    }
   
}
?>
