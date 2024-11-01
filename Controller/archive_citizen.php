<?php
require_once '../Model/db_connection.php';
require_once '../Model/login_mod.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (isset($_POST['citizen_id']) && isset($_POST['action'])) {
        $citizen_id = intval($_POST['citizen_id']);
        $action = $_POST['action'];  // This could be 'archive' or 'unarchive'
        $citizen = new User($conn);
        
        if ($action === 'archive') {
            $_SESSION['status'] = "success";
            $result = $citizen->archiveCitizen($citizen_id, 'Unactive');

        } elseif ($action === 'unarchive') {
            $_SESSION['status'] = "success";
            $result = $citizen->archiveCitizen($citizen_id, 'Active');
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid action.']);
            exit;
        }

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Citizen ' . $action . 'd successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to ' . $action . ' citizen.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid citizen ID or action.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
