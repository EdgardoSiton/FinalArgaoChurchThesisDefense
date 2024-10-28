<?php
require_once __DIR__ . '/../Model/db_connection.php';
require_once __DIR__ . '/../Model/priest_mod.php';
require_once '../Model/login_mod.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['appointmentId']) && isset($_POST['appointmentType'])) {
    $appointmentId = intval($_POST['appointmentId']);
    $appointmentType = $_POST['appointmentType'];
    $action = $_POST['action'];  // Get the action (approve or decline)

    $priest = new Priest($conn); 

    if ($action === 'approve') {
        // Call approveAppointment method
        if ($priest->approveAppointment($appointmentId, $appointmentType)) {
            echo 'success'; // Send success response for approval
        } else {
            echo 'Error approving the appointment.'; // Error message for approval
        }
    } elseif ($action === 'decline') {
        // Check if reason for decline is provided
        if (isset($_POST['declineReason']) && !empty($_POST['declineReason'])) {
            $declineReason = $_POST['declineReason']; // Capture the reason

            // Call declineAppointment method with reason
            if ($priest->declineAppointment($appointmentId, $appointmentType, $declineReason)) {
                echo 'success'; // Send success response for decline
            } else {
                echo 'Error declining the appointment.'; // Error message for decline
            }
        } else {
            echo 'Decline reason is required.'; // Handle case where reason is not provided
        }
    } else {
        echo 'Invalid action.'; // Handle unknown actions
    }
} else {
    echo 'Invalid request.'; // Handle invalid requests
}



?>
