<?php require_once("header.php"); ?>
<?php 
// Database connection is already included in header.php

// Get summary data
function getSummaryData() {
    global $conn;
    $data = [];
    
    // Total bookings
    $bookingsQuery = "SELECT COUNT(*) as total_bookings FROM bookings";
    $bookingsResult = $conn->query($bookingsQuery);
    $data['total_bookings'] = ($bookingsResult && $bookingsResult->num_rows > 0) ? $bookingsResult->fetch_assoc()['total_bookings'] : 0;
    
    // Total revenue
    $revenueQuery = "SELECT SUM(total_price) as total_revenue FROM bookings";
    $revenueResult = $conn->query($revenueQuery);
    $data['total_revenue'] = ($revenueResult && $revenueResult->num_rows > 0) ? $revenueResult->fetch_assoc()['total_revenue'] : 0;
    
    // Pending approvals (bookings with pending status)
    $pendingQuery = "SELECT COUNT(*) as pending_count FROM bookings WHERE status = 'pending'";
    $pendingResult = $conn->query($pendingQuery);
    $data['pending_approvals'] = ($pendingResult && $pendingResult->num_rows > 0) ? $pendingResult->fetch_assoc()['pending_count'] : 0;
    
    // Active listings (available rooms)
    $listingsQuery = "SELECT COUNT(*) as listings_count FROM rooms WHERE status = 'available'";
    $listingsResult = $conn->query($listingsQuery);
    $data['active_listings'] = ($listingsResult && $listingsResult->num_rows > 0) ? $listingsResult->fetch_assoc()['listings_count'] : 0;
    
    return $data;
}

// Get recent bookings
function getRecentBookings($limit = 4) {
    global $conn;
    $bookings = [];
    $query = "SELECT b.id, b.guest_name, h.name as hotel_name, 
              DATEDIFF(b.check_out_date, b.check_in_date) as nights 
              FROM bookings b 
              JOIN hotels h ON b.hotel_id = h.id 
              ORDER BY b.created_at DESC 
              LIMIT $limit";
    
    $result = $conn->query($query);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $bookings[] = $row;
        }
    }
    return $bookings;
}

// Get new listings (recently added rooms)
function getNewListings($limit = 4) {
    global $conn;
    $listings = [];
    $query = "SELECT r.id, r.name, h.name as hotel_name 
              FROM rooms r 
              JOIN hotels h ON r.hotel_id = h.id 
              ORDER BY r.id DESC 
              LIMIT $limit";
    
    $result = $conn->query($query);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $listings[] = $row;
        }
    }
    return $listings;
}

// Get revenue data for last 30 days
function getRevenueData() {
    global $conn;
    $data = [];
    
    // Get dates for the last 30 days
    $dates = [];
    $revenues = [];
    
    for ($i = 29; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $dates[] = date('M d', strtotime($date)); // Format: Jan 01
        
        // Get revenue for this day
        $query = "SELECT SUM(total_price) as daily_revenue FROM bookings 
                  WHERE DATE(created_at) = '$date'";
        $result = $conn->query($query);
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $revenues[] = $row['daily_revenue'] ? floatval($row['daily_revenue']) : 0;
        } else {
            $revenues[] = 0;
        }
    }
    
    $data['dates'] = $dates;
    $data['revenues'] = $revenues;
    
    return $data;
}

// Get bookings by location
function getBookingsByLocation() {
    global $conn;
    $data = [];
    
    // This is a simplified example - adjust with actual city/location data in your schema
    $query = "SELECT h.name as location, COUNT(b.id) as booking_count 
              FROM bookings b 
              JOIN hotels h ON b.hotel_id = h.id 
              WHERE b.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
              GROUP BY h.name 
              ORDER BY booking_count DESC
              LIMIT 6";
    
    $result = $conn->query($query);
    if ($result && $result->num_rows > 0) {
        $locations = [];
        $counts = [];
        
        while ($row = $result->fetch_assoc()) {
            $locations[] = $row['location'];
            $counts[] = intval($row['booking_count']);
        }
        
        $data['locations'] = $locations;
        $data['counts'] = $counts;
    } else {
        // Sample data if no bookings
        $data['locations'] = ['No Data'];
        $data['counts'] = [0];
    }
    
    return $data;
}

// Fetch all data
$summaryData = getSummaryData();
$recentBookings = getRecentBookings();
$newListings = getNewListings();
$revenueData = getRevenueData();
$locationData = getBookingsByLocation();
?>
    <!-- Main Content -->
    <div class="container-fluid">
        <div class="row g-4">
            <!-- Booking Summary Section -->
            <div class="col-md-3 col-sm-6">
                <div class="card shadow-sm animate__animated animate__fadeInLeft">
                    <div class="card-header text-white" style="background-color: var(--primary-color);">Total Bookings</div>
                    <div class="card-body">
                        <h3 class="card-title"><?php echo $summaryData['total_bookings']; ?></h3>
                        <p class="card-text">Total bookings</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6">
                <div class="card shadow-sm animate__animated animate__fadeInUp">
                    <div class="card-header text-white" style="background-color: var(--secondary-color);">Revenue</div>
                    <div class="card-body">
                        <h3 class="card-title">$<?php echo number_format($summaryData['total_revenue'], 2); ?></h3>
                        <p class="card-text">Total revenue generated</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6">
                <div class="card shadow-sm animate__animated animate__fadeInRight">
                    <div class="card-header text-white" style="background-color: var(--primary-color);">Pending Approvals</div>
                    <div class="card-body">
                        <h3 class="card-title"><?php echo $summaryData['pending_approvals']; ?></h3>
                        <p class="card-text">Bookings awaiting approval</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6">
                <div class="card shadow-sm animate__animated animate__fadeInDown">
                    <div class="card-header text-white" style="background-color: var(--secondary-color);">Active Listings</div>
                    <div class="card-body">
                        <h3 class="card-title"><?php echo $summaryData['active_listings']; ?></h3>
                        <p class="card-text">Currently active listings</p>
                    </div>
                </div>
            </div>

            <!-- Recent Activity Section -->
            <div class="col-md-6">
                <div class="card shadow-sm animate__animated animate__fadeInLeft">
                    <div class="card-header text-white" style="background-color: var(--primary-color);">Recent Bookings</div>
                    <div class="card-body">
                        <ul class="list-group">
                            <?php if(empty($recentBookings)): ?>
                                <li class="list-group-item">No recent bookings found</li>
                            <?php else: ?>
                                <?php foreach($recentBookings as $booking): ?>
                                    <li class="list-group-item">
                                        <i class="fa fa-calendar-check-o me-2"></i>
                                        <?php echo htmlspecialchars($booking['guest_name']); ?> booked 
                                        <strong><?php echo htmlspecialchars($booking['hotel_name']); ?></strong> 
                                        for <?php echo $booking['nights']; ?> night<?php echo $booking['nights'] > 1 ? 's' : ''; ?>
                                    </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card shadow-sm animate__animated animate__fadeInRight">
                    <div class="card-header text-white" style="background-color: var(--secondary-color);">New Listings</div>
                    <div class="card-body">
                        <ul class="list-group">
                            <?php if(empty($newListings)): ?>
                                <li class="list-group-item">No new listings found</li>
                            <?php else: ?>
                                <?php foreach($newListings as $listing): ?>
                                    <li class="list-group-item">
                                        <i class="fa fa-home me-2"></i>
                                        <?php echo htmlspecialchars($listing['name']); ?> 
                                        <small class="text-muted">(<?php echo htmlspecialchars($listing['hotel_name']); ?>)</small>
                                    </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Financial Overview Section -->
            <div class="col-md-6">
                <div class="card shadow-sm animate__animated animate__fadeInLeft">
                    <div class="card-header text-white" style="background-color: var(--primary-color);">Revenue Trend</div>
                    <div class="card-body">
                        <h5 class="mb-3">Revenue over the last 30 days</h5>
                        <canvas id="revenueChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card shadow-sm animate__animated animate__fadeInRight">
                    <div class="card-header text-white" style="background-color: var(--secondary-color);">Bookings by Location</div>
                    <div class="card-body">
                        <h5 class="mb-3">Bookings in the last 30 days</h5>
                        <canvas id="locationChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

<!-- Include Chart.js library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Data for Revenue Chart
const revenueData = {
    labels: <?php echo json_encode($revenueData['dates']); ?>,
    datasets: [{
        label: 'Daily Revenue ($)',
        data: <?php echo json_encode($revenueData['revenues']); ?>,
        backgroundColor: 'rgba(173, 139, 58, 0.2)',
        borderColor: 'rgba(173, 139, 58, 1)',
        borderWidth: 2,
        tension: 0.4
    }]
};

// Revenue Chart Configuration
const revenueConfig = {
    type: 'line',
    data: revenueData,
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top',
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return `$${context.raw.toFixed(2)}`;
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '$' + value;
                    }
                }
            }
        }
    }
};

// Data for Location Chart
const locationData = {
    labels: <?php echo json_encode($locationData['locations']); ?>,
    datasets: [{
        label: 'Number of Bookings',
        data: <?php echo json_encode($locationData['counts']); ?>,
        backgroundColor: [
            'rgba(255, 99, 132, 0.7)',
            'rgba(54, 162, 235, 0.7)',
            'rgba(255, 206, 86, 0.7)',
            'rgba(75, 192, 192, 0.7)',
            'rgba(153, 102, 255, 0.7)',
            'rgba(255, 159, 64, 0.7)'
        ],
        borderColor: [
            'rgba(255, 99, 132, 1)',
            'rgba(54, 162, 235, 1)',
            'rgba(255, 206, 86, 1)',
            'rgba(75, 192, 192, 1)',
            'rgba(153, 102, 255, 1)',
            'rgba(255, 159, 64, 1)'
        ],
        borderWidth: 1
    }]
};

// Location Chart Configuration
const locationConfig = {
    type: 'bar',
    data: locationData,
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top',
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    precision: 0
                }
            }
        }
    }
};

// Initialize Charts when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Create Revenue Chart
    const revenueChart = new Chart(
        document.getElementById('revenueChart'),
        revenueConfig
    );
    
    // Create Location Chart
    const locationChart = new Chart(
        document.getElementById('locationChart'),
        locationConfig
    );
});
</script>

<?php require_once("footer.php"); ?>

