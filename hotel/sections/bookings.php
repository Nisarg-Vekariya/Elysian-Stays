<?php
require_once '../config/database.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<div class='alert alert-warning'>Please log in to view bookings.</div>";
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

// Handle booking operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_status':
                $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ? AND hotel_id = ?");
                $stmt->execute([$_POST['status'], $_POST['booking_id'], $hotel_id]);
                
                // If status is cancelled or completed, make the room available
                if (in_array($_POST['status'], ['cancelled', 'completed'])) {
                    $stmt = $conn->prepare("UPDATE rooms SET status = 'available' WHERE id = (SELECT room_id FROM bookings WHERE id = ?)");
                    $stmt->execute([$_POST['booking_id']]);
                }
                
                echo json_encode(['success' => true]);
                exit;
        }
    }
}

// Get total bookings count for pagination
$stmt = $conn->prepare("SELECT COUNT(*) FROM bookings WHERE hotel_id = ?");
$stmt->execute([$hotel_id]);
$total_bookings = $stmt->fetchColumn();

// Pagination settings
$bookings_per_page = 10;
$total_pages = ceil($total_bookings / $bookings_per_page);
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($current_page - 1) * $bookings_per_page;

// Get bookings with pagination
$sql = "
    SELECT b.*, r.name as room_name 
    FROM bookings b 
    LEFT JOIN rooms r ON b.room_id = r.id 
    WHERE b.hotel_id = :hotel_id
    ORDER BY b.id DESC 
    LIMIT $bookings_per_page OFFSET $offset
";

$stmt = $conn->prepare($sql);
$stmt->execute(['hotel_id' => $hotel_id]);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get booking statistics
$stmt = $conn->prepare("
    SELECT 
        COUNT(*) as total_bookings,
        SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_bookings,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_bookings,
        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_bookings,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_bookings,
        SUM(total_price) as total_revenue
    FROM bookings 
    WHERE hotel_id = ?
");
$stmt->execute([$hotel_id]);
$stats = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<style>
    /* Alert styles */
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
    
    /* Main content styles */
    .content-section {
        padding: 30px;
        max-width: 1200px;
        margin: 0 auto;
    }
    
    .bookings-dashboard {
        margin-bottom: 40px;
    }
    
    .content-section h2 {
        font-size: 28px;
        margin-bottom: 25px;
        color: #333;
        border-bottom: 2px solid #ad8b3a;
        padding-bottom: 10px;
        position: relative;
        display: flex;
        align-items: center;
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
    
    .content-section h2:before {
        content: '\f073';
        font-family: 'Font Awesome 5 Free';
        font-weight: 900;
        margin-right: 10px;
        color: #ad8b3a;
    }
    
    /* Stats cards */
    .stats-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .stat-card {
        background-color: white;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        padding: 20px;
        text-align: center;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    
    .stat-card:nth-child(1) {
        border-top: 3px solid #4a90e2;
    }
    
    .stat-card:nth-child(2) {
        border-top: 3px solid #50b45e;
    }
    
    .stat-card:nth-child(3) {
        border-top: 3px solid #f0ad4e;
    }
    
    .stat-card:nth-child(4) {
        border-top: 3px solid #5bc0de;
    }
    
    .stat-card:nth-child(5) {
        border-top: 3px solid #d9534f;
    }
    
    .stat-card:nth-child(6) {
        border-top: 3px solid #ad8b3a;
    }
    
    .stat-card h3 {
        font-size: 16px;
        color: #555;
        margin-top: 0;
        margin-bottom: 15px;
    }
    
    .stat-card h3 i {
        margin-right: 5px;
        color: #777;
    }
    
    .stat-value {
        font-size: 24px;
        font-weight: bold;
        color: #333;
        margin: 0;
    }
    
    /* Table header */
    .table-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }
    
    .table-header h3 {
        font-size: 18px;
        margin: 0;
        color: #444;
        display: flex;
        align-items: center;
    }
    
    .table-header h3 i {
        margin-right: 8px;
        color: #ad8b3a;
    }
    
    .table-info {
        font-size: 14px;
        color: #777;
    }
    
    /* Table styles */
    .table-responsive {
        background-color: white;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        padding: 20px;
        margin-bottom: 30px;
        overflow-x: auto;
    }
    
    .table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .table th {
        background-color: #f8f9fa;
        color: #555;
        padding: 12px 15px;
        text-align: left;
        font-weight: 600;
        border-bottom: 2px solid #ddd;
    }
    
    .table td {
        padding: 12px 15px;
        border-bottom: 1px solid #eee;
        color: #333;
        vertical-align: middle;
    }
    
    .table tbody tr:hover {
        background-color: #f8f9fa;
    }
    
    .table tbody tr:last-child td {
        border-bottom: none;
    }
    
    .text-center {
        text-align: center;
    }
    
    /* Room and guest info */
    .room-name {
        font-weight: 500;
        color: #333;
    }
    
    .guest-info {
        display: flex;
        flex-direction: column;
    }
    
    .guest-name {
        font-weight: 500;
        color: #333;
        margin-bottom: 3px;
    }
    
    .guest-email {
        font-size: 12px;
        color: #666;
    }
    
    /* Booking dates */
    .booking-dates {
        display: flex;
        flex-direction: column;
    }
    
    .date-range {
        margin-bottom: 3px;
    }
    
    .date-range i {
        color: #888;
        margin-right: 5px;
    }
    
    .nights-count {
        font-size: 12px;
        color: #777;
    }
    
    .price {
        font-weight: 600;
        color: #333;
    }
    
    /* No data message */
    .no-data {
        text-align: center;
        padding: 40px 20px;
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
        margin: 0 0 10px 0;
        font-size: 18px;
        color: #555;
    }
    
    .no-data span {
        color: #888;
        font-size: 14px;
    }
    
    /* Status styling */
    .status-select {
        padding: 7px 12px;
        border-radius: 4px;
        border: 1px solid #ddd;
        background-color: white;
        cursor: pointer;
        width: 100%;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%23666666' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        padding-right: 32px;
        transition: all 0.3s ease;
    }
    
    .status-select:focus {
        outline: none;
        box-shadow: 0 0 0 2px rgba(173, 139, 58, 0.3);
        border-color: #ad8b3a;
    }
    
    .status-select option:hover {
        background-color: #f8f9fa;
    }
    
    .status-select option[value="pending"]:hover {
        background-color: #fff3cd;
    }
    
    .status-select option[value="confirmed"]:hover {
        background-color: #d1e7dd;
    }
    
    .status-select option[value="completed"]:hover {
        background-color: #cfe2ff;
    }
    
    .status-select option[value="cancelled"]:hover {
        background-color: #f8d7da;
    }
    
    .status-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }
    
    .status-indicator {
        display: inline-block;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        margin-left: 8px;
    }
    
    .status-indicator.pending {
        background-color: #ffc107;
    }
    
    .status-indicator.confirmed {
        background-color: #198754;
    }
    
    .status-indicator.completed {
        background-color: #0d6efd;
    }
    
    .status-indicator.cancelled {
        background-color: #dc3545;
    }
    
    /* Action buttons */
    .btn {
        display: inline-block;
        font-weight: 400;
        text-align: center;
        vertical-align: middle;
        cursor: pointer;
        border: 1px solid transparent;
        padding: 0.375rem 0.75rem;
        font-size: 14px;
        line-height: 1.5;
        border-radius: 5px;
        transition: all 0.3s;
    }
    
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 12px;
    }
    
    .btn-info {
        background-color: #4a90e2;
        color: white;
        border-color: #4a90e2;
    }
    
    .btn-info:hover {
        background-color: #357ebd;
        border-color: #357ebd;
    }
    
    /* Pagination */
    .pagination {
        display: flex;
        justify-content: center;
        margin-top: 20px;
        gap: 5px;
        flex-wrap: wrap;
    }
    
    .pagination .btn {
        background-color: white;
        color: #333;
        border: 1px solid #ddd;
        min-width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        text-decoration: none;
    }
    
    .pagination .btn:hover {
        background-color: #f0f0f0;
        border-color: #ccc;
    }
    
    .pagination .btn.active {
        background-color: #ad8b3a;
        color: white;
        border-color: #ad8b3a;
    }
    
    .page-ellipsis {
        color: #aaa;
        padding: 0 5px;
        display: flex;
        align-items: center;
    }
    
    .prev-page, .next-page {
        color: #666;
    }
    
    /* Responsive design */
    @media (max-width: 992px) {
        .stats-container {
            grid-template-columns: repeat(3, 1fr);
        }
    }
    
    @media (max-width: 768px) {
        .stats-container {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .table th, .table td {
            padding: 10px 12px;
        }
        
        .table-header {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .table-info {
            margin-top: 5px;
        }
    }
    
    @media (max-width: 576px) {
        .stats-container {
            grid-template-columns: 1fr;
        }
        
        .content-section {
            padding: 20px 15px;
        }
        
        .stat-card {
            padding: 15px;
        }
        
        .table {
            font-size: 13px;
        }
    }
</style>

<div class="content-section bookings-dashboard">
    <h2>Bookings Management</h2>

    <!-- Booking Statistics -->
    <div class="stats-container">
        <div class="stat-card">
            <h3><i class="fas fa-calendar-check"></i> Total Bookings</h3>
            <p class="stat-value"><?php echo number_format($stats['total_bookings'] ?? 0); ?></p>
        </div>
        <div class="stat-card">
            <h3><i class="fas fa-check-circle"></i> Confirmed</h3>
            <p class="stat-value"><?php echo number_format($stats['confirmed_bookings'] ?? 0); ?></p>
        </div>
        <div class="stat-card">
            <h3><i class="fas fa-clock"></i> Pending</h3>
            <p class="stat-value"><?php echo number_format($stats['pending_bookings'] ?? 0); ?></p>
        </div>
        <div class="stat-card">
            <h3><i class="fas fa-clipboard-check"></i> Completed</h3>
            <p class="stat-value"><?php echo number_format($stats['completed_bookings'] ?? 0); ?></p>
        </div>
        <div class="stat-card">
            <h3><i class="fas fa-ban"></i> Cancelled</h3>
            <p class="stat-value"><?php echo number_format($stats['cancelled_bookings'] ?? 0); ?></p>
        </div>
        <div class="stat-card">
            <h3><i class="fas fa-dollar-sign"></i> Revenue</h3>
            <p class="stat-value">$<?php echo number_format($stats['total_revenue'] ?? 0, 2); ?></p>
        </div>
    </div>

    <!-- Bookings Table Header -->
    <div class="table-header">
        <h3><i class="fas fa-list"></i> Recent Bookings</h3>
        <div class="table-info">Showing <?php echo count($bookings); ?> of <?php echo $total_bookings; ?> bookings</div>
    </div>

    <!-- Bookings Table -->
    <div class="table-responsive">
        <?php if (empty($bookings)): ?>
            <div class="no-data">
                <i class="far fa-calendar-times"></i>
                <p>No bookings found for your hotel</p>
                <span>Bookings will appear here when guests reserve rooms at your property</span>
            </div>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Room</th>
                        <th>Guest</th>
                        <th>Dates</th>
                        <th>Price</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $booking): 
                        // Calculate nights
                        $check_in = new DateTime($booking['check_in_date']);
                        $check_out = new DateTime($booking['check_out_date']);
                        $nights = $check_in->diff($check_out)->days;
                    ?>
                        <tr>
                            <td><?php echo $booking['id']; ?></td>
                            <td>
                                <div class="room-name"><?php echo htmlspecialchars($booking['room_name']); ?></div>
                            </td>
                            <td>
                                <div class="guest-info">
                                    <span class="guest-name"><?php echo htmlspecialchars($booking['guest_name']); ?></span>
                                    <?php if (!empty($booking['guest_email'])): ?>
                                        <span class="guest-email"><?php echo htmlspecialchars($booking['guest_email']); ?></span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div class="booking-dates">
                                    <div class="date-range">
                                        <i class="fas fa-calendar-alt"></i> 
                                        <?php echo date('M d', strtotime($booking['check_in_date'])); ?> - 
                                        <?php echo date('M d, Y', strtotime($booking['check_out_date'])); ?>
                                    </div>
                                    <div class="nights-count"><?php echo $nights; ?> night<?php echo $nights > 1 ? 's' : ''; ?></div>
                                </div>
                            </td>
                            <td>
                                <div class="price">$<?php echo number_format($booking['total_price'], 2); ?></div>
                            </td>
                            <td>
                                <div class="status-wrapper">
                                    <select onchange="updateBookingStatus(<?php echo $booking['id']; ?>, this.value)" class="status-select">
                                        <option value="pending" <?php echo $booking['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="confirmed" <?php echo $booking['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                        <option value="completed" <?php echo $booking['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                        <option value="cancelled" <?php echo $booking['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                    <span class="status-indicator <?php echo $booking['status']; ?>"></span>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php if ($current_page > 1): ?>
                <a href="?section=bookings&page=<?php echo $current_page - 1; ?>" class="btn prev-page">
                    <i class="fas fa-chevron-left"></i>
                </a>
            <?php endif; ?>
            
            <?php
            // Show limited pagination buttons with ellipsis for many pages
            $startPage = max(1, $current_page - 2);
            $endPage = min($total_pages, $current_page + 2);
            
            if ($startPage > 1) {
                echo '<a href="?section=bookings&page=1" class="btn">1</a>';
                if ($startPage > 2) {
                    echo '<span class="page-ellipsis">...</span>';
                }
            }
            
            for ($i = $startPage; $i <= $endPage; $i++): ?>
                <a href="?section=bookings&page=<?php echo $i; ?>" 
                   class="btn <?php echo $i === $current_page ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor;
            
            if ($endPage < $total_pages) {
                if ($endPage < $total_pages - 1) {
                    echo '<span class="page-ellipsis">...</span>';
                }
                echo '<a href="?section=bookings&page=' . $total_pages . '" class="btn">' . $total_pages . '</a>';
            }
            ?>
            
            <?php if ($current_page < $total_pages): ?>
                <a href="?section=bookings&page=<?php echo $current_page + 1; ?>" class="btn next-page">
                    <i class="fas fa-chevron-right"></i>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function updateBookingStatus(bookingId, status) {
    if (confirm('Are you sure you want to update this booking status?')) {
        $.post('sections/bookings.php', {
            action: 'update_status',
            booking_id: bookingId,
            status: status
        }, function(response) {
            if (response.success) {
                showNotification('Booking status updated successfully', 'success');
                loadSection('bookings');
            } else {
                showNotification('Failed to update booking status', 'error');
            }
        }, 'json');
    }
}

function viewBookingDetails(bookingId) {
    // Implement booking details view
    alert('Booking details view will be implemented here');
}
</script> 