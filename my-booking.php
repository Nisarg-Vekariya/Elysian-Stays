<?php
require_once 'db_connect.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
    ]);
}

// Restrict access to logged-in users
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get current user ID
$user_id = $_SESSION['user_id'];

// Process review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    // Get form data
    $hotel_id = $_POST['hotel_id'];
    $rating = $_POST['rating'];
    $review_text = $_POST['review_text'];
    $author_name = $_POST['author_name'];

    // Set review date to current date
    $review_date = date("F Y");

    // Insert review into database
    $insert_review = "INSERT INTO reviews (hotel_id, author_name, review_date, review_text, rating, status) VALUES (?, ?, ?, ?, ?, 'active')";
    $stmt = $conn->prepare($insert_review);
    $stmt->bind_param("isssi", $hotel_id, $author_name, $review_date, $review_text, $rating);

    if ($stmt->execute()) {
        // Set success cookie - expires in 5 seconds
        setcookie("review_status", "success", time() + 5, "/");
        setcookie("review_message", "Your review has been submitted successfully!", time() + 5, "/");
    } else {
        // Set error cookie - expires in 5 seconds
        setcookie("review_status", "error", time() + 5, "/");
        setcookie("review_message", "Failed to submit review. Please try again.", time() + 5, "/");
    }
    
    // Redirect to same page to prevent form resubmission
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Fetch user's bookings from database
$bookings_query = "SELECT b.*, h.name AS hotel_name, h.id AS hotel_id, r.name AS room_name, r.image AS room_image 
                   FROM bookings b
                   JOIN hotels h ON b.hotel_id = h.id
                   JOIN rooms r ON b.room_id = r.id
                   WHERE b.guest_email = (SELECT email FROM users WHERE id = ?)
                   ORDER BY b.check_in_date DESC";
$stmt = $conn->prepare($bookings_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$bookings_result = $stmt->get_result();

// Check if user has already reviewed a hotel and get the review if it exists
function getUserReview($conn, $hotel_id, $user_id)
{
    $review_check = "SELECT r.* FROM reviews r 
                     JOIN users u ON r.author_name = u.name 
                     WHERE r.hotel_id = ? AND u.id = ?";
    $stmt = $conn->prepare($review_check);
    $stmt->bind_param("ii", $hotel_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $review = $result->fetch_assoc();
    $stmt->close();
    return $review;
}

// Modify existing hasUserReviewed function
function hasUserReviewed($conn, $hotel_id, $user_id)
{
    $review_check = "SELECT COUNT(*) as review_count FROM reviews r 
                    JOIN users u ON r.author_name = u.name 
                    WHERE r.hotel_id = ? AND u.id = ?";
    $stmt = $conn->prepare($review_check);
    $stmt->bind_param("ii", $hotel_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $count = $row['review_count'];
    $stmt->close();
    return $count > 0;
}

// Get user's name
$user_query = "SELECT name FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($user_name);
$stmt->fetch();
$stmt->close();
?>

<?php require_once 'header.php'; ?>
<title>My Bookings - Elysian Stays</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    @import url('https://fonts.googleapis.com/css2?family=Cinzel:wght@400..900&display=swap');
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap');

    body {
        font-family: 'Inter', sans-serif;
        margin: 0;
        padding: 0;
        background-color: #f8f9fa;
    }

    :root {
        --primary: #ad8b3a;
        --secondary: #45443F;
    }

    .hero {
        position: relative;
        background-image: url('Images/Luxury-Hotels-in-Kerala.jpg');
        background-size: cover;
        background-position: center;
        height: 400px;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        font-family: 'Cinzel', serif;
    }

    .hero h1 {
        font-size: 64px;
        margin-bottom: -20px;
    }

    .booking-card {
        transition: all 0.3s ease;
        border: none;
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
    }

    .booking-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
    }

    .status-badge {
        background: var(--primary);
        color: white;
        padding: 8px 15px;
        border-radius: 20px;
        font-size: 0.9rem;
    }

    .status-cancelled {
        background: #dc3545;
    }

    .status-pending {
        background: #ffc107;
        color: #212529;
    }

    .status-completed {
        background: #28a745;
    }

    .btn-primary-custom {
        background: var(--primary);
        border: none;
        color: white;
        padding: 8px 25px;
    }

    .btn-primary-custom:hover {
        background: #9c7a32;
        color: white;
    }

    .hotel-image {
        height: 200px;
        object-fit: cover;
        border-radius: 8px;
    }

    .section-title {
        color: var(--primary);
        border-bottom: 2px solid var(--primary);
        padding-bottom: 10px;
    }

    .price-tag {
        color: var(--primary);
        font-size: 1.5rem;
        font-weight: bold;
    }

    .no-bookings {
        text-align: center;
        padding: 50px;
        background-color: white;
        border-radius: 8px;
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
    }

    .no-bookings i {
        font-size: 48px;
        color: var(--primary);
        margin-bottom: 20px;
    }

    /* Review styles */
    .review-btn {
        background-color: var(--primary);
        border: none;
        color: white;
        transition: all 0.3s;
    }

    .review-btn:hover {
        background-color: #9c7a32;
        transform: translateY(-2px);
    }

    .review-form {
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
        margin-top: 20px;
    }

    .star-rating {
        display: flex;
        flex-direction: row-reverse;
        justify-content: flex-end;
    }

    .star-rating input {
        display: none;
    }

    .star-rating label {
        color: #ddd;
        font-size: 24px;
        padding: 0 5px;
        cursor: pointer;
    }

    .star-rating label:hover,
    .star-rating label:hover~label,
    .star-rating input:checked~label {
        color: #FFD700;
    }

    .alert {
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 4px;
        animation: fadeOut 5s forwards;
    }

    .alert-success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .alert-danger {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    
    @keyframes fadeOut {
        0% { opacity: 1; }
        80% { opacity: 1; }
        100% { opacity: 0; }
    }

    .view-review-btn {
        background-color: #6c757d;
        border: none;
        color: white;
        transition: all 0.3s;
        padding: 8px 15px;
        border-radius: 5px;
    }

    .view-review-btn:hover {
        background-color: #5a6268;
        transform: translateY(-2px);
    }

    .stars .filled {
        color: #FFD700;
    }

    .stars .fas {
        font-size: 20px;
        margin-right: 2px;
    }

    .user-review {
        padding: 15px;
        border-radius: 8px;
        background-color: #f8f9fa;
    }

    .review-text {
        margin-top: 15px;
        line-height: 1.6;
    }

    /* Modal Fixes */
    .modal {
        z-index: 1060 !important;
        background-color: rgba(0,0,0,0.5);
    }

    .modal-backdrop {
        z-index: 1050 !important;
        background-color: transparent !important;
    }

    .modal-content {
        z-index: 1061 !important;
        position: relative;
        border-radius: 8px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .modal-header {
        border-bottom: 1px solid #e9ecef;
        background-color: #f8f9fa;
    }

    .modal-footer {
        border-top: 1px solid #e9ecef;
        background-color: #f8f9fa;
    }
</style>

<!-- Hero Section -->
<div class="hero animate__animated animate__fadeIn">
    <h1 class="animate__animated animate__fadeInDown">My Bookings</h1>
    <div class="search-bar animate__animated animate__zoomIn">
    </div>
</div>

<!-- Main Content -->
<div class="container py-5 mt-5 animate__animated animate__fadeIn">

    <?php if (isset($_COOKIE['review_status'])): ?>
        <div class="alert alert-<?= $_COOKIE['review_status'] === 'success' ? 'success' : 'danger' ?>">
            <?= htmlspecialchars($_COOKIE['review_message']) ?>
        </div>
        <script>
            // Auto-remove the alert after 5 seconds
            setTimeout(function() {
                const alertElement = document.querySelector('.alert');
                if (alertElement) {
                    alertElement.style.display = 'none';
                }
            }, 5000);
        </script>
    <?php endif; ?>

    <?php if ($bookings_result->num_rows === 0): ?>
        <!-- No Bookings Message -->
        <div class="row">
            <div class="col-12">
                <div class="no-bookings">
                    <i class="fas fa-calendar-times"></i>
                    <h3>No Bookings Found</h3>
                    <p>You haven't made any bookings yet. Start exploring our hotels and book your perfect stay!</p>
                    <a href="index.php" class="btn btn-primary-custom">Explore Hotels</a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <?php
        $animation_delay = 0;
        while ($booking = $bookings_result->fetch_assoc()):
            // Calculate number of nights
            $check_in = new DateTime($booking['check_in_date']);
            $check_out = new DateTime($booking['check_out_date']);
            $nights = $check_in->diff($check_out)->days;

            // Determine status badge class
            $status_class = '';
            if ($booking['status'] === 'cancelled') {
                $status_class = 'status-cancelled';
            } elseif ($booking['status'] === 'pending') {
                $status_class = 'status-pending';
            } elseif ($booking['status'] === 'completed') {
                $status_class = 'status-completed';
            }

            // Format dates
            $check_in_formatted = $check_in->format('d M Y');
            $check_out_formatted = $check_out->format('d M Y');

            // Check if user has already reviewed this hotel
            $can_review = ($booking['status'] === 'completed' || $booking['status'] === 'confirmed') &&
                !hasUserReviewed($conn, $booking['hotel_id'], $user_id);
                
            // If user has already reviewed, get the review
            $user_review = null;
            if (($booking['status'] === 'completed' || $booking['status'] === 'confirmed') && 
                !$can_review) {
                $user_review = getUserReview($conn, $booking['hotel_id'], $user_id);
            }
        ?>
            <!-- Booking Card -->
            <div class="row mb-4 animate__animated animate__fadeInUp" style="animation-delay: <?= $animation_delay ?>s">
                <div class="col-12">
                    <div class="card booking-card p-4 mb-4">
                        <div class="row g-4">
                            <div class="col-md-4">
                                <img src="<?= htmlspecialchars($booking['room_image'] ?? 'https://via.placeholder.com/400x200') ?>" class="hotel-image w-100" alt="Hotel Image">
                            </div>
                            <div class="col-md-8">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <h3 class="mb-0"><?= htmlspecialchars($booking['hotel_name']) ?></h3>
                                    <span class="status-badge <?= $status_class ?>"><?= ucfirst($booking['status']) ?></span>
                                </div>
                                <div class="mb-3">
                                    <p class="text-muted mb-1"><i class="fa fa-calendar"></i> <?= $check_in_formatted ?> - <?= $check_out_formatted ?></p>
                                    <p class="text-muted mb-1"><i class="fa fa-moon"></i> <?= $nights ?> Night<?= $nights > 1 ? 's' : '' ?></p>
                                    <p class="text-muted"><i class="fa fa-user"></i> <?= htmlspecialchars($booking['guest_name']) ?></p>
                                    <p class="text-muted"><i class="fa fa-home"></i> <?= htmlspecialchars($booking['room_name']) ?></p>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="price-tag">$<?= number_format($booking['total_price'], 2) ?></span>
                                        <span class="text-muted">/ total</span>
                                    </div>
                                    <div>
                                        <?php if ($can_review): ?>
                                            <button class="btn review-btn" data-bs-toggle="collapse" data-bs-target="#reviewForm<?= $booking['id'] ?>">
                                                <i class="fas fa-star me-1"></i> Write a Review
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <?php if ($can_review): ?>
                                    <!-- Review Form (Collapsed by default) -->
                                    <div class="collapse mt-3" id="reviewForm<?= $booking['id'] ?>">
                                        <div class="review-form">
                                            <h4>Share Your Experience</h4>
                                            <form method="POST" action="">
                                                <input type="hidden" name="hotel_id" value="<?= $booking['hotel_id'] ?>">
                                                <input type="hidden" name="author_name" value="<?= htmlspecialchars($user_name) ?>">

                                                <div class="mb-3">
                                                    <label class="form-label">Rating</label>
                                                    <div class="star-rating">
                                                        <input type="radio" id="star5_<?= $booking['id'] ?>" name="rating" value="5" required />
                                                        <label for="star5_<?= $booking['id'] ?>"><i class="fas fa-star"></i></label>
                                                        <input type="radio" id="star4_<?= $booking['id'] ?>" name="rating" value="4" />
                                                        <label for="star4_<?= $booking['id'] ?>"><i class="fas fa-star"></i></label>
                                                        <input type="radio" id="star3_<?= $booking['id'] ?>" name="rating" value="3" />
                                                        <label for="star3_<?= $booking['id'] ?>"><i class="fas fa-star"></i></label>
                                                        <input type="radio" id="star2_<?= $booking['id'] ?>" name="rating" value="2" />
                                                        <label for="star2_<?= $booking['id'] ?>"><i class="fas fa-star"></i></label>
                                                        <input type="radio" id="star1_<?= $booking['id'] ?>" name="rating" value="1" />
                                                        <label for="star1_<?= $booking['id'] ?>"><i class="fas fa-star"></i></label>
                                                    </div>
                                                </div>

                                                <div class="mb-3">
                                                    <label for="review_text_<?= $booking['id'] ?>" class="form-label">Your Review</label>
                                                    <textarea class="form-control" id="review_text_<?= $booking['id'] ?>" name="review_text" rows="4" required placeholder="Share your experience at this hotel..."></textarea>
                                                </div>

                                                <button type="submit" name="submit_review" class="btn review-btn">Submit Review</button>
                                            </form>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if ($user_review): ?>
                                    <!-- View Review Button -->
                                    <button class="btn view-review-btn" data-bs-toggle="collapse" data-bs-target="#viewReview<?= $booking['id'] ?>">
                                        <i class="fas fa-eye me-1"></i> View Review
                                    </button>
                                    <!-- User Review (Collapsed by default) -->
                                    <div class="collapse mt-3" id="viewReview<?= $booking['id'] ?>">
                                        <div class="user-review">
                                            <h4>Your Review</h4>
                                            <div class="stars">
                                                <?php for ($i = 0; $i < 5; $i++): ?>
                                                    <i class="fas fa-star<?= $i < $user_review['rating'] ? ' filled' : '' ?>"></i>
                                                <?php endfor; ?>
                                            </div>
                                            <p class="review-text">"<?= htmlspecialchars($user_review['review_text']) ?>"</p>
                                            <p class="text-muted">Reviewed on <?= htmlspecialchars($user_review['review_date']) ?></p>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php
            $animation_delay += 0.2;
        endwhile;
        ?>
    <?php endif; ?>

</div>
<script>
// Initialize all modals
document.addEventListener('DOMContentLoaded', function() {
    var modals = document.querySelectorAll('.modal');
    
    modals.forEach(function(modal) {
        modal.addEventListener('show.bs.modal', function() {
            document.body.style.overflow = 'hidden';
            document.body.style.paddingRight = '0px';
        });
        
        modal.addEventListener('hidden.bs.modal', function() {
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
        });
    });
});
</script>

<?php require_once 'footer.php'; ?>