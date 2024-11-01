<?php
session_start();
require_once __DIR__ . '/../Model/db_connection.php';
require_once __DIR__ . '/../Model/login_mod.php';

// Instantiate User class
$user = new User($conn);
$errorMessage = '';

// Handle new OTP request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['request_new_otp'])) {
        $newOtp = $user->regenerateAndSendOTP(); // Send the OTP
        $_SESSION['otp'] = $newOtp; // Store in session
        $errorMessage = "A new OTP has been sent to your email.";
    }

    // Handle OTP verification
    if (isset($_POST['otp'])) {
        $inputOtp = $_POST['otp'];
        $otpResult = $user->verifyOTP($inputOtp);

        // Check the result of OTP verification
        if ($otpResult === "OTP verified successfully! Your account is now active.") {
            $_SESSION['status'] = "success";
            header('Location: ../index.php');
            exit();
        } else {
            $errorMessage = "Invalid OTP. Please try again.";
        }
    }
}

// Store any error messages in the session to pass to the view
if (!empty($errorMessage)) {
    $_SESSION['errorMessage'] = $errorMessage;
}

// Redirect to the OTP view
header('Location: ../otp_view.php'); // Change this to the path of your OTP view file
exit();
