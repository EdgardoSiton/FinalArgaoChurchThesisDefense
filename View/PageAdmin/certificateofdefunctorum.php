<?php
require_once '../../Model/db_connection.php';
require_once '../../Model/admin_mod.php';

if (isset($_GET['id'])) {
    $defunctorumId = $_GET['id'];

    $admin = new Admin($conn);
    $defunctorumRecord = $admin->getDefunctorumRecordById($defunctorumId);
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
    <title>Certificate of DEFUNCTORUM</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            background-color: #f5f5dc;
            margin: 0; /* Remove default margin */
        }
        .certificate {
            padding: 20px;
            background: white;
            max-width: 800px;
            margin: auto;
            position: relative;
        }
        .certificate h1 {
            font-size: 30px;
            margin-bottom: 0.5em;
        }
        .certificate p {
            padding-left: 30px;
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

        .underline {
            margin-top: 20px;  
            display: inline-block;
            border-bottom: 1px solid #000;
            width: 700px;
            float: right;
        }

        .small-underline {
            margin-top: 25px;  
            display: inline-block;
            border-bottom: 1px solid #000;
            width: 300px;
            float: right;
        }

        .spacing {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .bold {
            font-weight: bold;
            text-align: center;
        }

        .centered {
            text-align: center;
        }

        .pastor-signature {
            text-align: right;
            padding-right: 30px;
        }

        .seal {
            position: absolute;
            top: 60%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 800px; /* Adjust size of the seal */
            height: 800px;
            background-image: url('images/logo1.jpg'); /* Path to your logo */
            background-size: contain;
            background-repeat: no-repeat;
            opacity: 0.1; /* Adjust opacity as needed */
            z-index: 1; /* Ensure the seal appears above the white background */
        }

        .signatures {
            margin-top: 40px;
            text-align: left;
        }

        .signatures p {
            text-align: center;
        }

        .bold {
            font-weight: normal;
            text-align: center;
        }


          /*PRINT */

          .print-button {
            margin-top: 20px;
            text-align: right;
        }
        .print-button button {
            font-size: 16px;
            padding: 10px 20px;
            cursor: pointer;
            background-color: #102c57; 
            color: white; /* White text */
            border: none; /* Remove borders */
            border-radius: 5px; /* Rounded corners */
            box-shadow: 0 4px #999; /* Add shadow */
        }
        .print-button button:hover {
            background-color: #102c57; 
        }
        .print-button button:active {
            background-color: #102c57; 
            box-shadow: 0 2px #666; /* Reduce shadow */
            transform: translateY(2px); /* Move button 2px down on click */
        }

        @media print {
            .print-button {
                display: none; /* Hide the button when printing */
            }
        }

        @media print {
            @page {
                margin: 0; /* Remove default margin */
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
        <p style="font-weight: bold;">____________________________________________________________________________</p>
        <h1>CERTIFICATE OF DEFUNCTORUM</h1>
        <p style="padding-top: 20px">This is to certify</p>
        
        <div class="spacing">
            <p>That</p>
            <span class="underline"><?php echo htmlspecialchars($defunctorumRecord['fullname']); ?></span>
        </div>
        
        <div class="spacing">
            <p>born at</p>
            <span class="underline"><?php echo htmlspecialchars($defunctorumRecord['place_of_birth']); ?></span>
        </div>

        <div class="spacing">
            <p>on the</p>
            <span class="small-underline">
                <?php
                    $dateOfBirth = $defunctorumRecord['date_of_birth'];
                    echo htmlspecialchars(date('jS', strtotime($dateOfBirth)));
                ?>
            </span>
            <p>day of</p>
            <span class="small-underline">
                <?php
                    echo htmlspecialchars(date('F Y', strtotime($dateOfBirth)));
                ?>
            </span>
        </div>

        <div class="spacing">
            <p>died at</p>
            <span class="underline"><?php echo htmlspecialchars($defunctorumRecord['place_of_death']); ?></span>
        </div>

        <div class="spacing">
            <p>on the</p>
            <span class="small-underline">
                <?php
                    $dateOfDeath = $defunctorumRecord['date_of_death'];
                    echo htmlspecialchars(date('jS', strtotime($dateOfDeath)));
                ?>
            </span>
            <p>day of</p>
            <span class="small-underline">
                <?php
                    echo htmlspecialchars(date('F Y', strtotime($dateOfDeath)));
                ?>
            </span>
        </div>

        <p class="centered"><strong>and was buried with the rites of</strong></p>
        <p class="centered" style="font-size: 25px;"><strong>The Roman Catholic Church</strong></p>
        <p class="centered">in</p>

        <div class="spacing">
            <span class="underline">St. Michael the Archangel Parish Church</span>
        </div>

        <p class="centered">as it appears from the Death Register of this Church.</p>

        <div class="signatures">
            <p>Staff Officer</p>
            <span class="underline"></span>
            <p><strong>Rev.</strong></p>
            <p><?php echo date("F j, Y"); ?></p>
            <p>______________________________</p>
            <p><strong>Dated</strong></p>
        </div>
    </div>

    <div class="print-button">
        <button onclick="window.print()">Print Certificate</button>
    </div>
</body>
</html>