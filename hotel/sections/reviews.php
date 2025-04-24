<?php
require_once '../config/database.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<div class='alert alert-warning'>Please log in to view reviews.</div>";
    exit;
}

// Get hotel ID for the logged-in user
$stmt = $conn->prepare("SELECT id FROM hotels WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$result) {
    echo "<div class='alert alert-warning'>No hotel found for your account.</div>";
    exit;
}

$hotel_id = $result['id'];

// Handle review operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'delete':
                $stmt = $conn->prepare("DELETE FROM reviews WHERE id = ? AND hotel_id = ?");
                $stmt->execute([$_POST['review_id'], $hotel_id]);
                echo json_encode(['success' => true]);
                exit;
                
            case 'update_status':
                $stmt = $conn->prepare("UPDATE reviews SET status = ? WHERE id = ? AND hotel_id = ?");
                $stmt->execute([$_POST['status'], $_POST['review_id'], $hotel_id]);
                echo json_encode(['success' => true]);
                exit;
        }
    }
}

// Get all reviews with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Get total reviews for pagination
$stmt = $conn->prepare("SELECT COUNT(*) FROM reviews WHERE hotel_id = ?");
$stmt->execute([$hotel_id]);
$total_reviews = $stmt->fetchColumn();
$total_pages = ceil($total_reviews / $limit);

// Get paginated reviews
$sql = "SELECT * FROM reviews WHERE hotel_id = :hotel_id ORDER BY id DESC LIMIT $limit OFFSET $offset";
$stmt = $conn->prepare($sql);
$stmt->execute(['hotel_id' => $hotel_id]);

$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate average rating
$stmt = $conn->prepare("SELECT AVG(rating) as avg_rating FROM reviews WHERE hotel_id = ?");
$stmt->execute([$hotel_id]);
$avg_rating_data = $stmt->fetch(PDO::FETCH_ASSOC);
$avg_rating = $avg_rating_data['avg_rating'] !== null ? number_format($avg_rating_data['avg_rating'], 1) : '0.0';

// Get rating distribution
$stmt = $conn->prepare("SELECT rating, COUNT(*) as count FROM reviews WHERE hotel_id = ? GROUP BY rating ORDER BY rating DESC");
$stmt->execute([$hotel_id]);
$rating_distribution = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content-section reviews-dashboard">
    <h2><i class="fas fa-star-half-alt me-2"></i>Customer Reviews</h2>
    
    <!-- Reviews Summary -->
    <div class="stats-container">
        <div class="stat-card">
            <h3>Average Rating</h3>
            <div class="rating-display">
                <?php echo $avg_rating; ?> <i class="fas fa-star"></i>
            </div>
            <p>Based on <?php echo $total_reviews; ?> <?php echo $total_reviews == 1 ? 'review' : 'reviews'; ?></p>
        </div>
        
        <div class="stat-card">
            <h3>Rating Distribution</h3>
            <?php
            if ($total_reviews > 0) {
                foreach ($rating_distribution as $rating) {
                    $percentage = ($rating['count'] / $total_reviews) * 100;
                    echo '<div class="rating-bar">';
                    echo '<div class="rating-stars">' . str_repeat('⭐', $rating['rating']) . '</div>';
                    echo '<div class="rating-progress">';
                    echo '<div class="rating-progress-bar" style="width: ' . $percentage . '%;"></div>';
                    echo '</div>';
                    echo '<div class="rating-count">' . $rating['count'] . '</div>';
                    echo '</div>';
                }
            } else {
                echo '<div class="no-ratings"><i class="far fa-star"></i><p>No ratings yet</p></div>';
            }
            ?>
        </div>
    </div>

    <!-- Reviews Filter -->
    <div class="reviews-controls">
        <h3><i class="fas fa-list me-2"></i>Review List</h3>
        <div class="reviews-count">Showing <?php echo count($reviews); ?> of <?php echo $total_reviews; ?> reviews</div>
    </div>

    <!-- Reviews List -->
    <div class="reviews-list">
        <?php if (empty($reviews)): ?>
            <div class="no-data">
                <i class="far fa-comment-dots"></i>
                <p>No reviews yet. Reviews from your guests will appear here.</p>
            </div>
        <?php else: ?>
            <?php foreach ($reviews as $review): ?>
                <div class="review-card">
                    <div class="review-header">
                        <div class="reviewer-info">
                            <div class="reviewer-avatar">
                                <i class="fas fa-user-circle"></i>
                            </div>
                            <div class="reviewer-details">
                                <h3><?php echo htmlspecialchars($review['author_name']); ?></h3>
                                <div class="review-rating">
                                    <?php echo str_repeat('<i class="fas fa-star"></i>', $review['rating']); ?>
                                    <?php echo str_repeat('<i class="far fa-star"></i>', 5 - $review['rating']); ?>
                                </div>
                                <p class="review-date"><i class="far fa-calendar-alt me-1"></i><?php echo htmlspecialchars($review['review_date']); ?></p>
                            </div>
                        </div>
                        <div class="review-actions">
                            <select onchange="updateReviewStatus(<?php echo $review['id']; ?>, this.value)" class="status-select">
                                <option value="active" <?php echo $review['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo $review['status'] === 'inactive' ? 'selected' : ''; ?>>Hidden</option>
                            </select>
                            <button class="btn btn-danger" onclick="deleteReview(<?php echo $review['id']; ?>)" title="Delete Review">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="review-content">
                        <p class="review-text"><?php echo nl2br(htmlspecialchars($review['review_text'])); ?></p>
                    </div>
                    <?php if ($review['status'] === 'inactive'): ?>
                        <div class="review-status-badge">
                            <i class="fas fa-eye-slash"></i> Hidden from public view
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <button class="btn page-nav" onclick="loadReviewsPage(<?php echo $page - 1; ?>)">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                <?php endif; ?>
                
                <?php
                // Show limited pagination buttons with ellipsis for many pages
                $startPage = max(1, $page - 2);
                $endPage = min($total_pages, $page + 2);
                
                if ($startPage > 1) {
                    echo '<button class="btn" onclick="loadReviewsPage(1)">1</button>';
                    if ($startPage > 2) {
                        echo '<span class="page-ellipsis">...</span>';
                    }
                }
                
                for ($i = $startPage; $i <= $endPage; $i++): ?>
                    <button class="btn <?php echo $i === $page ? 'active' : ''; ?>"
                            onclick="loadReviewsPage(<?php echo $i; ?>)">
                        <?php echo $i; ?>
                    </button>
                <?php endfor;
                
                if ($endPage < $total_pages) {
                    if ($endPage < $total_pages - 1) {
                        echo '<span class="page-ellipsis">...</span>';
                    }
                    echo '<button class="btn" onclick="loadReviewsPage(' . $total_pages . ')">' . $total_pages . '</button>';
                }
                ?>
                
                <?php if ($page < $total_pages): ?>
                    <button class="btn page-nav" onclick="loadReviewsPage(<?php echo $page + 1; ?>)">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function deleteReview(reviewId) {
    if (confirm('Are you sure you want to delete this review?')) {
        $.post('sections/reviews.php', {
            action: 'delete',
            review_id: reviewId
        }, function(response) {
            if (response.success) {
                loadSection('reviews');
            }
        }, 'json');
    }
}

function updateReviewStatus(reviewId, status) {
    $.post('sections/reviews.php', {
        action: 'update_status',
        review_id: reviewId,
        status: status
    }, function(response) {
        if (response.success) {
            // Optionally show a success message
        }
    }, 'json');
}

function loadReviewsPage(page) {
    loadSection('reviews?page=' + page);
}
</script>

<style>
    .content-section {
        padding: 30px;
    }
    
    .reviews-dashboard {
        max-width: 1200px;
        margin: 0 auto;
    }
    
    .content-section h2 {
        font-size: 28px;
        margin-bottom: 25px;
        color: #333;
        border-bottom: 2px solid #ad8b3a;
        padding-bottom: 10px;
        position: relative;
    }
    
    .content-section h2:after {
        content: '';
        position: absolute;
        width: 80px;
        height: 3px;
        background-color: #ad8b3a;
        bottom: -2px;
        left: 0;
    }
    
    .me-1 {
        margin-right: 0.25rem;
    }
    
    .me-2 {
        margin-right: 0.5rem;
    }
    
    .stats-container {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 40px;
    }
    
    .stat-card {
        background-color: white;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        padding: 25px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    
    .stat-card h3 {
        color: #555;
        font-size: 18px;
        margin-bottom: 20px;
        text-align: center;
    }
    
    .rating-display {
        font-size: 42px;
        color: #333;
        text-align: center;
        margin-bottom: 10px;
        font-weight: bold;
    }
    
    .rating-display .fas {
        color: #FFD700;
        margin-left: 5px;
    }
    
    .stat-card p {
        text-align: center;
        color: #777;
        font-size: 14px;
    }
    
    .rating-bar {
        display: flex;
        align-items: center;
        margin-bottom: 12px;
    }
    
    .rating-stars {
        width: 75px;
        font-size: 14px;
    }
    
    .rating-progress {
        flex-grow: 1;
        height: 8px;
        background-color: #f0f0f0;
        border-radius: 10px;
        overflow: hidden;
        margin: 0 15px;
    }
    
    .rating-progress-bar {
        height: 100%;
        background: linear-gradient(to right, #ad8b3a, #d4af37);
        border-radius: 10px;
    }
    
    .rating-count {
        width: 30px;
        text-align: right;
        color: #666;
    }
    
    .no-ratings {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 20px;
        color: #aaa;
    }
    
    .no-ratings i {
        font-size: 32px;
        margin-bottom: 10px;
    }
    
    .no-ratings p {
        margin: 0;
    }
    
    /* Reviews controls */
    .reviews-controls {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    
    .reviews-controls h3 {
        font-size: 20px;
        margin: 0;
        color: #444;
    }
    
    .reviews-count {
        color: #666;
        font-size: 14px;
    }
    
    /* Review list styling */
    .reviews-list {
        margin-top: 20px;
    }
    
    .review-card {
        background-color: white;
        border-radius: 10px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.05);
        padding: 20px;
        margin-bottom: 20px;
        transition: transform 0.2s ease;
        position: relative;
    }
    
    .review-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .review-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 15px;
    }
    
    .reviewer-info {
        display: flex;
        align-items: flex-start;
    }
    
    .reviewer-avatar {
        font-size: 36px;
        color: #ccc;
        margin-right: 15px;
    }
    
    .reviewer-details h3 {
        margin: 0 0 5px 0;
        font-size: 18px;
        color: #333;
    }
    
    .review-rating {
        margin: 5px 0;
        color: #FFD700;
    }
    
    .review-rating .fas {
        color: #FFD700;
        margin-right: 2px;
    }
    
    .review-rating .far {
        color: #e0e0e0;
        margin-right: 2px;
    }
    
    .review-date {
        font-size: 14px;
        color: #888;
        margin: 5px 0 0;
    }
    
    .review-actions {
        display: flex;
        align-items: center;
    }
    
    .status-select {
        border: 1px solid #ddd;
        border-radius: 5px;
        padding: 8px 12px;
        margin-right: 10px;
        background-color: white;
        font-size: 14px;
        transition: border-color 0.3s;
        cursor: pointer;
    }
    
    .status-select:focus {
        outline: none;
        border-color: #ad8b3a;
    }
    
    .btn-danger {
        background-color: #ff5a5f;
        color: white;
        border: none;
        border-radius: 5px;
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }
    
    .btn-danger:hover {
        background-color: #e04146;
    }
    
    .review-content {
        padding-left: 51px; /* Aligns with the reviewer info */
    }
    
    .review-text {
        color: #555;
        line-height: 1.6;
        font-size: 15px;
        margin-bottom: 0;
    }
    
    .review-status-badge {
        position: absolute;
        bottom: 10px;
        right: 10px;
        background-color: #f8f9fa;
        color: #6c757d;
        padding: 5px 10px;
        font-size: 12px;
        border-radius: 15px;
        display: flex;
        align-items: center;
    }
    
    .review-status-badge i {
        margin-right: 5px;
    }
    
    .no-data {
        text-align: center;
        padding: 40px;
        color: #777;
        font-size: 16px;
        background-color: white;
        border-radius: 10px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.05);
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    
    .no-data i {
        font-size: 48px;
        color: #ddd;
        margin-bottom: 15px;
    }
    
    .no-data p {
        margin: 0;
    }
    
    /* Pagination styling */
    .pagination {
        display: flex;
        justify-content: center;
        margin-top: 30px;
        gap: 5px;
        align-items: center;
    }
    
    .pagination .btn {
        background-color: white;
        color: #555;
        border: 1px solid #ddd;
        border-radius: 5px;
        padding: 8px 15px;
        cursor: pointer;
        transition: all 0.3s ease;
        min-width: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .pagination .page-nav {
        padding: 8px 12px;
    }
    
    .pagination .btn:hover {
        background-color: #f8f8f8;
    }
    
    .pagination .btn.active {
        background-color: #ad8b3a;
        color: white;
        border-color: #ad8b3a;
    }
    
    .page-ellipsis {
        color: #aaa;
        padding: 0 5px;
    }
    
    /* Alert messages */
    .alert {
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-size: 15px;
    }
    
    .alert-warning {
        background-color: #fff3cd;
        color: #856404;
        border: 1px solid #ffeeba;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .stats-container {
            grid-template-columns: 1fr;
        }
        
        .review-header {
            flex-direction: column;
        }
        
        .review-actions {
            margin-top: 15px;
            align-self: flex-end;
        }
        
        .review-content {
            padding-left: 0;
        }
    }
</style> 