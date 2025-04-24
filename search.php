<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
    ]);
}

// Include database connection
require_once 'db_connect.php';

// Restrict access to logged-in users
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Initialize search parameters
$search_destination = isset($_GET['destination']) ? trim($_GET['destination']) : '';
$search_checkin = isset($_GET['check_in']) ? $_GET['check_in'] : '';
$search_checkout = isset($_GET['check_out']) ? $_GET['check_out'] : '';
$search_guests = isset($_GET['guests']) ? intval($_GET['guests']) : 0;
$search_filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$selected_amenities = isset($_GET['selected_amenities']) ? $_GET['selected_amenities'] : [];

// Fetch all hotels with their lowest room price
$hotel_query = "SELECT h.*, MIN(r.price) as min_price, 
                (SELECT ROUND(AVG(rev.rating)) FROM reviews rev WHERE rev.hotel_id = h.id AND rev.status = 'active') as avg_rating 
                FROM hotels h 
                LEFT JOIN rooms r ON h.id = r.hotel_id
                WHERE 1=1 ";

// Add search filters
$params = array();
if (!empty($search_destination)) {
    $hotel_query .= " AND (h.name LIKE ? OR h.tagline LIKE ? OR h.about_description1 LIKE ?)";
    $search_param = "%$search_destination%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

// Filter by room capacity (guests)
if ($search_guests > 0) {
    $hotel_query .= " AND EXISTS (SELECT 1 FROM rooms WHERE hotel_id = h.id AND capacity >= ?)";
    $params[] = $search_guests;
}

// Filter by date availability if dates are provided
if (!empty($search_checkin) && !empty($search_checkout)) {
    // Find hotels with rooms that aren't booked for the selected dates
    $hotel_query .= " AND EXISTS (
        SELECT 1 FROM rooms rm 
        WHERE rm.hotel_id = h.id 
        AND rm.status = 'available'
        AND NOT EXISTS (
            SELECT 1 FROM bookings b 
            WHERE b.room_id = rm.id 
            AND b.status IN ('confirmed', 'pending')
            AND (
                (b.check_in_date <= ? AND b.check_out_date > ?) OR
                (b.check_in_date < ? AND b.check_out_date >= ?) OR
                (b.check_in_date >= ? AND b.check_out_date <= ?)
            )
        )
    )";
    
    $params[] = $search_checkout; // End date of booking must be after our check-in
    $params[] = $search_checkin;  // Start date of booking must be before our check-out
    $params[] = $search_checkout; // For the overlap case
    $params[] = $search_checkin;  // For the overlap case
    $params[] = $search_checkin;  // For the containment case
    $params[] = $search_checkout; // For the containment case
}

// Filter by amenities if selected
if (!empty($selected_amenities)) {
    // Modified to find hotels that have ALL the selected amenities
    $hotel_query .= " AND (
        SELECT COUNT(DISTINCT a.name) 
        FROM hotel_amenities ha 
        JOIN amenities a ON ha.amenity_id = a.id 
        WHERE ha.hotel_id = h.id AND a.name IN (";
    
    $placeholders = str_repeat('?,', count($selected_amenities) - 1) . '?';
    $hotel_query .= $placeholders . ")) = ?";
    
    // Add amenity names to params
    foreach ($selected_amenities as $amenity) {
        $params[] = $amenity;
    }
    
    // Add count of selected amenities to ensure ALL selected amenities are present
    $params[] = count($selected_amenities);
}

// Group by hotel
$hotel_query .= " GROUP BY h.id";

// Add ordering based on filter
if ($search_filter == 'price-low') {
    $hotel_query .= " ORDER BY min_price ASC";
} else if ($search_filter == 'price-high') {
    $hotel_query .= " ORDER BY min_price DESC";
} else if ($search_filter == 'rating') {
    $hotel_query .= " ORDER BY avg_rating DESC, min_price ASC";
} else {
    $hotel_query .= " ORDER BY h.name ASC"; // Default ordering
}

// Prepare and execute the query
$stmt = $conn->prepare($hotel_query);
if (!empty($params)) {
    $types = str_repeat('s', count($params));
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$hotels = [];
while ($row = $result->fetch_assoc()) {
    $hotels[] = $row;
}
$stmt->close();

// Fetch available amenities for filtering
$amenities_query = "SELECT DISTINCT a.name 
                    FROM amenities a 
                    LEFT JOIN hotel_amenities ha ON a.id = ha.amenity_id 
                    WHERE ha.hotel_id IS NOT NULL OR a.hotel_id IS NULL 
                    ORDER BY a.name";
$amenities_result = $conn->query($amenities_query);
$amenities = [];
if ($amenities_result && $amenities_result->num_rows > 0) {
    while ($row = $amenities_result->fetch_assoc()) {
        $amenities[] = $row['name'];
    }
}

// Function to preserve selected amenities in URL
function buildUrlWithAmenities($base_url, $amenities) {
    if (empty($amenities)) return $base_url;
    
    $url = $base_url;
    foreach ($amenities as $amenity) {
        $url .= '&selected_amenities[]=' . urlencode($amenity);
    }
    return $url;
}
?>

<?php require_once 'header.php'; ?>
<title>Elysian Stays - Find Your Perfect Hotel</title>
<link rel="stylesheet" href="css/search.css">
<style>
    /* Hotel thumbnail image styling */
    .hotel-card .card-img-top {
        height: 200px;
        object-fit: cover;
        object-position: center;
        width: 100%;
    }
    
    /* Consistent height for hotel cards */
    .hotel-card {
        height: 100%;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .hotel-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    
    /* Card body with consistent height */
    .hotel-card .card-body {
        display: flex;
        flex-direction: column;
    }
    
    .hotel-card .hotel-features {
        margin-top: auto;
    }
</style>
<div class="hero-section">
    <div class="container">
        <h1 class="text-center mb-4 animate__animated animate__fadeInDown" style="font-size: 60px;
            margin-bottom: -20px;">Discover Luxury Stays</h1>
        <p class="text-center mb-5 fs-5 animate__animated animate__fadeInUp">Find your perfect getaway in our handpicked selection of premium hotels</p>
    </div>
</div>

<div class="container mt-5">
    <div class="search-container p-4 mb-5 animate__animated animate__fadeIn">
        <h2 class="search-title mb-4 h2mod">Search Your Dream Hotel</h2>
        <div class="divider"></div>
        <form id="search-form" method="GET" action="search.php">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="destination" class="form-label">Destination</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa fa-map-marker"></i></span>
                        <input type="text" class="form-control" id="destination" name="destination" placeholder="Where are you going?" value="<?php echo htmlspecialchars($search_destination); ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <label for="check-in" class="form-label">Check-in</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                        <input type="date" class="form-control" id="check-in" name="check_in" value="<?php echo htmlspecialchars($search_checkin); ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <label for="check-out" class="form-label">Check-out</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                        <input type="date" class="form-control" id="check-out" name="check_out" value="<?php echo htmlspecialchars($search_checkout); ?>">
                    </div>
                </div>
                <div class="col-md-2">
                    <label for="guests" class="form-label">Guests</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa fa-user"></i></span>
                        <select class="form-select" id="guests" name="guests">
                            <option value="0" <?php echo $search_guests == 0 ? 'selected' : ''; ?>>Any</option>
                            <option value="1" <?php echo $search_guests == 1 ? 'selected' : ''; ?>>1</option>
                            <option value="2" <?php echo $search_guests == 2 ? 'selected' : ''; ?>>2</option>
                            <option value="3" <?php echo $search_guests == 3 ? 'selected' : ''; ?>>3</option>
                            <option value="4" <?php echo $search_guests == 4 ? 'selected' : ''; ?>>4+</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <?php if (!empty($selected_amenities)): ?>
                <?php foreach ($selected_amenities as $amenity): ?>
                    <input type="hidden" name="selected_amenities[]" value="<?php echo htmlspecialchars($amenity); ?>">
                <?php endforeach; ?>
            <?php endif; ?>
            
            <div class="text-center mt-4">
                <button type="submit" class="btn btn-search btn-lg">
                    <i class="fa fa-search me-2"></i>Search Hotels
                </button>
            </div>
        </form>
    </div>

    <div class="filter-container p-4 mb-5 animate__animated animate__fadeIn">
        <h2 class="filter-title h2mod mb-3">Refine Your Search</h2>
        <div class="divider"></div>
        <div class="btn-group d-flex flex-wrap" role="group" aria-label="Hotel filters" id="filter-buttons">
            <a href="<?php echo buildUrlWithAmenities('?destination=' . urlencode($search_destination) . '&check_in=' . urlencode($search_checkin) . '&check_out=' . urlencode($search_checkout) . '&guests=' . $search_guests . '&filter=all', $selected_amenities); ?>"
                class="btn btn-filter <?php echo $search_filter == 'all' ? 'active' : ''; ?> m-1">
                <i class="fa fa-th-large me-2"></i>All
            </a>
            <a href="<?php echo buildUrlWithAmenities('?destination=' . urlencode($search_destination) . '&check_in=' . urlencode($search_checkin) . '&check_out=' . urlencode($search_checkout) . '&guests=' . $search_guests . '&filter=price-low', $selected_amenities); ?>"
                class="btn btn-filter <?php echo $search_filter == 'price-low' ? 'active' : ''; ?> m-1">
                <i class="fa fa-usd me-2"></i>Price (Low to High)
            </a>
            <a href="<?php echo buildUrlWithAmenities('?destination=' . urlencode($search_destination) . '&check_in=' . urlencode($search_checkin) . '&check_out=' . urlencode($search_checkout) . '&guests=' . $search_guests . '&filter=price-high', $selected_amenities); ?>"
                class="btn btn-filter <?php echo $search_filter == 'price-high' ? 'active' : ''; ?> m-1">
                <i class="fa fa-usd me-2"></i>Price (High to Low)
            </a>
            <a href="<?php echo buildUrlWithAmenities('?destination=' . urlencode($search_destination) . '&check_in=' . urlencode($search_checkin) . '&check_out=' . urlencode($search_checkout) . '&guests=' . $search_guests . '&filter=rating', $selected_amenities); ?>"
                class="btn btn-filter <?php echo $search_filter == 'rating' ? 'active' : ''; ?> m-1">
                <i class="fa fa-star me-2"></i>Rating
            </a>
            <button type="button" class="btn btn-filter m-1" data-bs-toggle="modal" data-bs-target="#amenitiesModal">
                <i class="fa fa-list-ul me-2"></i>Amenities <?php echo !empty($selected_amenities) ? '(' . count($selected_amenities) . ')' : ''; ?>
            </button>
        </div>
    </div>

    <div class="row" id="hotel-list">
        <?php if (empty($hotels)): ?>
            <div class="col-12 text-center p-5">
                <h3>No hotels found matching your criteria</h3>
                <p>Try adjusting your search parameters or explore our recommendations below.</p>
                
                <?php
                // Show some popular hotels as recommendations
                $recommend_query = "SELECT h.*, MIN(r.price) as min_price, 
                    (SELECT ROUND(AVG(rev.rating)) FROM reviews rev WHERE rev.hotel_id = h.id AND rev.status = 'active') as avg_rating 
                    FROM hotels h 
                    LEFT JOIN rooms r ON h.id = r.hotel_id
                    GROUP BY h.id
                    ORDER BY avg_rating DESC, min_price ASC
                    LIMIT 3";
                $recommend_result = $conn->query($recommend_query);
                if ($recommend_result && $recommend_result->num_rows > 0):
                ?>
                <div class="mt-5">
                    <h4 class="mb-4">Popular Hotels You Might Like</h4>
                    <div class="row">
                    <?php while ($hotel = $recommend_result->fetch_assoc()): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card hotel-card">
                                <img src="<?php echo !empty($hotel['background_image']) ? htmlspecialchars($hotel['background_image']) : 'Images/hotel1_bg.webp'; ?>"
                                    class="card-img-top" alt="<?php echo htmlspecialchars($hotel['name']); ?>">
                                <div class="card-body">
                                    <h5 class="card-title hotel-name"><?php echo htmlspecialchars($hotel['name']); ?></h5>
                                    <p class="card-text"><?php echo htmlspecialchars(substr($hotel['about_description1'] ?? '', 0, 100) . (strlen($hotel['about_description1'] ?? '') > 100 ? '...' : '')); ?></p>
                                    <p class="hotel-price">
                                        <?php
                                        if (isset($hotel['min_price']) && $hotel['min_price'] !== null) {
                                            echo '$' . number_format((float)$hotel['min_price'], 2) . ' <small class="text-muted">/ night</small>';
                                        } else {
                                            echo '<span class="text-muted">Price not available</span>';
                                        }
                                        ?>
                                    </p>
                                    <p class="hotel-rating">
                                        <?php
                                        $rating = isset($hotel['avg_rating']) ? intval($hotel['avg_rating']) : 0;
                                        for ($i = 1; $i <= 5; $i++) {
                                            if ($i <= $rating) {
                                                echo '<i class="fa fa-star"></i>';
                                            } else {
                                                echo '<i class="fa fa-star-o"></i>';
                                            }
                                        }
                                        ?>
                                    </p>
                                    
                                    <div class="hotel-features">
                                        <?php
                                        // Get up to 3 amenities for this hotel
                                        $amenities_query = "SELECT a.name, a.icon FROM amenities a 
                                                          JOIN hotel_amenities ha ON a.id = ha.amenity_id 
                                                          WHERE ha.hotel_id = ? LIMIT 3";
                                        $amenities_stmt = $conn->prepare($amenities_query);
                                        $amenities_stmt->bind_param('i', $hotel['id']);
                                        $amenities_stmt->execute();
                                        $amenities_result = $amenities_stmt->get_result();

                                        if ($amenities_result->num_rows > 0) {
                                            while ($amenity = $amenities_result->fetch_assoc()) {
                                                $icon_class = 'fa-check'; // Default icon
                                                
                                                // Use the icon from the database if available
                                                if (!empty($amenity['icon'])) {
                                                    $icon_class = $amenity['icon'];
                                                } else {
                                                    // Fallback to name-based icon assignment
                                                    if (stripos($amenity['name'], 'wifi') !== false) {
                                                        $icon_class = 'fa-wifi';
                                                    } elseif (stripos($amenity['name'], 'pool') !== false) {
                                                        $icon_class = 'fa-swimming-pool';
                                                    } elseif (stripos($amenity['name'], 'restaurant') !== false || stripos($amenity['name'], 'dining') !== false) {
                                                        $icon_class = 'fa-utensils';
                                                    } elseif (stripos($amenity['name'], 'parking') !== false) {
                                                        $icon_class = 'fa-parking';
                                                    } elseif (stripos($amenity['name'], 'breakfast') !== false || stripos($amenity['name'], 'coffee') !== false) {
                                                        $icon_class = 'fa-coffee';
                                                    } elseif (stripos($amenity['name'], 'spa') !== false) {
                                                        $icon_class = 'fa-spa';
                                                    }
                                                }

                                                echo '<span><i class="fa ' . $icon_class . '"></i> ' . htmlspecialchars($amenity['name']) . '</span>';
                                            }
                                        } else {
                                            echo '<span><i class="fa fa-info-circle"></i> No amenities listed</span>';
                                        }
                                        $amenities_stmt->close();
                                        ?>
                                    </div>
                                </div>
                                <div class="card-footer bg-white border-top-0">
                                    <a href="hotel.php?id=<?php echo $hotel['id']; ?>" class="custom-btn">View Details</a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <?php foreach ($hotels as $index => $hotel): ?>
                <div class="col-md-4 mb-4 animate__animated animate__fadeInUp" style="animation-delay: <?php echo 0.2 * $index; ?>s;">
                    <div class="card hotel-card">
                        <img src="<?php echo !empty($hotel['background_image']) ? htmlspecialchars($hotel['background_image']) : 'Images/hotel1_bg.webp'; ?>"
                            class="card-img-top" alt="<?php echo htmlspecialchars($hotel['name']); ?>">
                        <div class="card-body">
                            <h5 class="card-title hotel-name"><?php echo htmlspecialchars($hotel['name']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars(substr($hotel['about_description1'] ?? '', 0, 100) . (strlen($hotel['about_description1'] ?? '') > 100 ? '...' : '')); ?></p>
                            <p class="hotel-price">
                                <?php
                                if (isset($hotel['min_price']) && $hotel['min_price'] !== null) {
                                    echo '$' . number_format((float)$hotel['min_price'], 2) . ' <small class="text-muted">/ night</small>';
                                } else {
                                    echo '<span class="text-muted">Price not available</span>';
                                }
                                ?>
                            </p>
                            <p class="hotel-rating">
                                <?php
                                $rating = isset($hotel['avg_rating']) ? intval($hotel['avg_rating']) : 0;
                                for ($i = 1; $i <= 5; $i++) {
                                    if ($i <= $rating) {
                                        echo '<i class="fa fa-star"></i>';
                                    } else {
                                        echo '<i class="fa fa-star-o"></i>';
                                    }
                                }
                                ?>
                            </p>
                            <div class="hotel-features">
                                <?php
                                // Get up to 3 amenities for this hotel using the junction table
                                $amenities_query = "SELECT a.name, a.icon FROM amenities a 
                                                  JOIN hotel_amenities ha ON a.id = ha.amenity_id 
                                                  WHERE ha.hotel_id = ? LIMIT 3";
                                $amenities_stmt = $conn->prepare($amenities_query);
                                $amenities_stmt->bind_param('i', $hotel['id']);
                                $amenities_stmt->execute();
                                $amenities_result = $amenities_stmt->get_result();

                                if ($amenities_result->num_rows > 0) {
                                    while ($amenity = $amenities_result->fetch_assoc()) {
                                        $icon_class = 'fa-check'; // Default icon
                                        
                                        // Use the icon from the database if available
                                        if (!empty($amenity['icon'])) {
                                            $icon_class = $amenity['icon'];
                                        } else {
                                            // Fallback to name-based icon assignment
                                            if (stripos($amenity['name'], 'wifi') !== false) {
                                                $icon_class = 'fa-wifi';
                                            } elseif (stripos($amenity['name'], 'pool') !== false) {
                                                $icon_class = 'fa-swimming-pool';
                                            } elseif (stripos($amenity['name'], 'restaurant') !== false || stripos($amenity['name'], 'dining') !== false) {
                                                $icon_class = 'fa-utensils';
                                            } elseif (stripos($amenity['name'], 'parking') !== false) {
                                                $icon_class = 'fa-parking';
                                            } elseif (stripos($amenity['name'], 'breakfast') !== false || stripos($amenity['name'], 'coffee') !== false) {
                                                $icon_class = 'fa-coffee';
                                            } elseif (stripos($amenity['name'], 'spa') !== false) {
                                                $icon_class = 'fa-spa';
                                            }
                                        }

                                        echo '<span><i class="fa ' . $icon_class . '"></i> ' . htmlspecialchars($amenity['name']) . '</span>';
                                    }
                                } else {
                                    echo '<span><i class="fa fa-info-circle"></i> No amenities listed</span>';
                                }
                                $amenities_stmt->close();
                                ?>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-top-0">
                            <a href="hotel.php?id=<?php echo $hotel['id']; ?><?php echo !empty($search_checkin) && !empty($search_checkout) ? '&check_in=' . urlencode($search_checkin) . '&check_out=' . urlencode($search_checkout) : ''; ?>" class="custom-btn">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Amenities Filter Modal -->
<div class="modal fade" id="amenitiesModal" tabindex="-1" aria-labelledby="amenitiesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="amenitiesModalLabel">Filter by Amenities</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="amenities-form" method="GET" action="search.php">
                    <input type="hidden" name="destination" value="<?php echo htmlspecialchars($search_destination); ?>">
                    <input type="hidden" name="check_in" value="<?php echo htmlspecialchars($search_checkin); ?>">
                    <input type="hidden" name="check_out" value="<?php echo htmlspecialchars($search_checkout); ?>">
                    <input type="hidden" name="guests" value="<?php echo $search_guests; ?>">
                    <input type="hidden" name="filter" value="<?php echo htmlspecialchars($search_filter); ?>">

                    <div class="row">
                        <?php foreach ($amenities as $amenity): ?>
                            <div class="col-md-6 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="selected_amenities[]"
                                        value="<?php echo htmlspecialchars($amenity); ?>" 
                                        id="amenity-<?php echo htmlspecialchars(strtolower(str_replace(' ', '-', $amenity))); ?>"
                                        <?php echo in_array($amenity, $selected_amenities) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="amenity-<?php echo htmlspecialchars(strtolower(str_replace(' ', '-', $amenity))); ?>">
                                        <?php echo htmlspecialchars($amenity); ?>
                                    </label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="mt-4 d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary" id="clear-amenities">Clear All</button>
                        <button type="submit" class="btn btn-theme">Apply Filters</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
<script src="js/font-awesome-6.0.0-beta3-all.min.js"></script>
<script src="js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Set minimum check-in date to today
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('check-in').min = today;

        // Set minimum check-out date to check-in date or today
        document.getElementById('check-in').addEventListener('change', function() {
            const checkinDate = this.value;
            document.getElementById('check-out').min = checkinDate || today;

            // If check-out is before new check-in, update it
            if (document.getElementById('check-out').value < checkinDate) {
                document.getElementById('check-out').value = checkinDate;
            }
        });

        // Set initial check-out min value
        if (document.getElementById('check-in').value) {
            document.getElementById('check-out').min = document.getElementById('check-in').value;
        } else {
            document.getElementById('check-out').min = today;
        }
        
        // Clear amenities button
        document.getElementById('clear-amenities').addEventListener('click', function() {
            const checkboxes = document.querySelectorAll('#amenities-form input[type="checkbox"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
        });
    });
</script>