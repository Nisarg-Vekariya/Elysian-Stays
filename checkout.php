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

// Include Stripe library
require_once 'vendor/autoload.php';
\Stripe\Stripe::setApiKey('use your owned stripe api key');

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get booking details from URL parameters
$hotel_id = isset($_GET['hotel_id']) ? intval($_GET['hotel_id']) : 0;
$room_id = isset($_GET['room_id']) ? intval($_GET['room_id']) : 0;
$check_in = isset($_GET['check_in']) ? $_GET['check_in'] : '';
$check_out = isset($_GET['check_out']) ? $_GET['check_out'] : '';
$guests = isset($_GET['guests']) ? intval($_GET['guests']) : 1;

// Validate input
if (!$hotel_id || !$room_id || empty($check_in) || empty($check_out)) {
    // Redirect back to hotel page if parameters are missing
    header("Location: search.php");
    exit();
}

// Calculate number of nights
$check_in_date = new DateTime($check_in);
$check_out_date = new DateTime($check_out);
$interval = $check_in_date->diff($check_out_date);
$total_nights = $interval->days;

if ($total_nights < 1) {
    $total_nights = 1; // Minimum one night
}

// Fetch hotel details
$hotel_query = "SELECT name, tagline, about_image FROM hotels WHERE id = ?";
$hotel_stmt = $conn->prepare($hotel_query);
$hotel_stmt->bind_param("i", $hotel_id);
$hotel_stmt->execute();
$hotel_result = $hotel_stmt->get_result();
$hotel = $hotel_result->fetch_assoc();
$hotel_stmt->close();

if (!$hotel) {
    // Redirect if hotel not found
    header("Location: search.php");
    exit();
}

// Fetch room details
$room_query = "SELECT name, description, price, image FROM rooms WHERE id = ? AND hotel_id = ?";
$room_stmt = $conn->prepare($room_query);
$room_stmt->bind_param("ii", $room_id, $hotel_id);
$room_stmt->execute();
$room_result = $room_stmt->get_result();
$room = $room_result->fetch_assoc();
$room_stmt->close();

if (!$room) {
    // Redirect if room not found
    header("Location: hotel.php?id=" . $hotel_id);
    exit();
}

// Calculate costs
$room_cost_per_night = $room['price'];
$subtotal = $room_cost_per_night * $total_nights;
$platform_fee_percentage = 15;
$platform_fee = ($subtotal * $platform_fee_percentage) / 100;
$tax_percentage = 10;
$taxes = ($subtotal * $tax_percentage) / 100;
$total_amount = $subtotal + $platform_fee + $taxes;

// Handle discount code
$discount_amount = 0;
$discount_message = "";
$final_amount = $total_amount;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['discount_code'])) {
    $discount_code = trim($_POST['discount_code']);
    
    // Query to check if discount code exists and is active
    $discount_query = "SELECT coupon_code, discount FROM sliders WHERE coupon_code = ? AND is_active = 1";
    $discount_stmt = $conn->prepare($discount_query);
    $discount_stmt->bind_param("s", $discount_code);
    $discount_stmt->execute();
    $discount_result = $discount_stmt->get_result();
    
    if ($discount_result->num_rows > 0) {
        $discount_data = $discount_result->fetch_assoc();
        $discount_percentage = $discount_data['discount'];
        $discount_amount = ($total_amount * $discount_percentage) / 100;
        $final_amount = $total_amount - $discount_amount;
        $discount_message = "<div class='alert alert-success'>Discount code applied successfully! You saved $" . number_format($discount_amount, 2) . ".</div>";
    } else {
        $discount_message = "<div class='alert alert-danger'>Invalid discount code. Please try again.</div>";
    }
    $discount_stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - <?php echo htmlspecialchars($hotel['name']); ?></title>
    <script src="js/bootstrap.bundle.min.js"></script>
    <link href="css/animate.min.css" rel="stylesheet">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
    <script src="js/jquery-3.6.4.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cinzel:wght@400..900&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap');

        body {
            background-color: #fff;
            margin: 0;
            padding: 0;
            animation: fadeInBody 1.5s;
            font-family: 'Inter', serif;
        }

        @keyframes fadeInBody {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .navbar {
            background-color: #45443F;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar .logo {
            color: #ad8b3a;
            font-size: 24px;
            font-weight: bold;
        }

        .navbar .back-button {
            color: white;
            text-decoration: none;
            background-color: #ad8b3a;
            border: none;
        }

        .checkout-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            max-width: 800px;
            margin: 50px auto;
            padding: 30px;
            animation: bounceIn 1s;
        }

        @keyframes bounceIn {
            0% {
                opacity: 0;
                transform: scale(0.9);
            }

            50% {
                opacity: 1;
                transform: scale(1.05);
            }

            100% {
                transform: scale(1);
            }
        }

        .theme-color {
            color: #ad8b3a;
            font-family: 'Cinzel', serif;
            text-transform: capitalize;
            font-weight: 500;
        }

        .btn-theme {
            background-color: #ad8b3a;
            color: white;
            transition: transform 0.3s;
        }

        .btn-theme:hover {
            background-color: #906f2f;
            transform: translateY(-3px);
        }

        .receipt-box {
            border: 2px dashed #ad8b3a;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            background: #fefae0;
            animation: fadeInUp 1s;
        }

        .receipt-box h5 {
            border-bottom: 2px solid #ad8b3a;
            padding-bottom: 10px;
            font-family: 'Cinzel', serif;
        }

        .hotel-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        footer {
            background-color: #000;
            color: white;
            text-align: center;
            padding: 10px 0;
            bottom: 0;
            width: 100%;
            z-index: 1000;
        }

        footer a {
            color: #ad8b3a;
            text-decoration: none;
        }

        footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-light bg-light animate__animated animate__fadeInDown">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">Elysian Stays</a>
            <a href="javascript:history.back()"><i class="fas fa-arrow-left"></i><button class="btn back-button btn-theme">Back</button></a>
        </div>
    </nav>

    <!-- Checkout Container -->
    <div class="checkout-container animate__animated animate__fadeInUp">
        <!-- Hotel Image -->
        <img src="<?php echo htmlspecialchars($hotel['about_image']); ?>" alt="<?php echo htmlspecialchars($hotel['name']); ?>" class="hotel-image">
        
        <!-- Guide Section -->
        <div class="guide mb-4">
            <h5 class="theme-color">Booking Details</h5>
            <p>Please review your booking details below. Make sure all the information is correct before proceeding to payment.</p>
        </div>

        <!-- Discount Code Form -->
        <div class="discount-form mb-4">
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">
                <div class="input-group">
                    <input type="text" class="form-control" name="discount_code" placeholder="Enter discount code">
                    <button type="submit" class="btn btn-theme">Apply</button>
                </div>
            </form>
            <?php echo $discount_message; ?>
        </div>

        <!-- Receipt Section -->
        <div class="receipt-box">
            <h5 class="theme-color">Booking Receipt</h5>
            <p><strong>Hotel:</strong> <?php echo htmlspecialchars($hotel['name']); ?></p>
            <p><strong>Room Type:</strong> <?php echo htmlspecialchars($room['name']); ?></p>
            <p><strong>Check-in:</strong> <?php echo date('d M Y', strtotime($check_in)); ?></p>
            <p><strong>Check-out:</strong> <?php echo date('d M Y', strtotime($check_out)); ?></p>
            <p><strong>Total Nights:</strong> <?php echo $total_nights; ?></p>
            <p><strong>Guests:</strong> <?php echo $guests; ?></p>
            <hr>
            <p><strong>Room Cost (per night):</strong> $<?php echo number_format($room_cost_per_night, 2); ?></p>
            <p><strong>Number of Nights:</strong> <?php echo $total_nights; ?></p>
            <p><strong>Subtotal:</strong> $<?php echo number_format($subtotal, 2); ?></p>
            <p><strong>Platform Fee (<?php echo $platform_fee_percentage; ?>%):</strong> $<?php echo number_format($platform_fee, 2); ?></p>
            <p><strong>Taxes (<?php echo $tax_percentage; ?>%):</strong> $<?php echo number_format($taxes, 2); ?></p>
            
            <?php if ($discount_amount > 0): ?>
            <p><strong>Discount:</strong> -$<?php echo number_format($discount_amount, 2); ?></p>
            <?php endif; ?>
            
            <hr>
            <p class="fw-bold fs-5"><strong>Total Amount:</strong> $<?php echo number_format($final_amount, 2); ?></p>
            
            <form id="payment-form">
                <input type="hidden" name="hotel_id" value="<?php echo $hotel_id; ?>">
                <input type="hidden" name="room_id" value="<?php echo $room_id; ?>">
                <input type="hidden" name="check_in" value="<?php echo $check_in; ?>">
                <input type="hidden" name="check_out" value="<?php echo $check_out; ?>">
                <input type="hidden" name="guests" value="<?php echo $guests; ?>">
                <input type="hidden" name="total_nights" value="<?php echo $total_nights; ?>">
                <input type="hidden" name="subtotal" value="<?php echo $subtotal; ?>">
                <input type="hidden" name="platform_fee" value="<?php echo $platform_fee; ?>">
                <input type="hidden" name="taxes" value="<?php echo $taxes; ?>">
                <input type="hidden" name="discount_amount" value="<?php echo $discount_amount; ?>">
                <input type="hidden" name="total_amount" value="<?php echo $final_amount; ?>">
                
                <div id="card-element" class="mb-3">
                    <!-- Stripe Card Element will be inserted here -->
                </div>
                
                <div id="card-errors" class="alert alert-danger d-none" role="alert"></div>
                
                <button type="submit" class="btn btn-theme w-100" id="submit-button">
                    Pay $<?php echo number_format($final_amount, 2); ?>
                </button>
            </form>

            <!-- Add this right after the submit button in your payment form -->
            <div id="payment-processing" class="alert alert-info d-none mt-3">
                <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                Processing your payment, please wait...
            </div>

            <div id="payment-success" class="alert alert-success d-none mt-3">
                <i class="fas fa-check-circle me-2"></i>
                Payment successful! Redirecting to confirmation page...
            </div>
        </div>
        
        <!-- Booking Details -->
        <div class="booking-details mb-4">
            <h5 class="theme-color">Room Details</h5>
            <p><?php echo htmlspecialchars($room['description']); ?></p>
        </div>
        
        <!-- Cancellation Policy -->
        <div class="cancellation-policy">
            <h5 class="theme-color">Cancellation Policy</h5>
            <p>Free cancellation up to 48 hours before check-in. Cancellations made less than 48 hours before check-in are subject to a charge equivalent to one night's stay.</p>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; <?php echo date('Y'); ?> <a href="index.php">Elysian Stays</a>. All Rights Reserved.</p>
    </footer>

    <!-- Add this before closing body tag -->
    <script src="https://js.stripe.com/v3/"></script>
    <script>
        const stripe = Stripe('pk_test_51QxlopQRpFQsvP4nvYXsHgUc50M8njS9u6Z20v1LRUJSqOeTK6uM78SY5V4LUpuPUhJ3H8SAQNo3Tol9jSbZY2kl00pAVwjMxG');
        const elements = stripe.elements();
        
        // Create card Element with custom styling
        const card = elements.create('card', {
            hidePostalCode: true,
            style: {
                base: {
                    color: '#32325d',
                    fontFamily: '"Inter", "Helvetica Neue", Helvetica, sans-serif',
                    fontSmoothing: 'antialiased',
                    fontSize: '16px',
                    '::placeholder': {
                        color: '#aab7c4'
                    }
                },
                invalid: {
                    color: '#fa755a',
                    iconColor: '#fa755a'
                }
            }
        });
        
        card.mount('#card-element');
        
        // Handle form submission
        const form = document.getElementById('payment-form');
        const submitButton = document.getElementById('submit-button');
        const errorElement = document.getElementById('card-errors');
        
        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            submitButton.disabled = true;
            errorElement.classList.add('d-none');
            
            // Show processing message
            document.getElementById('payment-processing').classList.remove('d-none');
            
            try {
                console.log('Submitting payment form');
                
                // Step 1: Create PaymentIntent on the server
                const response = await fetch('process_payment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        amount: <?php echo $final_amount * 100; ?>, // Convert to cents
                        hotel_id: <?php echo $hotel_id; ?>,
                        room_id: <?php echo $room_id; ?>,
                        check_in: '<?php echo $check_in; ?>',
                        check_out: '<?php echo $check_out; ?>'
                    })
                });
                
                console.log('Got response from server');
                
                // Check if we got a valid JSON response
                const responseText = await response.text();
                
                try {
                    const result = JSON.parse(responseText);
                    console.log('Parsed result:', result);
                    
                    if (result.error) {
                        throw new Error(result.error);
                    }
                    
                    // Step 2: Use the client secret to confirm payment
                    console.log('Confirming card payment with secret:', result.clientSecret);
                    const { error, paymentIntent } = await stripe.confirmCardPayment(
                        result.clientSecret, {
                            payment_method: {
                                card: card
                            }
                        }
                    );
                    
                    if (error) {
                        throw error;
                    }
                    
                    console.log('Payment confirmed:', paymentIntent);
                    
                    if (paymentIntent.status === 'succeeded') {
                        // Show success message
                        document.getElementById('payment-processing').classList.add('d-none');
                        document.getElementById('payment-success').classList.remove('d-none');
                        
                        // Step 3: Save the booking
                        const bookingData = {
                            payment_intent_id: paymentIntent.id,
                            hotel_id: <?php echo $hotel_id; ?>,
                            room_id: <?php echo $room_id; ?>,
                            check_in: '<?php echo $check_in; ?>',
                            check_out: '<?php echo $check_out; ?>',
                            amount: <?php echo $final_amount; ?>
                        };
                        
                        console.log('Saving booking:', bookingData);
                        
                        // Create a save_booking.php endpoint or reuse process_payment.php
                        const bookingResponse = await fetch('save_booking.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify(bookingData)
                        });
                        
                        const bookingResult = await bookingResponse.json();
                        
                        if (bookingResult.booking_id) {
                            // Add a short delay before redirecting for better UX
                            setTimeout(() => {
                                window.location.href = 'booking_confirmation.php?booking_id=' + bookingResult.booking_id;
                            }, 1500);
                        } else {
                            throw new Error('Failed to save booking');
                        }
                    } else {
                        throw new Error('Payment processing failed');
                    }
                    
                } catch (parseError) {
                    console.error('Failed to parse server response:', responseText);
                    throw new Error('Server returned invalid data: ' + responseText.substring(0, 100));
                }
                
            } catch (error) {
                console.error('Error:', error);
                errorElement.textContent = error.message;
                errorElement.classList.remove('d-none');
                submitButton.disabled = false;
            }
        });
    </script>
</body>
</html>
