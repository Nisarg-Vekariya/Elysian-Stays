<?php
// Start session at the very beginning
session_start();

require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo '<div class="alert alert-warning">Please log in to view your hotel overview.</div>';
    exit;
}

$user_id = $_SESSION['user_id'];

// Get hotel ID for the logged-in user
$stmt = $conn->prepare("SELECT id FROM hotels WHERE user_id = ?");
$stmt->execute([$user_id]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$result) {
    // If no hotel exists, create one
    $stmt = $conn->prepare("INSERT INTO hotels (user_id, name) VALUES (?, 'My Hotel')");
    $stmt->execute([$user_id]);
    $hotel_id = $conn->lastInsertId();
} else {
    $hotel_id = $result['id'];
}

// Get total rooms
$stmt = $conn->prepare("SELECT COUNT(*) FROM rooms WHERE hotel_id = ?");
$stmt->execute([$hotel_id]);
$total_rooms = $stmt->fetchColumn();

// Get available rooms
$stmt = $conn->prepare("SELECT COUNT(*) FROM rooms WHERE hotel_id = ? AND status = 'available'");
$stmt->execute([$hotel_id]);
$available_rooms = $stmt->fetchColumn();

// Get total reviews
$stmt = $conn->prepare("SELECT COUNT(*) FROM reviews WHERE hotel_id = ?");
$stmt->execute([$hotel_id]);
$total_reviews = $stmt->fetchColumn();

// Get average rating
$stmt = $conn->prepare("SELECT AVG(rating) FROM reviews WHERE hotel_id = ?");
$stmt->execute([$hotel_id]);
$avg_rating_value = $stmt->fetchColumn();
$avg_rating = $avg_rating_value !== null ? number_format($avg_rating_value, 1) : '0.0';

// Get pending bookings count
$stmt = $conn->prepare("SELECT COUNT(*) FROM bookings WHERE hotel_id = ? AND status = 'pending'");
$stmt->execute([$hotel_id]);
$pending_bookings = $stmt->fetchColumn();
?>

<div class="stats-container">
    <div class="stat-card">
        <h3>Total Rooms</h3>
        <p class="stat-number"><?php echo $total_rooms; ?></p>
    </div>
    <div class="stat-card">
        <h3>Available Rooms</h3>
        <p class="stat-number"><?php echo $available_rooms; ?></p>
    </div>
    <div class="stat-card">
        <h3>Total Reviews</h3>
        <p class="stat-number"><?php echo $total_reviews; ?></p>
    </div>
    <div class="stat-card">
        <h3>Average Rating</h3>
        <p class="stat-number"><?php echo $avg_rating; ?> <i class="fas fa-star" style="color: #FFD700;"></i></p>
    </div>
    <div class="stat-card">
        <h3>Pending Bookings</h3>
        <p class="stat-number"><?php echo $pending_bookings; ?></p>
    </div>
</div>

<div class="content-section">
    <h2>Quick Actions</h2>
    <div class="quick-actions">
        <button class="btn btn-info" onclick="loadSection('bookings')">
            <i class="fas fa-calendar-check"></i> View Bookings
            <?php if ($pending_bookings > 0): ?>
                <span class="badge"><?php echo $pending_bookings; ?></span>
            <?php endif; ?>
        </button>
        <button class="btn btn-success" onclick="loadSection('reviews')">
            <i class="fas fa-star"></i> Manage Reviews
        </button>
        <button class="btn btn-warning" onclick="loadSection('profile')">
            <i class="fas fa-edit"></i> Edit Profile
        </button>
    </div>
</div>

<div class="content-section">
    <h2>Recent Reviews</h2>
    <div class="recent-reviews">
        <?php
        $stmt = $conn->prepare("SELECT * FROM reviews WHERE hotel_id = ? ORDER BY id DESC LIMIT 3");
        $stmt->execute([$hotel_id]);
        $recent_reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($recent_reviews) {
            foreach ($recent_reviews as $review) {
                echo '<div class="review-card">';
                echo '<div class="review-header">';
                echo '<strong>' . htmlspecialchars($review['author_name']) . '</strong>';
                echo '<span class="rating">' . str_repeat('⭐', $review['rating']) . '</span>';
                echo '</div>';
                echo '<p class="review-date">' . htmlspecialchars($review['review_date']) . '</p>';
                echo '<p class="review-text">' . htmlspecialchars($review['review_text']) . '</p>';
                echo '</div>';
            }
        } else {
            echo '<p class="no-data">No reviews yet.</p>';
        }
        ?>
    </div>
</div>

<!-- Add Room Modal -->
<div id="roomModal" class="modal">
    <div class="modal-content">
        <h3>Add New Room</h3>
        <form id="roomForm" onsubmit="handleRoomSubmit(event)">
            <input type="hidden" name="action" value="add">
            
            <div class="form-group">
                <label>Room Name:</label>
                <input type="text" name="name" required class="form-control">
            </div>
            
            <div class="form-group">
                <label>Description:</label>
                <textarea name="description" required class="form-control"></textarea>
            </div>
            
            <div class="form-group">
                <label>Image URL:</label>
                <input type="text" name="image" required class="form-control">
            </div>
            
            <div class="form-group">
                <label>Price per Night:</label>
                <input type="number" name="price" required min="0" step="0.01" class="form-control">
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="closeRoomModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Room</button>
            </div>
        </form>
    </div>
</div>

<style>
.quick-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.quick-actions .btn {
    width: 100%;
    padding: 15px;
    justify-content: center;
    font-size: 16px;
}

.btn-primary { background-color: var(--primary-color); }
.btn-info { background-color: var(--info); }
.btn-success { background-color: var(--success); }
.btn-warning { background-color: var(--warning); }
.btn-secondary { background-color: var(--secondary-color); }

.badge {
    background-color: var(--danger);
    color: white;
    padding: 2px 8px;
    border-radius: 10px;
    font-size: 12px;
    margin-left: 5px;
}

.review-card {
    background: white;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 15px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.review-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.review-date {
    color: #666;
    font-size: 14px;
    margin-bottom: 10px;
}

.review-text {
    color: #333;
}

.rating {
    color: #FFD700;
}

.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 1000;
}

.modal-content {
    background: white;
    width: 90%;
    max-width: 600px;
    margin: 50px auto;
    padding: 20px;
    border-radius: 8px;
    position: relative;
}
</style>

<script>
function showAddRoomModal() {
    document.getElementById('roomModal').style.display = 'block';
}

function closeRoomModal() {
    document.getElementById('roomModal').style.display = 'none';
}

function handleRoomSubmit(event) {
    event.preventDefault();
    const formData = new FormData(event.target);
    
    $.post('sections/rooms.php', formData, function(response) {
        if (response.success) {
            closeRoomModal();
            showNotification('Room added successfully', 'success');
            loadSection('rooms');
        } else {
            showNotification('Failed to add room', 'error');
        }
    }, 'json');
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('roomModal');
    if (event.target === modal) {
        closeRoomModal();
    }
}
</script> 