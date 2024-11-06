<?php 

require_once '../../Controller/login_con.php';
// Prevent browser from caching the login page
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0"); // Proxies.

// Check if the user wants to log out
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
        <meta charset="utf-8">
        <title>ARGAO CHURCH MANAGEMENT SYSTEM</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta content="" name="keywords" />
    <meta content="" name="description" />
    <link rel="icon" href="../assets/img/mainlogo.jpg" type="image/x-icon"
    />

        <!-- Google Web Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&family=Inter:slnt,wght@-10..0,100..900&display=swap" rel="stylesheet">

        <!-- Icon Font Stylesheet -->
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css"/>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

        <!-- Libraries Stylesheet -->
        <link rel="stylesheet" href="lib/animate/animate.min.css"/>
        <link href="lib/lightbox/css/lightbox.min.css" rel="stylesheet">
        <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">


        <!-- Customized Bootstrap Stylesheet -->
        <link href="css/bootstrap.min.css" rel="stylesheet">
        <link href="css/sigin.css" rel="stylesheet">

        <!-- Template Stylesheet -->
        <link href="css/style.css" rel="stylesheet">
        <style>
                   .error{
        color: red;
        font-size: 14px;
        margin-top: 5px;
        color: red;            /* Text color for error messages */
    font-size: 12px;      /* Font size for error messages */
    margin-top: 5px;      /* Space above the error message */
    display: block;        /* Ensures the error message is on a new line */
    line-height: 1.2;     /* Adjusts line height for better readability */
    }
             .error-message {
        color: red;
        font-size: 14px;
        margin-top: 5px;
        color: red;            /* Text color for error messages */
    font-size: 12px;      /* Font size for error messages */
    margin-top: 5px;      /* Space above the error message */
    display: block;        /* Ensures the error message is on a new line */
    line-height: 1.2;     /* Adjusts line height for better readability */
    }
    .input-error {
        border: 1px solid red;
    }
    .birthday-input {
    font-family: Arial, sans-serif;
    margin-bottom: 10px;
}

.birthday-input label {
    display: block;
    font-weight: bold;
    margin-bottom: 5px;
}

.birthday-selectors {
    display: flex;
    gap: 5px;
    width: 20px;
}


.birthday-selectors select {
    padding: 5px;
    border: 1px solid #0a58ca;
    border-radius: 5px;
    width: 100px;
    font-size: 14px;
    color: #555;
}

.birthday-selectors select:focus {
    outline: none;
    border-color: #0a58ca;
}


small {
    display: block;
    color: #555;
    margin-top: 5px;
}
     .float-left{
        width:900px;
     }
        .back-button {
float:right;       
margin-right:110px;  
margin-top:20px;  

            padding: 0.5rem 1rem;
            background-color: #0066a8;
            color: #fff;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            font-size: 1rem;
            transition: background-color 0.3s ease;
        }  .back-button:hover {
            background-color: #004a80;
        }
        .clearfix::after {
    content: "";
    display: table;
    clear: both;
}p{
    color:#3b3b3b; text-align: justify; text-justify: inter-word; font-size: 15px; line-height: 1.6; margin-top: 10px; margin-left: 10px;
}
.button1 {
  padding: 6px 24px;
  border: 2px solid #fff;
  background: transparent;
  border-radius: 6px;
  cursor: pointer;
}
.button1:active {
  transform: scale(0.98);
} .form_container h2 {
  font-size: 22px;
  color: #0b0217;
  text-align: center;
}
.input_box {
  position: relative;
  margin-top: 35px;
  width: 100%;
  height: 40px;
  display: flex;
position: relative;
gap:25px;
}
.input_box input,select {
  height: 100%;
  width: 100%;
  border: none;
  outline: none;
  padding: 0 30px;
  color: #333;
  transition: all 0.2s ease;
  border-bottom: 1.5px solid #aaaaaa;
}

.input_box input:focus {
  border-color: #7d2ae8;
}
.input_box i {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  font-size: 20px;
  color: #707070;
}
.input_box i.email,
.input_box i.password {
  left: 0;
}
.input_box input:focus ~ i.email,
.input_box input:focus ~ i.password {
  color: #7d2ae8;
}
.input_box i.pw_hide {
  right: 0;
  font-size: 18px;
  cursor: pointer;
}
.option_field {
  margin-top: 14px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  position: relative;
    
    width: 100%;
    height: 40px;
}
.form_container a {
  color: #7d2ae8;
  font-size: 12px;
}
.form_container a:hover {
  text-decoration: underline;
}
.checkbox {
  display: flex;
  column-gap: 8px;
  white-space: nowrap;
}
.checkbox input {
  accent-color: #7d2ae8;
}
.checkbox label {
  font-size: 12px;
  cursor: pointer;
  user-select: none;
  color: #0b0217;
}
.form_container .button {
  background: #7d2ae8;
  margin-top: 30px;
  width: 100%;
  padding: 10px 0;
  border-radius: 10px;
}
.login_signup {
  font-size: 12px;
  text-align: center;
  margin-top: 15px;
}
.option_field a{
  color: #7d2ae8;
  font-size: 12px;
}
.option_field a:hover {
  text-decoration: underline;
}
.input_group {
      /* display: flex; */
      flex-direction: column;
    /* position: relative; */
    /* margin-top: -21px; */
    width: 100%;
    height: 40px;
    /* display: flex; */
    position: relative;
}
  

  /* Label styling */
  .input_group label {
    font-size: 15px;
    font-weight: 500;
  }

  /* Input styling */
  .input_group input[type="date"],
  .input_group input[type="file"] {
    padding: 10px;
    font-size: 14px;
    height: 100%;
    width: 90%;
  border: none;
  outline: none;
  color: #333;
  transition: all 0.2s ease;
  border-bottom: 1.5px solid #aaaaaa;
  }
  @media (max-width: 950px) {
    .responsive-image {
        display: none;
    }
}

    </style>
    </head>

    <body>
 
    <div class="container-fluid nav-bar px-0 px-lg-4 py-lg-0">
      <div class="container">
      <?php require_once 'navbar.php'?>

      </div>
    </div>

      <div class="container1">
      <div class="forms-container">
        <div class="signin-signup">
        <form method="POST" action="" class="forgot-password-form">
          <input type="hidden" name="forgot_password_form" value="2">
          <h2 class="title">Forgot Password</h2>
          <h5> Please enter your email to receive the verification code</h5>
          <div class="error" id="login_error">
    <?php
    if (isset($_SESSION['login_error'])) {
        echo htmlspecialchars($_SESSION['login_error']);
        unset($_SESSION['login_error']); // Clear the message after displaying
    }
    ?>
</div>





            <div class="input_box">
              <input type="text" name="email" class="form-control" id="email" placeholder="name@example.com"/>
              <i class="uil uil-envelope-alt email"></i>
            </div>
          
            <div class="option_field">
          
            </div>
            <button type="submit" class="btn btn-primary py-3 w-100 mb-4">Sign In</button>
            <div class="login_signup">Don't have an account? <a href="signin.php"   id="sign-up-signbutton">Signin</a></div>
           
          </form>


       
         
        </div>
   
      </div>

      <div class="panels-container">
      <div class="panel left-panel">
    <div class="content">
        <h2 style="font-weight:bolder;">WELCOME TO ARGAO CHURCH OFFICIAL WEBSITE</h2>
        <p>
            Discover the rich history, faith, and community spirit of the Archdiocesan Shrine of San Miguel Arcangel. Our church has been a cornerstone of Argao, Cebu, for centuries, providing a place of worship, celebration, and connection for all. Log in to access church schedules, events, and services, and stay connected with our vibrant parish community. Thank you for being part of our journey of faith!
        </p>
    </div>
    <img src="img/log.svg" class="image responsive-image" alt="" />
</div>

        <div class="panel right-panel">
          <div class="content">
            <h3>JOIN OUR ARGAO CHURCH COMMUNITY</h3>
            <p>
            Become a part of the Archdiocesan Shrine of San Miguel Arcangel family by creating your account today. By signing up, you’ll gain access to our event schedules, church services, announcements, and more. Connect with our parish, stay updated on upcoming activities, and engage with a community that shares your faith. We look forward to welcoming you!
            </p>
           
          </div>
          <img src="img/register.svg" class="image" alt="" />
        </div>
      </div>
    </div>

    <script src="app.js"></script>
    <script>
      document.getElementById("sign-up-signbutton").addEventListener("click", function() {
    document.querySelector(".sign-in-form").style.display = "none";
    document.querySelector(".sign-up-form").style.display = "block";
});

document.getElementById("sign-in-signbutton").addEventListener("click", function() {
    document.querySelector(".sign-up-form").style.display = "none";
    document.querySelector(".sign-in-form").style.display = "block";
});


function showError(inputId, errorMessage) {
    const errorElement = document.getElementById(inputId + "_error");
    if (errorElement) {
        errorElement.textContent = errorMessage;
    }
}

function clearError(inputId) {
    const errorElement = document.getElementById(inputId + "_error");
    if (errorElement) {
        errorElement.textContent = "";
    }
}

        const sign_in_signbutton = document.querySelector("#sign-in-signbutton");
const sign_up_signbutton = document.querySelector("#sign-up-signbutton");
const container = document.querySelector(".container1");

sign_up_signbutton.addEventListener("click", () => {
  container.classList.add("sign-up-mode");
});

sign_in_signbutton.addEventListener("click", () => {
  container.classList.remove("sign-up-mode");
});
    </script>
    <?php require_once 'footer.php'?>

    </div>
    </div>

        <!-- Back to Top -->
<script>
 const pwShowHide = document.querySelectorAll(".pw_hide");

pwShowHide.forEach((icon) => {
  icon.addEventListener("click", () => {
    // Select the input field that is inside the same parent as the icon
    let getPwInput = icon.parentElement.querySelector("input");
    
    if (getPwInput.type === "password") {
      getPwInput.type = "text"; // Show password
      icon.classList.replace("uil-eye-slash", "uil-eye"); // Toggle icon
    } else {
      getPwInput.type = "password"; // Hide password
      icon.classList.replace("uil-eye", "uil-eye-slash"); // Toggle icon
    }
  });
});


          
    var acc = document.getElementsByClassName("accordion");
    var i;

    for (i = 0; i < acc.length; i++) {
        acc[i].addEventListener("click", function() {
            this.classList.toggle("active");
            var panel = this.nextElementSibling;
            if (panel.style.display === "block") {
                panel.style.display = "none";
            } else {
                panel.style.display = "block";
            }
        });
    }
</script>
        <!-- JavaScript Libraries -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
        <script src="lib/wow/wow.min.js"></script>
        <script src="lib/easing/easing.min.js"></script>
        <script src="lib/waypoints/waypoints.min.js"></script>
        <script src="lib/counterup/counterup.min.js"></script>
        <script src="lib/lightbox/js/lightbox.min.js"></script>
        <script src="lib/owlcarousel/owl.carousel.min.js"></script>
        

        <!-- Template Javascript -->
        <script src="js/main.js"></script>
    </body>

</html>