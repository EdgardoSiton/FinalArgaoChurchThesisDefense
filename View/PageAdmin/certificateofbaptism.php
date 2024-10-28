<?php
require_once '../../Model/db_connection.php';
require_once '../../Model/admin_mod.php';

if (isset($_GET['id'])) {
    $baptismId = $_GET['id'];

    $admin = new Admin($conn);
    $baptismRecord = $admin->getBaptismRecordById($baptismId);

 
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
    <title>Certificate of Baptism</title>
</head>
<body>
    <?php if ($baptismRecord): ?>
    <div class="certificate">
        <div class="seal"></div>
        <div class="header">
            <img src="images/logo1.jpg" alt="Logo Left" class="logo">
            <div class="maintitle">ST. MICHAEL THE ARCHANGEL PARISH CHURCH <br> ARGAO, CHURCH</div>
            <img src="images/logo222.png" alt="Logo Right" class="logo">
        </div>
        <div class="content">
            <p style="font-weight: bold; text-align: center;">____________________________________________________________________________</p>
            <div class="title">CERTIFICATE OF BAPTISM</div>
            <div class="subtitle">This is to certify that</div>
            <div class="details">
                <p><strong>Name of Child:</strong> <span class="underline"><?php echo htmlspecialchars($baptismRecord['citizen_name']); ?></span></p>
                <p><strong>Date of Birth:</strong> <span class="underline"><?php echo htmlspecialchars($baptismRecord['birth_date']); ?></span></p>
                <p><strong>Place of Birth:</strong> <span class="underline"><?php echo htmlspecialchars($baptismRecord['place_of_birth']); ?></span></p>
                <p><strong>Name of Father:</strong> <span class="underline"><?php echo htmlspecialchars($baptismRecord['father_name']); ?></span></p>
                <p><strong>Maiden Name of Mother:</strong> <span class="underline"><?php echo htmlspecialchars($baptismRecord['mother_name']); ?></span></p>
                <p><strong>Address of Parents:</strong> <span class="underline"><?php echo htmlspecialchars($baptismRecord['address']); ?></span></p>
            </div>

            <div>
                <p class="subtitle">Was solemnly baptised according to the Rite of the Roman Catholic Church at the</p>
                <p><strong>Name of Parish:</strong> <span class="underline">St. Michael the Archangel Parish Church</span></p>
                <p><strong>Address of Parish:</strong> <span class="underline">Argao Cebu, Philippines</span></p>
                <p><strong>Date of Baptism:</strong> <span class="underline"><?php echo htmlspecialchars($baptismRecord['scheduleDate']); ?></span></p>
                <p><strong>Baptised by:</strong> <span class="underline">Priest of Argao Church</span></p>
                <p><strong>Sponsored by:</strong> <span class="underline"><?php echo htmlspecialchars($baptismRecord['godparent']); ?></span></p>
            </div>

            <div class="certification">
                <p class="subtitle">THIS IS TO CERTIFY that the above data are true and correct and agree with the Book of Baptism to which I refer in testimony hereof...</p>
            </div>

            <div class="signatures">
                <p>___________________________</p>
                <p class="bold">PARISH PRIEST</p>
                <p>By: ___________________________</p>
                <p class="bold">PARISH STAFF</p>
            </div>
        </div>
    </div>

    <div class="print-button">
        <button onclick="window.print()">Print Certificate</button>
    </div>
    <?php else: ?>
        <p>Record not found.</p>
    <?php endif; ?>
</body>
</html>
<script>
 document.addEventListener('DOMContentLoaded', function() {
    var currentDate = new Date();
    var day = currentDate.getDate();
    var monthIndex = currentDate.getMonth();
    var year = currentDate.getFullYear(); // Get current year
    
    // Determine day suffix (st, nd, rd, or th)
    var daySuffix;
    if (day >= 11 && day <= 13) {
        daySuffix = "th";
    } else {
        switch (day % 10) {
            case 1:  daySuffix = "st"; break;
            case 2:  daySuffix = "nd"; break;
            case 3:  daySuffix = "rd"; break;
            default: daySuffix = "th";
        }
    }
    
    // Array of month names
    var monthNames = [
        "January", "February", "March",
        "April", "May", "June", "July",
        "August", "September", "October",
        "November", "December"
    ];

    // Update HTML content with current date values
    document.getElementById('dayOfMonth').textContent = day;
    document.getElementById('daySuffix').textContent = daySuffix;
    document.getElementById('month').textContent = monthNames[monthIndex];
    document.getElementById('year').textContent = year; // Update year
});

</script>
<style>
body {
            font-family: Arial, sans-serif; 
            text-align: center;
            background-color: #f5f5dc;
            margin: 0; /* Remove default margin */
        }
        .underlines {
    border-bottom: 1px solid #000;
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
            text-align: left;
            padding-left: 30px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo {
            width: 100px;
            height: 100px;
            object-fit: cover; /* Ensure the image fits within the circle */
        }
        .maintitle {
            font-size: 20px;
            text-align: center;
        }

        .title {
            font-size: 28px;
            margin-bottom: 20px;
            margin-top: 20px;
            font-weight: bold;
        }

        .subtitle {
            padding-left: 30px;
            margin-top: 30px;
            text-align: left;
            font-size: 1.2em;
            margin-bottom: 30px;
        }

        .details, .baptism-details, .certification {
            font-size: 14px;
            text-align: left;
            margin-top: 30px;
        }
        

    .details p, .baptism-details p, .certification p {
        margin: 10px 0;
    }

    .underline {
        display: inline-block;
        border-bottom: 1px solid #000;
        width: 600px; /* Adjust width as needed */
        float: right;
    }

    .bold {
        font-weight: bold;
        text-align: center;
    }

    .signatures {
        margin-top: 40px;
        text-align: left;
    }

    .signatures p {
        text-align: center;
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

    .underline {
    display: inline-block;
    border-bottom: 1px solid #000;
    width: 100%; /* Adjust width as needed */
    margin-top: 5px;
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
