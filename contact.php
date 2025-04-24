<?php
require('header.php');

// Function to get contact hero data
function getContactHero($conn) {
    $stmt = $conn->prepare("SELECT title, background_image, search_placeholder FROM contact_hero LIMIT 1");
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    
    // Ensure background_image has proper path
    if (!empty($data['background_image']) && strpos($data['background_image'], 'http') !== 0) {
        // If it's a relative path, make sure it starts with /
        if (strpos($data['background_image'], '/') !== 0) {
            $data['background_image'] = '/' . $data['background_image'];
        }
    }
    
    return $data;
}
// Function to get toll-free numbers
function getContactNumbers($conn) {
    $result = $conn->query("SELECT region, number FROM contact_numbers ORDER BY display_order");
    $numbers = [];
    while ($row = $result->fetch_assoc()) {
        $numbers[] = $row;
    }
    return $numbers;
}



// Function to get assistance centers
function getAssistanceCenters($conn) {
    $result = $conn->query("SELECT city, address, phone, email FROM assistance_centers ORDER BY display_order");
    $centers = [];
    while ($row = $result->fetch_assoc()) {
        $centers[] = $row;
    }
    return $centers;
}

// Function to get registered office info
function getRegisteredOffice($conn) {
    $result = $conn->query("SELECT * FROM registered_office LIMIT 1");
    return $result->fetch_assoc();
}

// Function to get page content
function getPageContent($conn, $section_name) {
    $stmt = $conn->prepare("SELECT title, content FROM page_content WHERE section_name = ?");
    $stmt->bind_param("s", $section_name);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Get all hotel names for autocomplete
function getHotelNames($conn) {
    $hotelNames = array();
    $result = $conn->query("SELECT id, name FROM hotels ORDER BY name");
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $hotelNames[] = $row['name'];
        }
    }
    // Debug: Log the number of hotels found
    error_log("Found " . count($hotelNames) . " hotels for autocomplete");
    return $hotelNames;
}

// Get all data from database
$heroData = getContactHero($conn);
$contactNumbers = getContactNumbers($conn);
$assistanceCenters = getAssistanceCenters($conn);
$registeredOffice = getRegisteredOffice($conn);
$contactIntro = getPageContent($conn, 'contact_intro');
$careTitle = getPageContent($conn, 'care_section_title');
$careContent = getPageContent($conn, 'care_section_content');
$hotelNames = getHotelNames($conn);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $hotel = trim($_POST['hotel']);
    $date_of_stay = $_POST['date'] ?: NULL;
    $phone = trim($_POST['phone']);
    $comments = trim($_POST['comments']);

    if (!empty($name) && !empty($email) && !empty($phone)) {
        $stmt = $conn->prepare("INSERT INTO feedback (name, email, hotel, date_of_stay, phone, comments) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $name, $email, $hotel, $date_of_stay, $phone, $comments);

        if ($stmt->execute()) {
            $_SESSION['toast'] = ['type' => 'success', 'message' => 'Thank you for your feedback!'];
        } else {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Failed to submit feedback. Please try again.'];
        }

        $stmt->close();
    } else {
        $_SESSION['toast'] = ['type' => 'warning', 'message' => 'Please fill all required fields.'];
    }

    $conn->close();
    ?>
    <script>window.location.href="contact.php"</script>
    <?php
    exit;
}
?>
<title>Contact</title>

<!-- Hero Section -->
<div class="hero animate__animated animate__fadeIn" id="heroabout" 
    style="<?php 
        $bgImage = !empty($heroData['background_image']) ? 'background-image: url(\''.htmlspecialchars($heroData['background_image']).'\')' : '';
        echo $bgImage;
    ?>">

    <h1 class="animate__animated animate__fadeInDown"><?php echo htmlspecialchars($heroData['title'] ?? 'Contact'); ?></h1>
    <a href="search.php"><div class="search-bar animate__animated animate__zoomIn">
        <input type="text" class="form-control" placeholder="<?php echo htmlspecialchars($heroData['search_placeholder'] ?? 'Click here to search for Destinations or Hotels.'); ?>">
    </div></a>
</div>

<!-- Contact Section -->
<div class="contact-section animate-on-scroll">
    <h2 class="animate-on-scroll" data-animation="animate__fadeInUp">Get in Touch</h2>
    <div class="divider animate-on-scroll" data-animation="animate__fadeInUp"></div>
    <div class="lightdark animate-on-scroll">
        <div class="text-center mb-4 animate-on-scroll" data-animation="animate__fadeInUp">
            <p class="h2mod mt-7"><?= htmlspecialchars($contactIntro['title'] ?? 'Worldwide Reservation Centre') ?></p>
            <p><?= htmlspecialchars($contactIntro['content'] ?? 'Elysian Stays Reservations Worldwide Centre is accessible 24/7. Toll-free contact numbers are below.') ?></p>
        </div>
        <div class="contact-table animate-on-scroll" data-animation="animate__fadeInUp">
            <table class="table table-bordered text-center">
                <thead class="table">
                    <tr>
                        <th>Region</th>
                        <th>Contact Number</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($contactNumbers as $number): ?>
                    <tr>
                        <td><?= htmlspecialchars($number['region']) ?></td>
                        <td><?= htmlspecialchars($number['number']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p class="text-center"><small>*Local / STD charges as applicable</small></p>
        </div>
    </div>
</div>

<div class="container py-5">
    <!-- Section Title -->
    <h2 class="section-title h2mod">Global Assistance Center</h2>
    <div class="divider mb-4"></div>
    <!-- Contact Cards -->
    <div class="row" id="contact-cards">
        <?php foreach ($assistanceCenters as $center): ?>
        <div class="col-md-4 contact-card">
            <h5><?= htmlspecialchars($center['city']) ?></h5>
            <div class="contact-details">
                <p><?= htmlspecialchars($center['address']) ?></p>
                <p>Phone: <?= htmlspecialchars($center['phone']) ?></p>
                <p>Email: <a href="mailto:<?= htmlspecialchars($center['email']) ?>" style="text-decoration: none;"><?= htmlspecialchars($center['email']) ?></a></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Form -->
<div class="container py-5 animate__animated animate__fadeIn">
    <div class="row">
        <div class="col-auto">
            <p class="h2mod animate__animated animate__bounceInLeft"><?= htmlspecialchars($careTitle['title'] ?? 'CARE@Elysian Stays') ?></p>
        </div>
        <div class="col text-right">
            <p class="text-p animate__animated animate__fadeInUp" style="padding-left: 20%;">
                <?= htmlspecialchars($careContent['content'] ?? 'We take pride in crafting moments that stay with you forever...') ?>
            </p>
        </div>
        <hr class="animate__animated animate__zoomIn" style="margin: 20px 0;">
    </div>

    <form class="animate__animated animate__fadeInUp needs-validation" id="feedbackForm" method="POST" action="contact.php" novalidate>
        <div class="row g-3">
            <div class="col-md-6">
                <label for="name" class="form-label">Name*</label>
                <input type="text" class="form-control animate__animated animate__fadeInLeft" id="name" name="name" placeholder="Enter your name" required>
                <div class="valid-feedback">Looks good!</div>
                <div class="invalid-feedback">Please provide your name.</div>
            </div>
            <div class="col-md-6">
                <label for="email" class="form-label">Email*</label>
                <input type="email" class="form-control animate__animated animate__fadeInRight" id="email" name="email" placeholder="Enter your email" required>
                <div class="valid-feedback">Looks good!</div>
                <div class="invalid-feedback">Please provide a valid email address.</div>
            </div>
        </div>
        <div class="row g-3 mt-3">
            <div class="col-md-6">
                <label for="hotel" class="form-label">Hotel</label>
                <input type="text" class="form-control animate__animated animate__fadeInLeft" id="hotel" name="hotel" placeholder="Start typing to see hotel suggestions" list="hotelList">
                <!-- Fallback using HTML5 datalist -->
                <datalist id="hotelList">
                    <?php foreach($hotelNames as $hotel): ?>
                    <option value="<?php echo htmlspecialchars($hotel); ?>">
                    <?php endforeach; ?>
                </datalist>
                <div class="valid-feedback">Looks good!</div>
            </div>
            <div class="col-md-6">
                <label for="date" class="form-label">Date of Stay</label>
                <input type="date" class="form-control animate__animated animate__fadeInRight" id="date" name="date">
                <div class="valid-feedback" id="date">Looks good!</div>
            </div>
        </div>
        <div class="row g-3 mt-3">
            <div class="col-md-6">
                <label for="phone" class="form-label">Mobile Number*</label>
                <div class="input-group">
                    <span class="input-group-text animate__animated animate__fadeInLeft">+91</span>
                    <input type="tel" class="form-control animate__animated animate__fadeInLeft" id="phone" name="phone" placeholder="Enter your mobile number" pattern="^\d{10}$" required>
                    <div class="valid-feedback">Looks good!</div>
                    <div class="invalid-feedback">Please provide a valid 10-digit phone number.</div>
                </div>
            </div>
            <div class="col-md-12">
                <label for="comments" class="form-label">Comments</label>
                <textarea class="form-control animate__animated animate__fadeInUp" id="comments" name="comments" rows="3" placeholder="Share your feedback"></textarea>
                <div class="valid-feedback">Looks good!</div>
            </div>
        </div>
        <div class="form-check mt-4">
            <input class="form-check-input animate__animated animate__fadeIn" type="checkbox" id="terms" name="terms" required>
            <label class="form-check-label ahef animate__animated animate__fadeIn" for="terms">
                I have read and agree to the <a href="privacy-policy.php">Privacy Policy</a> and <a href="ToS.php">Terms & Conditions</a>
            </label>
            <div class="invalid-feedback">
                You must agree to the terms and conditions.
            </div>
        </div>
        <div class="text-center mt-4">
            <button type="submit" class="button-outline button  px-5 animate__animated">Submit</button>
        </div>
    </form>
</div>

<section class="registered-office text-center py-5 animate__animated animate__fadeIn">
    <div class="container">
        <h2 class="section-title h2mod animate__animated animate__bounceInDown">REGISTERED OFFICE</h2>
        <div class="divider mb-4 animate__animated animate__zoomIn"></div>
        <div class="content mt-4 text-p ahef animate__animated animate__fadeInUp">
            <p><?= nl2br(htmlspecialchars($registeredOffice['address'] ?? '')) ?></p>
            <p>
                <a href="<?= htmlspecialchars($registeredOffice['map_link'] ?? '#') ?>" class="view-map animate__animated animate__pulse">View map</a>
            </p>
            <p>Phone: <?= htmlspecialchars($registeredOffice['phone'] ?? '') ?></p>
            <p>
                <a href="mailto:<?= htmlspecialchars($registeredOffice['email'] ?? '') ?>" class="email animate__animated animate__fadeInUp">
                    <?= htmlspecialchars($registeredOffice['email'] ?? '') ?>
                </a>
            </p>
        </div>
    </div>
</section>

<!-- Bootstrap Toast -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
    <?php if (isset($_SESSION['toast'])): ?>
    <div class="toast align-items-center text-white bg-<?= $_SESSION['toast']['type'] ?> border-0 show" role="alert">
        <div class="d-flex">
            <div class="toast-body">
                <?= htmlspecialchars($_SESSION['toast']['message']) ?>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
    <?php unset($_SESSION['toast']); endif; ?>
</div>

<?php require_once 'footer.php'; ?>

<!-- Hotel Autocomplete Script -->
<script>
$(document).ready(function() {
    // Initialize autocomplete with hotel names
    const hotelNames = <?php echo json_encode($hotelNames); ?>;
    $("#hotel").autocomplete({
        source: hotelNames,
        minLength: 1,
        delay: 100
    });
});
</script>