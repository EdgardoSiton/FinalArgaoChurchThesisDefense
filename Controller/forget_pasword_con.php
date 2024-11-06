<?php
require_once __DIR__ . '/../Model/db_connection.php';
require_once __DIR__ . '/../Model/login_mod.php';

session_start(); // Start the session

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['forgot_password_form'])) {
   
        $email = $_POST['email'];
    
        $user = new User($conn);
    
        // Check if the email exists in the database
        $userInfo = $user->getUserInfo($email);
        if ($userInfo) {
            // Email exists, proceed with password reset process
            // Here you can generate a reset token and send an email, etc.
            // For example:
            // $resetToken = bin2hex(random_bytes(16));
            // $user->storeResetToken($email, $resetToken); // Store the reset token in the database
            // Send reset email (implement email sending logic here)

            $_SESSION['message'] = 'A password reset link has been sent to your email address.';
            header('Location: ../../View/PageLanding/forgot_password_success.php'); // Redirect to a success page
            exit;
        } else {
            // Email not found
            $_SESSION['error'] = 'This email address is not registered.';
            header('Location: ../View/PageLanding/forgotstep1.php'); // Redirect back to the forgot password page
            exit;
        }
    }
}
?>
