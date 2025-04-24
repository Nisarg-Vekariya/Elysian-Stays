<?php
require("db_connect.php");

// Fetch all required data from database
$earningsData = $conn->query("SELECT * FROM earnings_estimates LIMIT 1")->fetch_assoc();
$basePrice = $earningsData['base_price'] ?? 4898;
$minNights = $earningsData['min_nights'] ?? 1;
$maxNights = $earningsData['max_nights'] ?? 30;

$setupFeatures = [];
$setupResult = $conn->query("SELECT * FROM setup_features WHERE is_active = TRUE ORDER BY display_order");
while ($row = $setupResult->fetch_assoc()) {
    $setupFeatures[] = $row;
}

$protectionFeatures = [];
$protectionResult = $conn->query("SELECT * FROM protection_features WHERE is_active = TRUE ORDER BY display_order");
while ($row = $protectionResult->fetch_assoc()) {
    $protectionFeatures[] = $row;
}

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize variables
$success_message = "";
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Database connection
        $servername = "localhost";
        $username = "root"; // Replace with your database username
        $password = ""; // Replace with your database password
        $dbname = "Elysian_Stays"; // Replace with your database name

        // Create connection
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Check connection
        if ($conn->connect_error) {
            throw new Exception("Database connection failed: " . $conn->connect_error);
        }

        // Get form data
        $name = htmlspecialchars($_POST['name']);
        $username = htmlspecialchars($_POST['username']);
        $email = htmlspecialchars($_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password
        $phone = htmlspecialchars($_POST['phone']);
        $city = htmlspecialchars($_POST['city']);
        $country = htmlspecialchars($_POST['country']);

        // Handle file upload
        $target_dir = "uploads/"; // Directory where the file will be saved
        $default_profile_pic = "user-iconset-no-profile.jpg"; // Default profile picture
        $profile_pic = $default_profile_pic; // Default value

        // Check if a file was uploaded
        if (!empty($_FILES['profilePic']['name'])) {
            $profile_pic = basename($_FILES['profilePic']['name']);
            $profile_pic_tmp_name = $_FILES['profilePic']['tmp_name'];
            $target_file = $target_dir . $profile_pic;

            // Check file size (max 2MB)
            $max_file_size = 2 * 1024 * 1024; // 2MB in bytes
            if ($_FILES['profilePic']['size'] > $max_file_size) {
                throw new Exception("Profile picture size must be less than 2MB.");
            }

            // Check if the directory exists, if not, create it
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true); // Create the directory with full permissions
            }

            // Move the uploaded file to the target directory
            if (!move_uploaded_file($profile_pic_tmp_name, $target_file)) {
                throw new Exception("Error uploading profile picture.");
            }
        }

        // Generate token
        $token = bin2hex(random_bytes(50));

        // Insert data into the database
        $sql = "INSERT INTO users (name, username, email, password, phone, city, country, profile_pic, token, role, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'hotel', 'inactive')";

        // Use prepared statements to prevent SQL injection
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Database error: " . $conn->error);
        }

        $stmt->bind_param("sssssssss", $name, $username, $email, $password, $phone, $city, $country, $profile_pic, $token);

        if ($stmt->execute()) {
            $success_message = "Signup successful!";
        } else {
            throw new Exception("Error: " . $stmt->error);
        }

        // Close connection
        $stmt->close();
        $conn->close();
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Earnings Calculator - Elysian Stays</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/font-awesome.css" rel="stylesheet">
    <link href="css/animate.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/footer.css">
    <link rel="stylesheet" href="css/list-your-place.css">
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar navbar-light bg-light animate__animated animate__fadeInDown">
        <div class="container">
            <p><a href="javascript:history.back()" class="btn btn-link me-3"></a>
                <a href="index.php" class="btn btn-link me-3">
                    <img src="Images/Fallback.svg" alt="back" width="20vw" class="">
                </a>

                <a class="navbar-brand fw-bold" href="index.php">Elysian Stays Earnings</a>

            </p>
            <div>
                Ready to Host Your Place it?
                <a href="signup_hotel.php"><button type="button" class="btn btn-theme" style="background-color: #ad8b3a; color: white;">Elysian Stays Setup</button></a>
    </nav>

    <?php if (!empty($success_message)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo $success_message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (!empty($error_message)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo $error_message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>



   <!-- Hero Section -->
   <section class="hero d-flex flex-column justify-content-center align-items-center text-center bg-white vh-100" style="z-index: -9000;">
        <div class="container animate__animated animate__fadeInUp">
            <h1 class="fw-bold" style="color: #ad8b3a;">Elysian Stays Partner Program</h1>
            <h2 class="fw-bold my-3">You could earn up to</h2>
            <div class="earnings-display">
                <h2 id="earnings" class="fw-bold">$<?php echo number_format($basePrice * 7); ?></h2>
                <p id="nights" class="text-muted">
                    <span style="font-weight: bolder; text-decoration: underline;">7 nights</span> at an estimated $<?php echo number_format($basePrice); ?> a night
                </p>
            </div>

            <div class="slider-container my-3">
                <input id="range-slider" type="range" class="form-range w-50" min="<?php echo $minNights; ?>" max="<?php echo $maxNights; ?>" value="7">
            </div>

            <a href="#" class="a-theme" data-bs-toggle="modal" data-bs-target="#earningsModal">Learn how we estimate your earnings</a>
        </div>
    </section>

    <!-- Modal -->
    <div class="modal fade" id="earningsModal" tabindex="-1" aria-labelledby="earningsModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="earningsModalLabel">How we estimate your earning potential</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>To estimate your earnings, we review the past 12 months of booking data from similar Elysian Stays listings. We choose these listings based on the information you share about your place. If you enter an address, you'll get a more specific estimate based on the listings closest to you. If you enter an area, we look at the top 50% of similar listings in that area, based on their earnings.</p>
                    <p>Based on these similar listings, we estimate the average nightly earnings and multiply that number by the number of nights you indicate you will host. We also provide the average number of nights booked per month in your area, assuming places are available on Elysian Stays every night of the month. (Nightly earnings are the price set by each Host minus the Elysian Stays Host service fee. We don't subtract taxes or hosting expenses.)</p>
                    <p>Your actual earnings will depend on several factors, including your availability, price, and the demand in your area. Your ability to host may also depend on local laws. Learn more about responsible hosting.</p>
                    <p>These earning estimates are not an appraisal or estimate of property value.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Setup -->
    <section class="py-5">
        <div class="container text-center">
            <h2 class="fw-bold mb-4 animate__animated animate__fadeInDown">Host your place easily with Elysian Stays Setup</h2>
            <div class="d-flex justify-content-center">
                <img src="Images/phone-mockup.webp" class="img-fluid animate__animated animate__zoomIn" alt="Phone Mockup">
            </div>

            <div class="row mt-5">
                <?php foreach ($setupFeatures as $index => $feature): ?>
                    <div class="col-md-4 animate__animated animate__fadeInUp animate__delay-<?php echo $index + 1; ?>s">
                        <h5 class="fw-bold"><?php echo htmlspecialchars($feature['title']); ?></h5>
                        <p><?php echo htmlspecialchars($feature['description']); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Aircover Section -->
    <section class="aircover-section py-5">
        <div class="container">
            <div class="aircover-header text-center mb-5">
                <h1 class="animate__animated animate__fadeInDown">Elysian Stays it with top-to-bottom protection</h1>
                <p class="animate__animated animate__fadeInDown animate__delay-1s">
                    Comprehensive coverage for hosts and guests, providing peace of mind with every stay.
                </p>
            </div>
            <div class="feature-table">
                <div class="row header-row animate__animated animate__fadeInLeft">
                    <div class="col-6">Feature</div>
                    <div class="col-3">Elysian Stays</div>
                    <div class="col-3">Competitors</div>
                </div>
                
                <?php foreach ($protectionFeatures as $index => $feature): ?>
                    <div class="row animate__animated animate__fadeInLeft animate__delay-<?php echo $index + 1; ?>s">
                        <div class="col-6">
                            <div class="feature-title"><?php echo htmlspecialchars($feature['title']); ?></div>
                            <div class="feature-description">
                                <?php echo htmlspecialchars($feature['description']); ?>
                            </div>
                        </div>
                        <div class="col-3 text-center check-mark"><?php echo $feature['elysian_has'] ? '✓' : '✗'; ?></div>
                        <div class="col-3 text-center cross-mark"><?php echo $feature['competitors_have'] ? '✓' : '✗'; ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="footer-container">
            <div class="footer-about" data-animation="animate__fadeInLeft">
                <h3>About Us</h3>
                <p>Discover the best hotels worldwide with unbeatable deals and exceptional services. Book your stay with confidence!</p>
            </div>
            <div class="footer-links" data-animation="animate__fadeInUp">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="search.php">Top Hotels</a></li>
                    <li><a href="index.php#why-choose-us">Why Choose Us</a></li>
                    <li><a href="index.php#booking-process">Booking Process</a></li>
                    <li><a href="contact.php">Contact Us</a></li>
                </ul>
            </div>
            <div class="footer-contact" data-animation="animate__fadeInRight">
                <h3>Contact Us</h3>
                <p><strong>Email:</strong> support@ElysianStays.com</p>
                <p><strong>Phone:</strong> +123 456 7890</p>
                <div class="social-icons">
                    <a href="#"><i class="fa-brands fa-facebook"></i></a>
                    <a href="#"><i class="fa-brands fa-twitter"></i></a>
                    <a href="#"><i class="fa-brands fa-instagram"></i></a>
                    <a href="#"><i class="fa-brands fa-youtube"></i></a>
                </div>

            </div>
            <div class="footer-subscribe" data-animation="animate__fadeInUp">
                <h3>Stay Updated</h3>
                <form class="subscribe-form">
                    <input type="email" placeholder="Enter your email" required>
                    <button type="submit">Subscribe</button>
                </form>
            </div>
        </div>
        <div class="footer-bottom" data-animation="animate__zoomIn">
            <p>&copy; 2025 Elysian Stays. All rights reserved.</p>
        </div>
    </footer>

    <script src="js/bootstrap.bundle.min.js"></script>
    <script src="js/font-awesome-6.0.0-beta3-all.min.js"></script>
    <script src="js/jquery-3.6.4.min.js"></script>
    <script src="js/jquery.validate.min.js"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
    <script src="js/list-your-place.js"></script>
   
    </script>
</body>

</html>