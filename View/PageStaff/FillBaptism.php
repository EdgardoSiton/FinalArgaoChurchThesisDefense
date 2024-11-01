<?php

require_once '../../Model/staff_mod.php';
require_once '../../Controller/fetchpending_con.php';
require_once '../../Model/db_connection.php';
require_once '../../Model/citizen_mod.php';

// Retrieve date and time from session
$scheduleDate = $_SESSION['selectedDate'] ?? null;
$startTime = $_SESSION['startTime'] ?? null;
$endTime = $_SESSION['endTime'] ?? null;

// Assuming you're storing session data for the user's name and citizen ID
$nme = $_SESSION['fullname'];
$regId = $_SESSION['citizend_id'];

// Create instances of the required classes
$citizen = new Citizen($conn);
$staff = new Staff($conn);

// Fetch available priests based on the selected schedule
$priests = $citizen->getAvailablePriests($scheduleDate, $startTime, $endTime);


$baptismfill_id = isset($_GET['id']) ? intval($_GET['id']) : null;
if ($baptismfill_id) {
    // Fetch schedule_id from baptismfill
    $scheduleId = $staff->getScheduleId($baptismfill_id);
} else {
    echo "No baptism ID provided.";
    $scheduleId = null;
}

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
// Define the getTimeRange function

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
    .border-error {
    border: 1px solid red !important; /* Adding !important for higher specificity */
    border-radius: 1px; /* Optional: for rounded corners */
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
      


// Retrieve data from sessionStorage
const selectedDate = sessionStorage.getItem('selectedDate');
const startTime = sessionStorage.getItem('startTime');
const endTime = sessionStorage.getItem('endTime');

// Use the retrieved data as needed
console.log('Selected Date:', selectedDate);
console.log('Start Time:', startTime);
console.log('End Time:', endTime);

document.addEventListener('DOMContentLoaded', function() {
    const selectedDate = sessionStorage.getItem('selectedDate');
    const selectedTimeRange = sessionStorage.getItem('selectedTimeRange');
    console.log('Selected Date:', selectedDate);  // Check if date is properly stored
    console.log('Selected Time Range:', selectedTimeRange);  // Check if time range is stored correctly
    if (selectedDate) {
        document.getElementById('date').value = selectedDate;
    }

    if (selectedTimeRange) {
        const [startTime, endTime] = selectedTimeRange.split(' - ');
        document.getElementById('start_time').value = startTime.trim();
        document.getElementById('end_time').value = endTime.trim();
    }
});
function getSundaysBeforeDate(targetDate) {
    const sundays = [];
    const currentDate = new Date(); // Start counting from today

    // Set the target date to only consider its date part
    targetDate.setHours(0, 0, 0, 0);

    // If the target date is before today, return an empty array
    if (targetDate < currentDate) return sundays;

    // Check if today is Sunday, and if so, skip it
    if (currentDate.getDay() === 0) {
        currentDate.setDate(currentDate.getDate() + 7); // Move to the next Sunday
    } else {
        // Find the next Sunday
        currentDate.setDate(currentDate.getDate() + (7 - currentDate.getDay()));
    }

    // Collect all Sundays until the target date (excluding today if it's a Sunday)
    while (currentDate <= targetDate) {
        sundays.push(new Date(currentDate));
        currentDate.setDate(currentDate.getDate() + 7);
    }

    return sundays;
}


function populateSundaysDropdown(targetDate) {
    const sundaysDropdown = document.getElementById('sundays');
    sundaysDropdown.innerHTML = ''; // Clear any previous options

    const relevantSundays = getSundaysBeforeDate(targetDate).sort((a, b) => a.getTime() - b.getTime());

    // Populate dropdown with filtered Sundays
    relevantSundays.forEach(sunday => {
        const option = document.createElement('option');
        const formattedDate = formatDateToYYYYMMDD(sunday); // Format as 'YYYY-MM-DD'
        const schedule_id = Math.random().toString(36).substr(2, 9); // Random schedule ID for demo

        option.value = `${schedule_id}|${formattedDate}|9:00 AM|11:00 AM`; // Four parts separated by '|'
        option.textContent = `${formattedDate} - 9:00 AM to 11:00 AM`; // Displayed text
        sundaysDropdown.appendChild(option);
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const dateInput = document.getElementById('date');

    // Update dropdown based on the selected date
    dateInput.addEventListener('change', function() {
        const selectedDate = new Date(dateInput.value); // Get the newly selected date
        if (!isNaN(selectedDate.getTime())) {
            populateSundaysDropdown(selectedDate); // Populate dropdown with Sundays before this date
        } else {
            clearSundaysDropdown(); // Clear dropdown if invalid date
        }
    });

    // Initial population of dropdown based on the selected date
    if (dateInput.value) {
        const selectedDate = new Date(dateInput.value); // Get the provided date
        if (!isNaN(selectedDate.getTime())) {
            populateSundaysDropdown(selectedDate); // Populate dropdown with relevant Sundays
        } else {
            clearSundaysDropdown(); // Clear dropdown if invalid date
        }
    }

    // Clear form fields on button click
    document.querySelector('.btn-info').addEventListener('click', function() {
        document.querySelectorAll('.form-control').forEach(function(element) {
            if (element.type === 'text' || element.tagName === 'TEXTAREA' || element.type === 'date') {
                if (element.id !== 'date' && element.id !== 'start_time' && element.id !== 'end_time') {
                    element.value = ''; // Clear the value
                }
            } else if (element.type === 'radio' || element.type === 'checkbox') {
                element.checked = false; // Uncheck radio and checkbox inputs
            }
        });
    });
});

// Function to clear the Sundays dropdown
function clearSundaysDropdown() {
    const sundaysDropdown = document.getElementById('sundays');
    sundaysDropdown.innerHTML = ''; // Clear any previous options
}

// Helper function to format date as 'YYYY-MM-DD'
function formatDateToYYYYMMDD(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0'); // Month is 0-indexed, so add 1
    const day = String(date.getDate()).padStart(2, '0'); // Pad day with leading zero if necessary
    return `${year}-${month}-${day}`; // Format as 'YYYY-MM-DD'
}


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
            <div class="page-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Walk in Baptism Fill-up Form</div>
                    </div>
                    <div class="card-body">
                    <form method="post" action="../../Controller/citizen_con.php" onsubmit="return validateForm()">
                    
    <input type="hidden" name="walkinbaptism_id" name="form_type" value="baptism">
    <div class="row">
        <div class="col-md-6 col-lg-4">
        <div class="form-group">
    <label for="date">Date</label>
    <input type="text" class="form-control" id="date" name="date" placeholder="Select a date" readonly />
    <span class="error" id="dateError"></span>
</div>
            <div class="form-group">
                <label for="firstname">Firstname of person to be baptized:</label>
                <input type="text" class="form-control" id="firstname" name="firstname" placeholder="Enter Firstname"   
        />
                <span class="error" id="firstnameError"></span>
            </div>
            <div class="form-group">
                <label for="lastname">Last Name of person to be baptized:</label>
                <input type="text" class="form-control" id="lastname" name="lastname" placeholder="Enter Lastname"  />
                <span class="error" id="lastnameError"></span>
            </div>
            <div class="form-group">
                <label for="middlename">Middle Name of person to be baptized:</label>
                <input type="text" class="form-control" id="middlename" name="middlename" placeholder="Enter Middlename"  />
                <span class="error" id="middlenameError"></span>
            </div>
            <input type="hidden" id="fullname" name="fullname" />
            <div class="form-group">
                <label for="address">Address</label>
                <textarea class="form-control" id="address" name="address" placeholder="Enter Address"></textarea>
                <span class="error" id="addressError"></span>
            </div>
            <div class="form-group">
                <label>Gender</label><br />
                <div class="d-flex">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="gender" id="flexRadioDefault1" value="Male" />
                        <label class="form-check-label" for="flexRadioDefault1">Male</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="gender" id="flexRadioDefault2" value="Female" />
                        <label class="form-check-label" for="flexRadioDefault2">Female</label>
                    </div>
                </div>
                <span class="error" id="genderError"></span>
            </div>
            <div class="form-group">
                <label for="religion">Religion</label>
                <input type="text" class="form-control" id="religion" name="religion" placeholder="Enter Religion" />
                <span class="error" id="religionError"></span>
            </div>
        </div>
        <div class="col-md-6 col-lg-4">
            <div class="form-group">
                <label for="start_time">Start Time</label>
                <input type="text" class="form-control" id="start_time" name="start_time" placeholder="" readonly />
              <span class="error" id="startTimeError"></span>
            </div>
            <div class="form-group">
                <label for="pbirth">Place of Birth</label>
                <input type="text" class="form-control" id="pbirth" name="pbirth" placeholder="Enter Place of Birth" />
                <span class="error" id="pbirthError"></span>
            </div>
            <div class="form-group">
    <div class="birthday-input">
        <label for="month">Date of Birth</label>
        <div class="birthday-selectors">
            <select id="months" name="month">
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

            <select id="days" name="day">
                <option value="">Day</option>
                <!-- Generate options 1 to 31 -->
                <?php for ($i = 1; $i <= 31; $i++): ?>
                    <option value="<?php echo sprintf('%02d', $i); ?>"><?php echo $i; ?></option>
                <?php endfor; ?>
            </select>

            <select id="years" name="year">
                <option value="">Year</option>
                <!-- Generate options from 1900 to current year -->
                <?php for ($i = date('Y'); $i >= 1900; $i--): ?>
                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                <?php endfor; ?>
            </select>
        </div>
      
                    <span class="error" id="dobError"></span>
                
    </div>
    
</div>
            <div class="form-group">
                <label for="father_name">Father's Fullname</label>
                <input type="text" class="form-control" id="father_name" name="father_fullname" placeholder="Enter Father's Fullname" />
                <span class="error" id="fatherNameError"></span>
            </div>
            <div class="form-group">
                <label for="mother_name">Mother's Fullname</label>
                <input type="text" class="form-control" id="mother_name" name="mother_fullname" placeholder="Enter Mother's Fullname" />
                <span class="error" id="motherNameError"></span>
            </div>
        </div>
        <div class="col-md-6 col-lg-4">
            <div class="form-group">
                <label for="end_time">End Time</label>
                <input type="text" class="form-control" id="end_time" name="end_time" placeholder="" readonly />
                  <span class="error" id="endTimeError"></span>
            </div>
            <div class="form-group">
                <label for="parents_residence">Parents Residence</label>
                <textarea class="form-control" id="parents_residence" name="parent_resident" placeholder="Enter Parents Residence"></textarea>
                <span class="error" id="parentsResidenceError"></span>
            </div>
            <div class="form-group">
                <label for="godparents">List Of GodParents</label>
                <textarea class="form-control" id="godparents" name="godparent" placeholder="Enter List Of GodParents"></textarea>
                <span class="error" id="godparentsError"></span>
            </div>
        </div>
    </div>
    <div class="card-action">
    <div class="card-header">
                        <div class="card-title">Seminar Schedule and Payableamount</div>
                    </div>
    <div class="col-md-6 col-lg-4">

    <div class="form-group"> 
    <label for="eventType">Select Priest</label>
    <select class="form-control" id="eventType" name="eventType">
        <option value="" disabled selected>Select Priest</option>
        <!-- Populate priests in the dropdown -->
        <?php foreach ($priests as $priest): ?>
            <option value="<?php echo htmlspecialchars($priest['citizend_id']); ?>">
                <?php echo htmlspecialchars($priest['fullname']); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <span class="error" id="eventTypeError"></span>
</div>


<div class="form-group"> 
    <label for="sundays">Select Seminar</label>
    <select class="form-control" id="sundays" name="sundays">
    <option value="" disabled selected>Select Seminar</option>    
    </select>
    <span class="error" id="seminarError"></span>
</div>
<div class="form-group">
                        <label for="eventTitle1">Seminar Speaker</label>
                        <input type="text" class="form-control" id="eventTitle1" name="eventspeaker" placeholder="Enter Amount">
                        <span class="error" id="eventspeakerError"></span>
                    </div>

<div class="form-group">
    <label for="pay_amount">Payable Amount</label>
    <input type="number" class="form-control" id="pay_amount" name="pay_amount" placeholder="Enter Payable Amount" />
    <span class="error" id="payAmountError"></span>
</div>
 
       
        </div>
            </div>
    <div class="card-action">
        <button type="submit" class="btn btn-success">Submit</button>
        <button type="button" class="btn btn-danger" onclick="window.location.href='your_cancel_url.php'">Cancel</button>
        <button type="button" class="btn btn-info" onclick="clearForm()">Clear</button>
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
    let isValid = true;

    // Helper function to validate field
    function validateField(id, errorId, message) {
        const field = document.getElementById(id);
        const value = field.value.trim();
        if (value === '') {
            document.getElementById(errorId).innerText = message;
            field.classList.add('error');
            isValid = false;
        } else {
            document.getElementById(errorId).innerText = '';
            field.classList.remove('error');
        }
    }

    // Clear previous error messages and styles
    document.querySelectorAll('.error').forEach(e => e.innerHTML = '');
    document.querySelectorAll('.form-control').forEach(e => e.classList.remove('error'));

    // Validate fields
    validateField('firstname', 'firstnameError', 'Firstname is required');
    validateField('lastname', 'lastnameError', 'Lastname is required');
    validateField('address', 'addressError', 'Address is required');
    validateField('religion', 'religionError', 'Religion is required');
    validateField('pbirth', 'pbirthError', 'Place of Birth is required');
    validateField('father_name', 'fatherNameError', 'Father\'s Fullname is required');
    validateField('mother_name', 'motherNameError', 'Mother\'s Fullname is required');
    validateField('parents_residence', 'parentsResidenceError', 'Parents Residence is required');
    validateField('godparents', 'godparentsError', 'List Of Godparents is required');
    validateField('date', 'dateError', 'Date is required');
    validateField('start_time', 'startTimeError', 'Start Time is required');
    validateField('end_time', 'endTimeError', 'End Time is required');
    function validateSelect(selectId, errorId, errorMessage) {
    const selectElement = document.getElementById(selectId);
    const errorElement = document.getElementById(errorId);
    
    if (selectElement.value === "") {
        errorElement.textContent = errorMessage;
        selectElement.classList.add('border-error'); // Add error border
        console.log(`Class added to ${selectId}`); // Debugging line
        isValid = false;
    } else {
        errorElement.textContent = ""; // Clear the error if a valid option is selected
        selectElement.classList.remove('border-error'); // Remove error border
        console.log(`Class removed from ${selectId}`); // Debugging line
    }
}

    validateSelect('eventType', 'eventTypeError', 'Please select a Priest.');

    // Validate gender
    const gender = document.querySelector('input[name="gender"]:checked');
    if (!gender) {
        document.getElementById('genderError').innerText = 'Gender is required';
        document.querySelector('input[name="gender"]').classList.add('error');
        isValid = false;
    } else {
        document.getElementById('genderError').innerText = '';
        document.querySelector('input[name="gender"]').classList.remove('error');
    }

    // Validate date of birth
    const month = document.getElementById('months').value;
    const day = document.getElementById('days').value;
    const year = document.getElementById('years').value;
    if (month === '' || day === '' || year === '') {
        document.getElementById('dobError').innerText = 'Date of birth is required';
        isValid = false;
    } else {
        document.getElementById('dobError').innerText = '';
    }
    const seminar = document.getElementById('sundays').value;
    if (seminar === '') {
        document.getElementById('seminarError').innerText = 'Please select a seminar';
        document.getElementById('sundays').classList.add('error');
        isValid = false;
    } else {
        document.getElementById('seminarError').innerText = '';
        document.getElementById('sundays').classList.remove('error');
    }
    const priest = document.getElementById('eventType').value;
 

    
 const payAmount = document.getElementById('pay_amount').value;
    if (payAmount === '' || isNaN(payAmount) || payAmount <= 0) {
        document.getElementById('payAmountError').innerText = 'Please enter a valid payable amount';
        document.getElementById('pay_amount').classList.add('error');
        isValid = false;
    } else {
        document.getElementById('payAmountError').innerText = '';
        document.getElementById('pay_amount').classList.remove('error');
    }
    // Check if the form is valid
    if (!isValid) {
        console.log('Validation failed, form not submitted.');
        return false;  // Prevent form submission
    }

    console.log('Validation passed, form will be submitted.');
    return true;  // Allow form submission
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
