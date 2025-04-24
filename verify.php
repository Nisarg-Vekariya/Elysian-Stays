<?php
// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$servername = "localhost";
$username = "root"; // Replace with your database username
$password = ""; // Replace with your database password
$dbname = "Elysian_Stays"; // Replace with your database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Check if the token is provided in the URL
if (isset($_GET['token'])) {
    $token = htmlspecialchars($_GET['token']);

    // Fetch the user with the given token
    $sql = "SELECT * FROM users WHERE token = ? AND status = 'inactive'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        // Token is valid, activate the user
        $user = $result->fetch_assoc();
        $userId = $user['id'];

        // Update the user's status to 'active'
        $update_sql = "UPDATE users SET status = 'active', token = NULL WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("i", $userId);

        if ($update_stmt->execute()) {
            $success_message = "Your email has been verified successfully! You can now log in.";
        } else {
            $error_message = "Error verifying your email. Please try again.";
        }
        $update_stmt->close();
    } else {
        $error_message = "Invalid or expired verification link.";
    }
    $stmt->close();
} else {
    $error_message = "No verification token provided.";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification - Elysian Stays</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/font-awesome.css" rel="stylesheet">
    <style>
        :root {
            --theme-color: #ad8b3a;
            --theme-hover: #8c6f2e;
        }

        body {
            background-color: #f8f9fa;
            font-family: 'Arial', sans-serif;
        }

        .verification-container {
            max-width: 500px;
            margin: 0 auto;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            text-align: center;
        }

        .alert {
            margin-bottom: 1rem;
        }

        .btn-theme {
            background-color: var(--theme-color);
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 5px;
            transition: background-color 0.3s ease;
            color: white;
        }

        .btn-theme:hover {
            background-color: var(--theme-hover);
        }
    </style>
</head>
<body>
    <div class="container mt-5 mb-5">
        <div class="verification-container">
            <h2 class="mb-4" style="color: var(--theme-color);">Email Verification</h2>
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success">
                    <?php echo $success_message; ?>
                </div>
                <a href="login.php" class="btn btn-theme">Log In</a>
            <?php elseif (!empty($error_message)): ?>
                <div class="alert alert-danger">
                    <?php echo $error_message; ?>
                </div>
                <a href="signup_hotel.php" class="btn btn-theme">Sign Up Again</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js" integrity="sha256-oP6HI9z1XaZNBrJURtCoUT5SUnxFr8s3BzRl+cbzUq8=" crossorigin="anonymous"></script>
    <script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>