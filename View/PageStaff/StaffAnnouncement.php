<?php
session_start();
$email = $_SESSION['email'];
$nme = $_SESSION['fullname'];
$regId = $_SESSION['citizend_id'];
require_once '../../Model/staff_mod.php';
require_once '../../Model/db_connection.php';
require_once '../../Model/citizen_mod.php';
$staff = new Staff($conn);
$citizen = new Citizen($conn);
$announcements = $staff->getAnnouncements();
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
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
    <!-- Fonts and icons -->
    <script src="../assets/js/plugin/webfont/webfont.min.js"></script>
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

    <!-- CSS Files -->
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="../assets/css/plugins.min.css" />
    <link rel="stylesheet" href="../assets/css/kaiadmin.min.css" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<!-- Bootstrap JS -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>

    <!-- CSS Just for demo purpose, don't include it in your project -->
    <link rel="stylesheet" href="../assets/css/demo.css" />
    <style>
textarea.form-control {
    min-height: calc(1.5em + 10rem + calc(var(--bs-border-width) * 2));
    width: 100%; /* or a specific width like 400px */
}
.modal-dialog {
    max-width: 800px; /* Adjust this width as necessary */
}

</style>
  </head>
  <body>
  <div class="modal fade" id="editAnnouncementModal" tabindex="-1" role="dialog" aria-labelledby="editAnnouncementLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editAnnouncementLabel">Edit Announcement</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="editAnnouncementForm">
                <input type="hidden" name="action" value="update"> 
                    <input type="hidden" id="announcement_id" name="announcement_id">
                    <div class="form-group">
                        <label for="speaker_ann">Speaker</label>
                        <input type="text" class="form-control" id="speaker_ann" name="speaker_ann" required>
                    </div>
                    <div class="form-group">
                        <label for="title">Title</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="capacity">Capacity</label>
                        <input type="number" class="form-control" id="capacity" name="capacity" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveChanges">Save changes</button>
            </div>
        </div>
    </div>
</div>

  <?php  require_once 'sidebar.php'?>
      <div class="main-panel">
      <?php  require_once 'header.php'?>
        <div class="container">
            <div class="page-inner">
                <div
                  class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4"
                >
                  <div>
                    <h3 class="fw-bold mb-3">Staff Announcement</h3>
                  </div>
                  <div class="ms-md-auto py-2 py-md-0">
                    <a href="FillScheduleForm.php?type=Announcement"   class="btn btn-primary btn-round">Add Announcement</a>
                    

                  </div>
                </div>
                <div class="row">
    <?php foreach ($announcements as $announcement): ?>
        <div class="col-md-4">
            <div class="card card-post card-round">
                <div class="card-body">
                <h5>
                <select onchange="handleAction(this, '<?php echo htmlspecialchars($announcement['announcement_id']); ?>')">
    <option value="" disabled selected>Actions</option>
    <option value="edit">Edit</option>
    <option value="delete">Delete</option>
</select>
        </h5>
                    <div class="separator-solid"></div>
                    
                    <p class="card-category text-info mb-1">
                  
                    <h3 class="card-title">
                        <a href="#">Speaker:<?php echo htmlspecialchars($announcement['speaker_ann']) ?></a>
                    </h3>
                    <h3 class="card-title">
                        <a href="#">Priest:<?php echo htmlspecialchars($announcement['fullname']) ?></a>
                    </h3>
                    <a href="#">
                        Event Date: <?php 
                            $date = htmlspecialchars(date('F j, Y', strtotime($announcement['schedule_date'])));
                            $startTime = htmlspecialchars(date('g:i a', strtotime($announcement['schedule_start_time'])));
                            $endTime = htmlspecialchars(date('g:i a', strtotime($announcement['schedule_end_time'])));
                            echo "$date - $startTime - $endTime";
                        ?>
                    </a>
                    <br>
                    <a href="#">
                        Seminar Date: <?php 
                            $date = htmlspecialchars(date('F j, Y', strtotime($announcement['seminar_date'])));
                            $startTime = htmlspecialchars(date('g:i a', strtotime($announcement['seminar_start_time'])));
                            $endTime = htmlspecialchars(date('g:i a', strtotime($announcement['seminar_end_time'])));
                            echo "$date - $startTime - $endTime";
                        ?>
                    </a>
                    </p>
                    <h3 class="card-title">
                        <a href="#"><?php echo htmlspecialchars($announcement['title']) ?></a>
                    </h3>
                    <p class="card-text">
                        <?php echo htmlspecialchars($announcement['description']) ?>
                    </p>
                    <h3 class="card-title">
                        <a href="#">Capacity: <?php echo htmlspecialchars($announcement['capacity']) ?></a>
                    </h3>

                    <?php
                    // Determine the form link based on event type
                    $eventType = htmlspecialchars($announcement['event_type']);
                    $formLink = '#'; // Default link in case none match

                    // Set form link based on event type
                    switch ($eventType) {
                        case 'MassBaptism':
                            $formLink = 'MassFillBaptismForm.php';
                            break;
                        case 'MassConfirmation':
                            $formLink = 'MassFillConfirmationForm.php';
                            break;
                        case 'MassMarriage':
                            $formLink = 'MassFillWeddingForm.php';
                            break;
                    }

                    // Check if capacity is zero
                    if ($announcement['capacity'] > 0) {
                        // Show Register button if capacity is available
                        echo '<a href="' . $formLink . '?announcement_id=' . htmlspecialchars($announcement['announcement_id']) . '" class="btn btn-primary btn-rounded btn-sm register-btn" data-announcement-id="' . htmlspecialchars($announcement['announcement_id']) . '">Register Now</a>';
                    } else {
                        // Show Fully Booked message in red if capacity is zero
                        echo '<p class="text-danger"><strong>Fully Booked</strong></p>';
                    }

                    // Add a "Print Seminar" button that navigates to the print page based on event type
                    if ($eventType == 'MassBaptism') {
                        echo '<a href="generatemassbaptismreport.php?announcement_id=' . htmlspecialchars($announcement['announcement_id']) . '" class="btn btn-secondary btn-rounded btn-sm">Print Seminar List</a>';
                    } elseif ($eventType == 'MassConfirmation') {
                        echo '<a href="generatemassconfirmationreport.php?announcement_id=' . htmlspecialchars($announcement['announcement_id']) . '" class="btn btn-secondary btn-rounded btn-sm">Print Seminar List</a>';
                    } elseif ($eventType == 'MassMarriage') {
                        echo '<a href="generatemassmarriagereport.php?announcement_id=' . htmlspecialchars($announcement['announcement_id']) . '" class="btn btn-secondary btn-rounded btn-sm">Print Seminar List</a>';
                    }
                    ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

</div>

    </div>
    
   
    <!-- jQuery, Popper.js, and Bootstrap JS -->
    <!-- jQuery first -->



<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<!-- Popper.js (required for Bootstrap 4) -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<!-- Bootstrap JS -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  <!-- Sweet Alert -->
  <script src="../assets/js/plugin/sweetalert/sweetalert.min.js"></script>
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

    <!-- Chart Circle -->
    <script src="../assets/js/plugin/chart-circle/circles.min.js"></script>

    <!-- Datatables -->
    <script src="../assets/js/plugin/datatables/datatables.min.js"></script>


    <!-- jQuery Vector Maps -->
    <script src="../assets/js/plugin/jsvectormap/jsvectormap.min.js"></script>
    <script src="../assets/js/plugin/jsvectormap/world.js"></script>


    <!-- Kaiadmin JS -->
    <script src="../assets/js/kaiadmin.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
      .dark-green-check-icon .swal2-success-line-tip,
.dark-green-check-icon .swal2-success-line-long {
    background-color: #1e7e34 !important; /* Darker green for the check lines */
}
.dark-green-check-icon .swal2-success-ring {
    border-color: #1e7e34 !important; /* Darker green for the ring */
}

    </style>
    <script>
function handleAction(select, announcementId) {
    const action = select.value; // Get the selected action

    if (action === "edit") {
        const announcements = <?php echo json_encode($announcements); ?>; // Fetch announcements from PHP
        const announcement = announcements.find(a => a.announcement_id == announcementId);

        if (announcement) {
            document.getElementById("announcement_id").value = announcement.announcement_id;
            document.getElementById("speaker_ann").value = announcement.speaker_ann;
            document.getElementById("title").value = announcement.title;
            document.getElementById("description").value = announcement.description;
            document.getElementById("capacity").value = announcement.capacity;

            // Show the modal
            $('#editAnnouncementModal').modal('show');
        }
    } else if (action === "delete") {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '../../Controller/updateannouncement.php', // Your PHP file handling the deletion
                    type: 'POST',
                    data: { announcement_id: announcementId, action: 'delete' },
                    success: function(response) {
                        Swal.fire(
                            'Deleted!',
                            'The announcement has been deleted.',
                            'success'
                        ).then(() => {
                            location.reload(); // Refresh page after deletion
                        });
                    },
                    error: function(xhr, status, error) {
                        Swal.fire('Error', 'There was a problem deleting the announcement.', 'error');
                    }
                });
            }
        });
    }
}

document.addEventListener("DOMContentLoaded", function() {
    document.getElementById("saveChanges").addEventListener("click", function() {
        Swal.fire({
            title: 'Are you sure?',
            text: "Do you want to save these changes?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, save changes!'
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData(document.getElementById("editAnnouncementForm"));
                
                fetch("../../Controller/updateannouncement.php", {
                    method: "POST",
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Saved!', 'Announcement updated successfully!', 'success')
                        .then(() => {
                            location.reload(); // Reload the page to see changes
                        });
                    } else {
                        Swal.fire('Error', 'Error updating announcement: ' + data.error, 'error');
                    }
                })
                .catch(error => {
                    console.error("Error:", error);
                    Swal.fire('Error', 'An unexpected error occurred.', 'error');
                });
            }
        });
    });
});


document.addEventListener('DOMContentLoaded', function() {
    <?php
    if (isset($_SESSION['status']) && $_SESSION['status'] == 'success') {
        echo "Swal.fire({
            icon: 'success',
            title: 'Form submitted successfully!',
            text: 'Please Check your Information.',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            customClass: {
                popup: 'wider-toast',
                icon: 'dark-green-check-icon'
            },
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
    if (isset($_SESSION['status']) && $_SESSION['status'] == 'successs') {
        echo "Swal.fire({
            icon: 'success',
            title: 'Form submitted successfully!',
            text: 'Has been Change.',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            customClass: {
                popup: 'wider-toast',
                icon: 'dark-green-check-icon'
            },
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
    if (isset($_SESSION['status']) && $_SESSION['status'] == 'deleted') {
        echo "Swal.fire({
            icon: 'success',
            title: 'Form submitted successfully!',
            text: 'Has been Deleted.',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            customClass: {
                popup: 'wider-toast',
                icon: 'dark-green-check-icon'
            },
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });";
        unset($_SESSION['status']);
    }
    ?>
});
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
      <script>
      //== Class definition
      var SweetAlert2Demo = (function () {
        //== Demos
        var initDemos = function () {
          //== Sweetalert Demo 1
        

        


          $("#alert_demo_6").click(function (e) {
            swal("Event added successfully!", {
              icon: "success",  
              buttons: false,
              timer: 1000,
            });
          });

       

        };

        return {
          //== Init
          init: function () {
            initDemos();
          },
        };
      })();

      //== Class Initialization
      jQuery(document).ready(function () {
        SweetAlert2Demo.init();
      });
    </script>
  </body>
</html>