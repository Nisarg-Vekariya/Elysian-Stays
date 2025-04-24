<?php
require_once '../config/database.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<div class='alert alert-warning'>Please log in to view revenue analytics.</div>";
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

// Get current month and previous month
$currentMonth = date('Y-m');
$previousMonth = date('Y-m', strtotime('-1 month'));

// Get total revenue for current month
$stmt = $conn->prepare("SELECT SUM(total_price) AS total_revenue FROM bookings WHERE hotel_id = ? AND DATE_FORMAT(created_at, '%Y-%m') = ?");
$stmt->execute([$hotel_id, $currentMonth]);
$currentMonthRevenue = $stmt->fetch(PDO::FETCH_ASSOC)['total_revenue'] ?? 0;

// Get total revenue for previous month
$stmt = $conn->prepare("SELECT SUM(total_price) AS total_revenue FROM bookings WHERE hotel_id = ? AND DATE_FORMAT(created_at, '%Y-%m') = ?");
$stmt->execute([$hotel_id, $previousMonth]);
$previousMonthRevenue = $stmt->fetch(PDO::FETCH_ASSOC)['total_revenue'] ?? 0;

// Calculate revenue change percentage
$revenueChange = $previousMonthRevenue > 0 ? (($currentMonthRevenue - $previousMonthRevenue) / $previousMonthRevenue) * 100 : 0;

// Get total bookings for current month
$stmt = $conn->prepare("SELECT COUNT(*) AS total_bookings FROM bookings WHERE hotel_id = ? AND DATE_FORMAT(created_at, '%Y-%m') = ?");
$stmt->execute([$hotel_id, $currentMonth]);
$currentMonthBookings = $stmt->fetch(PDO::FETCH_ASSOC)['total_bookings'] ?? 0;

// Get total bookings for previous month
$stmt = $conn->prepare("SELECT COUNT(*) AS total_bookings FROM bookings WHERE hotel_id = ? AND DATE_FORMAT(created_at, '%Y-%m') = ?");
$stmt->execute([$hotel_id, $previousMonth]);
$previousMonthBookings = $stmt->fetch(PDO::FETCH_ASSOC)['total_bookings'] ?? 0;

// Calculate bookings change percentage
$bookingsChange = $previousMonthBookings > 0 ? (($currentMonthBookings - $previousMonthBookings) / $previousMonthBookings) * 100 : 0;

// Get average booking value
$stmt = $conn->prepare("SELECT AVG(total_price) AS avg_booking_value FROM bookings WHERE hotel_id = ? AND DATE_FORMAT(created_at, '%Y-%m') = ?");
$stmt->execute([$hotel_id, $currentMonth]);
$avgBookingValue = $stmt->fetch(PDO::FETCH_ASSOC)['avg_booking_value'] ?? 0;

// Get total rooms
$stmt = $conn->prepare("SELECT COUNT(*) AS total_rooms FROM rooms WHERE hotel_id = ?");
$stmt->execute([$hotel_id]);
$totalRooms = $stmt->fetch(PDO::FETCH_ASSOC)['total_rooms'] ?? 0;

// Calculate occupancy rate
$daysInMonth = date('t');
$totalRoomNights = $totalRooms * $daysInMonth;
$stmt = $conn->prepare("
    SELECT COUNT(*) AS booked_nights 
    FROM bookings 
    WHERE hotel_id = ? 
    AND DATE_FORMAT(created_at, '%Y-%m') = ?
    AND status IN ('confirmed', 'completed')
");
$stmt->execute([$hotel_id, $currentMonth]);
$bookedNights = $stmt->fetch(PDO::FETCH_ASSOC)['booked_nights'] ?? 0;
$occupancyRate = $totalRoomNights > 0 ? ($bookedNights / $totalRoomNights) * 100 : 0;

// Get all-time monthly revenue data for the chart
$stmt = $conn->prepare("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        SUM(total_price) as monthly_revenue,
        COUNT(*) as monthly_bookings
    FROM bookings 
    WHERE hotel_id = ? 
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month ASC
");
$stmt->execute([$hotel_id]);
$monthlyData = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total all-time revenue
$stmt = $conn->prepare("SELECT SUM(total_price) AS total_revenue FROM bookings WHERE hotel_id = ?");
$stmt->execute([$hotel_id]);
$allTimeRevenue = $stmt->fetch(PDO::FETCH_ASSOC)['total_revenue'] ?? 0;

// Get total all-time bookings
$stmt = $conn->prepare("SELECT COUNT(*) AS total_bookings FROM bookings WHERE hotel_id = ?");
$stmt->execute([$hotel_id]);
$allTimeBookings = $stmt->fetch(PDO::FETCH_ASSOC)['total_bookings'] ?? 0;

// Prepare data for chart
$months = [];
$monthlyRevenues = [];
$monthlyBookings = [];

foreach ($monthlyData as $data) {
    $months[] = date('M Y', strtotime($data['month'] . '-01'));
    $monthlyRevenues[] = $data['monthly_revenue'];
    $monthlyBookings[] = $data['monthly_bookings'];
}

// If no data, add current month with zeros
if (empty($months)) {
    $months[] = date('M Y');
    $monthlyRevenues[] = 0;
    $monthlyBookings[] = 0;
}

// Get booking status distribution
$stmt = $conn->prepare("
    SELECT 
        status,
        COUNT(*) as count,
        SUM(total_price) as revenue
    FROM bookings 
    WHERE hotel_id = ? AND DATE_FORMAT(created_at, '%Y-%m') = ?
    GROUP BY status
");
$stmt->execute([$hotel_id, $currentMonth]);
$statusData = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Prepare status data for chart
$statusLabels = [];
$statusCounts = [];
$statusRevenues = [];

foreach ($statusData as $status) {
    $statusLabels[] = ucfirst($status['status']);
    $statusCounts[] = $status['count'];
    $statusRevenues[] = $status['revenue'];
}
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
    
    .revenue-dashboard {
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
        content: '\f201';
        font-family: 'Font Awesome 5 Free';
        font-weight: 900;
        margin-right: 10px;
        color: #ad8b3a;
    }
    
    /* Stats cards */
    .stats-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
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
    
    .stat-change {
        font-size: 14px;
        margin-top: 10px;
    }
    
    .stat-change.positive {
        color: #28a745;
    }
    
    .stat-change.negative {
        color: #dc3545;
    }
    
    /* Chart containers */
    .chart-container {
        background-color: white;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        padding: 20px;
        margin-bottom: 30px;
    }
    
    .chart-container h3 {
        font-size: 18px;
        margin-bottom: 20px;
        color: #444;
        display: flex;
        align-items: center;
    }
    
    .chart-container h3 i {
        margin-right: 8px;
        color: #ad8b3a;
    }
    
    /* Status distribution */
    .status-distribution {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 15px;
        margin-top: 20px;
    }
    
    .status-item {
        background-color: white;
        border-radius: 8px;
        padding: 15px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.05);
    }
    
    .status-item h4 {
        font-size: 14px;
        margin-bottom: 10px;
        color: #555;
    }
    
    .status-value {
        font-size: 18px;
        font-weight: bold;
        color: #333;
    }
    
    /* Responsive design */
    @media (max-width: 992px) {
        .stats-container {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    
    @media (max-width: 576px) {
        .stats-container {
            grid-template-columns: 1fr;
        }
        
        .content-section {
            padding: 20px 15px;
        }
    }
    
    /* Highlight card styles */
    .highlight-card {
        background: linear-gradient(135deg, #ad8b3a20, #ffffff);
        border-left: 4px solid #ad8b3a;
    }
    
    .highlight-card .stat-value {
        font-size: 28px;
        color: #ad8b3a;
    }
</style>

<div class="content-section revenue-dashboard">
    <h2 class="animate__animated animate__fadeIn">Revenue Analytics</h2>

    <!-- All-Time Revenue Stats -->
    <div class="stats-container">
        <div class="stat-card animate__animated animate__fadeInUp animate-delay-100">
            <h3><i class="fas fa-dollar-sign"></i> Monthly Revenue</h3>
            <p class="stat-value">$<?php echo number_format($currentMonthRevenue, 2); ?></p>
            <p class="stat-change <?php echo $revenueChange >= 0 ? 'positive' : 'negative'; ?>">
                <i class="fas fa-<?php echo $revenueChange >= 0 ? 'arrow-up' : 'arrow-down'; ?>"></i>
                <?php echo abs(round($revenueChange, 1)); ?>% from last month
            </p>
        </div>
        <div class="stat-card animate__animated animate__fadeInUp animate-delay-200">
            <h3><i class="fas fa-calendar-check"></i> Monthly Bookings</h3>
            <p class="stat-value"><?php echo $currentMonthBookings; ?></p>
            <p class="stat-change <?php echo $bookingsChange >= 0 ? 'positive' : 'negative'; ?>">
                <i class="fas fa-<?php echo $bookingsChange >= 0 ? 'arrow-up' : 'arrow-down'; ?>"></i>
                <?php echo abs(round($bookingsChange, 1)); ?>% from last month
            </p>
        </div>
        <div class="stat-card animate__animated animate__fadeInUp animate-delay-300">
            <h3><i class="fas fa-chart-pie"></i> Average Booking Value</h3>
            <p class="stat-value">$<?php echo number_format($avgBookingValue, 2); ?></p>
        </div>
        <div class="stat-card animate__animated animate__fadeInUp animate-delay-400">
            <h3><i class="fas fa-bed"></i> Occupancy Rate</h3>
            <p class="stat-value"><?php echo round($occupancyRate, 1); ?>%</p>
        </div>
    </div>

    <!-- All-Time Revenue Summary -->
    <div class="stats-container">
        <div class="stat-card highlight-card animate__animated animate__zoomIn animate-delay-100">
            <h3><i class="fas fa-money-bill-wave"></i> All-Time Revenue</h3>
            <p class="stat-value animate__animated animate__pulse animate__infinite animate__slower">$<?php echo number_format($allTimeRevenue, 2); ?></p>
        </div>
        <div class="stat-card highlight-card animate__animated animate__zoomIn animate-delay-300">
            <h3><i class="fas fa-users"></i> All-Time Bookings</h3>
            <p class="stat-value animate__animated animate__pulse animate__infinite animate__slower">
                <?php echo number_format($allTimeBookings); ?>
            </p>
        </div>
    </div>

    <!-- Booking Status Distribution -->
    <div class="chart-container animate__animated animate__fadeIn animate-delay-500">
        <h3><i class="fas fa-chart-pie"></i> Booking Status Distribution</h3>
        <div class="status-distribution">
            <?php 
            $delay = 100;
            foreach ($statusData as $status): 
                $delay += 100;
                $delayClass = "animate-delay-" . ($delay > 500 ? "500" : $delay);
            ?>
                <div class="status-item animate__animated animate__fadeInRight <?php echo $delayClass; ?>">
                    <h4><?php echo ucfirst($status['status']); ?></h4>
                    <p class="status-value"><?php echo $status['count']; ?> bookings</p>
                    <p class="text-muted">$<?php echo number_format($status['revenue'], 2); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div> 