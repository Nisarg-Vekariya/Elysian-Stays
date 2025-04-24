<?php 
require("header.php");

// Set default values for pagination, search and sorting
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10; // Number of bookings per page
$offset = ($page - 1) * $per_page;
$search = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';
$sort_by = isset($_GET['sort_by']) ? htmlspecialchars($_GET['sort_by']) : 'check_in_date';
$sort_order = isset($_GET['sort_order']) ? htmlspecialchars($_GET['sort_order']) : 'desc';

// Function to sanitize input data
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to validate date format
function validate_date($date) {
    if (empty($date)) return false;
    
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

// Function to format date to YYYY-MM-DD
function format_date($date) {
    if (empty($date)) {
        error_log("Empty date passed to format_date");
        return null;
    }
    
    // Log the input date for debugging
    error_log("format_date input: " . $date);
    
    // Try to parse the date to ensure it's valid
    $d = DateTime::createFromFormat('Y-m-d', $date);
    if ($d && $d->format('Y-m-d') === $date) {
        error_log("Date already in YYYY-MM-DD format: " . $date);
        return $date; // Already in correct format
    }
    
    // Try other possible formats
    foreach (['d-m-Y', 'd/m/Y', 'm/d/Y', 'Y/m/d', 'd.m.Y'] as $format) {
        $d = DateTime::createFromFormat($format, $date);
        if ($d) {
            $formatted = $d->format('Y-m-d');
            error_log("Converted from {$format} to YYYY-MM-DD: " . $formatted);
            return $formatted;
        }
    }
    
    // Handle YYYY format directly (this seems to be causing the issue)
    if (preg_match('/^\d{4}$/', $date)) {
        error_log("ERROR: Year-only value detected: " . $date);
        return null; // Return null for just a year
    }
    
    // As a last resort, try strtotime
    $timestamp = strtotime($date);
    if ($timestamp === false) {
        error_log("Failed to parse date: " . $date);
        return null;
    }
    
    $formatted = date('Y-m-d', $timestamp);
    error_log("Converted using strtotime to YYYY-MM-DD: " . $formatted);
    return $formatted;
}

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Add new booking
    if (isset($_POST['add_booking'])) {
        $room_id = intval($_POST['roomType']);
        $guest_name = sanitize_input($_POST['customerName']);
        $guest_email = sanitize_input($_POST['guest_email']);
        $guest_phone = sanitize_input($_POST['guest_phone']);
        
        // Get raw date values
        $raw_check_in = sanitize_input($_POST['checkInDate']);
        $raw_check_out = sanitize_input($_POST['checkOutDate']);
        
        // Format dates
        $check_in_date = format_date($raw_check_in);
        $check_out_date = format_date($raw_check_out);
        
        $total_price = floatval($_POST['total_price']);
        $status = strtolower(sanitize_input($_POST['status']));
        
        // Validate inputs
        $errors = [];
        if (empty($guest_name) || strlen($guest_name) < 3) {
            $errors['customerName'] = "Name must be at least 3 characters";
        }
        if (empty($guest_email) || !filter_var($guest_email, FILTER_VALIDATE_EMAIL)) {
            $errors['guest_email'] = "Valid email is required";
        }
        if (empty($guest_phone)) {
            $errors['guest_phone'] = "Phone number is required";
        }
        if (empty($room_id)) {
            $errors['roomType'] = "Please select a room";
        }
        
        // Date validation
        if (empty($raw_check_in)) {
            $errors['checkInDate'] = "Check-in date is required";
        } else if (!$check_in_date) {
            $errors['checkInDate'] = "Invalid date format. Please use YYYY-MM-DD format.";
        }
        
        if (empty($raw_check_out)) {
            $errors['checkOutDate'] = "Check-out date is required";
        } else if (!$check_out_date) {
            $errors['checkOutDate'] = "Invalid date format. Please use YYYY-MM-DD format.";
        }
        
        if ($check_in_date && $check_out_date && $check_in_date >= $check_out_date) {
            $errors['checkOutDate'] = "Check-out date must be after check-in date";
        }
        
        if (empty($status)) {
            $errors['status'] = "Please select a status";
        }
        if ($total_price <= 0) {
            $errors['total_price'] = "Price must be greater than zero";
        }
        
        // If no errors, insert booking
        if (empty($errors)) {
            try {
                // Get hotel_id from room
                $stmt = $conn->prepare("SELECT hotel_id FROM rooms WHERE id = ?");
                $stmt->bind_param("i", $room_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $hotel = $result->fetch_assoc();
                
                if (!$hotel) {
                    throw new Exception("Room not found");
                }
                
                $hotel_id = $hotel['hotel_id'];
                
                $sql = "INSERT INTO bookings (hotel_id, room_id, guest_name, guest_email, guest_phone, 
                        check_in_date, check_out_date, total_price, status) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                
                if (!$stmt) {
                    throw new Exception("Prepare failed: " . $conn->error);
                }
                
                $stmt->bind_param("iisssssds", 
                    $hotel_id,
                    $room_id,
                    $guest_name,
                    $guest_email,
                    $guest_phone,
                    $check_in_date,
                    $check_out_date,
                    $total_price,
                    $status
                );
                
                if ($stmt->execute()) {
                    $success_message = "Booking added successfully!";
                } else {
                    $error_message = "Error: " . $stmt->error;
                }
                $stmt->close();
            } catch (Exception $e) {
                $error_message = "Error: " . $e->getMessage();
            }
        } else {
            $error_message = "Validation errors: " . implode(", ", $errors);
        }
    }
    
    // Update existing booking
    if (isset($_POST['update_booking'])) {
        $booking_id = intval($_POST['bookingId']);
        $room_id = intval($_POST['roomType']); // Ensure this is an integer
        $guest_name = sanitize_input($_POST['customerName']);
        $guest_email = sanitize_input($_POST['guest_email']);
        $guest_phone = sanitize_input($_POST['guest_phone']);
        
        // Get raw date values
        $raw_check_in = sanitize_input($_POST['checkInDate']);
        $raw_check_out = sanitize_input($_POST['checkOutDate']);
        
        // Debug raw dates
        error_log("Raw check-in date: " . $raw_check_in);
        error_log("Raw check-out date: " . $raw_check_out);
        
        // Format dates
        $check_in_date = format_date($raw_check_in);
        $check_out_date = format_date($raw_check_out);
        
        // Debug formatted dates
        error_log("Formatted check-in date: " . $check_in_date);
        error_log("Formatted check-out date: " . $check_out_date);
        
        // Get total_price BEFORE trying to use it
        $total_price = floatval($_POST['total_price']);
        $status = strtolower(sanitize_input($_POST['status']));
        
        // Dump all variables for debugging
        error_log("DEBUG VALUES IN UPDATE: room_id={$room_id}, guest_name={$guest_name}, check_in_date={$check_in_date}, check_out_date={$check_out_date}, total_price={$total_price}, status={$status}, booking_id={$booking_id}");
        
        // Validate inputs
        $errors = [];
        if (empty($guest_name) || strlen($guest_name) < 3) {
            $errors['customerName'] = "Name must be at least 3 characters";
        }
        if (empty($guest_email) || !filter_var($guest_email, FILTER_VALIDATE_EMAIL)) {
            $errors['guest_email'] = "Valid email is required";
        }
        if (empty($guest_phone)) {
            $errors['guest_phone'] = "Phone number is required";
        }
        if (empty($room_id)) {
            $errors['roomType'] = "Please select a room";
        }
        
        // Date validation with better error handling
        if (empty($raw_check_in)) {
            $errors['checkInDate'] = "Check-in date is required";
        } else if (!$check_in_date) {
            $errors['checkInDate'] = "Invalid date format. Please use YYYY-MM-DD format.";
        }
        
        if (empty($raw_check_out)) {
            $errors['checkOutDate'] = "Check-out date is required";
        } else if (!$check_out_date) {
            $errors['checkOutDate'] = "Invalid date format. Please use YYYY-MM-DD format.";
        }
        
        if ($check_in_date && $check_out_date && $check_in_date >= $check_out_date) {
            $errors['checkOutDate'] = "Check-out date must be after check-in date";
        }
        
        if (empty($status)) {
            $errors['status'] = "Please select a status";
        }
        if ($total_price <= 0) {
            $errors['total_price'] = "Price must be greater than zero";
        }
        
        // If no errors, update booking
        if (empty($errors)) {
            try {
                $sql = "UPDATE bookings SET 
                        room_id = ?, 
                        guest_name = ?, 
                        guest_email = ?, 
                        guest_phone = ?, 
                        check_in_date = ?, 
                        check_out_date = ?, 
                        total_price = ?,
                        status = ? 
                        WHERE id = ?";
                $stmt = $conn->prepare($sql);
                
                // Fix the parameter binding by ensuring the type string has the correct number of types
                // i = integer, s = string, d = double (float)
                // We have 9 parameters: room_id(i), guest_name(s), guest_email(s), guest_phone(s),
                // check_in_date(s), check_out_date(s), total_price(d), status(s), booking_id(i)
                $stmt->bind_param("isssssdsi", 
                    $room_id,               // i - integer
                    $guest_name,            // s - string
                    $guest_email,           // s - string
                    $guest_phone,           // s - string
                    $check_in_date,         // s - string
                    $check_out_date,        // s - string
                    $total_price,           // d - double (float)
                    $status,                // s - string
                    $booking_id             // i - integer
                );
                
                if ($stmt->execute()) {
                    $success_message = "Booking updated successfully!";
                } else {
                    $error_message = "Error: " . $stmt->error;
                }
                $stmt->close();
            } catch (Exception $e) {
                $error_message = "Error: " . $e->getMessage();
                // For debugging
                error_log("Database error in update booking: " . $e->getMessage());
            }
        } else {
            // For debugging
            $error_message = "Validation errors: " . implode(", ", $errors);
        }
    }
    
    // Cancel booking
    if (isset($_POST['cancel_booking'])) {
        $booking_id = intval($_POST['booking_id']);
        
        $sql = "UPDATE bookings SET status = 'cancelled' WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $booking_id);
        
        if ($stmt->execute()) {
            $success_message = "Booking cancelled successfully!";
        } else {
            $error_message = "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Build query for fetching bookings with search and sort
$count_sql = "SELECT COUNT(*) as total FROM bookings b 
              JOIN rooms r ON b.room_id = r.id
              JOIN hotels h ON b.hotel_id = h.id";

$sql = "SELECT b.id, b.guest_name, b.guest_email, b.guest_phone, 
               r.name as room_name, h.name as hotel_name, 
               b.check_in_date, b.check_out_date, b.status, b.total_price,
               b.created_at, b.hotel_id, b.room_id
        FROM bookings b 
        JOIN rooms r ON b.room_id = r.id
        JOIN hotels h ON b.hotel_id = h.id";

// Add search condition if search term is provided
if (!empty($search)) {
    $search_condition = " WHERE b.guest_name LIKE ? OR b.guest_email LIKE ? OR b.guest_phone LIKE ? 
                         OR r.name LIKE ? OR h.name LIKE ? OR b.status LIKE ?";
    $count_sql .= $search_condition;
    $sql .= $search_condition;
    $search_param = "%$search%";
}

// Add sorting
$valid_sort_columns = ['id', 'guest_name', 'room_name', 'hotel_name', 'check_in_date', 'check_out_date', 'status', 'total_price'];
$valid_sort_orders = ['asc', 'desc'];

if (!in_array($sort_by, $valid_sort_columns)) {
    $sort_by = 'check_in_date';
}

if (!in_array($sort_order, $valid_sort_orders)) {
    $sort_order = 'desc';
}

$sql .= " ORDER BY $sort_by $sort_order";

// Add pagination
$sql .= " LIMIT $per_page OFFSET $offset";

// Fetch total number of bookings for pagination
if (!empty($search)) {
    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->bind_param("ssssss", $search_param, $search_param, $search_param, $search_param, $search_param, $search_param);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $count_row = $count_result->fetch_assoc();
    $total_bookings = $count_row['total'];
    $count_stmt->close();
} else {
    $count_result = $conn->query($count_sql);
    $count_row = $count_result->fetch_assoc();
    $total_bookings = $count_row['total'];
}

// Calculate total pages
$total_pages = ceil($total_bookings / $per_page);

// Fetch bookings
$bookings = [];
if (!empty($search)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $search_param, $search_param, $search_param, $search_param, $search_param, $search_param);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $bookings[] = $row;
        }
    }
    $stmt->close();
} else {
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $bookings[] = $row;
        }
    }
}

// Function to create sort URL
function getSortUrl($column, $current_sort_by, $current_sort_order) {
    $new_sort_order = ($current_sort_by === $column && $current_sort_order === 'asc') ? 'desc' : 'asc';
    $params = $_GET;
    $params['sort_by'] = $column;
    $params['sort_order'] = $new_sort_order;
    return '?' . http_build_query($params);
}

// Function to get sort icon
function getSortIcon($column, $current_sort_by, $current_sort_order) {
    if ($current_sort_by !== $column) {
        return '<i class="fas fa-sort"></i>';
    }
    return ($current_sort_order === 'asc') ? '<i class="fas fa-sort-up"></i>' : '<i class="fas fa-sort-down"></i>';
}

// Fetch hotels for dropdown
$hotels_sql = "SELECT id, name FROM hotels ORDER BY name";
$hotels_result = $conn->query($hotels_sql);
$hotels = [];
if ($hotels_result->num_rows > 0) {
    while ($row = $hotels_result->fetch_assoc()) {
        $hotels[$row['id']] = $row['name'];
    }
}

// Fetch room types for dropdowns
$sql = "SELECT id, name, hotel_id, price FROM rooms ORDER BY hotel_id, name";
$room_result = $conn->query($sql);
$room_types = [];
$room_prices = [];
if ($room_result->num_rows > 0) {
    while ($row = $room_result->fetch_assoc()) {
        $room_types[$row['id']] = $row['name'] . ' (' . $hotels[$row['hotel_id']] . ')';
        $room_prices[$row['id']] = $row['price'];
    }
}
?>

<title>Manage Bookings - Elysian Stays</title>
<div class="container my-5 animate__animated animate__fadeIn">
    <h1 class="text-center mb-4" style="color: var(--primary-color);">Manage Bookings</h1>
    
    <?php if (isset($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $success_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $error_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="main-content p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="m-0" style="color: var(--primary-color);">Booking List</h5>
            <button type="button" class="btn btn-theme" data-bs-toggle="modal" data-bs-target="#addBookingModal">
                <i class="fas fa-plus me-2"></i>Add Booking
            </button>
        </div>

        <!-- Search and Filters -->
        <div class="row mb-4">
            <div class="col-md-6">
                <form action="" method="GET" class="d-flex">
                    <input type="text" name="search" class="form-control me-2" placeholder="Search bookings..." value="<?php echo $search; ?>">
                    <input type="hidden" name="sort_by" value="<?php echo $sort_by; ?>">
                    <input type="hidden" name="sort_order" value="<?php echo $sort_order; ?>">
                    <button type="submit" class="btn btn-theme">Search</button>
                </form>
            </div>
            <div class="col-md-6 text-end">
                <?php if(!empty($search)): ?>
                    <a href="manage-bookings.php" class="btn btn-outline-secondary">Clear Search</a>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover bookings-table">
                <thead>
                    <tr>
                        <th><a href="<?php echo getSortUrl('id', $sort_by, $sort_order); ?>" class="text-decoration-none text-dark">#<?php echo getSortIcon('id', $sort_by, $sort_order); ?></a></th>
                        <th><a href="<?php echo getSortUrl('guest_name', $sort_by, $sort_order); ?>" class="text-decoration-none text-dark">Customer Name<?php echo getSortIcon('guest_name', $sort_by, $sort_order); ?></a></th>
                        <th><a href="<?php echo getSortUrl('room_name', $sort_by, $sort_order); ?>" class="text-decoration-none text-dark">Room<?php echo getSortIcon('room_name', $sort_by, $sort_order); ?></a></th>
                        <th><a href="<?php echo getSortUrl('check_in_date', $sort_by, $sort_order); ?>" class="text-decoration-none text-dark">Check-in Date<?php echo getSortIcon('check_in_date', $sort_by, $sort_order); ?></a></th>
                        <th><a href="<?php echo getSortUrl('check_out_date', $sort_by, $sort_order); ?>" class="text-decoration-none text-dark">Check-out Date<?php echo getSortIcon('check_out_date', $sort_by, $sort_order); ?></a></th>
                        <th><a href="<?php echo getSortUrl('total_price', $sort_by, $sort_order); ?>" class="text-decoration-none text-dark">Price<?php echo getSortIcon('total_price', $sort_by, $sort_order); ?></a></th>
                        <th><a href="<?php echo getSortUrl('status', $sort_by, $sort_order); ?>" class="text-decoration-none text-dark">Status<?php echo getSortIcon('status', $sort_by, $sort_order); ?></a></th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($bookings)): ?>
                        <tr>
                            <td colspan="8" class="text-center">No bookings found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($bookings as $index => $booking): ?>
                            <tr>
                                <td><?php echo (($page - 1) * $per_page) + $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($booking['guest_name']); ?></td>
                                <td><?php echo htmlspecialchars($booking['room_name']); ?></td>
                                <td><?php echo htmlspecialchars($booking['check_in_date']); ?></td>
                                <td><?php echo htmlspecialchars($booking['check_out_date']); ?></td>
                                <td>$<?php echo number_format($booking['total_price'], 2); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo ($booking['status'] == 'confirmed') ? 'success' : 
                                            (($booking['status'] == 'pending') ? 'warning' : 
                                             (($booking['status'] == 'completed') ? 'info' : 'danger')); 
                                    ?>">
                                        <?php echo ucfirst($booking['status']); ?>
                                    </span>
                                </td>
                                <td class="action-buttons">
                                    <button class="btn btn-sm btn-primary view-booking" 
                                            title="View Booking" 
                                            data-booking-id="<?php echo $booking['id']; ?>">
                                        <i class="fa fa-eye"></i><span class="d-none d-md-inline"> View</span>
                                    </button>
                                    
                                    <button class="btn btn-sm btn-warning edit-booking" 
                                            title="Edit Booking" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editBookingModal"
                                            data-booking-id="<?php echo $booking['id']; ?>"
                                            data-guest-name="<?php echo htmlspecialchars($booking['guest_name']); ?>"
                                            data-room-type="<?php echo $booking['room_name']; ?>"
                                            data-check-in="<?php echo $booking['check_in_date']; ?>"
                                            data-check-out="<?php echo $booking['check_out_date']; ?>"
                                            data-status="<?php echo $booking['status']; ?>">
                                        <i class="fa fa-edit"></i><span class="d-none d-md-inline"> Edit</span>
                                    </button>
                                    
                                    <form method="post" class="d-inline cancel-booking-form">
                                        <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                        <button type="submit" name="cancel_booking" class="btn btn-sm btn-danger" 
                                                title="Cancel Booking" 
                                                onclick="return confirm('Are you sure you want to cancel this booking?');"
                                                <?php echo ($booking['status'] == 'cancelled') ? 'disabled' : ''; ?>>
                                            <i class="fa fa-times"></i><span class="d-none d-md-inline"> Cancel</span>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if($total_pages > 1): ?>
            <nav aria-label="Booking pagination">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page-1; ?>&search=<?php echo $search; ?>&sort_by=<?php echo $sort_by; ?>&sort_order=<?php echo $sort_order; ?>">Previous</a>
                    </li>
                    
                    <?php for($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo $search; ?>&sort_by=<?php echo $sort_by; ?>&sort_order=<?php echo $sort_order; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    
                    <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page+1; ?>&search=<?php echo $search; ?>&sort_by=<?php echo $sort_by; ?>&sort_order=<?php echo $sort_order; ?>">Next</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>

        <div class="mt-3 text-center">
            <p>Showing <?php echo min(($page - 1) * $per_page + 1, $total_bookings); ?> to <?php echo min($page * $per_page, $total_bookings); ?> of <?php echo $total_bookings; ?> bookings</p>
        </div>
    </div>
</div>

<!-- Edit Booking Modal -->
<div class="modal fade" id="editBookingModal" tabindex="-1" aria-labelledby="editBookingModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editBookingForm" method="post" action="">
                <div class="modal-header">
                    <h5 class="modal-title" id="editBookingModalLabel">Edit Booking</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="editBookingId" name="bookingId">
                    
                    <div class="mb-3">
                        <label for="editCustomerName" class="form-label">Customer Name</label>
                        <input type="text" class="form-control" id="editCustomerName" name="customerName" 
                               placeholder="Enter customer name" required minlength="3">
                        <?php if (isset($errors['customerName'])): ?>
                            <span class="text-danger"><?php echo $errors['customerName']; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editGuestEmail" class="form-label">Email</label>
                        <input type="email" class="form-control" id="editGuestEmail" name="guest_email" 
                               placeholder="Enter email" required>
                        <?php if (isset($errors['guest_email'])): ?>
                            <span class="text-danger"><?php echo $errors['guest_email']; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editGuestPhone" class="form-label">Phone</label>
                        <input type="tel" class="form-control" id="editGuestPhone" name="guest_phone" 
                               placeholder="Enter phone number" required>
                        <?php if (isset($errors['guest_phone'])): ?>
                            <span class="text-danger"><?php echo $errors['guest_phone']; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editRoomType" class="form-label">Room</label>
                        <select class="form-select" id="editRoomType" name="roomType" required>
                            <option value="">Select room</option>
                            <?php foreach ($room_types as $id => $name): ?>
                                <option value="<?php echo $id; ?>" data-price="<?php echo $room_prices[$id]; ?>">
                                    <?php echo htmlspecialchars($name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['roomType'])): ?>
                            <span class="text-danger"><?php echo $errors['roomType']; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editCheckInDate" class="form-label">Check-in Date</label>
                        <input type="date" class="form-control" id="editCheckInDate" name="checkInDate" required>
                        <?php if (isset($errors['checkInDate'])): ?>
                            <span class="text-danger"><?php echo $errors['checkInDate']; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editCheckOutDate" class="form-label">Check-out Date</label>
                        <input type="date" class="form-control" id="editCheckOutDate" name="checkOutDate" required>
                        <?php if (isset($errors['checkOutDate'])): ?>
                            <span class="text-danger"><?php echo $errors['checkOutDate']; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editTotalPrice" class="form-label">Total Price</label>
                        <input type="number" step="0.01" min="0" class="form-control" id="editTotalPrice" name="total_price" required>
                        <?php if (isset($errors['total_price'])): ?>
                            <span class="text-danger"><?php echo $errors['total_price']; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editStatus" class="form-label">Status</label>
                        <select class="form-select" id="editStatus" name="status" required>
                            <option value="">Select status</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="pending">Pending</option>
                            <option value="cancelled">Cancelled</option>
                            <option value="completed">Completed</option>
                        </select>
                        <?php if (isset($errors['status'])): ?>
                            <span class="text-danger"><?php echo $errors['status']; ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="update_booking" class="btn btn-theme">Update Booking</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Booking Modal -->
<div class="modal fade" id="viewBookingModal" tabindex="-1" aria-labelledby="viewBookingModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewBookingModalLabel">Booking Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="viewBookingDetails">
                <!-- Booking details will be loaded here via AJAX -->
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p>Loading booking details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Booking Modal -->
<div class="modal fade" id="addBookingModal" tabindex="-1" aria-labelledby="addBookingModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addBookingForm" method="post" action="">
                <div class="modal-header">
                    <h5 class="modal-title" id="addBookingModalLabel">Add New Booking</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="customerName" class="form-label">Customer Name</label>
                        <input type="text" class="form-control" id="customerName" name="customerName" 
                               placeholder="Enter customer name" required minlength="3">
                        <?php if (isset($errors['customerName'])): ?>
                            <span class="text-danger"><?php echo $errors['customerName']; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label for="guest_email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="guest_email" name="guest_email" 
                               placeholder="Enter email" required>
                        <?php if (isset($errors['guest_email'])): ?>
                            <span class="text-danger"><?php echo $errors['guest_email']; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label for="guest_phone" class="form-label">Phone</label>
                        <input type="tel" class="form-control" id="guest_phone" name="guest_phone" 
                               placeholder="Enter phone number" required>
                        <?php if (isset($errors['guest_phone'])): ?>
                            <span class="text-danger"><?php echo $errors['guest_phone']; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label for="roomType" class="form-label">Room</label>
                        <select class="form-select" id="roomType" name="roomType" required>
                            <option value="">Select room</option>
                            <?php foreach ($room_types as $id => $name): ?>
                                <option value="<?php echo $id; ?>" data-price="<?php echo $room_prices[$id]; ?>">
                                    <?php echo htmlspecialchars($name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['roomType'])): ?>
                            <span class="text-danger"><?php echo $errors['roomType']; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label for="checkInDate" class="form-label">Check-in Date</label>
                        <input type="date" class="form-control" id="checkInDate" name="checkInDate" required>
                        <?php if (isset($errors['checkInDate'])): ?>
                            <span class="text-danger"><?php echo $errors['checkInDate']; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label for="checkOutDate" class="form-label">Check-out Date</label>
                        <input type="date" class="form-control" id="checkOutDate" name="checkOutDate" required>
                        <?php if (isset($errors['checkOutDate'])): ?>
                            <span class="text-danger"><?php echo $errors['checkOutDate']; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label for="total_price" class="form-label">Total Price</label>
                        <input type="number" step="0.01" min="0" class="form-control" id="total_price" name="total_price" required readonly>
                        <?php if (isset($errors['total_price'])): ?>
                            <span class="text-danger"><?php echo $errors['total_price']; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="">Select status</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="pending">Pending</option>
                            <option value="cancelled">Cancelled</option>
                            <option value="completed">Completed</option>
                        </select>
                        <?php if (isset($errors['status'])): ?>
                            <span class="text-danger"><?php echo $errors['status']; ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="add_booking" class="btn btn-theme">Add Booking</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// JavaScript to handle the edit booking modal population
document.addEventListener('DOMContentLoaded', function() {
    // Populate edit booking modal with data
    const editBookingButtons = document.querySelectorAll('.edit-booking');
    editBookingButtons.forEach(button => {
        button.addEventListener('click', function() {
            const bookingId = this.getAttribute('data-booking-id');
            
            // Instead of using data attributes for all fields, we'll fetch the booking details from the server
            fetch('get_booking_details.php?id=' + bookingId)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        throw new Error(data.error);
                    }
                    
                    // Populate all fields with the fetched data
                    document.getElementById('editBookingId').value = data.id;
                    document.getElementById('editCustomerName').value = data.guest_name;
                    document.getElementById('editGuestEmail').value = data.guest_email;
                    document.getElementById('editGuestPhone').value = data.guest_phone;
                    
                    // Format dates to ensure proper format display
                    const formatDateForInput = (dateString) => {
                        if (!dateString) return '';
                        
                        console.log("Raw date from server:", dateString);
                        
                        // Try to parse the date
                        const date = new Date(dateString);
                        if (isNaN(date.getTime())) {
                            console.error("Invalid date:", dateString);
                            return dateString; // If invalid, return original
                        }
                        
                        // Format as YYYY-MM-DD (display format)
                        const year = date.getFullYear();
                        const month = String(date.getMonth() + 1).padStart(2, '0');
                        const day = String(date.getDate()).padStart(2, '0');
                        const formatted = `${year}-${month}-${day}`;
                        
                        console.log("Formatted date for display:", formatted);
                        return formatted;
                    };
                    
                    document.getElementById('editCheckInDate').value = formatDateForInput(data.check_in_date);
                    document.getElementById('editCheckOutDate').value = formatDateForInput(data.check_out_date);
                    document.getElementById('editTotalPrice').value = data.total_price;
                    
                    // Select the correct room in the dropdown
                    const roomSelect = document.getElementById('editRoomType');
                    for (let i = 0; i < roomSelect.options.length; i++) {
                        if (roomSelect.options[i].value == data.room_id) {
                            roomSelect.selectedIndex = i;
                            break;
                        }
                    }
                    
                    // Select the appropriate status
                    const statusSelect = document.getElementById('editStatus');
                    for (let i = 0; i < statusSelect.options.length; i++) {
                        if (statusSelect.options[i].value === data.status) {
                            statusSelect.selectedIndex = i;
                            break;
                        }
                    }
                })
                .catch(error => {
                    console.error('Error fetching booking details:', error);
                    alert('Error loading booking details. Please try again.');
                });
        });
    });
    
    // Update the UPDATE booking handler in the PHP code
    // Function to calculate total price for edit booking
    const calculateEditPrice = function() {
        const roomTypeSelect = document.getElementById('editRoomType');
        const checkInDateStr = document.getElementById('editCheckInDate').value;
        const checkOutDateStr = document.getElementById('editCheckOutDate').value;
        
        if (roomTypeSelect.value && checkInDateStr && checkOutDateStr) {
            // Get the price from the data attribute
            const selectedOption = roomTypeSelect.options[roomTypeSelect.selectedIndex];
            const pricePerNight = selectedOption ? parseFloat(selectedOption.dataset.price) : 0;
            
            // Parse dates properly - prioritize YYYY-MM-DD format
            const parseDate = (dateStr) => {
                // Try to parse YYYY-MM-DD format first (preferred format)
                if (/^\d{4}-\d{2}-\d{2}$/.test(dateStr)) {
                    return new Date(dateStr);
                }
                
                // Try to parse DD-MM-YYYY format as fallback
                if (/^\d{2}-\d{2}-\d{4}$/.test(dateStr)) {
                    const [day, month, year] = dateStr.split('-');
                    return new Date(year, month - 1, day);
                }
                
                // As a fallback, try the generic Date constructor
                return new Date(dateStr);
            };
            
            const checkin = parseDate(checkInDateStr);
            const checkout = parseDate(checkOutDateStr);
            
            // Validate that we have valid dates
            if (!isNaN(checkin.getTime()) && !isNaN(checkout.getTime())) {
                const nights = Math.ceil((checkout - checkin) / (1000 * 60 * 60 * 24));
                
                if (nights > 0 && pricePerNight > 0) {
                    const totalPrice = pricePerNight * nights;
                    document.getElementById('editTotalPrice').value = totalPrice.toFixed(2);
                }
            }
        }
    };
    
    // Add event listeners for edit price calculation
    document.getElementById('editRoomType').addEventListener('change', calculateEditPrice);
    document.getElementById('editCheckInDate').addEventListener('change', calculateEditPrice);
    document.getElementById('editCheckOutDate').addEventListener('change', calculateEditPrice);
    
    // View booking details
    const viewBookingButtons = document.querySelectorAll('.view-booking');
    viewBookingButtons.forEach(button => {
        button.addEventListener('click', function() {
            const bookingId = this.getAttribute('data-booking-id');
            const viewBookingModal = new bootstrap.Modal(document.getElementById('viewBookingModal'));
            viewBookingModal.show();
            
            // Set initial loading state
            document.getElementById('viewBookingDetails').innerHTML = `
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p>Loading booking details...</p>
                </div>
            `;
            
            // Fetch booking details via AJAX
            fetch('get_booking_details.php?id=' + bookingId)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        throw new Error(data.error);
                    }
                    
                    // Format status badge
                    const statusClass = getStatusClass(data.status);
                    const formattedStatus = `<span class="badge bg-${statusClass}">${data.status.charAt(0).toUpperCase() + data.status.slice(1)}</span>`;
                    
                    // Populate the modal with the booking details
                    document.getElementById('viewBookingDetails').innerHTML = `
                        <div class="booking-detail">
                            <div class="mb-4 border-bottom pb-2">
                                <h4>Booking #BK${String(data.id).padStart(5, '0')}</h4>
                                <p class="text-muted">Created on ${new Date(data.created_at).toLocaleString()}</p>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <h5>Guest Information</h5>
                                    <p><strong>Name:</strong> ${data.guest_name}</p>
                                    <p><strong>Email:</strong> ${data.guest_email}</p>
                                    <p><strong>Phone:</strong> ${data.guest_phone}</p>
                                </div>
                                <div class="col-md-6">
                                    <h5>Booking Details</h5>
                                    <p><strong>Status:</strong> ${formattedStatus}</p>
                                    <p><strong>Check-in:</strong> ${data.check_in_date}</p>
                                    <p><strong>Check-out:</strong> ${data.check_out_date}</p>
                                    <p><strong>Duration:</strong> ${data.nights} night${data.nights !== 1 ? 's' : ''}</p>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <h5>Accommodation</h5>
                                    <p><strong>Hotel:</strong> ${data.hotel_name}</p>
                                    <p><strong>Room:</strong> ${data.room_name}</p>
                                </div>
                                <div class="col-md-6">
                                    <h5>Payment</h5>
                                    <p><strong>Total Price:</strong> $${parseFloat(data.total_price).toFixed(2)}</p>
                                </div>
                            </div>
                        </div>
                    `;
                })
                .catch(error => {
                    console.error('Error fetching booking details:', error);
                    document.getElementById('viewBookingDetails').innerHTML = `
                        <div class="alert alert-danger">
                            ${error.message || 'Error loading booking details. Please try again.'}
                        </div>
                    `;
                });
        });
    });
    
    // Calculate total price based on room selection and dates
    const calculatePrice = function() {
        const roomTypeSelect = document.getElementById('roomType');
        const checkInDateStr = document.getElementById('checkInDate').value;
        const checkOutDateStr = document.getElementById('checkOutDate').value;
        
        if (roomTypeSelect.value && checkInDateStr && checkOutDateStr) {
            // Get the price from the data attribute
            const selectedOption = roomTypeSelect.options[roomTypeSelect.selectedIndex];
            const pricePerNight = selectedOption ? parseFloat(selectedOption.dataset.price) : 0;
            
            // Parse dates properly - prioritize YYYY-MM-DD format
            const parseDate = (dateStr) => {
                // Try to parse YYYY-MM-DD format first (preferred format)
                if (/^\d{4}-\d{2}-\d{2}$/.test(dateStr)) {
                    return new Date(dateStr);
                }
                
                // Try to parse DD-MM-YYYY format as fallback
                if (/^\d{2}-\d{2}-\d{4}$/.test(dateStr)) {
                    const [day, month, year] = dateStr.split('-');
                    return new Date(year, month - 1, day);
                }
                
                // As a fallback, try the generic Date constructor
                return new Date(dateStr);
            };
            
            const checkin = parseDate(checkInDateStr);
            const checkout = parseDate(checkOutDateStr);
            
            // Validate that we have valid dates
            if (!isNaN(checkin.getTime()) && !isNaN(checkout.getTime())) {
                const nights = Math.ceil((checkout - checkin) / (1000 * 60 * 60 * 24));
                
                if (nights > 0 && pricePerNight > 0) {
                    const totalPrice = pricePerNight * nights;
                    document.getElementById('total_price').value = totalPrice.toFixed(2);
                }
            }
        }
    };
    
    // Add event listeners for price calculation
    document.getElementById('roomType').addEventListener('change', calculatePrice);
    document.getElementById('checkInDate').addEventListener('change', calculatePrice);
    document.getElementById('checkOutDate').addEventListener('change', calculatePrice);
});

// Helper function to get status badge class
function getStatusClass(status) {
    switch(status) {
        case 'confirmed': return 'success';
        case 'pending': return 'warning';
        case 'cancelled': return 'danger';
        case 'completed': return 'info';
        default: return 'secondary';
    }
}
</script>

<?php require("footer.php"); ?>