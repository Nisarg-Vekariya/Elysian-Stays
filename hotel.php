<?php require_once 'header.php';

// Get hotel ID from URL or set default
$hotel_id = isset($_GET['id']) ? (int)$_GET['id'] : 1;

// Get check-in and check-out dates from URL (if coming from search page)
$check_in = isset($_GET['check_in']) ? $_GET['check_in'] : date('Y-m-d');
$check_out = isset($_GET['check_out']) ? $_GET['check_out'] : date('Y-m-d', strtotime('+1 day'));
$guests = isset($_GET['guests']) ? (int)$_GET['guests'] : 1;

// Initialize variables
$hotel = null;
$rooms = [];
$amenities = [];
$gallery_images = [];
$reviews = [];
$contact = null;
$error = '';

try {
    // Get hotel details
    $hotel_query = "SELECT * FROM hotels WHERE id = ?";
    $stmt = $conn->prepare($hotel_query);
    $stmt->bind_param("i", $hotel_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $hotel = $result->fetch_assoc();

    // Redirect if hotel not found
    if (!$hotel) {
        header("Location: search.php");
        exit();
    }

    // Get rooms
    $rooms_query = "SELECT * FROM rooms WHERE hotel_id = ?";
    $stmt = $conn->prepare($rooms_query);
    $stmt->bind_param("i", $hotel_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $rooms[] = $row;
    }

    // Check room availability for the selected dates
    $available_rooms = [];
    $room_availability = [];
    foreach ($rooms as $room) {
        // Check if room is already booked for the selected dates
        $availability_query = "SELECT COUNT(*) as booking_count 
                              FROM bookings 
                              WHERE room_id = ? 
                              AND status IN ('confirmed', 'pending')
                              AND (
                                  (check_in_date <= ? AND check_out_date > ?) OR
                                  (check_in_date < ? AND check_out_date >= ?) OR
                                  (check_in_date >= ? AND check_out_date <= ?)
                              )";
        
        $stmt = $conn->prepare($availability_query);
        $stmt->bind_param("issssss", $room['id'], $check_out, $check_in, $check_out, $check_in, $check_in, $check_out);
        $stmt->execute();
        $availability_result = $stmt->get_result();
        $availability = $availability_result->fetch_assoc();
        
        // Check both booking conflicts and room status
        $is_date_available = ($availability['booking_count'] == 0);
        $is_room_active = (isset($room['status']) && $room['status'] === 'available');
        
        $room['is_available'] = $is_date_available && $is_room_active;
        $room['capacity_sufficient'] = ($room['capacity'] >= $guests);
        $room['is_date_available'] = $is_date_available;
        $room['is_room_active'] = $is_room_active;
        
        $room_availability[$room['id']] = [
            'is_available' => $room['is_available'],
            'capacity_sufficient' => $room['capacity_sufficient'],
            'is_date_available' => $is_date_available,
            'is_room_active' => $is_room_active
        ];
        
        if ($room['is_available']) {
            $available_rooms[] = $room;
        }
    }

    // Get amenities using the junction table
    $amenities_query = "
        SELECT a.* 
        FROM amenities a
        JOIN hotel_amenities ha ON a.id = ha.amenity_id
        WHERE ha.hotel_id = ?
    ";
    $stmt = $conn->prepare($amenities_query);
    $stmt->bind_param("i", $hotel_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $amenities[] = $row;
    }

    // Get gallery images
    $gallery_query = "SELECT * FROM gallery_images WHERE hotel_id = ?";
    $stmt = $conn->prepare($gallery_query);
    $stmt->bind_param("i", $hotel_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $gallery_images[] = $row;
    }

    // Get reviews
    $reviews_query = "SELECT * FROM reviews WHERE hotel_id = ? AND status = 'active'";
    $stmt = $conn->prepare($reviews_query);
    $stmt->bind_param("i", $hotel_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $reviews[] = $row;
    }

    // Get contact info
    $contact_query = "SELECT * FROM contact_info WHERE hotel_id = ?";
    $stmt = $conn->prepare($contact_query);
    $stmt->bind_param("i", $hotel_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $contact = $result->fetch_assoc();

    // Set default contact values if not found
    if (!$contact) {
        $contact = [
            'email' => 'Not available',
            'phone' => 'Not available',
            'address' => 'Not available'
        ];
    }

} catch (Exception $e) {
    $error = "An error occurred: " . $e->getMessage();
}
?>

<title><?php echo htmlspecialchars($hotel['name']); ?></title>
<link rel="stylesheet" href="css/hotel.css">
<style>
    .booking-form-container {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 30px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .booking-dates-form .form-row {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        align-items: flex-end;
    }
    
    .booking-dates-form .form-group {
        flex: 1;
        min-width: 150px;
    }
    
    .booking-dates-form label {
        display: block;
        margin-bottom: 5px;
        font-weight: 500;
        color: #343a40;
    }
    
    .booking-dates-form input {
        width: 100%;
        padding: 10px;
        border: 1px solid #dee2e6;
        border-radius: 4px;
    }
    
    .booking-dates-form button {
        padding: 10px 15px;
        margin-top: 23px;
    }
    
    .unavailable-message {
        background-color: #fff3cd;
        color: #664d03;
        border: 1px solid #ffecb5;
        border-radius: 4px;
        padding: 10px;
        margin-top: 10px;
        font-size: 0.9rem;
        text-align: center;
    }
    
    .room-capacity {
        color: #6c757d;
        font-size: 0.9rem;
        margin-bottom: 10px;
    }
    
    .no-rooms-message {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 30px;
        margin: 20px auto;
        max-width: 600px;
        text-align: center;
        font-size: 1.1rem;
        color: #495057;
        grid-column: 1 / -1; /* Make it span full width in the grid */
    }
    
    @media (max-width: 768px) {
        .booking-dates-form .form-row {
            flex-direction: column;
        }
        
        .booking-dates-form .form-group {
            width: 100%;
        }
        
        .booking-dates-form button {
            width: 100%;
            margin-top: 10px;
        }
    }
</style>
<header class="hero-section" <?php if (!empty($hotel['background_image'])): ?>style="background-image: url('<?php echo htmlspecialchars($hotel['background_image']); ?>');"<?php endif; ?>>
    <div class="hero-content animate__animated animate__fadeIn">
        <h1 class="animate__animated animate__slideInDown"><?php echo htmlspecialchars($hotel['name']); ?></h1>
        <p class="animate__animated animate__slideInUp animate__delay-1s"><?php echo htmlspecialchars($hotel['tagline']); ?></p>
        <a href="#rooms" class="btn animate__animated animate__bounceIn animate__delay-2s">Book Now</a>
    </div>
</header>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger">
        <?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>

<section class="about-section animate__animated animate__fadeIn" id="about">
    <div class="container">
        <div class="about-content">
            <span>About</span>
            <h2 class="animate__animated animate__slideInLeft"><?php echo htmlspecialchars($hotel['about_title']); ?></h2>
            <div class="divider"></div>
            <p class="animate__animated animate__fadeIn animate__delay-1s">
                <?php echo htmlspecialchars($hotel['about_description1']); ?>
            </p>
            <p class="animate__animated animate__fadeIn animate__delay-2s">
                <?php echo htmlspecialchars($hotel['about_description2']); ?>
            </p>
            <a href="#rooms" class="btn animate__animated animate__bounceIn animate__delay-3s">Explore Rooms</a>
        </div>
        <div class="about-image animate__animated animate__fadeInRight">
            <img src="<?php echo htmlspecialchars($hotel['about_image']); ?>" alt="About <?php echo htmlspecialchars($hotel['name']); ?>">
        </div>
    </div>
</section>

<section class="rooms-section animate__animated animate__fadeIn" id="rooms">
    <div class="container">
        <h2 class="animate__animated animate__slideInDown"><?php echo htmlspecialchars($hotel['rooms_title']); ?></h2>
        <div class="divider"></div>
        <p class="section-description animate__animated animate__fadeIn animate__delay-1s">
            <?php echo htmlspecialchars($hotel['rooms_description']); ?>
        </p>
        
        <!-- Booking Dates Form -->
        <div class="booking-form-container animate__animated animate__fadeIn animate__delay-1s">
            <form id="booking-dates-form" class="booking-dates-form" method="GET" action="hotel.php">
                <input type="hidden" name="id" value="<?php echo $hotel_id; ?>">
                <div class="form-row">
                    <div class="form-group">
                        <label for="booking-check-in">Check-in</label>
                        <input type="date" id="booking-check-in" name="check_in" value="<?php echo htmlspecialchars($check_in); ?>" min="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="booking-check-out">Check-out</label>
                        <input type="date" id="booking-check-out" name="check_out" value="<?php echo htmlspecialchars($check_out); ?>" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="booking-guests">Guests</label>
                        <input type="number" id="booking-guests" name="guests" value="<?php echo $guests; ?>" min="1" max="10" required>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn">Update Dates</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="rooms-grid">
            <?php 
            $delay = 1;
            $rooms_available = false;
            
            foreach ($rooms as $room):
                $is_available = $room_availability[$room['id']]['is_available'];
                $capacity_sufficient = $room_availability[$room['id']]['capacity_sufficient'];
                
                if ($is_available && $capacity_sufficient) {
                    $rooms_available = true;
                }
            ?>
                <div class="room-card animate__animated animate__fadeInUp animate__delay-<?php echo $delay; ?>s">
                    <img src="<?php echo htmlspecialchars($room['image']); ?>" alt="<?php echo htmlspecialchars($room['name']); ?>">
                    <h3><?php echo htmlspecialchars($room['name']); ?></h3>
                    <p><?php echo htmlspecialchars($room['description']); ?></p>
                    <p class="room-capacity">Max guests: <?php echo htmlspecialchars($room['capacity']); ?></p>
                    <p class="price">
                        <?php 
                        if (isset($room['price']) && $room['price'] !== null && $room['price'] !== '') {
                            echo '$' . number_format($room['price'], 2) . ' per night';
                        } else {
                            echo 'Price not available';
                        }
                        ?>
                    </p>
                    
                    <?php if (!$room_availability[$room['id']]['is_room_active']): ?>
                        <div class="unavailable-message">Sorry, this room is currently booked</div>
                    <?php elseif (!$room_availability[$room['id']]['is_date_available']): ?>
                        <div class="unavailable-message">Sorry, this room is not available for the selected dates</div>
                    <?php elseif (!$room_availability[$room['id']]['capacity_sufficient']): ?>
                        <div class="unavailable-message">This room cannot accommodate <?php echo $guests; ?> guests</div>
                    <?php else: ?>
                        <a href="checkout.php?hotel_id=<?php echo $hotel_id; ?>&room_id=<?php echo $room['id']; ?>&check_in=<?php echo htmlspecialchars($check_in); ?>&check_out=<?php echo htmlspecialchars($check_out); ?>&guests=<?php echo $guests; ?>" class="btn">Book Now</a>
                    <?php endif; ?>
                </div>
            <?php
                $delay++;
            endforeach;
            
            if (!$rooms_available):
            ?>
                <div class="no-rooms-message animate__animated animate__fadeIn">
                    <p>No rooms available for the selected dates and number of guests. Please try different dates or reduce the number of guests.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="amenities-section animate__animated animate__fadeIn" id="amenities">
    <div class="container">
        <h2 class="animate__animated animate__slideInDown"><?php echo htmlspecialchars($hotel['amenities_title']); ?></h2>
        <div class="divider"></div>
        <p class="section-description animate__animated animate__fadeIn animate__delay-1s">
            <?php echo htmlspecialchars($hotel['amenities_description']); ?>
        </p>
        <div class="amenities-grid">
            <?php
            $delay = 1;
            foreach ($amenities as $amenity):
            ?>
                <div class="amenity-card animate__animated animate__zoomIn animate__delay-<?php echo $delay; ?>s">
                    <i class="<?php echo htmlspecialchars($amenity['icon']); ?>"></i>
                    <h3><?php echo htmlspecialchars($amenity['name']); ?></h3>
                    <p><?php echo htmlspecialchars($amenity['description']); ?></p>
                </div>
            <?php
                $delay++;
            endforeach;
            ?>
        </div>
    </div>
</section>

<section class="gallery-section animate__animated animate__fadeIn" id="gallery">
    <div class="container">
        <h2 class="animate__animated animate__slideInDown"><?php echo htmlspecialchars($hotel['gallery_title']); ?></h2>
        <div class="divider"></div>
        <p class="section-description animate__animated animate__fadeIn animate__delay-1s">
            <?php echo htmlspecialchars($hotel['gallery_description']); ?>
        </p>
        <div class="gallery-grid">
            <?php
            $delay = 1;
            foreach ($gallery_images as $gallery_image):
            ?>
                <div class="gallery-item animate__animated animate__zoomIn animate__delay-<?php echo $delay; ?>s">
                    <img src="<?php echo htmlspecialchars($gallery_image['image']); ?>" alt="<?php echo htmlspecialchars($gallery_image['alt_text']); ?>">
                </div>
            <?php
                $delay++;
            endforeach;
            ?>
        </div>
    </div>
</section>

<!-- "What Our Guests Say" -->
<section class="reviews-section animate__animated animate__fadeIn">
    <h2 class="reviews-title animate__animated animate__slideInDown">What Our Guests Say</h2>
    <div class="divider"></div>
    <?php
    // Check if we have any reviews
    if (!empty($reviews)):
    ?>
        <div class="reviews-slider">
            <div class="slider-container">
                <?php
                $active = true;
                foreach ($reviews as $review):
                ?>
                    <div class="slide <?php echo $active ? 'active' : ''; ?>">
                        <div class="review-card">
                            <p class="review-text">"<?php echo htmlspecialchars($review['review_text']); ?>"</p>
                            <div class="review-author">
                                <span class="author-name"><?php echo htmlspecialchars($review['author_name']); ?></span>
                                <span class="author-date"><?php echo htmlspecialchars($review['review_date']); ?></span>
                            </div>
                        </div>
                    </div>
                <?php
                    $active = false;
                endforeach;
                ?>
            </div>
            <button class="slider-btn slider-prev">❮</button>
            <button class="slider-btn slider-next">❯</button>
            <div class="slider-dots"></div>
        </div>
    <?php else: ?>
        <div class="no-reviews animate__animated animate__fadeIn animate__delay-1s">
            <p class="text-center">No reviews available for this hotel yet. Be the first to share your experience!</p>
        </div>
    <?php endif; ?>
</section>

<section id="contact" class="animate__animated animate__fadeIn">
    <div class="container">
        <h2 class="animate__animated animate__slideInDown">Contact Hotel</h2>
        <div class="divider"></div>
        <div class="contact-info">
            <div class="contact-item animate__animated animate__fadeInUp animate__delay-1s">
                <h3>Email</h3>
                <p><?php echo htmlspecialchars($contact['email']); ?></p>
            </div>
            <div class="contact-item animate__animated animate__fadeInUp animate__delay-2s">
                <h3>Phone</h3>
                <p><?php echo htmlspecialchars($contact['phone']); ?></p>
            </div>
            <div class="contact-item animate__animated animate__fadeInUp animate__delay-3s">
                <h3>Address</h3>
                <p><?php echo htmlspecialchars($contact['address']); ?></p>
            </div>
        </div>
    </div>
</section>

<section id="location" class="animate__animated animate__fadeIn">
    <div class="container">
        <h2 class="animate__animated animate__slideInDown">Hotel Location</h2>
        <div class="divider"></div>
        <div class="map-container animate__animated animate__zoomIn animate__delay-1s">
            <iframe
                src="<?php echo htmlspecialchars(isset($hotel['map_embed_url']) ? $hotel['map_embed_url'] : 'https://maps.google.com/maps?q=hotels&output=embed'); ?>"
                width="100%"
                height="400"
                frameborder="0"
                style="border:0;"
                allowfullscreen="">
            </iframe>
        </div>
    </div>
</section>

<!-- Booking Form Handler Script -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get the form elements
        const bookingForm = document.getElementById('booking-dates-form');
        const checkInInput = document.getElementById('booking-check-in');
        const checkOutInput = document.getElementById('booking-check-out');
        const guestsInput = document.getElementById('booking-guests');
        const bookButtons = document.querySelectorAll('.room-card .btn');

        // Set minimum check-out date based on check-in date
        checkInInput.addEventListener('change', function() {
            const checkInDate = new Date(this.value);
            checkInDate.setDate(checkInDate.getDate() + 1);
            const minCheckOutDate = checkInDate.toISOString().split('T')[0];

            checkOutInput.min = minCheckOutDate;

            // If check-out date is before new min date, update it
            if (checkOutInput.value < minCheckOutDate) {
                checkOutInput.value = minCheckOutDate;
            }
        });

        // Function to update all booking links
        function updateBookingLinks() {
            const checkIn = checkInInput.value;
            const checkOut = checkOutInput.value;
            const guests = guestsInput.value;

            bookButtons.forEach(button => {
                // Get the current href and extract the room_id parameter
                const currentHref = button.getAttribute('href');
                const urlParams = new URLSearchParams(currentHref.split('?')[1]);
                const roomId = urlParams.get('room_id');
                const hotelId = urlParams.get('hotel_id');

                // Create new URL with updated parameters
                const newHref = `checkout.php?hotel_id=${hotelId}&room_id=${roomId}&check_in=${checkIn}&check_out=${checkOut}&guests=${guests}`;

                // Update the button href
                button.setAttribute('href', newHref);
            });
        }

        // Initialize links with form values
        updateBookingLinks();
    });
</script>

<?php require_once 'footer.php'; ?>