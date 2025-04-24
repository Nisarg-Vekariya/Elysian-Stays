<?php require_once 'header.php'; ?>
<?php

$query = "SELECT * FROM sliders WHERE is_active = 1 ORDER BY created_at DESC";
$result = $conn->query($query);

$sliders = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $sliders[] = $row;
    }
}
// Fetch features for Why Choose Us section
$features_result = $conn->query("SELECT * FROM features WHERE is_active = TRUE ORDER BY display_order");
$features = $features_result->fetch_all(MYSQLI_ASSOC);

// Fetch booking process steps
$steps_result = $conn->query("SELECT * FROM booking_steps ORDER BY display_order");
$steps = $steps_result->fetch_all(MYSQLI_ASSOC);

// Fetch hotels for carousel
$carousel_query = "SELECT id, name, tagline, about_description1, background_image FROM hotels 
                   WHERE background_image != '' 
                   ORDER BY id DESC LIMIT 4";
$carousel_result = $conn->query($carousel_query);
$carousel_items = [];
if ($carousel_result && $carousel_result->num_rows > 0) {
    while($row = $carousel_result->fetch_assoc()) {
        $carousel_items[] = $row;
    }
}

$conn->close();
?>

<!-- Carousel -->
<div class="carousel">
    <!-- List Item -->
    <div class="list">
        <?php foreach ($carousel_items as $item): ?>
        <div class="item">
            <img src="<?php echo htmlspecialchars($item['background_image']); ?>">
            <div class="content">
                <div class="title"><?php echo htmlspecialchars($item['name']); ?></div>
                <div class="topic"><?php echo htmlspecialchars($item['tagline']); ?></div>
                <div class="des">
                    <?php echo htmlspecialchars($item['about_description1']); ?>
                </div>
                <div class="buttons">
                    <button>
                        <a href="hotel.php?id=<?php echo $item['id']; ?>" style="color: black; text-decoration: none;">
                            SEE MORE
                        </a>
                    </button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <!-- List Thumbnail -->
    <div class="thumbnail">
        <?php foreach ($carousel_items as $item): ?>
        <div class="item">
            <img src="<?php echo htmlspecialchars($item['background_image']); ?>">
            <div class="content">
                <div class="title"><?php echo htmlspecialchars($item['name']); ?></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Next Prev -->
    <div class="arrows">
        <button id="prev"><</button>
        <button id="next">></button>
    </div>
    <!-- Time Running -->
    <div class="time"></div>
</div>

<!-- Bootstrap Toast Container -->
<div aria-live="polite" aria-atomic="true" class="position-relative">
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <!-- Toast -->
        <div id="copyToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <strong class="me-auto">Success</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                Coupon code copied successfully!
            </div>
        </div>
    </div>
</div>
<div class="text-carousel">
    <!-- Carousel Slides -->
    <?php foreach ($sliders as $index => $slider): ?>
    <div class="carousel-slide <?php echo $index === 0 ? 'active animate__animated animate__fadeInRight' : ''; ?>">
        <p class="carousel-text"><?php echo htmlspecialchars($slider['title']); ?> <span id="coupon<?php echo $index + 1; ?>" class="coupon-code"><?php echo htmlspecialchars($slider['coupon_code']); ?></span></p>
        <button class="carousel-view-more" onclick="copyCoupon('coupon<?php echo $index + 1; ?>')">Copy Code</button>
    </div>
    <?php endforeach; ?>

    <!-- Navigation Buttons -->
    <div class="carousel-buttons">
        <button class="prev">&#10094;</button>
        <button class="next">&#10095;</button>
    </div>

    <!-- Indicators -->
    <div class="carousel-indicators">
        <?php foreach ($sliders as $index => $slider): ?>
        <span class="dot <?php echo $index === 0 ? 'active' : ''; ?>"></span>
        <?php endforeach; ?>
    </div>
</div>



<!-- Search in Home Page -->
<div class="booking-section animate__animated animate__fadeInUp">
    <div class="booking-header animate__animated animate__fadeInDown animate__delay-1s">
        <h2>Book a Room</h2>
        <p>Discover the perfect space for you!</p>
    </div>
    <button class="btn-submit"><a href="search.php" style="text-decoration: none; color:white;">BOOK NOW</a></button>
</div>

<!-- Booking Process -->
<div class="booking-process animate__animated animate__fadeIn">
    <h2 class="h2mod animate__animated animate__fadeInDown">Book your stay in few simple steps</h2>
    <div class="divider animate__animated animate__zoomIn animate__delay-1s"></div>
    <div class="process-steps">
        <div class="steps">
            <?php foreach ($steps as $step): ?>
            <div class="step animate__animated animate__fadeInLeft animate__delay-<?= $step['step_number']+1 ?>s" 
                 onclick="showDetail(<?= $step['step_number'] ?>)">
                <h3>Step <?= $step['step_number'] ?>: <?= htmlspecialchars($step['title']) ?></h3>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="details">
            <?php foreach ($steps as $step): ?>
            <div class="detail animate__animated animate__fadeInUp animate__delay-<?= $step['animation_delay'] ?>" 
                 id="detail-<?= $step['step_number'] ?>" style="display: none;">
                <p><strong>Step <?= $step['step_number'] ?>: <?= htmlspecialchars($step['title']) ?></strong></p>
                <p><?= htmlspecialchars($step['description']) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Why Choose Us -->
<section class="why-choose-us animate__animated">
    <h2 class="section-title h2mod">Why Choose Us?</h2>
    <div class="divider"></div>
    <div class="features-container">
        <?php foreach ($features as $feature): ?>
        <div class="feature-card animate__animated">
            <img src="<?= htmlspecialchars($feature['icon_url']) ?>" 
                 alt="<?= htmlspecialchars($feature['title']) ?>" 
                 class="feature-icon <?= !empty($feature['special_class']) ? htmlspecialchars($feature['special_class']) : '' ?>">
            <h3><?= htmlspecialchars($feature['title']) ?></h3>
            <p><?= htmlspecialchars($feature['description']) ?></p>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<?php require_once 'footer.php'; ?>