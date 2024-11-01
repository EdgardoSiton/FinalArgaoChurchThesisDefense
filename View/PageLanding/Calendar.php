<?php
require_once '../../Controller/login_con.php';
require_once '../../Model/staff_mod.php';
header("Cache-Control: no-cache, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0"); // Proxies.
// Check if the user wants to log out
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    // Destroy all session data to log the user out
    session_unset();
    session_destroy();
    
    // Redirect to login page after logout
    header("Location: ../../index.php");
    exit();
}

// Check if the user is already logged in
if (isset($_SESSION['email']) && isset($_SESSION['user_type'])) {
    // Retrieve user status from session or query it from the database, if necessary
    $userType = $_SESSION['user_type'];
    $r_status = $_SESSION['r_status']; // Assumes r_status is stored in session when logging in
    
    // Redirect based on user type and r_status
    switch ($userType) {
        case "Staff":
            if ($r_status === "Active") {
                header("Location: ../PageStaff/StaffDashboard.php");
                exit();
            }
            break;
        case "Citizen":
            if ($r_status === "Approved") {
                header("Location: ../PageCitizen/CitizenPage.php");
                exit();
            }
            break;
            case "Priest":
                if ($r_status === "Active") {
                    header("Location: ../PagePriest/index.php");
                    exit();
                }
                break;
                case "Admin":
                      header("Location: ../PageAdmin/AdminDashboard.php");
                      exit();
                  
                  break;
    }
  }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="css/calendar.css">
</head>

<body>
    <div class="cal-container">
<div id="calendar"></div>
</div>
<script src="//cdnjs.cloudflare.com/ajax/libs/moment.js/2.5.1/moment.min.js"></script>
<script src="js/calendar.js"></script>
</body>
</html>