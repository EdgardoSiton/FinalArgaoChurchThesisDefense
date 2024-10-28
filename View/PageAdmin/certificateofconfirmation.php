<?php
require_once '../../Model/db_connection.php';
require_once '../../Model/admin_mod.php';

if (isset($_GET['id'])) {
    $confirmationId = $_GET['id'];  // Use `id` parameter as the confirmation ID
    $admin = new Admin($conn);
    $confirmationRecord = $admin->getConfirmationRecordById($confirmationId);

    if (!$confirmationRecord) {
        echo "No confirmation record found for this ID.";
        exit;
    }
} else {
    echo "No ID provided.";
    exit;
}
session_start();
$nme = $_SESSION['fullname'];
$regId = $_SESSION['citizend_id'];
$loggedInUserEmail = isset($_SESSION['email']) ? $_SESSION['email'] : null;
$r_status = isset($_SESSION['user_type']) ? $_SESSION['user_type'] : null;

if (!$loggedInUserEmail) {
  header("Location: ../../index.php");
  exit();
}

// Redirect staff users to the staff page, not the citizen page
if ($r_status === "Staff") {
  header("Location: ../PageStaff/StaffDashboard.php"); // Change to your staff page
  exit();
}
if ($r_status === "Citizen") {
  header("Location: ../PageCitizen/CitizenPage.php"); // Change to your staff page
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
    <title>Certificate of Confirmation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 0;
            background-color: #f5f5dc;
        }
        .certificate {
            background: white;
            max-width: 800px;
            margin: auto;
            padding: 20px;
            position: relative;
        }
        .certificate h1 {
            font-size: 30px;
            margin-bottom: 0.5em;
        }
        .certificate p {
            text-align: center;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo {
            width: 100px;
            height: 100px;
            object-fit: cover;
        }
        .maintitle {
            font-size: 20px;
            text-align: center;
        }
        .underline, .longunderline, .small-underline {
            display: inline-block;
            border-bottom: 1px solid #000;
        }
        .underline {
            width: 600px;
            margin: 0 auto; /* Centering */
        }
        .small-underline {
            width: 200px;
            margin: 0 auto; /* Centering */
        }
        .longunderline {
            width: 690px;
            margin: 0 auto; /* Centering */
        }
        .underclass {
            font-style: italic;
        }
        .seal {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 800px;
            height: 790px;
            background-image: url('images/logo1.jpg');
            background-size: contain;
            background-repeat: no-repeat;
            opacity: 0.1;
            z-index: 1;
        }
        .print-button {
            margin-top: 20px;
            text-align: right;
        }
        .print-button button {
            font-size: 16px;
            padding: 10px 20px;
            cursor: pointer;
            background-color: #102c57;
            color: white;
            border: none;
            border-radius: 5px;
            box-shadow: 0 4px #999;
        }
        .print-button button:hover {
            background-color: #102c57;
        }
        .print-button button:active {
            background-color: #102c57;
            box-shadow: 0 2px #666;
            transform: translateY(2px);
        }
        @media print {
            .print-button {
                display: none;
            }
            @page {
                margin: 0;
            }
        }
    </style>
</head>
<body>
<div class="certificate">
        <div class="seal"></div>
        <div class="header">
            <img src="images/logo1.jpg" alt="Logo Left" class="logo">
            <div class="maintitle">ST. MICHAEL THE ARCHANGEL PARISH CHURCH <br> ARGAO, CHURCH</div>
            <img src="images/logo222.png" alt="Logo Right" class="logo">
        </div>
        <p style="font-weight: bold; text-align: center;">____________________________________________________________________________</p>
        <h1>CERTIFICATE OF CONFIRMATION</h1>
        <p class="maincaption" style="padding-top: 20px">This is to certify</p>
        <p class="spacing">that <span class="longunderline"><?php echo htmlspecialchars($confirmationRecord['fullname']); ?></span></p>
        <p class="underclass">(CONFIRMATION NAME)</p>
        <p class="spacing">son/daughter of <span class="underline"><?php echo htmlspecialchars($confirmationRecord['father_fullname']); ?></span></p>
        <p class="underclass">(FATHER'S NAME)</p>
        <p class="spacing">and <span class="longunderline"><?php echo htmlspecialchars($confirmationRecord['mother_fullname']); ?></span></p>
        <p class="underclass">(MOTHER'S NAME)</p>
        <p class="spacing">was Baptized <span class="underline"><?php echo htmlspecialchars(date('F j, Y', strtotime($confirmationRecord['date_of_baptism']))); ?> in <?php echo htmlspecialchars($confirmationRecord['church_address']); ?></span></p>
        <p class="underclass">(DATE, PLACE)</p>
        <p class="spacing">at <span class="longunderline"><?php echo htmlspecialchars($confirmationRecord['name_of_church']); ?> </span></p>
        <p class="underclass">(CHURCH, CITY, STATE)</p>
        <p class="spacing">received the, <p style="font-size: 25px; font-weight: bold;">SACRAMENT OF CONFIRMATION</p> on <span class="longunderline"><?php echo htmlspecialchars(date('F j, Y', strtotime($confirmationRecord['confirmation_date']))); ?></span></p>
        <p class="underclass">(MONTH, DAY, YEAR)</p>
        <p class="spacing">in the church of Archdiocesan Shrine of San Miguel Arcangel,</p>
        <p class="spacing">at <span class="longunderline">Argao Cebu, Philippines</span></p>
        <p class="spacing" style="padding-top: 30px;">Dated: <span class="small-underline"><?php echo date('Y-m-d'); ?></span></p>
        <p class="spacing">Issued by: <span class="small-underline">Staff</span></p>
    </div>
    <div class="print-button">
        <button onclick="window.print()">Print Certificate</button>
    </div>
</body>
</html>
