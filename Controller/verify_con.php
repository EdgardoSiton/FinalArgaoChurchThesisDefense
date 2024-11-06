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
        // Check if email is set in the session
        if (isset($_SESSION['otp_email'])) {
            $email = $_SESSION['otp_email']; // Use the correct session variable
            if ($user->regenerateAndSendOTPs($email)) {
                $_SESSION['successMessage'] = "A new OTP has been sent to your email.";
            } else {
                $errorMessage = "Failed to send a new OTP. Please try again.";
            }
        } else {
            $errorMessage = "Email not found in session. Please log in again.";
        }
    }
    
    // Handle OTP verification
    if (isset($_POST['otp'])) {
        $inputOtp = $_POST['otp'];

        if ($user->verifyOTPs($inputOtp)) {
            // OTP verified successfully, now prompt for a new password
            $_SESSION['otp_verified'] = true; // Mark OTP as verified
            header('Location: ../View/PageLanding/changepassword.php'); // Redirect to change password page
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
header('Location: ../View/PageLanding/otp_verification.php'); // Path to your OTP view file
exit();
