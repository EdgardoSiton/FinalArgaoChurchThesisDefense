<?php
require_once __DIR__ . '/../Model/db_connection.php';
require_once __DIR__ . '/../Model/login_mod.php';

session_start(); // Start the session

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['login_form'])) {
   
    $email = $_POST['email'];
    $password = $_POST['password'];


    $user = new User($conn);

    // Check if the email exists
    $userInfo = $user->getUserInfo($email);
    if ($userInfo) {
        // Validate password
        $loginResult = $user->login($email, $password);
        if ($loginResult === true) {
            $accountType = $userInfo['user_type'];
            $status = $userInfo['r_status'];
            $regId = $userInfo['citizend_id']; 
            $nme = $userInfo['fullname'];
            $gender = $userInfo['gender'];
            // Store user info in session
            $_SESSION['email'] = $email;
            $_SESSION['user_type'] = $accountType;
            $_SESSION['r_status'] = $status;
            $_SESSION['citizend_id'] = $regId; 
            $_SESSION['fullname'] = $nme;
            $_SESSION['gender'] = $gender;

            // Redirect based on the account type and status
            if ($status === 'Approved') {
                if ($accountType === 'Citizen') {
                    header('Location: ../../View/PageCitizen/CitizenPage.php');
                    exit;
                } else {
                    $_SESSION['login_error'] = 'Unknown account type';
                    header('Location: ../../View/PageLanding/signin.php');
                    exit;
                }
            } elseif ($accountType === 'Admin') {
                header('Location: ../../View/PageAdmin/AdminDashboard.php');
                exit;
            } elseif ($accountType === 'Staff') {
                if ($status === 'Active') {
                    header('Location: ../../View/PageStaff/StaffDashboard.php');
                    exit;
                } else {
                    $_SESSION['login_error'] = 'Please Contact to ArgaoParishChurch you have been Unactive';
                    header('Location: ../../View/PageLanding/signin.php');
                    exit;
                   
                }
               
          
            } elseif ($accountType === 'Priest') {
                if ($status === 'Active') {
                    header('Location: ../../View/PagePriest/index.php');
                    exit;
                } else {
                    $_SESSION['login_error'] = 'Please Contact to ArgaoParishChurch you have been Unactive';
                    header('Location: ../../View/PageLanding/signin.php');
                    exit;
                   
                }
             
            } else {
                // Account not approved yet
                $_SESSION['login_error'] = 'Waiting for approval by the management';
                header('Location: ../../View/PageLanding/signin.php');
                exit;
            }
        } else {
            // Password incorrect
            $_SESSION['login_error'] = 'Invalid credentials';
            header('Location: ../../View/PageLanding/signin.php');
            exit;
        }
    } else {
        // Email not found
        $_SESSION['login_error'] = 'Invalid credentials';
        header('Location: ../../View/PageLanding/signin.php');
        exit;
    }
} elseif (isset($_POST['signup_form'])) {
    $errors = [];
    // Assuming further validation here

    if (empty($errors)) {
        // Instantiate Registration class
        $registration = new User($conn);
        $registrationData = $_POST; // Perform further validation if necessary
        $registrationResult = $registration->registerUser($registrationData);

        // Check if registration was successful
        if ($registrationResult === "Registration successful and notification sent") {
            // Redirect to success page or login page
            $_SESSION['status'] = "success";
            header('Location: ../../index.php');
            exit();
        } else {
            // Display error message
            echo '<script>alert("' . $registrationResult . '");</script>';
        }
    } else {
        // Display error messages
        foreach ($errors as $error) {
            echo '<script>alert("' . $error . '");</script>';
        }
    }
}elseif (isset($_POST['signup_forms'])) {
    $errors = [];
    // Assuming further validation here

    if (empty($errors)) {
        // Instantiate Registration class
        $registration = new User($conn);
        $registrationData = $_POST; // Perform further validation if necessary
        $registrationResult = $registration->registerUsers($registrationData);

        // Check if registration was successful
        if ($registrationResult === "Registration successful and notification sent") {
            // Redirect to success page or login page
            $_SESSION['status'] = "success";
            header('Location: ../PageAdmin/AdminDashboard.php');
            exit();
        } else {
            // Display error message
            echo '<script>alert("' . $registrationResult . '");</script>';
        }
    } else {
        // Display error messages
        foreach ($errors as $error) {
            echo '<script>alert("' . $error . '");</script>';
        }
    }
}
elseif (isset($_POST['signup_formss'])) {
    $errors = [];
    // Assuming further validation here

    if (empty($errors)) {
        // Instantiate Registration class
        $registration = new User($conn);
        $registrationData = $_POST; // Perform further validation if necessary
        $registrationResult = $registration->registerUserss($registrationData);

        // Check if registration was successful
        if ($registrationResult === "Registration successful and notification sent") {
            // Redirect to success page or login page
            $_SESSION['status'] = "success";
            header('Location: ../PageAdmin/AdminDashboard.php');
            exit();
        } else {
            // Display error message
            echo '<script>alert("' . $registrationResult . '");</script>';
        }
    } else {
        // Display error messages
        foreach ($errors as $error) {
            echo '<script>alert("' . $error . '");</script>';
        }
    }
}

}
?>
