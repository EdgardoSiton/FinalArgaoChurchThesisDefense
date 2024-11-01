<?php
session_start();
require_once '../Model/db_connection.php';
require_once '../Model/staff_mod.php';

$staff = new Staff($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $response = ['success' => false];

    if ($action === 'update') {
        $announcementId = $_POST['announcement_id'];
        $speakerAnn = $_POST['speaker_ann'];
        $title = $_POST['title'];
        $description = $_POST['description'];
        $capacity = $_POST['capacity'];
        $_SESSION['status'] = "successs";
        $success = $staff->updateAnnouncement($announcementId, $speakerAnn, $title, $description, $capacity);
        $response['success'] = $success;
        $response['error'] = $success ? '' : $conn->error;

    } elseif ($action === 'delete') {
        $announcementId = $_POST['announcement_id'];
        $_SESSION['status'] = "deleted";
        $success = $staff->deleteAnnouncement($announcementId);
        $response['success'] = $success;
        $response['error'] = $success ? '' : 'Deletion failed.';
    }

    echo json_encode($response);
    exit;
}
?>
