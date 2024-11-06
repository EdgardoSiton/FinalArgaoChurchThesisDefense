<?php
session_start();

require_once '../../Model/staff_mod.php';
require_once '../../Model/db_connection.php';
require_once '../../Model/citizen_mod.php';
$userManager = new Staff($conn);
$citizen = new Citizen($conn);
$pendingCount = $userManager->countPendingAppointments();
$MasspendingCount = $userManager->countPendingMassAppointments();
$currentUsers = $userManager->getCurrentUsers();
$approvedRegistrations = $userManager->getApprovedRegistrations();
$pendingRequestCount = $userManager->countPendingRequestForms();
$pendingCitizenCount = $userManager->countPendingCitizenAccounts();
$countApprovePriestForms = $userManager->countApprovePriestForms();
$approvedCount = count($approvedRegistrations);
$scheduleDate = $_SESSION['selectedDate'] ?? null;
$startTime = $_SESSION['startTime'] ?? null;
$endTime = $_SESSION['endTime'] ?? null;

if ($scheduleDate && $startTime && $endTime) {
    // No conversion needed; just use the existing time formats
    // Fetch available priests based on the current start and end time
    $priests = $citizen->getAvailablePriests($scheduleDate, $startTime, $endTime);
} else {
    $priests = []; // Default to empty if no date/time is set
    
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
    <script>
function navigateToEvent() {
    const selectedEvent = document.getElementById('event_filter').value;
    if (selectedEvent) {
        window.location.href = selectedEvent; // Redirects the user to the selected report page.
    }
}

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

    <!-- CSS Files -->
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="../assets/css/plugins.min.css" />
    <link rel="stylesheet" href="../assets/css/kaiadmin.min.css" />

    <!-- CSS Just for demo purpose, don't include it in your project -->
    <link rel="stylesheet" href="../assets/css/demo.css" />
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
  </head>
  <body>
  <?php  require_once 'sidebar.php'?>
      <!-- End Sidebar -->

      <div class="main-panel">
      <?php  require_once 'header.php'?>
      <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Add Priest for Mass</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="modalForm">
                <div class="modal-body">
                    <input type="hidden" name="addcalendar" value="addcalendar">
<div class="form-group">
    <label for="eventDate">Event Date</label>
    <input type="date" class="form-control" id="eventDate" name="cal_date" placeholder="Enter event date" required min="<?php echo date('Y-m-d'); ?>">
</div>

<div class="form-group">
    <label for="startTime">Start Time</label>
    <select class="form-control" id="startTime" name="startTime" required onchange="handleStartTime()">
        <option value="" disabled selected>Select Start Time</option>
        <?php
        // Define available start times
        $daySlots = ['08:30', '10:00', '11:30', '13:30', '15:00', '16:30']; // Day slots (auto 1 hour)
        $eveningMorningSlots = ['18:00', '19:00', '20:00', '21:00', '22:00', '23:00', '00:00', '1:00', '2:00', '3:00', '4:00', '5:00', '6:00', '7:00']; // Flexible slots

        foreach ($daySlots as $startTime) {
            echo '<option value="' . $startTime . '" data-type="day">' .$startTime. '</option>';
        }

        foreach ($eveningMorningSlots as $startTime) {
            echo '<option value="' . $startTime . '" data-type="day">' .$startTime. '</option>';
        }
        ?>
    </select>
</div>


<div class="form-group" id="endTimeGroup" style="display:none;">
    <label for="endTime">End Time</label>
    <input type="text" class="form-control" id="endTime" name="endTime" readonly>
</div>

<div class="form-group" id="flexibleEndTimeGroup" style="display:none;">
  
<input type="time" class="form-control" id="endTime" name="endTime" >

    </select>
</div>
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
</div>



                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="submitEvent">Add Event</button>
                </div>
            </form>
        </div>
    </div>
</div>

        <div class="container">
          <div class="page-inner">
          <select id="event_filter" name="event_filter" onchange="navigateToEvent()">
    <option value="">Generate Seminar Report</option>
    <option value="generatereport.php">Baptism</option>
    <option value="weddinggeneratereport.php">Wedding</option>
</select>

            <div
              class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4"
            >
              <div>
                <h3 class="fw-bold mb-3">Staff Dashboard</h3>
        
                <button class="btn btn-primary btn-round" type="button" onclick="window.location.href='FillScheduleForm.php?type=RequestForm'">
       Inside Request Form
    </button>
    
             
               <button class="btn btn-primary btn-round" type="button" onclick="window.location.href='FillRequestSchedule.php?type=RequestForm'">
        Outside Request Form
    </button>
     
    <button style="position: absolute; top: 85px; right: 35px;" type="button" class="btn btn-primary btn-round" data-toggle="modal" data-target="#myModal">
 Add Priest for Mass
</button>


              </div>
              <div class="ms-md-auto py-2 py-md-0">
                
    <div class="dropdown">
        <button class="btn btn-primary btn-round dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
            Add Walkin Schedule
        </button>
        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
            <li><a class="dropdown-item" href="FillScheduleForm.php?type=baptism">Baptism</a></li>
            <li><a class="dropdown-item" href="FillScheduleForm.php?type=confirmation">Confirmation</a></li>
            <li><a class="dropdown-item" href="FillScheduleForm.php?type=Funeral">Funeral</a></li>
            <li><a class="dropdown-item" href="FillScheduleForm.php?type=Wedding">Wedding</a></li>

        </ul>
        
    </div>
    
    
</div>


            </div>
            
            <div class="row">
              <div class="col-sm-6 col-md-3">
                <div class="card card-stats card-round">
                  <div class="card-body">
                    <div class="row align-items-center">
                      <div class="col-icon">
                        <div
                          class="icon-big text-center icon-primary bubble-shadow-small"
                        >
                          <i class="fas fa-users"></i>
                        </div>
                      </div>
                      <div class="col col-stats ms-3 ms-sm-0">
                        <div class="numbers">
                        <div class="card-category">Registered Account</div>
        <h4 class="card-title"><?php echo $approvedCount; ?></h4>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-sm-6 col-md-3">
                <div class="card card-stats card-round">
                  <div class="card-body">
                    <div class="row align-items-center">
                      <div class="col-icon">
                        <div
                          class="icon-big text-center icon-info bubble-shadow-small"
                        >
                          <i class="far fa-calendar-check
                          "></i>
                        </div>
                      </div>
                      <div class="col col-stats ms-3 ms-sm-0">
                        <div class="numbers">
                        <p class="card-category">Event Scheduling Waiting Approval</p>
<h4 class="card-title"><?php echo $pendingCount; ?></h4>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-sm-6 col-md-3">
                <div class="card card-stats card-round">
                  <div class="card-body">
                    <div class="row align-items-center">
                      <div class="col-icon">
                        <div
                          class="icon-big text-center icon-info bubble-shadow-small"
                        >
                          <i class="far fa-calendar-check
                          "></i>
                        </div>
                      </div>
                      <div class="col col-stats ms-3 ms-sm-0">
                        <div class="numbers">
                        <p class="card-category">EventMass Scheduling Waiting Approval</p>
<h4 class="card-title"><?php echo $MasspendingCount; ?></h4>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-sm-6 col-md-3">
                <div class="card card-stats card-round">
                  <div class="card-body">
                    <div class="row align-items-center">
                      <div class="col-icon">
                        <div
                          class="icon-big text-center icon-success bubble-shadow-small"
                        >
                          <i class="fas fa-th-large"></i>
                        </div>
                      </div>
                      <div class="col col-stats ms-3 ms-sm-0">
                        <div class="numbers">
                        <p class="card-category">RequestForm Scheduling Pending</p>
<h4 class="card-title"><?php echo number_format($pendingRequestCount); ?></h4>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-sm-6 col-md-3">
                <div class="card card-stats card-round">
                  <div class="card-body">
                    <div class="row align-items-center">
                      <div class="col-icon">
                        <div
                          class="icon-big text-center icon-secondary bubble-shadow-small"
                        >
                          <i class="fas fa-dice-two"></i>
                        </div>
                      </div>
                      <div class="col col-stats ms-3 ms-sm-0">
                        <div class="numbers">
                        <p class="card-category">Citizen Pending Account</p>
<h4 class="card-title"><?php echo number_format($pendingCitizenCount); ?></h4>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-sm-6 col-md-3">
                <div class="card card-stats card-round">
                  <div class="card-body">
                    <div class="row align-items-center">
                      <div class="col-icon">
                        <div
                          class="icon-big text-center icon-secondary bubble-shadow-small"
                        >
                          <i class="fas fa-dice-two"></i>
                        </div>
                      </div>
                      <div class="col col-stats ms-3 ms-sm-0">
                        <div class="numbers">
                        <p class="card-category">Priest Has been approved .Approved their Schedule Now</p>
<h4 class="card-title"><?php echo number_format($countApprovePriestForms); ?></h4>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            
           
            
          
            <div class="row">
              <div class="col-md-4">
                <div class="card card-round">
                  <div class="card-body">
                    <div class="card-head-row card-tools-still-right">
                      <div class="card-title">New Citizen Pending This Week</div>
                      <div class="card-tools">
                        <div class="dropdown">
            
                       
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="card-list py-4">
    <?php foreach ($currentUsers as $user): ?>
        <div class="item-list">
            <div class="avatar">
                <span class="avatar-title rounded-circle border border-white bg-primary">
                    <?php echo strtoupper(substr($user['fullname'], 0, 2)); ?>
                </span>
            </div>
            <div class="info-user ms-3">
                <div class="username"><?php echo htmlspecialchars($user['fullname']); ?></div>
                <div class="status"><?php echo htmlspecialchars($user['email']); ?></div>
            </div>
            <button class="btn btn-icon btn-link op-8 me-1">
        
            </button>
            <button class="btn btn-icon btn-link btn-danger op-8">
            
            </button>
        </div>
    <?php endforeach; ?>
</div>

                  </div>
                </div>
              </div>
      
            </div>
          </div>
        </div>


     
    </div>
    <!--   Core JS Files   -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<!-- Popper.js (required for Bootstrap 4) -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<!-- Bootstrap JS -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="../assets/js/core/jquery-3.7.1.min.js"></script>
    <script src="../assets/js/core/popper.min.js"></script>
    <script src="../assets/js/core/bootstrap.min.js"></script>

    <!-- jQuery Scrollbar -->
    <script src="../assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js"></script>

    <!-- Chart JS -->
    <script src="../assets/js/plugin/chart.js/chart.min.js"></script>

    <!-- jQuery Sparkline -->
    <script src="../assets/js/plugin/jquery.sparkline/jquery.sparkline.min.js"></script>

    <!-- Chart Circle -->
    <script src="../assets/js/plugin/chart-circle/circles.min.js"></script>

    <!-- Datatables -->
    <script src="../assets/js/plugin/datatables/datatables.min.js"></script>


    <!-- jQuery Vector Maps -->
    <script src="../assets/js/plugin/jsvectormap/jsvectormap.min.js"></script>
    <script src="../assets/js/plugin/jsvectormap/world.js"></script>

    <!-- Sweet Alert -->
    <script src="../assets/js/plugin/sweetalert/sweetalert.min.js"></script>

    <!-- Kaiadmin JS -->
    <script src="../assets/js/kaiadmin.min.js"></script>

    <!-- Kaiadmin DEMO methods, don't include it in your project! -->
    <script src="../assets/js/setting-demo.js"></script>
    <script src="../assets/js/demo.js"></script>
    <!-- Include SweetAlert2 CSS and JS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
            document.addEventListener('DOMContentLoaded', function() {
    <?php
    if (isset($_SESSION['status']) && $_SESSION['status'] == 'successs') {
        echo "Swal.fire({
            icon: 'success',
            title: 'Form submitted successfully!',
            text: 'All set! Your request has been processed.',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });";
        unset($_SESSION['status']);
    }
    ?>
});
      document.addEventListener('DOMContentLoaded', function() {
    <?php
    if (isset($_SESSION['status']) && $_SESSION['status'] == 'success') {
        echo "Swal.fire({
            icon: 'success',
            title: 'Form submitted successfully!',
            text: 'Your submission has been received and is awaiting approval for priest.',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });";
        unset($_SESSION['status']);
    }
    ?>
});
$(document).ready(function() {
    $('#submitEvent').on('click', function() {
        // Gather form data
        var formData = {
            addcalendar: $('input[name="addcalendar"]').val(),
            cal_date: $('#eventDate').val(),
            startTime: $('#startTime').val(),
            endTime: $('#endTime').val() || $('#flexibleEndTime').val(),
            eventType: $('#eventType').val(),
        };

        // Validation check: ensure all fields are filled
        if (!formData.cal_date) {
            Swal.fire({
                icon: 'error',
                title: 'Event Date Missing',
                text: 'Please select a valid event date.',
            });
            return;
        }

        if (!formData.startTime) {
            Swal.fire({
                icon: 'error',
                title: 'Start Time Missing',
                text: 'Please select a start time.',
            });
            return;
        }

        if (!formData.eventType) {
            Swal.fire({
                icon: 'error',
                title: 'Priest Selection Missing',
                text: 'Please select a priest for this event.',
            });
            return;
        }

        // Send AJAX request
        $.ajax({
            type: 'POST',
            url: '../../Controller/insert_mass_con.php', // The PHP file that will handle the database insertion
            data: formData,
            success: function(response) {
                // Hide the modal
                $('#myModal').modal('hide'); // Assuming your modal has the ID 'myModal'

                // Automatically refresh the page after hiding the modal
                location.reload(); // Refresh the page
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'An error occurred while adding the event.',
                });
            }
        });
    });
});

function handleStartTime() {
    const startTimeSelect = document.getElementById('startTime');
    const selectedOption = startTimeSelect.options[startTimeSelect.selectedIndex];
    const startTime = startTimeSelect.value;

    const endTimeGroup = document.getElementById('endTimeGroup');
    const endTimeInput = document.getElementById('endTime');
    
    const flexibleEndTimeGroup = document.getElementById('flexibleEndTimeGroup');
    
    if (selectedOption.dataset.type === "day") {
        // Automatically set the end time for day slots (1 hour later)
        const [hours, minutes] = startTime.split(':');
        const startDate = new Date();
        startDate.setHours(parseInt(hours), parseInt(minutes), 0, 0);
        
        const endDate = new Date(startDate);
        endDate.setHours(endDate.getHours() + 1);
        
        // Get hours and minutes in military format without leading zeros
        const endHours = endDate.getHours(); // 0-23
        const endMinutes = endDate.getMinutes(); // 0-59
        
        // Format the end time
        const endTime = `${endHours}:${endMinutes < 10 ? '0' + endMinutes : endMinutes}`; // Add leading zero if minutes < 10
        
        // Show the end time field and set value
        endTimeInput.value = endTime;
        endTimeGroup.style.display = 'block';
        flexibleEndTimeGroup.style.display = 'none';
    } else {
        // Show the flexible end time dropdown for evening and morning slots
        endTimeGroup.style.display = 'none';
        flexibleEndTimeGroup.style.display = 'block';
    }
}

function fetchAvailablePriests() {
    const scheduleDate = document.getElementById('eventDate').value;
    let startTime = document.getElementById('startTime').value;
    let endTime = document.getElementById('endTime').value;


    if (scheduleDate && startTime && endTime) {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', '../../Controller/get_available_priests.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function () {
            if (xhr.status === 200) {
                const priests = JSON.parse(xhr.responseText);
                const eventTypeSelect = document.getElementById('eventType');
                eventTypeSelect.innerHTML = '<option value="" disabled selected>Select Priest</option>'; // Reset options
                
                priests.forEach(priest => {
                    const option = document.createElement('option');
                    option.value = priest.citizend_id;
                    option.textContent = priest.fullname;
                    eventTypeSelect.appendChild(option);
                });
            }
        };
        xhr.send(`date=${scheduleDate}&startTime=${startTime}&endTime=${endTime}`);
    }
}

// Attach the fetchAvailablePriests function to the event listeners
document.getElementById('eventDate').addEventListener('change', fetchAvailablePriests);
document.getElementById('startTime').addEventListener('change', fetchAvailablePriests);
document.getElementById('endTime').addEventListener('change', fetchAvailablePriests);
      $("#lineChart").sparkline([102, 109, 120, 99, 110, 105, 115], {
        type: "line",
        height: "70",
        width: "100%",
        lineWidth: "    2",
        lineColor: "#177dff",
        fillColor: "rgba(23, 125, 255, 0.14)",
      });

      $("#lineChart2").sparkline([99, 125, 122, 105, 110, 124, 115], {
        type: "line",
        height: "70",
        width: "100%",
        lineWidth: "2",
        lineColor: "#f3545d",
        fillColor: "rgba(243, 84, 93, .14)",
      });

      $("#lineChart3").sparkline([105, 103, 123, 100, 95, 105, 115], {
        type: "line",
        height: "70",
        width: "100%",
        lineWidth: "2",
        lineColor: "#ffa534",
        fillColor: "rgba(255, 165, 52, .14)",
      });
    </script>
  </body>
</html>
