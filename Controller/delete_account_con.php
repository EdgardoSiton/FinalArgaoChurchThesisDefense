<?php
session_start();
require_once __DIR__ . '/../Model/db_connection.php';
require_once __DIR__ . '/../Model/login_mod.php';

// Instantiate User class
$user = new User($conn);

// Check if the user is logged in
if (isset($_SESSION['user_email'])) {
    $email = $_SESSION['user_email'];

    // Call the deleteAccount method
    $deleteSuccess = $user->deleteAccount($email);

    // Clear session data
    session_unset();
    session_destroy();

    // Optionally, redirect or provide feedback
    // header('Location: login.php'); // Redirect to login page after deletion
}
