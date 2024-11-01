<?php
session_start();
$errorMessage = isset($_SESSION['errorMessage']) ? $_SESSION['errorMessage'] : '';
unset($_SESSION['errorMessage']); // Clear the message after displaying it
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP</title>
</head>
<body>

<h1>Verify Your OTP</h1>

<form id="verifyForm" method="POST" action="Controller/otp_processing_con.php">
    <label for="otp">Enter OTP:</label>
    <input type="text" name="otp" required>
    <button type="submit">Verify OTP</button>
</form>

<form id="requestOtpForm" method="POST" action="Controller/otp_processing_con.php">
    <button type="submit" name="request_new_otp">Request New OTP</button>
</form>

<?php
// Display error or success messages
if ($errorMessage) {
    echo "<p style='color:red;'>$errorMessage</p>";
}
?>

<script>
  let isSubmitting = false; // Track form submissions
  let isUnloading = false; // Track if the page is unloading

  // Track form submissions
  document.getElementById("verifyForm").addEventListener("submit", function () {
      isSubmitting = true;
  });

  document.getElementById("requestOtpForm").addEventListener("submit", function () {
      isSubmitting = true;
  });

  // Listen for the beforeunload event
  window.addEventListener("beforeunload", function (event) {
      // If the user is not submitting a form and the page is unloading, send the delete request
      if (!isSubmitting) {
          isUnloading = true;
          navigator.sendBeacon('Controller/delete_account_con.php');
      }
  });

  // Optional: reset the unloading state when the page loads
  window.addEventListener("load", function () {
      if (isUnloading) {
          isUnloading = false; // Reset if the page is loaded
      }
  });
</script>

</body>
</html>
