<?php
session_start();
require_once __DIR__ . '/../Model/db_connection.php';
require_once __DIR__ . '/../Model/login_mod.php';

// Check if user is allowed to change password
if (!isset($_SESSION['otp_verified']) || $_SESSION['otp_verified'] !== true) {
    header("Location: otp_verification.php");
    exit();
}

$errorMessage = '';

// Validate form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword = $_POST['new_password'];

    // Instantiate User class and update password
    $user = new User($conn);
    $citizend_id = $_SESSION['citizend_id'];

    if ($user->changePassword($citizend_id, $newPassword)) {
        // Password change successful
        $_SESSION['status'] = "successs";
        header("Location: ../index.php");
        exit();
    } else {
        $errorMessage = "Failed to update password. Please try again.";
    }
}

// If there was an error, store it in session and redirect back
if (!empty($errorMessage)) {
    $_SESSION['errorMessage'] = $errorMessage;
    header("Location: change_password.php");
    exit();
}
?>
