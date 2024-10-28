<?php
require_once __DIR__ . '/../Model/db_connection.php';
require_once __DIR__ . '/../Model/login_mod.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';

    // Create an instance of your User class
    $user = new User($conn); // Ensure $conn is properly defined

    // Check if the email exists
    if ($user->checkEmailExistss($email)) {
        echo 'exists';
    } else {
        echo 'available';
    }
}
?>
