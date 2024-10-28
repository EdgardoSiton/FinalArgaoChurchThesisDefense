<?php
session_start();
require_once '../../Model/staff_mod.php';  // Your Staff class file
require_once '../../Model/db_connection.php';  // Your DB connection

// Initialize the Staff class with the database connection
$userManager = new Staff($conn);
$baptismReports = $userManager->generateBaptismReport();  // Call the method
$nme = $_SESSION['fullname'];
$regId = $_SESSION['citizend_id'];
$loggedInUserEmail = isset($_SESSION['email']) ? $_SESSION['email'] : null;
$r_status = isset($_SESSION['user_type']) ? $_SESSION['user_type'] : null;

if (!$loggedInUserEmail) {
  header("Location: ../../index.php");
  exit();
}

// Redirect staff users to the staff page, not the citizen page
if ($r_status === "Citizen") {
  header("Location: ../PageCitizen/CitizenPage.php"); // Change to your staff page
  exit();
}
if ($r_status === "Admin") {
  header("Location: ../PageAdmin/AdminDashboard.php"); // Change to your staff page
  exit();
}if ($r_status === "Priest") {
  header("Location: ../PagePriest/index.php"); // Change to your staff page
  exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Citizen Accounts Report</title>
    <script>
           function printReport() {
            window.print();
        }
    </script>
    <style>
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

    <!-- Formal Report Header -->
    <div class="report-header">
        <img src="../assets/img/mainlogo.jpg" alt="Church Logo"> <!-- Replace with your logo -->
        <h4>Argao Parish Church</h4>
        <p>Argao, Cebu</p>
        <p>0939-424-232</p>
        <p>argaoparishchurch@gmail.com</p>
    </div>

    <h2 style="text-align: center;">Seminar Report  </h2>

    <!-- Print Button -->
    <button class="button" onclick="printReport()">Print Report</button>

        <table class="table table-bordered">
            <thead>
                <tr>
                <th>Speaker</th>
                    <th>Seminar Date</th>
                    <th>Reference Number</th>
                    <th>Type</th>
                  
                    <th>Citizen Name</th>
                  
                 
                    <th>Payment Status</th>
                    <th>Payable Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($baptismReports)): ?>
                    <?php foreach ($baptismReports as $report): ?>
                        <tr>
                        <td><?= htmlspecialchars($report['speaker']) ?></td>
                        <td><?= htmlspecialchars($report['appointment_schedule_date']) ?></td>
                            <td><?= htmlspecialchars($report['ref_number']) ?></td>
                            <td><?= htmlspecialchars($report['type']) ?></td>
                   
                            <td><?= htmlspecialchars($report['fullnames']) ?></td>
                        
                           
                            <td><?= htmlspecialchars($report['p_status']) ?></td>
                            <td><?= htmlspecialchars($report['payable_amount']) ?></td>
                         
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8">No Baptism records found for this week.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <button class="button" onclick="history.back()">Back</button>
    </div>
</body>
</html>
