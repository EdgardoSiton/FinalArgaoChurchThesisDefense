<?php
session_start();
require_once '../Model/citizen_mod.php';
require_once '../Model/db_connection.php';

$citizen = new Citizen($conn);

$scheduleDate = $_POST['date'] ?? null;
$startTime = $_POST['startTime'] ?? null;
$endTime = $_POST['endTime'] ?? null;

if ($scheduleDate && $startTime && $endTime) {
    // Fetch available priests based on the provided time (already in 12-hour AM/PM format)
    $priests = $citizen->getAvailablePriests($scheduleDate, $startTime, $endTime);
    echo json_encode($priests);
} else {
    echo json_encode([]); // Return an empty array if no date or time is provided
}

?>
