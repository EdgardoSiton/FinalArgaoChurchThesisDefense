<?php
require_once '../../Controller/login_con.php';
$email = $_SESSION['email'];
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
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>ARGAO CHURCH MANAGEMENT SYSTEM</title>
    <meta
      content="width=device-width, initial-scale=1.0, shrink-to-fit=no"
      name="viewport"
    />
    <link rel="icon" href="../assets/img/mainlogo.jpg" type="image/x-icon"
    />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
    <!-- Fonts and icons -->
    <script src="assets/js/plugin/webfont/webfont.min.js"></script>
    <style>
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
.error {
        color: red;
        font-size: 0.875em;
        margin-top: 0.25em;
    }
    .form-control.error {
        border: 1px solid red;
    }
    </style>
    <script>
      WebFont.load({
        google: { families: ["Public Sans:300,400,500,600,700"] },
        custom: {
          families: [
            "Font Awesome 5 Solid",
            "Font Awesome 5 Regular",
            "Font Awesome 5 Brands",
            "simple-line-icons",
          ],
          urls: ["assets/css/fonts.min.css"],
        },
        active: function () {
          sessionStorage.fonts = true;
        },
      });
      




    </script>
 <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet"
 integrity="sha384-KyZXEAg3QhqLMpG8r+Knujsl7/1L_dstPt3HV5HzF6Gvk/e3s4Wz6iJgD/+ub2oU" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <!-- CSS Files -->
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="../assets/css/plugins.min.css" />
    <link rel="stylesheet" href="../assets/css/kaiadmin.min.css" />

    <!-- CSS Just for demo purpose, don't include it in your project -->
    <link rel="stylesheet" href="../assets/css/demo.css" />
   
  </head>
  <body>
  <?php  require_once 'sidebar.php'?>
      <div class="main-panel">
      <?php  require_once 'header.php'?>
        
     
        <div class="container">
            <div class="pa
            ge-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Priest Registration Account</div>
                    </div>
                    <div class="card-body">
                    <form method="POST" action=""  enctype="multipart/form-data" onsubmit="return validateForm()"class="sign-up-form">
                    
                    <input type="hidden" name="signup_forms" value="1">
    <div class="row">
        <div class="col-md-6 col-lg-4">

            <div class="form-group">
                <label for="firstname">Firstname of Priest:</label>
                <input type="text" class="form-control" name="first_name" id="first_name" placeholder="First Name">  
        
                <div class="error" id="first_name_error"></div>  
            </div>
            <div class="form-group">
                <label for="lastname">Last Name of Priest:</label>
                <input type="text" class="form-control" name="last_name" id="last_name" placeholder="Last Name">
                <div class="error" id="last_name_error"></div>
            </div>
            <div class="form-group">
                <label for="middlename">Middle Name of Priest:</label>
                <input type="text" class="form-control" name="middle_name" id="middle_name" placeholder="Middle Name">
                <div class="error" id="middle_name_error"></div>
            </div>
            <input type="hidden" id="fullname" name="fullname" />
            <div class="form-group">
                <label for="address">Address</label>
                <textarea class="form-control" id="address" name="address" placeholder="Enter Address"></textarea>
                <div class="error" id="address_error"></div>
            </div>
            <div class="form-group">
                <label>Gender</label><br />
                <select class="form-control" name="gender"  class="input_box" id="gender" placeholder="name@example.com">
            <option value="" disabled selected>Select gender</option>
            <option>Male</option>
            <option>Female</option>
        </select>
                <div class="error" id="gender_error"></div>
            </div>

        </div>
        <div class="col-md-6 col-lg-4">
     
    
            <div class="form-group">
    <div class="birthday-input">
        <label for="month">Date of Birth</label>
        <div class="birthday-selectors">
            <select id="month" name="month">
                <option value="">Month</option>
                <option value="01">January</option>
                <option value="02">February</option>
                <option value="03">March</option>
                <option value="04">April</option>
                <option value="05">May</option>
                <option value="06">June</option>
                <option value="07">July</option>
                <option value="08">August</option>
                <option value="09">September</option>
                <option value="10">October</option>
                <option value="11">November</option>
                <option value="12">December</option>
            </select>

            <select id="day" name="day">
                <option value="">Day</option>
                <!-- Generate options 1 to 31 -->
                <?php for ($i = 1; $i <= 31; $i++): ?>
                    <option value="<?php echo sprintf('%02d', $i); ?>"><?php echo $i; ?></option>
                <?php endfor; ?>
            </select>

            <select id="year" name="year">
                <option value="">Year</option>
                <!-- Generate options from 1900 to current year -->
                <?php for ($i = date('Y'); $i >= 1900; $i--): ?>
                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                <?php endfor; ?>
            </select>
        </div>
      
        <div class="error" id="c_date_birth_error"></div>
                
    </div>
    
</div>
            <div class="form-group">
                <label for="father_name">Phone Number</label>
                <input type="tel" class="form-control" name="phone" id="phone" placeholder="Phone number ">
                <div class="error" id="phone_error"></div>
            </div>
            <div class="form-group">
                <label for="mother_name">GMAIL</label>
                <input type="text" class="form-control" name="email" id="emails" placeholder="name@example.com">
                <div class="error" id="email_error"></div>
            </div>
        </div>
        <div class="col-md-6 col-lg-4">
        <div class="form-group">
        <label for="validID">Valid ID</label>
    <input type="file" class="form-control" id="valid_id" name="valid_id" accept="image/*" placeholder="Valid ID">
    <div class="error" id="valid_id_error"></div>
            </div>
            <div class="form-group">
                <label for="parents_residence">Password</label>
                <input type="password" class="form-control" id="passwords" name="password" placeholder="Password">
                <div class="error" id="password_error"></div>
            </div>
            <div class="form-group">
                <label for="godparents">ConfirmPassword</label>
                <input type="password" class="form-control" id="confirmpassword" name="confirmpassword" placeholder="Confirm Password">
                <div class="error" id="confirmpassword_error"></div>
            </div>
        </div>
    </div>

    <div class="card-action">
    <button type="submit" class="btn btn-success" >Register</button>
          </div>
</form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>

function validateForm() {
        // Clear previous errors
        clearErrors();

        let isValid = true;

        // Validate first name
        const firstName = document.getElementById("first_name").value.trim();
        if (firstName === "") {
            showError("first_name", "First Name is required");
            isValid = false;
        }

        // Validate last name
        const lastName = document.getElementById("last_name").value.trim();
        if (lastName === "") {
            showError("last_name", "Last Name is required");
            isValid = false;
        }

        // Validate address
        const address = document.getElementById("address").value.trim();
        if (address === "") {
            showError("address", "Address is required");
            isValid = false;
        }

        // Validate gender
        const gender = document.getElementById("gender").value;
        if (gender === "") {
            showError("gender", "Gender is required");
            isValid = false;
        }

        // Validate phone
        const phone = document.getElementById("phone").value.trim();
        if (phone === "") {
            showError("phone", "Phone number is required");
            isValid = false;
        }

        // Validate email
        const email = document.getElementById("emails").value.trim();
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (email === "") {
            showError("email", "Email is required");
            isValid = false;
        } else if (!emailPattern.test(email)) {
            showError("email", "Invalid email format");
            isValid = false;
        } else if (!email.endsWith("@gmail.com")) {
            showError("email", "Email must end with @gmail.com");
            isValid = false;
        }

        // Validate date of birth
        const month = document.getElementById("month").value;
        const day = document.getElementById("day").value;
        const year = document.getElementById("year").value;
        if (month === "" || day === "" || year === "") {
            showError("c_date_birth", "Date of Birth is required");
            isValid = false;
        } else if (!isValidDate(month, day, year)) {
            showError("c_date_birth", "Invalid Date of Birth");
            isValid = false;
        } else if (!isAtLeast15YearsOld(year, month, day)) {
            showError("c_date_birth", "You must be at least 16 years old");
            isValid = false;
        }

        // Validate password
        const password = document.getElementById("passwords").value.trim();
        if (password === "") {
            showError("password", "Password is required");
            isValid = false;
        }

        // Validate confirm password
        const confirmPassword = document.getElementById("confirmpassword").value.trim();
        if (confirmPassword !== password) {
            showError("confirmpassword", "Passwords do not match");
            isValid = false;
        }

        return isValid;
    }

    function showError(inputId, errorMessage) {
        const inputElement = document.getElementById(inputId);
        const errorElement = document.getElementById(inputId + "_error");

        if (inputElement) {
            inputElement.classList.add("input-error");
        }

        if (errorElement) {
            errorElement.textContent = errorMessage;
        }
    }

    function clearErrors() {
        const errorElements = document.querySelectorAll(".error");
        errorElements.forEach((element) => {
            element.textContent = "";
        });

        const inputElements = document.querySelectorAll(".form-control");
        inputElements.forEach((element) => {
            element.classList.remove("input-error");
        });
    }

    function isValidDate(month, day, year) {
        // Convert values to integers
        const monthInt = parseInt(month, 10);
        const dayInt = parseInt(day, 10);
        const yearInt = parseInt(year, 10);

        // Check if the date is valid
        if (monthInt < 1 || monthInt > 12) {
            return false;
        }

        const daysInMonth = new Date(yearInt, monthInt, 0).getDate();
        return dayInt > 0 && dayInt <= daysInMonth;
    }

    function isAtLeast15YearsOld(year, month, day) {
        const today = new Date();
        const birthDate = new Date(year, month - 1, day); // JavaScript months are 0-based
        const age = today.getFullYear() - birthDate.getFullYear();

        if (today.getMonth() + 1 < month || (today.getMonth() + 1 === month && today.getDate() < day)) {
            return age - 1 >= 15;
        }

        return age >= 16;
    }

</script>



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"
        integrity="sha384-KyZXEAg3QhqLMpG8r+8auK+4szKfEFbpLHsTf7iJgD/+ub2oU" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.js"></script>
    <!--   Core JS Files   -->
    <script src="../assets/js/core/jquery-3.7.1.min.js"></script>
    <script src="../assets/js/core/popper.min.js"></script>
    <script src="../assets/js/core/bootstrap.min.js"></script>

    <!-- jQuery Scrollbar -->
    <script src="../assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js"></script>

  
    <!-- Kaiadmin JS -->
    <script src="../assets/js/kaiadmin.min.js"></script>

  
    </script>
  </body>
</html>
