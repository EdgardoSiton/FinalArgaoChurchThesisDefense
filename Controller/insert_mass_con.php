<?php
require_once __DIR__ .'/../Model/db_connection.php';
require_once __DIR__ .'/../Model/login_mod.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Assuming you have a valid database connection in $conn
    $staff = new Staff($conn);
    $result = $staff->addMassSchedule($_POST['cal_date'], $_POST['startTime'], $_POST['endTime'], $_POST['eventType']);
    echo $result; // Display the result message
}

?>