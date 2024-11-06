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
    <title>OTP Verification</title>
    <link rel="stylesheet" href="../../assets/styles.css"> <!-- Add your styles here -->
</head>
<body>
    <div class="otp-container">
        <h2>OTP Verification</h2>
        <p>Please enter the OTP sent to your email.</p>
        <?php
// Display error or success messages
if ($errorMessage) {
    echo "<p style='color:red;'>$errorMessage</p>";
}
?>
    

        <form action="../../Controller/verify_con.php" method="POST">
            <label for="otp">OTP Code:</label>
            <input type="text" id="otp" name="otp" >
            <button type="submit" name="verify_otp">Verify OTP</button>
        </form>
        <br>
        <form id="request_new_otp" method="POST" action="../../Controller/verify_con.php">
        <button type="submit" name="request_new_otp">Request New OTP</button>
    </div>
    </form>


    <style>
        /* Inline styling for simplicity, replace with your CSS file as needed */
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .otp-container {
            width: 100%;
            max-width: 400px;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
            text-align: center;
        }
        h2 {
            margin-top: 0;
        }
        .error-message {
            color: red;
            margin-bottom: 10px;
        }
        input[type="text"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
    </style>
</body>
</html>
