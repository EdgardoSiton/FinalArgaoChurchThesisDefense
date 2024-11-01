<?php
require_once __DIR__ . '/../Model/admin_mod.php';
require_once __DIR__ .'/../Model/db_connection.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_donation') {
    // Create Admin object
    $admin = new Admin($conn);

    // Get data from AJAX request
    $d_name = $_POST['d_name'];
    $amount = $_POST['amount'];
    $donated_on = $_POST['donated_on'];
    $description = $_POST['description'];

    // Call the addDonation method
    if ($admin->addDonation($d_name, $amount, $donated_on, $description)) {
        // Send JSON response for success
    
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to add donation.']);
    }
    exit; // Prevent further output
}
