<?php
session_start();
$email = $_SESSION['email'];
$nme = $_SESSION['fullname'];
$regId = $_SESSION['citizend_id'];
require_once '../../Model/staff_mod.php';
require_once '../../Model/db_connection.php';
$staff = new Staff($conn);
$statusFilter = isset($_GET['status_filter']) ? $_GET['status_filter'] : null;
$pendingItems = $staff->getRequestAppointment($statusFilter);

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
    <link rel="stylesheet" href="../css/table.css" />
    <!-- CSS Just for demo purpose, don't include it in your project -->
    <link rel="stylesheet" href="../assets/css/demo.css" />
  </head>
  <body>
  <?php require_once 'sidebar.php'?>
<div class="main-panel">
<?php require_once 'header.php'?>
<div class="container">
    <div class="page-inner">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Request Form Appointment Details</h4>
                </div>
                <form method="GET" action="StaffRequestForm.php">
             
             <h6><select id="event_filter" name="event_filter" onchange="this.form.submit()">
                     <option value="">All</option>
    <option value="Fiesta Mass" <?php echo (isset($_GET['event_filter']) && $_GET['event_filter'] === 'Fiesta Mass') ? 'selected' : ''; ?>>Fiesta Mass</option>
    <option value="Novena Mass" <?php echo (isset($_GET['event_filter']) && $_GET['event_filter'] === 'Novena Mass') ? 'selected' : ''; ?>>Novena Mass</option>
    <option value="Wake Mass" <?php echo (isset($_GET['event_filter']) && $_GET['event_filter'] === 'Wake Mass') ? 'selected' : ''; ?>>Wake Mass</option>
    <option value="Monthly Mass" <?php echo (isset($_GET['event_filter']) && $_GET['event_filter'] === 'Monthly Mass') ? 'selected' : ''; ?>>Monthly Mass</option>
    <option value="1st Friday Mass" <?php echo (isset($_GET['event_filter']) && $_GET['event_filter'] === '1st Friday Mass') ? 'selected' : ''; ?>>1st Friday Mass</option>
    <option value="Cemetery Chapel Mass" <?php echo (isset($_GET['event_filter']) && $_GET['event_filter'] === 'Cemetery Chapel Mass') ? 'selected' : ''; ?>>Cemetery Chapel Mass</option>
    <option value="Baccalaureate Mass" <?php echo (isset($_GET['event_filter']) && $_GET['event_filter'] === 'Baccalaureate Mass') ? 'selected' : ''; ?>>Baccalaureate Mass</option>
    <option value="Anointing Of The Sick" <?php echo (isset($_GET['event_filter']) && $_GET['event_filter'] === 'Anointing Of The Sick') ? 'selected' : ''; ?>>Anointing of the Sick</option>
    <option value="Blessing" <?php echo (isset($_GET['event_filter']) && $_GET['event_filter'] === 'Blessing') ? 'selected' : ''; ?>>Blessing</option>
    <option value="Special Mass" <?php echo (isset($_GET['event_filter']) && $_GET['event_filter'] === 'Special Mass') ? 'selected' : ''; ?>>Special Mass</option>
      </select>
                 </h6>

                  <form method="GET" action="StaffRequestForm.php">
    <select id="status_filter" name="status_filter" onchange="this.form.submit()">
        <option value="">Default</option>
        <option value="CompletedPaid" <?php echo ($statusFilter === 'CompletedPaid') ? 'selected' : ''; ?>>Completed and Paid</option>
        <!-- Add other options as needed -->
    </select>
</form>
                <form method="POST" action="../../Controller/updatepayment_con.php">
                <div class="card-body">
                    <!-- Selected Rows Information -->
                    <div id="selected-info" class="alert alert-info" style="display:none;">
                        <span id="selected-count">0</span> row(s) selected
                        <button id="delete-btn" class="btn btn-danger" style="display:none;">
                            <i class="fa fa-trash"></i> Delete
                        </button>
                    </div>

              
    <div class="table-responsive">
<table id="multi-filter-select" class="display table table-striped table-hover">
    <thead>
        <tr>
            <th><input type="checkbox" id="select-all"></th>
            <th>ID NO.</th>
            <th>Request Category</th>
            <th>Request Person</th>
            <th>Request Appointment Date</th>
            <th>Request Appointment Time</th>
            <th>Request Address</th>
            <th>Request Phone Number</th>
            <th>Date of Follow Up</th>
            <th>Request Chapel</th>
            <th>Payable Amount</th>
            <th>Priest Name</th>
            <th>Event Status</th>
            <th>Payment Status</th>
        </tr>
    </thead>
    <tbody>
    <?php
                                    // Retrieve the selected event type from the GET request
                                    $eventFilter = isset($_GET['event_filter']) ? $_GET['event_filter'] : '';

                                    // Filter pending items based on the selected event type
                                    if (isset($pendingItems) && !empty($pendingItems)) {
                                        foreach ($pendingItems as $index => $item) {
                                            // Check if the event name matches the selected filter or if no filter is applied
                                            if ($eventFilter === '' || $item['req_category'] === $eventFilter) {
                                    ?>
                <tr>
                    <td>
                        <input type="checkbox" class="select-row" name="rappsched_ids[]" value="<?php echo htmlspecialchars($item['appsched_id']); ?>">
                    </td>
                    <td><?php echo htmlspecialchars($index + 1); ?></td>
                    <td><?php echo htmlspecialchars($item['req_category']); ?></td>
                    <td><?php echo htmlspecialchars($item['req_person']); ?></td>
                    <td><?php echo htmlspecialchars(date('Y/m/d', strtotime($item['date']))); ?></td>
                    <td><?php echo htmlspecialchars(date('g:i A', strtotime($item['start_time']))); ?></td>
                    <td><?php echo htmlspecialchars($item['req_address']); ?></td>
                    <td><?php echo htmlspecialchars($item['req_pnumber']); ?></td>
                    <td><?php echo htmlspecialchars($item['cal_date']); ?></td>
                    <td><?php echo htmlspecialchars($item['req_chapel']); ?></td>
                    <td><?php echo htmlspecialchars($item['payable_amount']); ?></td>
                    <td><?php echo htmlspecialchars($item['ref_number']); ?></td>
                 
                    <td>
                        <form method="POST" action="../../Controller/updatepayment_con.php">
                            <input type="hidden" name="rcappsched_id" value="<?php echo htmlspecialchars($item['appsched_id']); ?>">
                            <select name="rc_status" class="btn btn-xs <?php echo $item['c_status'] == 'Completed' ? 'btn-success' : 'btn-primary'; ?>" onchange="this.form.submit()">
                                <option value="Process" <?php echo $item['c_status'] == 'Process' ? 'selected' : ''; ?>>Process</option>
                                <option value="Completed" <?php echo $item['c_status'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                            </select>
                        </form>
                    </td>
                    <td>
                        <form method="POST" action="../../Controller/updatepayment_con.php">
                            <input type="hidden" name="rappsched_id" value="<?php echo htmlspecialchars($item['appsched_id']); ?>">
                            <select name="rp_status" class="btn btn-xs <?php echo $item['p_status'] == 'Paid' ? 'btn-success' : 'btn-primary'; ?>" onchange="this.form.submit()">
                                <option value="Paid" <?php echo $item['p_status'] == 'Paid' ? 'selected' : ''; ?>>Paid</option>
                                <option value="Unpaid" <?php echo $item['p_status'] == 'Unpaid' ? 'selected' : ''; ?>>Unpaid</option>
                            </select>
                        </form>
                    </td>
                </tr>
                <?php
                                            }
                                        }
                                    } else {
                                    ?>
            <tr>
                <td colspan="14">No pending Citizen found.</td>
            </tr>
            <?php } ?>
    </tbody>
</table>

    </div>

    <!-- Delete Button -->
    <button id="delete-btn" class="btn btn-danger" type="submit" style="display:none;">
        <i class="fa fa-trash"></i> Delete Selected
    </button>
</form>

            </div>
        </div>
    </div>
</div>



    <!--   Core JS Files   -->
    <script src="../assets/js/core/jquery-3.7.1.min.js"></script>
    <script src="../assets/js/core/popper.min.js"></script>
    <script src="../assets/js/core/bootstrap.min.js"></script>

    <!-- jQuery Scrollbar -->
    <script src="../assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js"></script>
    <!-- Datatables -->
    <script src="../assets/js/plugin/datatables/datatables.min.js"></script>
    <!-- Kaiadmin JS -->
    <script src="../assets/js/kaiadmin.min.js"></script>
    <!-- Kaiadmin DEMO methods, don't include it in your project! -->
    <script src="../assets/js/setting-demo2.js"></script>
    <script>
      document.getElementById('select-all').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.select-row');
    const deleteBtn = document.getElementById('delete-btn');
    checkboxes.forEach(checkbox => checkbox.checked = this.checked);
    toggleDeleteButton();
});

document.querySelectorAll('.select-row').forEach(checkbox => {
    checkbox.addEventListener('change', toggleDeleteButton);
});

function toggleDeleteButton() {
    const selectedCount = document.querySelectorAll('.select-row:checked').length;
    const deleteBtn = document.getElementById('delete-btn');
    if (selectedCount > 0) {
        deleteBtn.style.display = 'inline-block';
    } else {
        deleteBtn.style.display = 'none';
    }
}
      document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.select-row');
    const selectedInfo = document.getElementById('selected-info');
    const selectedCount = document.getElementById('selected-count');
    const deleteBtn = document.getElementById('delete-btn');
    let selectedRows = 0;

    checkboxes.forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            if (this.checked) {
                selectedRows++;
            } else {
                selectedRows--;
            }

            // Update the selected count display
            selectedCount.textContent = selectedRows;

            // Show the selected info bar if any rows are selected
            if (selectedRows > 0) {
                selectedInfo.style.display = 'block';
                deleteBtn.style.display = 'inline-block';
            } else {
                selectedInfo.style.display = 'none';
                deleteBtn.style.display = 'none';
            }
        });
    });

    deleteBtn.addEventListener('click', function() {
        const idsToDelete = [];
        checkboxes.forEach(function(checkbox) {
            if (checkbox.checked) {
                idsToDelete.push(checkbox.getAttribute('data-id'));
            }
        });

        if (idsToDelete.length > 0) {
            if (confirm('Are you sure you want to delete the selected items?')) {
                // Add your deletion logic here (e.g., an AJAX request to delete the selected rows)
                console.log('Deleting rows with IDs: ', idsToDelete);
                // Optionally, refresh the page or remove the selected rows from the table
            }
        }
    });
});

      $(document).ready(function () {
        $("#basic-datatables").DataTable({});

        $("#multi-filter-select").DataTable({
          pageLength: 5,
          initComplete: function () {
            this.api()
              .columns()
              .every(function () {
                var column = this;
                var select = $(
                  '<select class="form-select"><option value=""></option></select>'
                )
                  .appendTo($(column.footer()).empty())
                  .on("change", function () {
                    var val = $.fn.dataTable.util.escapeRegex($(this).val());

                    column
                      .search(val ? "^" + val + "$" : "", true, false)
                      .draw();
                  });

                column
                  .data()
                  .unique()
                  .sort()
                  .each(function (d, j) {
                    select.append(
                      '<option value="' + d + '">' + d + "</option>"
                    );
                  });
              });
          },
        });

        // Add Row
        $("#add-row").DataTable({
          pageLength: 5,
        });

        var action =
          '<td> <div class="form-button-action"> <button type="button" data-bs-toggle="tooltip" title="" class="btn btn-link btn-primary btn-lg" data-original-title="Edit Task"> <i class="fa fa-edit"></i> </button> <button type="button" data-bs-toggle="tooltip" title="" class="btn btn-link btn-danger" data-original-title="Remove"> <i class="fa fa-times"></i> </button> </div> </td>';

        $("#addRowButton").click(function () {
          $("#add-row")
            .dataTable()
            .fnAddData([
              $("#addName").val(),
              $("#addPosition").val(),
              $("#addOffice").val(),
              action,
            ]);
          $("#addRowModal").modal("hide");
        });
      });
    </script>
  </body>
</html>
