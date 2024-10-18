<?php

require_once '../../Model/db_connection.php';
require_once '../../Controller/citizen_con.php';
$loggedInUserEmail = isset($_SESSION['email']) ? $_SESSION['email'] : null;
$nme = $_SESSION['fullname'];
if (!$loggedInUserEmail) {
    header("Location: login.php");
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
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"
    />
    <!-- Fonts and icons -->
    <script src="assets/js/plugin/webfont/webfont.min.js"></script>
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

      const currentDate = new Date();
let currentMonth = currentDate.getMonth();
let currentYear = currentDate.getFullYear();
let selectedDateElement = null;

function moveDate(direction) {
    currentMonth += direction;
    if (currentMonth < 0) {
        currentMonth = 11;
        currentYear--;
    } else if (currentMonth > 11) {
        currentMonth = 0;
        currentYear++;
    }
    renderCalendar();
}

let initialLoad = true; // Track if this is the initial load of the calendar

function renderCalendar() {
    const daysElement = document.getElementById('days');
    daysElement.innerHTML = '';

    const monthYearElement = document.getElementById('monthYear');
    const months = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'
    ];

    // Update month and year display
    monthYearElement.innerHTML = `${months[currentMonth]}<br><span style="font-size:18px">${currentYear}</span>`;

    // Get the first day of the month
    const firstDay = new Date(currentYear, currentMonth, 1).getDay();
    const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();

    const today = new Date();
    today.setHours(0, 0, 0, 0); // Set to midnight to ignore time part for comparison
    const oneWeekFromNow = new Date(today.getTime() + 8 * 24 * 60 * 60 * 1000); // One week from today
    const fifteenDaysFromNow = new Date(today.getTime() + 16 * 24 * 60 * 60 * 1000); // 15 days from today
    const isCurrentMonth = today.getFullYear() === currentYear && today.getMonth() === currentMonth;

    let hasSelectableDate = false;

    // Get event type from the URL
    const urlParams = new URLSearchParams(window.location.search);
    const type = urlParams.get('type') || 'baptism'; // Default to 'baptism' if not set

    // Fill in empty days before the first day of the month
    for (let i = 0; i < firstDay; i++) {
        daysElement.innerHTML += '<li></li>';
    }

    // Fill in the actual days of the month
    for (let i = 1; i <= daysInMonth; i++) {
        const dayElement = document.createElement('li');
        dayElement.textContent = i;

        const dayDate = new Date(currentYear, currentMonth, i);
        dayDate.setHours(0, 0, 0, 0); // Ignore time part for comparison

        // Disable past dates and today
        if (dayDate < today) {
            dayElement.classList.add('past'); // Disable past and current date
        }
        // Disable today for Funeral events
        else if (type === 'Funeral' && dayDate.getTime() === today.getTime()) {
            dayElement.classList.add('past'); // Disable today for funerals
        }
        // Disable based on the event type
        else if (type === 'Wedding' && dayDate < fifteenDaysFromNow) {
            dayElement.classList.add('past'); // Disable for weddings if less than 15 days
        }
        else if (type !== 'Funeral' && dayDate < oneWeekFromNow) {
            dayElement.classList.add('past'); // Disable for all other events except funerals if less than 7 days
        }
        // Enable selectable future dates
        else {
            dayElement.addEventListener('click', () => selectDate(dayElement));
            hasSelectableDate = true;
        }

        daysElement.appendChild(dayElement);
    }

    // If no selectable dates and this is the initial load, move to the next month and re-render
    if (initialLoad && !hasSelectableDate) {
        currentMonth++;
        if (currentMonth > 11) {
            currentMonth = 0;
            currentYear++;
        }
        renderCalendar(); // Re-render the calendar for the next month
    }

    initialLoad = false; // Disable automatic month change after the first load
}




// Initialize calendar on page load
window.addEventListener('DOMContentLoaded', () => {
    renderCalendar();
});


function selectDate(dayElement) {
    if (selectedDateElement) {
        selectedDateElement.classList.remove('active');
    }
    dayElement.classList.add('active');
    selectedDateElement = dayElement;

    const selectedDate = new Date(currentYear, currentMonth, dayElement.textContent);
    const formattedDate = selectedDate.toLocaleDateString('en-CA'); // Format date in local time

    // Deselect any previously selected radio button
    const selectedRadioButton = document.querySelector('input[type="radio"]:checked');
    if (selectedRadioButton) {
        selectedRadioButton.checked = false;
    }

    // Get event type from the URL
    const urlParams = new URLSearchParams(window.location.search);
    const type = urlParams.get('type') || 'baptism'; // Default to 'baptism' if not set

    // Check if the selected date is within 15 days from today for weddings
    const today = new Date();
    const fifteenDaysFromNow = new Date(today.getTime() + 15 * 24 * 60 * 60 * 1000);
    const oneWeekFromNow = new Date(today.getTime() + 7 * 24 * 60 * 60 * 1000);

 
    // Make an AJAX request to fetch the schedule for the selected date
    fetch('../../Controller/getschedule_con.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `date=${formattedDate}`,
    })
    .then(response => response.json())
    .then(schedules => {
        console.log('Schedules:', schedules); // Debugging
        updateAvailableTimes(schedules, selectedDate, type === 'baptism'); // Pass the event type as isBaptism
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

function updateAvailableTimes(schedules, selectedDate, isBaptism) {
    const timeSlots = document.querySelectorAll('.time .form-check');

    // Check the day of the selected date
    const dayOfWeek = selectedDate.getDay(); // 0 = Sunday, 1 = Monday, ..., 6 = Saturday
    const isSunday = dayOfWeek === 0;
    const isWeekday = dayOfWeek >= 1 && dayOfWeek <= 5; // Monday to Friday
    const isTueThuFri = dayOfWeek === 2 || dayOfWeek === 4 || dayOfWeek === 5; // Tuesday, Thursday, Friday

    timeSlots.forEach(slot => {
        const timeRange = slot.querySelector('label').textContent.trim();
        const [startTime, endTime] = timeRange.split(' - ');

        const isBooked = schedules.some(schedule => {
            const dbStartTime = schedule.start_time.substring(0, 5); // Extract HH:MM from HH:MM:SS
            const dbEndTime = schedule.end_time.substring(0, 5);     // Extract HH:MM from HH:MM:SS
            return formatTime(dbStartTime) === startTime && formatTime(dbEndTime) === endTime;
        });

        const radioButton = slot.querySelector('input[type="radio"]');
        const statusText = slot.querySelector('h6');

        // Handle Sunday availability
        if (isSunday) {
            if (isBaptism) {
                statusText.textContent = 'Mass Schedule';
                statusText.style.color = 'gray';
                radioButton.disabled = true;
            } else if (startTime === '1:30 PM' && endTime === '2:30 PM') {
                statusText.textContent = isBooked ? 'Booked' : 'Available';
                statusText.style.color = isBooked ? 'red' : 'green';
                radioButton.disabled = isBooked;
            } else {
                statusText.textContent = 'Mass Schedule';
                statusText.style.color = 'gray';
                radioButton.disabled = true;
            }
        } 
        // Handle 4:30 PM - 5:30 PM slot for weekdays (Monday to Friday)
        else if (isWeekday && startTime === '4:30 PM' && endTime === '5:30 PM') {
            statusText.textContent = 'Mass Schedule';
            statusText.style.color = 'gray';
            radioButton.disabled = true;
        } 
        // Handle Tuesday, Thursday, and Friday unavailability for 11:30 AM - 12:30 PM
        else if (isTueThuFri && startTime === '11:30 AM' && endTime === '12:30 PM') {
            statusText.textContent = 'Prayer Schedule';
            statusText.style.color = 'gray';
            radioButton.disabled = true;
        } 
        // Default behavior for other slots
        else {
            statusText.textContent = isBooked ? 'Booked' : 'Available';
            statusText.style.color = isBooked ? 'red' : 'green';
            radioButton.disabled = isBooked;
        }
    });
}

function formatTime(timeString) {
    const [hours, minutes] = timeString.split(':');
    const ampm = hours >= 12 ? 'PM' : 'AM';
    const formattedHours = (hours % 12) || 12; // Convert 24-hour format to 12-hour format
    return `${formattedHours}:${minutes} ${ampm}`;
}

window.addEventListener('DOMContentLoaded', () => {
    const selectedRadioButton = document.querySelector('input[type="radio"]:checked');
    if (selectedRadioButton) {
        selectedRadioButton.checked = false;
    }
    renderCalendar();
});

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('scheduleForm');

    if (form) {
        form.addEventListener('submit', function(event) {
            event.preventDefault();

            const selectedDateElement = document.querySelector('.days .active');
            const selectedRadioButton = document.querySelector('input[name="flexRadioDefault"]:checked');

            if (selectedDateElement && selectedRadioButton) {
                const selectedDate = new Date(currentYear, currentMonth, selectedDateElement.textContent);
                selectedDate.setHours(12, 0, 0, 0);
                const formattedDate = selectedDate.toISOString().split('T')[0];
                const selectedTimeRange = selectedRadioButton.nextElementSibling.textContent.trim();

                // Store the selected date and time in sessionStorage
                sessionStorage.setItem('selectedDate', formattedDate);

                // Store start and end times separately
                sessionStorage.setItem('selectedTime', selectedTimeRange);

                // Get the type from the URL to determine the form type
                const urlParams = new URLSearchParams(window.location.search);
                const type = urlParams.get('type') || 'baptism'; // Default to 'baptism' if not set

                let nextPage;
                if (type === 'baptism') {
                    nextPage = `FillBaptismForm.php`;
                } else if (type === 'confirmation') {
                    nextPage = `FillConfirmationForm.php`;
                } else if (type === 'Wedding') {
                    nextPage = `FillWeddingForm.php`;
                } else if (type === 'Funeral') {
                    nextPage = `FillFuneralForm.php`;
                }else if (type === 'RequestForm') {
                    nextPage = `FillInsideRequestScheduleForm.php`;
                } else {
                    alert('Invalid scheduling type.');
                    return;
                }

                console.log('Redirecting to:', nextPage);
                window.location.assign(nextPage);
            } else {
                alert('Please select both a date and a time before submitting.');
            }
        });
    } else {
        console.error('Form element with id "scheduleForm" not found.');
    }
});


    </script>
        <style>
   
   body {
            margin: 0;
            background-color: #f4f4f9;
        }

        .container-cal {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            width: 100%;
            max-width: 1200px;
            margin: 20px auto;
            padding: 10px;
            box-sizing: border-box;
        }

        /* Calendar and time selection containers */
        .calendar, .schedule {
            flex: 1;
            margin: 10px;
            padding: 20px;
        }

        /* Calendar specific styles */
        .calendar h3 {
            text-align: center;
            font-size: 24px;
            margin-bottom: 20px;
            color: #333;
        }

        .month ul {
            display: flex;
            justify-content: space-between;
            padding: 0;
            list-style-type: none;
            margin: 0;
            padding: 20px;
            border: solid;

    background: rgba(172, 7, 40, 0.856);
        }

        .month ul li {
            font-size: 18px;
            cursor: pointer;
            color: #333;
        }

        .weekdays, .days {
            list-style-type: none;
            padding: 0;
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 5px;
    margin-bottom: -10px;
    border-right-style: solid;
    border-left-style: solid;
    border-bottom-style: solid;

        }

        .weekdays li, .days li {
            padding: 10px;
            text-align: center;
            font-weight: bold;
            color: #555;
            border-radius: 5px;
            cursor: pointer;
            
        }
      
        .days li.active, .days li.selected {
            background-color: #3498db;
            color: #fff;
            
        }

        /* Time selection styles */
        .time h3 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 22px;
            color: #333;
        }

       
    .time-options {
      display: flex;
    flex-direction: row;
    gap: 60px;
    margin-top: 5px;
    }
    .time-option {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .time-selection button {
        width: 50%;
        padding: 10px;
        cursor: pointer;
        border: none;
        border-radius: 4px;
        background-color: #007bff;
        color: white;
        transition: background-color 0.3s;
    }
    .time-selection button:hover {
        background-color: #0056b3;
    }
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .container-cal {
                flex-direction: column;
                align-items: center;
            }

            .calendar, .time{
              width: 100%;
       
            }

            .weekdays li, .days li {
                padding: 8px!important;
                font-size: 14px;
            }

            .time-options button {
                font-size: 14px;
                padding: 8px;
            }
            .form-group {
          margin:0;  
          }

        }
/* Style the radio button to be visible */
.styled-radio {
  width: 20px;
  height: 20px;
  accent-color: #007bff; /* Button color */
  cursor: pointer;
}
.form-group {
   /* align-items: end; */
   width: 65%;
	     /* border: 1px solid black; */
	     margin: 0 auto;
padding:0;
}


.radio-wrapper {
  display: flex;
  align-items: center;
  justify-content: flex-start;
  cursor: default;
    opacity: .5;
}
.form-check-label.time-slot {
  width: 150px; /* Set a fixed width for the time column */
  text-align: left;
}
.form-check {
    display: flex;
    align-items: center;
}

/* Style the label with border and background */
.form-check-label {
  margin-left: 10px;
  font-size: 16px;
  cursor: pointer;
  color: #333;
 
  padding: 8px;  /* Padding for button-like appearance */
  border-radius: 4px;  /* Rounded corners */
  transition: background-color 0.3s ease, border-color 0.3s ease;
}

/* Optional: Hover effect for better interactivity */
.form-check-label:hover {
  background-color: #ff69b4;
  color: white;
  border-color: #ff1493;
}

/* Hover effect for radio button */
.styled-radio:hover {
  border-color: #0056b3;
  box-shadow: 0 0 5px rgba(0, 0, 0, 0.2);
}

.form-check-label {
  margin-right: 10px; /* Space between label and h6 */
}

/* Style for the h6 element beside the label */
.time-status {
  font-size: 14px;
  margin-left: 20px; /* Ensure availability text is spaced properly */
  white-space: nowrap; /* Prevent the status text from wrapping */
  color: green;
  padding: 0;
  display: inline-block;
}
.form-check input{
  margin-top: 10px;
}

.days li {
            cursor: pointer;
        }
        .days li.active {
            background-color: #1abc9c;
            color: white;
            border-radius: 50%;
        }
        .days li.past {
            color: #ccc;
            cursor: not-allowed;
        }
    </style>
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css"
      rel="stylesheet"
      integrity="sha384-KyZXEAg3QhqLMpG8r+Knujsl7/1L_dstPt3HV5HzF6Gvk/e3s4Wz6iJgD/+ub2oU"
      crossorigin="anonymous"
    />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"
    />
    <!-- CSS Files -->
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="../assets/css/plugins.min.css" />
    <link rel="stylesheet" href="../assets/css/kaiadmin.min.css" />


  </head>
  <body style="background: #9e9e9e47">
   <!-- Navbar & Hero Start -->
   <div class="container-fluid nav-bar px-0 px-lg-4 py-lg-0">
      <div class="container">
       
      <?php require_once 'header.php'?>

      </div>
    </div>
    <!-- Navbar & Hero End -->
    

 
    
    </div>
  </div>
  <form id="scheduleForm">
  <div class="container-cal">
    <div class="calendar">
        <h3 style="padding-top: 20px;" class="fw-bold mb-3">Select Date</h3>
        <div class="month">
            <ul>
                <li class="prev" onclick="moveDate(-1)">&#10094;</li>
                <li id="monthYear">
                   <br>
                    <span style="font-size:18px">2024</span>
                </li>
                <li class="next" onclick="moveDate(1)">&#10095;</li>
              
            </ul>
        </div>

        <ul class="weekdays">
            <li>Su</li>
            <li>Mo</li>
            <li>Tue</li>
            <li>Wed</li>
            <li>Thu</li>
            <li>Fri</li>
            <li>Sat</li>
        </ul>

        <ul class="days" id="days">
            <!-- Days will be generated by JavaScript -->
        </ul>
    </div>


<div class="schedule">

<div class="time" > 
  <h3 style="padding-top: 20px; padding-left: 10px;" class="fw-bold mb-3">Select Time</h3>
  <div style="padding-left: 70px;" class="form-group">
    <div class="d-flex">
     
      <div class="form-check">
      <input
          class="form-check-input"
          type="radio"
          name="flexRadioDefault"
          id="flexRadioDefault1"
          value="8:30 AM - 9:30 AM"
        />
        <label
          style="padding-right: 130px;"
          class="form-check-label"
          for="timeSlot1"
        >
          8:30 AM - 9:30 AM
        </label>
        <label>
          <h6 style="font-size: 14px; color: green; padding:0; margin: 0;"></h6>
        </label>
      </div>
    </div>
    <div class="d-flex">
     
      <div class="form-check">
        <input
          class="form-check-input"
          type="radio"
          name="flexRadioDefault"
          id="flexRadioDefault2"
          value="10:00 AM - 11:30 AM"
          
        />
        <label
        style="padding-right: 121px;"
          class="form-check-label"
          for="timeSlot2"
        >
        10:00 AM - 11:00 AM
        </label>
        <label
        style="color: red;"
        class="form-check-label"
        for="flexRadioDefault2"
      >
      <h6 style="font-size: 14px; color: red; padding:0; margin: 0;" ></h6>
      </label>
      </div>
    </div>
    <div class="d-flex">
     
      <div class="form-check">
        <input
          class="form-check-input"
          type="radio"
          name="flexRadioDefault"
          id="flexRadioDefault3"
          value="11:30 AM - 12:30 PM"
        />
        <label
        style="padding-right: 115px;"
          class="form-check-label"
          for="timeSlot3"
        >
        11:30 AM - 12:30 PM
      </label>
      <label
        class="form-check-label"
        for="flexRadioDefault2"
      >
      <h6 style="font-size: 14px; color: green; padding:0; margin: 0;"></h6>
      </label>
      </div>
    </div>
    <div class="d-flex">
      <div class="form-check">
        <input
          class="form-check-input"
          type="radio"
          name="flexRadioDefault"
          id="flexRadioDefault4"
          value="1:30 PM - 2:30 PM"
        />
        <label
        style="padding-right: 113px;"
          class="form-check-label"
          for="timeSlot4"
        >
        1:30 PM - 2:30 PM
        </label>
        <label
        class="form-check-label"
        for="flexRadioDefault2"
      >
      <h6 style="font-size: 14px; color: green; padding:0; margin: 0;"></h6>
      </label>
      </div>
    </div>
    <div class="d-flex">
     
      <div class="form-check">
        <input
          class="form-check-input"
          type="radio"
          name="flexRadioDefault"
          id="flexRadioDefault5"
          value="3:00 PM - 4:00 PM"
        />
        <label
        style="padding-right: 115px;"
          class="form-check-label"
          for="timeSlot5"
        >
          3:00 PM - 4:00 PM
        </label>
        <label
        class="form-check-label"
        for="flexRadioDefault2"
      >
      <h6 style="font-size: 14px; color: green; padding:0; margin: 0;"></h6>
      </label>
      </div>
    </div>
    <div class="d-flex">
     
      <div class="form-check">
        <input
          class="form-check-input"
          type="radio"
          name="flexRadioDefault"
          id="flexRadioDefault6"
          value="3:30 PM - 4:30 PM"
        />
        <label
        style="padding-right: 115px;"
          class="form-check-label"
          for="timeSlot6"
        >
          4:30 PM - 5:30 PM
        </label>
        <label
        class="form-check-label"
        for="flexRadioDefault2"
      >
      <h6 style="font-size: 14px; color: green; padding:0; margin: 0;"></h6>
      </label>
      </div>
    </div>

    </div>
  </div>

</div>

</div>

</div>

</div>
<button type="submit" id="submitBtn" style="background: #1572e8!important; 
border: none;  
position: absolute;
top:600px;
right: 65px;
" class="btn btn-primary">Submit</button>

</form>
<?php require_once 'footer.php'?>

  <!-- container -->
    <script src="../assets/js/calendar.js"></script>

    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"
      integrity="sha384-KyZXEAg3QhqLMpG8r+8auK+4szKfEFbpLHsTf7iJgD/+ub2oU"
      crossorigin="anonymous"
    ></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.js"></script>
    <!--   Core JS Files   -->
    <script src="../assets/js/core/jquery-3.7.1.min.js"></script>
    <script src="../assets/js/core/popper.min.js"></script>
    <script src="../assets/js/core/bootstrap.min.js"></script>

    <!-- jQuery Scrollbar -->
    <script src="../assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js"></script>

    <!-- Chart JS -->
    <script src="../assets/js/plugin/chart.js/chart.min.js"></script>

    <!-- jQuery Sparkline -->
    <script src="../assets/js/plugin/jquery.sparkline/jquery.sparkline.min.js"></script>

    <!-- Sweet Alert -->
    <script src="../assets/js/plugin/sweetalert/sweetalert.min.js"></script>

    <!-- Kaiadmin JS -->
    <script src="../assets/js/kaiadmin.min.js"></script>
  </body>
</html>