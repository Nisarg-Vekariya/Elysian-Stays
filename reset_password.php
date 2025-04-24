<?php
session_start();

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once("db_connect.php");

// Initialize variables
$error_message = "";
$success_message = "";

// Check if the token is provided in the URL
if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Verify the token
    $sql = "SELECT * FROM users WHERE reset_token = ? AND reset_token_expiry > NOW()";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        // Handle the password reset form submission
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['new_password']) && isset($_POST['confirm_password'])) {
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];

            // Validate the new password
            if ($new_password === $confirm_password) {
                // Hash the new password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                // Update the user's password and clear the reset token
                $update_sql = "UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?";
                $stmt = $conn->prepare($update_sql);
                $stmt->bind_param("si", $hashed_password, $user['id']);

                if ($stmt->execute()) {
                    $success_message = "Your password has been reset successfully. You can now <a href='login.php'>login</a> with your new password.";
                } else {
                    $error_message = "Error updating your password. Please try again.";
                }
            } else {
                $error_message = "Passwords do not match.";
            }
        }
    } else {
        $error_message = "Invalid or expired reset token.";
    }
} else {
    $error_message = "No reset token provided.";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/font-awesome.css" rel="stylesheet">
    <link rel="stylesheet" href="css/login.css">
    <!-- jQuery -->
    <script src="js/jquery-3.6.4.min.js"></script>
    <!-- jQuery Validation -->
    <script src="js/jquery.validate.min.js"></script>
    <script src="js/additional-methods.min.js"></script>
</head>

<body>
    <div class="container1">
        <div class="left-section">
            <button class="back-button" onclick="window.location.href='index.php'">
                <img src="Images/Fallback.svg" width="50px" height="50px" />
            </button>
            <h1 class="animated-heading">Elysian Stays<span class="blinking-cursor">|</span></h1>
            <p class="description">
                Reset your password to continue your journey with Elysian Stays.
            </p>

            <!-- Display Error/Success Messages -->
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>

            <!-- Reset Password Form -->
            <?php if (empty($success_message) && empty($error_message)): ?>
                <form id="resetPasswordForm" class="login-form" method="POST" action="">
                    <input type="password" id="new_password" name="new_password" placeholder="New Password" class="input-field">
                    <div class="error-message" id="new_password-error"></div>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" class="input-field">
                    <div class="error-message" id="confirm_password-error"></div>
                    <button type="submit" class="submit-button">Reset Password</button>
                </form>
            <?php endif; ?>
        </div>

        <div class="right-section">
            <img src="Images/login1.jpg" alt="travel_pics" class="transition-image">
            <img src="Images/login2.jpg" alt="travel_pics" class="transition-image">
            <img src="Images/login3.jpg" alt="travel_pics" class="transition-image">
            <img src="Images/login4.jpg" alt="travel_pics" class="transition-image">
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Custom validation method for strong password
            $.validator.addMethod("strongPassword", function(value, element) {
                return this.optional(element) || /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/.test(value);
            }, "Password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, and one number.");

            // Initialize reset password form validation
            $("#resetPasswordForm").validate({
                rules: {
                    new_password: {
                        required: true,
                        strongPassword: true,
                    },
                    confirm_password: {
                        required: true,
                        equalTo: "#new_password",
                    },
                },
                messages: {
                    new_password: {
                        required: "Please enter a new password.",
                    },
                    confirm_password: {
                        required: "Please confirm your new password.",
                        equalTo: "Passwords do not match.",
                    },
                },
                submitHandler: function(form) {
                    form.submit();
                },
            });
        });
    </script>
</body>

</html>