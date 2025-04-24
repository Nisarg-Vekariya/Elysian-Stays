<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
    ]);
}

// Include database connection
require_once 'db_connect.php';
// Include PHPMailer
require 'vendor/autoload.php';

// Check if booking ID is provided
$booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;

if (!$booking_id) {
    header("Location: index.php");
    exit();
}

// Fetch booking details
$booking_query = "SELECT b.*, h.name as hotel_name, r.name as room_name, h.about_image, h.background_image 
                 FROM bookings b
                 JOIN hotels h ON b.hotel_id = h.id
                 JOIN rooms r ON b.room_id = r.id
                 WHERE b.id = ?";
$booking_stmt = $conn->prepare($booking_query);
$booking_stmt->bind_param("i", $booking_id);
$booking_stmt->execute();
$booking_result = $booking_stmt->get_result();
$booking = $booking_result->fetch_assoc();

// Redirect if booking not found
if (!$booking) {
    header("Location: index.php");
    exit();
}

// Send confirmation email to guest using PHPMailer
$email_sent = false;
try {
    // Create a new PHPMailer instance
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);

    // Server settings for Gmail
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com'; // Gmail's SMTP server
    $mail->SMTPAuth = true;
    $mail->Username = 'use your mail'; // Your Gmail address
    $mail->Password = 'use your app password'; // Your Gmail app password
    $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption
    $mail->Port = 587; // TCP port to connect to

    // Recipients
    $mail->setFrom('use your mail', 'Elysian Stays');
    $mail->addAddress($booking['guest_email'], $booking['guest_name']);

    // Create email message with HTML formatting
    $hotel_image = !empty($booking['background_image']) ? $booking['background_image'] : $booking['about_image'];

    // Content
    $mail->isHTML(true);
    $mail->Subject = "Booking Confirmation - Elysian Stays #" . $booking_id;
    $mail->Body = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Booking Confirmation</title>
        <style>
            @import url("https://fonts.googleapis.com/css2?family=Cinzel:wght@400..900&display=swap");
            @import url("https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap");
            
            body {
                font-family: "Inter", sans-serif;
                line-height: 1.6;
                color: #333;
                max-width: 650px;
                margin: 0 auto;
                background-color: #f8f8f8;
                padding: 0;
            }
            .header {
                background-color: #45443F;
                padding: 25px 0;
                text-align: center;
                border-top-left-radius: 8px;
                border-top-right-radius: 8px;
            }
            .header h1 {
                font-family: "Cinzel", serif;
                color: #ad8b3a;
                font-weight: 700;
                margin: 0;
                font-size: 32px;
                text-transform: uppercase;
                letter-spacing: 1px;
            }
            .header p {
                color: white;
                margin: 5px 0 0;
                font-size: 18px;
                font-weight: 300;
            }
            .content {
                background-color: white;
                padding: 30px;
                border-bottom-left-radius: 8px;
                border-bottom-right-radius: 8px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            }
            .greeting {
                font-size: 20px;
                font-weight: 500;
                margin-bottom: 20px;
                color: #333;
            }
            .confirmation-message {
                font-size: 17px;
                margin-bottom: 25px;
                color: #555;
            }
            .hotel-image-container {
                width: 100%;
                height: 220px;
                overflow: hidden;
                border-radius: 8px;
                margin-bottom: 25px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            }
            .hotel-image {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }
            .booking-details {
                background-color: #f9f9f9;
                border-left: 4px solid #ad8b3a;
                padding: 20px;
                margin: 25px 0;
                border-radius: 5px;
            }
            .booking-details h3 {
                font-family: "Cinzel", serif;
                color: #45443F;
                margin-top: 0;
                margin-bottom: 15px;
                font-size: 20px;
                border-bottom: 1px solid #ddd;
                padding-bottom: 10px;
            }
            .detail-row {
                margin-bottom: 12px;
                display: table;
                width: 100%;
            }
            .detail-label {
                font-weight: 600;
                color: #555;
                display: table-cell;
                width: 40%;
            }
            .detail-value {
                display: table-cell;
                color: #333;
            }
            .booking-id {
                font-size: 18px;
                font-weight: 700;
                color: #ad8b3a;
            }
            .contact-info {
                margin-top: 25px;
                margin-bottom: 30px;
                line-height: 1.7;
                color: #555;
            }
            .button {
                display: inline-block;
                background-color: #ad8b3a;
                color: white !important;
                padding: 12px 30px;
                text-decoration: none;
                border-radius: 5px;
                font-weight: 600;
                font-size: 16px;
                margin-top: 10px;
                text-align: center;
                transition: background-color 0.3s;
            }
            .button:hover {
                background-color: #8e7535;
            }
            .button-container {
                text-align: center;
                margin: 30px 0;
            }
            .divider {
                border-top: 1px solid #e0e0e0;
                margin: 30px 0;
            }
            .footer {
                text-align: center;
                margin-top: 30px;
                padding-top: 20px;
                border-top: 1px solid #eee;
                font-size: 14px;
                color: #777;
            }
            .footer p {
                margin: 5px 0;
            }
            .copyright {
                font-weight: 500;
                color: #555;
            }
            .hotel-name {
                font-family: "Cinzel", serif;
                font-weight: 600;
                color: #45443F;
            }
            .total-amount {
                font-weight: 700;
                color: #ad8b3a;
                font-size: 18px;
            }
            .automated-message {
                font-size: 12px;
                color: #999;
                margin-top: 15px;
            }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>Elysian Stays</h1>
            <p>Booking Confirmation</p>
        </div>
        <div class="content">
            <p class="greeting">Dear ' . htmlspecialchars($booking['guest_name']) . ',</p>
            <p class="confirmation-message">Thank you for choosing <strong>Elysian Stays</strong>. We are delighted to confirm your booking at <span class="hotel-name">' . htmlspecialchars($booking['hotel_name']) . '</span>.</p>
            
            <div class="hotel-image-container">
                <img src="' . $hotel_image . '" alt="' . htmlspecialchars($booking['hotel_name']) . '" class="hotel-image">
            </div>
            
            <div class="booking-details">
                <h3>Booking Details</h3>
                <div class="detail-row">
                    <div class="detail-label">Booking ID:</div>
                    <div class="detail-value booking-id">#' . $booking_id . '</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Hotel:</div>
                    <div class="detail-value">' . htmlspecialchars($booking['hotel_name']) . '</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Room Type:</div>
                    <div class="detail-value">' . htmlspecialchars($booking['room_name']) . '</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Check-in:</div>
                    <div class="detail-value">' . date('D, d M Y', strtotime($booking['check_in_date'])) . '</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Check-out:</div>
                    <div class="detail-value">' . date('D, d M Y', strtotime($booking['check_out_date'])) . '</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Total Amount:</div>
                    <div class="detail-value total-amount">$' . number_format($booking['total_price'], 2) . '</div>
                </div>
            </div>
            
            <p class="contact-info">If you have any questions or need to make changes to your reservation, please contact our support team at <a href="mailto:support@elysianstays.com" style="color: #ad8b3a; text-decoration: none; font-weight: 600;">support@elysianstays.com</a>.</p>
            
            <div class="button-container">
                <a href="http://localhost/elysian-stays/my-booking.php" class="button">View Your Bookings</a>
            </div>
            
            <div class="divider"></div>
            
            <div class="footer">
                <p class="copyright">&copy; ' . date('Y') . ' Elysian Stays. All Rights Reserved.</p>
                <p>Enjoy your stay with us!</p>
                <p class="automated-message">This is an automated message, please do not reply to this email.</p>
            </div>
        </div>
    </body>
    </html>';

    // Plain text alternative
    $mail->AltBody = "Dear " . $booking['guest_name'] . ",\n\n"
                   . "Thank you for choosing Elysian Stays. Your booking has been confirmed!\n\n"
                   . "Booking Details:\n"
                   . "Booking ID: #" . $booking_id . "\n"
                   . "Hotel: " . $booking['hotel_name'] . "\n"
                   . "Room Type: " . $booking['room_name'] . "\n"
                   . "Check-in: " . date('d M Y', strtotime($booking['check_in_date'])) . "\n"
                   . "Check-out: " . date('d M Y', strtotime($booking['check_out_date'])) . "\n"
                   . "Total Amount: $" . number_format($booking['total_price'], 2) . "\n\n"
                   . "If you have any questions or need to make changes to your reservation, please contact our support team at support@elysianstays.com.\n\n"
                   . "Thanks,\n"
                   . "Elysian Stays Team";

    // Send the email
    $mail->send();
    $email_sent = true;
} catch (Exception $e) {
    $email_sent = false;
    // Log error for debugging
    error_log("Error sending email: " . $mail->ErrorInfo);
}

// Store email status in session for display purposes
$_SESSION['email_status'] = $email_sent ? 'success' : 'failed';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation - Elysian Stays</title>
    <script src="js/bootstrap.bundle.min.js"></script>
    <link href="css/animate.min.css" rel="stylesheet">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cinzel:wght@400..900&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap');

        body {
            background-color: #fff;
            margin: 0;
            padding: 0;
            animation: fadeInBody 1.5s;
            font-family: 'Inter', serif;
        }

        @keyframes fadeInBody {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .navbar {
            background-color: #45443F;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .confirmation-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            max-width: 800px;
            margin: 50px auto;
            padding: 30px;
            animation: bounceIn 1s;
            text-align: center;
        }

        @keyframes bounceIn {
            0% {
                opacity: 0;
                transform: scale(0.9);
            }

            50% {
                opacity: 1;
                transform: scale(1.05);
            }

            100% {
                transform: scale(1);
            }
        }

        .theme-color {
            color: #ad8b3a;
            font-family: 'Cinzel', serif;
            text-transform: capitalize;
            font-weight: 500;
        }

        .btn-theme {
            background-color: #ad8b3a;
            color: white;
            transition: transform 0.3s;
        }

        .btn-theme:hover {
            background-color: #906f2f;
            transform: translateY(-3px);
        }

        .confirmation-box {
            border: 2px dashed #ad8b3a;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            background: #fefae0;
            animation: fadeInUp 1s;
        }

        .confirmation-box h5 {
            border-bottom: 2px solid #ad8b3a;
            padding-bottom: 10px;
            font-family: 'Cinzel', serif;
            text-align: center;
        }

        .hotel-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .success-icon {
            font-size: 80px;
            color: #28a745;
            margin-bottom: 20px;
        }
        
        .email-status {
            margin-top: 15px;
            padding: 8px 15px;
            border-radius: 5px;
            font-weight: 500;
        }
        
        .email-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .email-failed {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        footer {
            background-color: #000;
            color: white;
            text-align: center;
            padding: 10px 0;
            bottom: 0;
            width: 100%;
            z-index: 1000;
        }

        footer a {
            color: #ad8b3a;
            text-decoration: none;
        }

        footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-light bg-light animate__animated animate__fadeInDown">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">Elysian Stays</a>
            <a href="index.php" class="btn btn-theme">Home</a>
        </div>
    </nav>

    <!-- Confirmation Container -->
    <div class="confirmation-container animate__animated animate__fadeInUp">
        <!-- Hotel Image -->
        <img src="<?php echo !empty($booking['background_image']) ? htmlspecialchars($booking['background_image']) : htmlspecialchars($booking['about_image']); ?>" alt="<?php echo htmlspecialchars($booking['hotel_name']); ?>" class="hotel-image">

        <!-- Success Icon -->
        <div class="success-icon">
            <i class="fas fa-check-circle"></i>
        </div>

        <!-- Success Message -->
        <div class="mb-4">
            <h2 class="theme-color">Booking Confirmed!</h2>
            <p class="lead">Thank you for choosing Elysian Stays. Your payment has been processed successfully.</p>
            <p>Your booking confirmation number is: <strong>#<?php echo $booking_id; ?></strong></p>
            
            <!-- Email Status Message -->
            <?php if (isset($_SESSION['email_status'])): ?>
            <div class="email-status <?php echo $_SESSION['email_status'] == 'success' ? 'email-success' : 'email-failed'; ?>">
                <?php if ($_SESSION['email_status'] == 'success'): ?>
                    <i class="fas fa-envelope-open-text"></i> A confirmation email has been sent to <?php echo htmlspecialchars($booking['guest_email']); ?>
                <?php else: ?>
                    <i class="fas fa-exclamation-triangle"></i> We couldn't send a confirmation email at this time. Please save your booking details.
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Booking Details -->
        <div class="confirmation-box">
            <h5 class="theme-color">Booking Details</h5>
            <div class="row">
                <div class="col-md-6 text-md-start">
                    <p><strong>Hotel:</strong> <?php echo htmlspecialchars($booking['hotel_name']); ?></p>
                    <p><strong>Room Type:</strong> <?php echo htmlspecialchars($booking['room_name']); ?></p>
                    <p><strong>Guest Name:</strong> <?php echo htmlspecialchars($booking['guest_name']); ?></p>
                </div>
                <div class="col-md-6 text-md-start">
                    <p><strong>Check-in:</strong> <?php echo date('d M Y', strtotime($booking['check_in_date'])); ?></p>
                    <p><strong>Check-out:</strong> <?php echo date('d M Y', strtotime($booking['check_out_date'])); ?></p>
                    <p><strong>Total Amount:</strong> $<?php echo number_format($booking['total_price'], 2); ?></p>
                </div>
            </div>
        </div>

        <!-- Next Steps -->
        <div class="mb-4">
            <h5 class="theme-color">What's Next?</h5>
            <p>We've sent a confirmation email to <strong><?php echo htmlspecialchars($booking['guest_email']); ?></strong> with all the details of your booking.</p>
            <p>If you have any questions or need to make changes to your reservation, please contact us at <strong>support@elysianstays.com</strong>.</p>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex justify-content-center gap-3">
            <a href="index.php" class="btn btn-theme">Return to Home</a>
            <a href="my-booking.php" class="btn btn-outline-secondary">View All Bookings</a>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; <?php echo date('Y'); ?> <a href="index.php">Elysian Stays</a>. All Rights Reserved.</p>
    </footer>
</body>

</html>