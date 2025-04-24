<?php
session_start();

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once("db_connect.php");

// Include PHPMailer
require 'vendor/autoload.php';

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Initialize variables
$login_error = "";
$reset_success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['username']) && isset($_POST['password'])) {
    $username = htmlspecialchars($_POST['username']);
    $password = htmlspecialchars($_POST['password']);

    // Fetch user from the database
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            // Check if user status is active
            if ($user['status'] === 'active') {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                // Redirect based on role
                if ($user['role'] == 'admin') {
                    header("Location: admin/index.php");
                } elseif ($user['role'] == 'hotel') {
                    header("Location: hotel/index.php");
                } else {
                    header("Location: index.php");
                }
                exit();
            } else {
                $login_error = "Please verify your email before logging in. Check your inbox for the verification link.";
            }
        } else {
            $login_error = "Invalid username or password.";
        }
    } else {
        $login_error = "Invalid username or password.";
    }
}

// Forgot Password Form Handling
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['resetEmail'])) {
    $email = htmlspecialchars($_POST['resetEmail']);

    // Check if the email exists in the database
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        // Generate a reset token
        $reset_token = bin2hex(random_bytes(50));
        $reset_token_expiry = date("Y-m-d H:i:s", strtotime("+1 day")); // Token expires in 1 day

        // Update the user's record with the reset token
        $update_sql = "UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE email = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("sss", $reset_token, $reset_token_expiry, $email);
        $stmt->execute();

        // Send reset email using PHPMailer
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);

        try {
            // Server settings for Gmail
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Gmail's SMTP server
            $mail->SMTPAuth = true;
            $mail->Username = 'use your mail'; // Your Gmail address
            $mail->Password = 'use your app password'; // Your Gmail app password
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption
            $mail->Port = 587; // TCP port to connect to

            // Recipients
            $mail->setFrom('use your mail', 'Elysian Stays'); // Sender email and name
            $mail->addAddress($email, $user['name']); // Recipient email and name

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset - Elysian Stays';
            $reset_link = "http://localhost/elysian-stays/reset_password.php?token=$reset_token"; // Replace with your domain
            $mail->Body = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>Password Reset - Elysian Stays</title>
    </head>
    <body style='font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;'>
        <div style='max-width: 600px; margin: auto; background: #ffffff; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);'>
            <div style='text-align: center; padding-bottom: 20px;'>
                <img src='https://i.ibb.co/4y1S02M/Elysian-Stays.png' alt='Elysian Stays' style='max-width: 150px;'>
            </div>
            <h2 style='color: #ad8b3a; text-align: center;'>Reset Your Password</h2>
            <p style='color: #333; font-size: 16px;'>Hi {$user['name']},</p>
            <p style='color: #555; font-size: 16px;'>We received a request to reset your password for your <strong>Elysian Stays</strong> account. Click the button below to reset your password:</p>
            <div style='text-align: center; margin: 20px 0;'>
                <a href='$reset_link' style='background-color: #ad8b3a; color: #ffffff; padding: 12px 20px; text-decoration: none; border-radius: 5px; font-size: 16px; display: inline-block;'>Reset Password</a>
            </div>
            <p style='color: #555; font-size: 16px;'>Or copy and paste the following link into your browser:</p>
            <p style='word-break: break-word; text-align: center;'><a href='$reset_link' style='color: #ad8b3a;'>$reset_link</a></p>
            <hr style='border: none; border-top: 1px solid #ddd; margin: 20px 0;'>
            <p style='color: #777; font-size: 14px; text-align: center;'>If you did not request this, please ignore this email or contact support if you have concerns.</p>
            <p style='color: #777; font-size: 14px; text-align: center;'>Best regards,<br><strong>Elysian Stays Team</strong></p>
        </div>
    </body>
    </html>
";

            $mail->AltBody = "Hi {$user['name']},\n\nWe received a request to reset your password for your Elysian Stays account. Click the link below to reset your password:\n\n$reset_link\n\nIf you did not request this, please ignore this email or contact support if you have concerns.\n\nBest regards,\nElysian Stays Team";

            $mail->send();
            $reset_success = "A password reset link has been sent to your email.";
        } catch (Exception $e) {
            $login_error = "Error sending reset email: " . $mail->ErrorInfo;
        }
    } else {
        $login_error = "No account found with that email address.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
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
                Welcome to Elysian Stays—where adventure, discovery, and extraordinary experiences await. Let us guide your journey!
            </p>

            <!-- Login Form -->
            <form id="loginForm" class="login-form" method="POST" action="">
                <?php if (!empty($login_error)): ?>
                    <div class="alert alert-danger"><?php echo $login_error; ?></div>
                <?php endif; ?>
                <input type="text" id="username" name="username" placeholder="Username" class="input-field">
                <div class="error-message" id="username-error"></div>
                <input type="password" id="password" name="password" placeholder="Password" class="input-field">
                <div class="error-message" id="password-error"></div>
                <a class="forgot-password-link" onclick="showForgotPasswordForm()">Forgot Password?</a>
                <button type="submit" class="submit-button">Login</button>
                <p class="already-signed-up">
                    Not signed up? <a href="signup.php">Sign Up</a>
                </p>
            </form>

            <!-- Forgot Password Form -->
            <form id="forgotPasswordForm" class="forgot-password-form d-none" method="POST" action="">
                <?php if (!empty($reset_success)): ?>
                    <div class="alert alert-success"><?php echo $reset_success; ?></div>
                <?php endif; ?>
                <input type="email" id="resetEmail" name="resetEmail" placeholder="Enter your email" class="input-field">
                <div class="error-message" id="resetEmail-error"></div>
                <button type="submit" class="submit-button">Reset Password</button>
                <a class="forgot-password-link" onclick="showLoginForm()">Back to Login</a>
            </form>
        </div>

        <div class="right-section">
            <img src="Images/login1.jpg" alt="travel_pics" class="transition-image">
            <img src="Images/login2.jpg" alt="travel_pics" class="transition-image">
            <img src="Images/login3.jpg" alt="travel_pics" class="transition-image">
            <img src="Images/login4.jpg" alt="travel_pics" class="transition-image">
        </div>
    </div>

    <script>
        // Functionality for toggling between login and forgot password forms
        const loginForm = document.getElementById('loginForm');
        const forgotPasswordForm = document.getElementById('forgotPasswordForm');

        function showForgotPasswordForm() {
            loginForm.classList.add('d-none');
            forgotPasswordForm.classList.remove('d-none');
        }

        function showLoginForm() {
            forgotPasswordForm.classList.add('d-none');
            loginForm.classList.remove('d-none');
        }

        // Image slider functionality
        const images = document.querySelectorAll('.transition-image');
        let currentImageIndex = 0;

        function showNextImage() {
            images[currentImageIndex].style.opacity = 0;
            currentImageIndex = (currentImageIndex + 1) % images.length;
            images[currentImageIndex].style.opacity = 1;
        }

        setInterval(showNextImage, 3000);

        $(document).ready(function() {
            // Custom validation method for username (letters and numbers only)
            $.validator.addMethod("usernameFormat", function(value, element) {
                return this.optional(element) || /^[A-Za-z0-9]+$/.test(value);
            }, "Username must contain only letters and numbers.");

            // Custom validation method for password (at least one uppercase, one lowercase, and one number)
            $.validator.addMethod("strongPassword", function(value, element) {
                return this.optional(element) || /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/.test(value);
            }, "Password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, and one number.");

            // Initialize login form validation
            $("#loginForm").validate({
                rules: {
                    username: {
                        required: true,
                        minlength: 4
                    },
                    password: {
                        required: true,
                        minlength: 8,
                        pattern: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/
                    },
                },
                messages: {
                    username: {
                        required: "Please enter your username.",
                    },
                    password: {
                        required: "Please enter your password.",
                    },
                },
                submitHandler: function(form) {
                    form.submit();
                },
            });

            // Initialize forgot password form validation
            $("#forgotPasswordForm").validate({
                rules: {
                    resetEmail: {
                        required: true,
                        email: true,
                    },
                },
                messages: {
                    resetEmail: {
                        required: "Please enter your email address.",
                        email: "Please enter a valid email address.",
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