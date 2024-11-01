<?php
session_start();
require_once '../../Model/admin_mod.php';  // Your Staff class file
require_once '../../Model/db_connection.php';  // Your DB connection

// Initialize the Admin class with the database connection
$userManager = new Admin($conn);
$nme = $_SESSION['fullname'];
$regId = $_SESSION['citizend_id'];


// Fetch report type and date range from GET parameters
$reportType = isset($_GET['type']) ? $_GET['type'] : 'All'; // Default to 'All'
$dateRange = isset($_GET['days']) ? $_GET['days'] : null;

// Fetch reports using the provided parameters
$reportData = $userManager->generateAppointmentReport($reportType, $dateRange);


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Baptism Report</title>
    <script>
        function printReport() {
            window.print();
        }
    </script>
    <style>
        /* Your existing styles here */
        @media print {
            /* Styles for printing */
        }
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        /* Header styling */
        .report-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .report-header img {
            width: 80px;
            height: 80px;
        }
        .report-header h4 {
            margin: 0;
            padding: 0;
            font-size: 24px;
            color: #333;
        }
        .report-header p {
            margin: 5px 0;
            font-size: 16px;
            color: #555;
        }

        /* Button styling */
        .button {
            display: block;
            width: 150px;
            padding: 10px;
            margin: 20px auto;
            background-color: #4CAF50;
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
            cursor: pointer;
        }

        /* Table styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        @media print {
    .button {
        display: none;
    }
}
    </style>
</head>
<body>

<div class="report-header">
    <img src="../assets/img/mainlogo.jpg" alt="Church Logo">
    <h4>Argao Parish Church</h4>
    <p>Argao, Cebu</p>
    <p>0939-424-232</p>
    <p>argaoparishchurch@gmail.com</p>
</div>

<h2 style="text-align: center;"><?= htmlspecialchars($reportType); ?> Event Acknowledgement Report</h2>
<p style="text-align: center;">
    Date Range: 
    <?php 
$startDate = new DateTime(); // Current date
$endDate = new DateTime(); // Current date

if ($dateRange == 365) {  // Check for 1 year
    $startDate->modify('-1 year'); // Set start date to one year ago
} elseif ($dateRange == 30) {
    $startDate->modify('-1 month'); // 1 month ago
} elseif ($dateRange < 30) {
    $startDate->modify('-' . $dateRange . ' days'); // Subtract days for a custom range
} else {
    // For this week, no modification is needed; start from Monday
    $startDate->modify('last Monday');
    if ($startDate->format('w') == 0) { // If today is Sunday, adjust to last Monday
        $startDate->modify('-7 days');
    }
}

// Format the start and end dates with year
$startFormatted = $startDate->format('F j, Y');
$endFormatted = $endDate->format('F j, Y');

echo $startFormatted . ' to ' . $endFormatted;

    ?>
</p>
<button class="button" onclick="printReport()">Print Report</button>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>Full Name</th>
            <th>Event Name</th>
            <th>Roles</th>
            <th>Payable Amount</th>
    
        </tr>
    </thead>
    <tbody>
        <?php 
        $totalPayableAmount = 0; // Initialize total variable
        if (!empty($reportData)): ?>
            <?php foreach ($reportData as $report): ?>
                <tr>
                    <td><?= htmlspecialchars($report['fullnames']); ?></td>
                    <td><?= htmlspecialchars($report['Event_Name']); ?></td>
                    <td><?= htmlspecialchars($report['roles']); ?></td>
                    <td><?= htmlspecialchars($report['payable_amount']); ?></td>
           
                </tr>
                <?php 
                // Sum the payable amounts
                $totalPayableAmount += floatval($report['payable_amount']); 
                ?>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="4">
                    No <?= htmlspecialchars($reportType); ?> records found for the date range of <?= htmlspecialchars($dateRange ? $dateRange . ' days' : 'this week'); ?>.
                </td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<!-- Display the total payable amount -->
<?php if ($totalPayableAmount > 0): ?>
    <h3 style="text-align: right; margin-top: 20px;">Total Amount:₱<?= htmlspecialchars(number_format($totalPayableAmount, 2)); ?></h3>
<?php endif; ?>

<button class="button" onclick="history.back()">Back</button>

