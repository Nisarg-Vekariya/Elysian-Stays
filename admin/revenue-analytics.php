<?php
require_once("header.php");
include_once("../db_connect.php"); // Include the database connection file

// Get current month and previous month
$currentMonth = date('Y-m');
$previousMonth = date('Y-m', strtotime('-1 month'));

// Calculate total revenue for current month
$totalRevenueQuery = "SELECT SUM(total_price) AS total_revenue 
                     FROM bookings 
                     WHERE DATE_FORMAT(created_at, '%Y-%m') = '$currentMonth'";
$totalRevenueResult = $conn->query($totalRevenueQuery);
$totalRevenue = $totalRevenueResult->fetch_assoc()['total_revenue'] ?? 0;

// Calculate total revenue for previous month
$prevMonthRevenueQuery = "SELECT SUM(total_price) AS prev_revenue 
                          FROM bookings 
                          WHERE DATE_FORMAT(created_at, '%Y-%m') = '$previousMonth'";
$prevMonthRevenueResult = $conn->query($prevMonthRevenueQuery);
$prevMonthRevenue = $prevMonthRevenueResult->fetch_assoc()['prev_revenue'] ?? 0;

// Calculate percentage change
$revenueChange = 0;
$revenueChangeText = "No data from last month";
if ($prevMonthRevenue > 0) {
    $revenueChange = (($totalRevenue - $prevMonthRevenue) / $prevMonthRevenue) * 100;
    $revenueChangeText = abs(round($revenueChange)) . "% " . ($revenueChange >= 0 ? "increase" : "decrease") . " from last month";
} elseif ($prevMonthRevenue == 0 && $totalRevenue > 0) {
    $revenueChangeText = "New revenue this month";
} elseif ($totalRevenue == 0 && $prevMonthRevenue == 0) {
    $revenueChangeText = "No revenue recorded";
}

// Get total bookings for current month
$bookingsQuery = "SELECT COUNT(*) AS total_bookings 
                 FROM bookings 
                 WHERE DATE_FORMAT(created_at, '%Y-%m') = '$currentMonth'";
$bookingsResult = $conn->query($bookingsQuery);
$totalBookings = $bookingsResult->fetch_assoc()['total_bookings'] ?? 0;

// Get total bookings for previous month
$prevBookingsQuery = "SELECT COUNT(*) AS prev_bookings 
                     FROM bookings 
                     WHERE DATE_FORMAT(created_at, '%Y-%m') = '$previousMonth'";
$prevBookingsResult = $conn->query($prevBookingsQuery);
$prevBookings = $prevBookingsResult->fetch_assoc()['prev_bookings'] ?? 0;

// Calculate percentage change for bookings
$bookingsChange = 0;
$bookingsChangeText = "No data from last month";
if ($prevBookings > 0) {
    $bookingsChange = (($totalBookings - $prevBookings) / $prevBookings) * 100;
    $bookingsChangeText = abs(round($bookingsChange)) . "% " . ($bookingsChange >= 0 ? "increase" : "decrease") . " from last month";
} elseif ($prevBookings == 0 && $totalBookings > 0) {
    $bookingsChangeText = "New bookings this month";
} elseif ($totalBookings == 0 && $prevBookings == 0) {
    $bookingsChangeText = "No bookings recorded";
}

// Get highest revenue day for current month
$highestDayQuery = "SELECT DATE(created_at) AS booking_date, SUM(total_price) AS daily_revenue 
                   FROM bookings 
                   WHERE DATE_FORMAT(created_at, '%Y-%m') = '$currentMonth' 
                   GROUP BY DATE(created_at) 
                   ORDER BY daily_revenue DESC 
                   LIMIT 1";
$highestDayResult = $conn->query($highestDayQuery);
$highestDay = $highestDayResult->fetch_assoc();

// Get lowest revenue day for current month (excluding days with zero revenue)
$lowestDayQuery = "SELECT DATE(created_at) AS booking_date, SUM(total_price) AS daily_revenue 
                  FROM bookings 
                  WHERE DATE_FORMAT(created_at, '%Y-%m') = '$currentMonth' 
                  GROUP BY DATE(created_at) 
                  HAVING daily_revenue > 0 
                  ORDER BY daily_revenue ASC 
                  LIMIT 1";
$lowestDayResult = $conn->query($lowestDayQuery);
$lowestDay = $lowestDayResult->fetch_assoc();

// Get top 3 users by revenue
$topUsersQuery = "SELECT 
    guest_name,
    guest_email,
    COUNT(*) as booking_count,
    SUM(total_price) as user_revenue
FROM bookings 
WHERE DATE_FORMAT(created_at, '%Y-%m') = '$currentMonth'
GROUP BY guest_email, guest_name
ORDER BY user_revenue DESC
LIMIT 3";

$topUsersResult = $conn->query($topUsersQuery);
$topUsers = [];
$totalUserRevenue = 0;

while ($row = $topUsersResult->fetch_assoc()) {
    $topUsers[] = $row;
    $totalUserRevenue += $row['user_revenue'];
}

// Get revenue by source
$sourceRevenueQuery = "SELECT 
    CASE 
        WHEN guest_email LIKE '%@gmail.com%' THEN 'Google'
        WHEN guest_email LIKE '%@yahoo.com%' THEN 'Yahoo'
        WHEN guest_email LIKE '%@hotmail.com%' THEN 'Microsoft'
        ELSE 'Other'
    END as source,
    COUNT(*) as booking_count,
    SUM(total_price) as source_revenue
FROM bookings 
WHERE DATE_FORMAT(created_at, '%Y-%m') = '$currentMonth'
GROUP BY source
ORDER BY source_revenue DESC";

$sourceRevenueResult = $conn->query($sourceRevenueQuery);
$sourceRevenue = [];
$totalSourceRevenue = 0;

while ($row = $sourceRevenueResult->fetch_assoc()) {
    $sourceRevenue[] = $row;
    $totalSourceRevenue += $row['source_revenue'];
}

// Get revenue by hotel - show only top 3 hotels
$hotelRevenueQuery = "SELECT h.id, h.name, SUM(b.total_price) AS hotel_revenue 
                      FROM bookings b 
                      JOIN hotels h ON b.hotel_id = h.id 
                      WHERE DATE_FORMAT(b.created_at, '%Y-%m') = '$currentMonth' 
                      GROUP BY h.id 
                      ORDER BY hotel_revenue DESC 
                      LIMIT 3";
$hotelRevenueResult = $conn->query($hotelRevenueQuery);
$hotelRevenue = [];
while ($row = $hotelRevenueResult->fetch_assoc()) {
    $hotelRevenue[] = $row;
}

// If no hotels with revenue, add a placeholder
if (empty($hotelRevenue)) {
    $hotelRevenue[] = [
        'id' => 0,
        'name' => 'No Data',
        'hotel_revenue' => 0
    ];
}

// Format month name for display
$monthName = date('F', strtotime($currentMonth));

// Get daily revenue data for the chart
$dailyRevenueQuery = "SELECT 
    DATE(created_at) as date,
    SUM(total_price) as daily_revenue,
    COUNT(*) as daily_bookings
FROM bookings 
WHERE DATE_FORMAT(created_at, '%Y-%m') = '$currentMonth'
GROUP BY DATE(created_at)
ORDER BY date ASC";

$dailyRevenueResult = $conn->query($dailyRevenueQuery);
$dailyRevenue = [];
$dailyBookings = [];
$dates = [];

while ($row = $dailyRevenueResult->fetch_assoc()) {
    $dates[] = date('M d', strtotime($row['date']));
    $dailyRevenue[] = $row['daily_revenue'];
    $dailyBookings[] = $row['daily_bookings'];
}
?>

<title>Revenue Analytics - Elysian Stays</title>
<div class="container mt-5 revenue-analytics">
    <h1 class="text-center mb-5 animate__animated animate__fadeIn" style="color: var(--primary-color);">Revenue Analytics</h1>
    
    <div class="row g-4">
        <!-- Revenue Overview -->
        <div class="col-lg-6 animate__animated animate__fadeInLeft">
            <div class="card h-100">
                <div class="card-header">
                    <h5><i class="fa fa-line-chart me-2"></i>Revenue Overview</h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <!-- Monthly Revenue -->
                        <div class="col-md-6">
                            <div class="revenue-card">
                                <h4>Total Revenue</h4>
                                <p class="revenue-value">$<?php echo number_format($totalRevenue, 2); ?></p>
                                <p class="revenue-description">This month (<?php echo $monthName; ?>)</p>
                                <p class="revenue-change">
                                    <?php if ($prevMonthRevenue > 0 && $revenueChange != 0): ?>
                                    <i class="fa fa-<?php echo $revenueChange >= 0 ? 'arrow-up text-success' : 'arrow-down text-danger'; ?> me-1"></i>
                                    <?php endif; ?>
                                    <?php echo $revenueChangeText; ?>
                                </p>
                            </div>
                        </div>
                        <!-- Bookings -->
                        <div class="col-md-6">
                            <div class="revenue-card">
                                <h4>Bookings</h4>
                                <p class="revenue-value"><?php echo $totalBookings; ?></p>
                                <p class="revenue-description">Total bookings this month</p>
                                <p class="revenue-change">
                                    <?php if ($prevBookings > 0 && $bookingsChange != 0): ?>
                                    <i class="fa fa-<?php echo $bookingsChange >= 0 ? 'arrow-up text-success' : 'arrow-down text-danger'; ?> me-1"></i>
                                    <?php endif; ?>
                                    <?php echo $bookingsChangeText; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenue by Day -->
        <div class="col-lg-6 animate__animated animate__fadeInRight">
            <div class="card h-100">
                <div class="card-header">
                    <h5><i class="fa fa-calendar-day me-2"></i>Revenue by Day</h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="revenue-card">
                                <h4>Highest Revenue Day</h4>
                                <p class="revenue-value">
                                    $<?php echo $highestDay ? number_format($highestDay['daily_revenue'], 2) : '0.00'; ?>
                                </p>
                                <p class="revenue-description">
                                    <?php echo $highestDay ? date('jS \of F', strtotime($highestDay['booking_date'])) : 'No data available'; ?>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="revenue-card">
                                <h4>Lowest Revenue Day</h4>
                                <p class="revenue-value">
                                    $<?php echo $lowestDay ? number_format($lowestDay['daily_revenue'], 2) : '0.00'; ?>
                                </p>
                                <p class="revenue-description">
                                    <?php echo $lowestDay ? date('jS \of F', strtotime($lowestDay['booking_date'])) : 'No data available'; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenue by Source -->
        <div class="col-lg-6 animate__animated animate__fadeInLeft">
            <div class="card h-100">
                <div class="card-header">
                    <h5><i class="fa fa-users me-2"></i>Top 3 Users by Revenue</h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <?php foreach ($topUsers as $index => $user): ?>
                        <div class="col-md-4">
                            <div class="revenue-card">
                                <h4>
                                    <?php 
                                    $displayName = htmlspecialchars($user['guest_name']);
                                    if (strlen($displayName) > 15) {
                                        $displayName = substr($displayName, 0, 15) . '...';
                                    }
                                    echo $displayName;
                                    ?>
                                </h4>
                                <p class="revenue-value">$<?php echo number_format($user['user_revenue'], 2); ?></p>
                                <p class="revenue-description">
                                    <?php 
                                    if ($totalUserRevenue > 0) {
                                        echo round(($user['user_revenue'] / $totalUserRevenue) * 100) . '% of total revenue';
                                    } else {
                                        echo 'No revenue recorded';
                                    }
                                    ?>
                                </p>
                                <p class="revenue-description">
                                    <?php echo $user['booking_count']; ?> bookings
                                </p>
                                <p class="revenue-description small">
                                    <?php echo htmlspecialchars($user['guest_email']); ?>
                                </p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenue by Each Hotel -->
        <div class="col-lg-6 animate__animated animate__fadeInRight">
            <div class="card h-100">
                <div class="card-header">
                    <h5><i class="fa fa-hotel me-2"></i>Top 3 Hotels by Revenue</h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <?php 
                        // Always use col-md-4 for 3 hotels
                        $colClass = "col-md-4";
                        
                        foreach ($hotelRevenue as $index => $hotel): 
                        ?>
                        <div class="<?php echo $colClass; ?>">
                            <div class="hotel-revenue-card">
                                <h5><?php echo htmlspecialchars($hotel['name']); ?></h5>
                                <p class="hotel-revenue-value">$<?php echo number_format($hotel['hotel_revenue'], 2); ?></p>
                                <p class="revenue-description">
                                    <?php 
                                    if ($hotel['hotel_revenue'] > 0) {
                                        echo round(($hotel['hotel_revenue'] / $totalRevenue) * 100) . '% of total revenue';
                                    } else {
                                        echo 'No bookings this month';
                                    }
                                    ?>
                                </p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenue Chart -->
        <div class="col-12 animate__animated animate__fadeInUp mt-4">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fa fa-chart-line me-2"></i>Daily Revenue & Bookings</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="position: relative; height: 400px;">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Chart.js library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('revenueChart').getContext('2d');
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($dates); ?>,
            datasets: [
                {
                    label: 'Daily Revenue ($)',
                    data: <?php echo json_encode($dailyRevenue); ?>,
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.1)',
                    tension: 0.4,
                    yAxisID: 'y',
                    borderWidth: 2
                },
                {
                    label: 'Number of Bookings',
                    data: <?php echo json_encode($dailyBookings); ?>,
                    borderColor: 'rgb(255, 99, 132)',
                    backgroundColor: 'rgba(255, 99, 132, 0.1)',
                    tension: 0.4,
                    yAxisID: 'y1',
                    borderWidth: 2
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Revenue ($)',
                        font: {
                            size: 14,
                            weight: 'bold'
                        }
                    },
                    grid: {
                        drawOnChartArea: false
                    },
                    ticks: {
                        font: {
                            size: 12
                        }
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Number of Bookings',
                        font: {
                            size: 14,
                            weight: 'bold'
                        }
                    },
                    grid: {
                        drawOnChartArea: false
                    },
                    ticks: {
                        font: {
                            size: 12
                        }
                    }
                },
                x: {
                    ticks: {
                        font: {
                            size: 12
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.datasetIndex === 0) {
                                label += '$' + context.parsed.y.toFixed(2);
                            } else {
                                label += context.parsed.y;
                            }
                            return label;
                        }
                    },
                    bodyFont: {
                        size: 14
                    },
                    titleFont: {
                        size: 16,
                        weight: 'bold'
                    }
                },
                legend: {
                    position: 'top',
                    labels: {
                        font: {
                            size: 14,
                            weight: 'bold'
                        }
                    }
                }
            }
        }
    });
});
</script>

<?php 
// Close database connection
$conn->close(); 
require_once("footer.php"); 
?>
