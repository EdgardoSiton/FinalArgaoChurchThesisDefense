<?php 
session_start();
if (!isset($_SESSION['otp_verified']) || $_SESSION['otp_verified'] !== true) {
    header("Location: otp_verification.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Change Password</title>
    <style>
        /* Optional: Style to make error messages more visible */
        #password_error_message, #confirm_password_error_message {
            color: red;
            font-weight: bold;
        }
    </style>
    <script>
function validatePassword() {
    const newPassword = document.getElementById('new_password').value.trim();
    const confirmPassword = document.getElementById('confirm_password').value.trim();
    const passwordErrorMessage = document.getElementById('password_error_message');
    const confirmPasswordErrorMessage = document.getElementById('confirm_password_error_message');

    // Updated regex pattern to include dot as a valid special character
    const passwordRegex = /^(?=.*[A-Z])(?=.*\d)(?=.*[!@#\$%\^&\*\.])[A-Za-z\d!@#\$%\^&\*\.]{8,}$/;

    // Clear previous error messages
    passwordErrorMessage.textContent = "";
    confirmPasswordErrorMessage.textContent = "";

    let valid = true; // Assume valid unless proven otherwise

    // Validate new password
    if (newPassword === '') {
        passwordErrorMessage.textContent = "Please enter a password.";
        valid = false; // Set valid to false
    } else if (!passwordRegex.test(newPassword)) {
        passwordErrorMessage.textContent = "Password must be at least 8 characters long, with an uppercase letter, a number, and a special character.";
        valid = false; // Set valid to false
    }

    // Validate confirm password
    if (confirmPassword === '') {
        confirmPasswordErrorMessage.textContent = "Please enter the password confirmation.";
        valid = false; // Set valid to false
    } else if (newPassword !== confirmPassword) {
        confirmPasswordErrorMessage.textContent = "The confirmation password must match the new password.";
        valid = false; // Set valid to false
    }

    return valid; // Allow form submission only if valid is still true
}


    </script>
</head>
<body>
    <h2>Change Your Password</h2>

    <form action="../../Controller/process_change_password.php" method="POST" onsubmit="return validatePassword()">
        <label for="new_password">New Password:</label>
        <input type="password" id="new_password" name="new_password">
        <p id="password_error_message"></p> <!-- Specific error message for password -->

        <label for="confirm_password">Confirm Password:</label>
        <input type="password" id="confirm_password" name="confirm_password">
        
        <p id="confirm_password_error_message"></p> <!-- Specific error message for confirm password -->

        <button type="submit">Change Password</button>
    </form>
</body>
</html>
