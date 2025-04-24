<?php
// Set headers to return JSON
header('Content-Type: application/json');

// Turn off error display and log errors instead
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_log('Starting payment processing');

// Try-catch everything to ensure we return JSON
try {
    require_once 'vendor/autoload.php';
    require_once 'db_connect.php';

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Unauthorized');
    }

    // Set Stripe API key
    \Stripe\Stripe::setApiKey('sk_test_51QxlopQRpFQsvP4nzsoBneMD3VRI7Tk7KB3dLJGPvmbacZ41TBvSbOKw6daDxzMnsgl54WWacBAKqNtIr832NVFh00o4qJjwhF');

    // Get JSON POST data
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    error_log('Received data: ' . $json);

    if (!$data) {
        throw new Exception('Invalid request data');
    }

    // Get user details from session or database
    $user_query = "SELECT name, email, phone FROM users WHERE id = ?";
    $user_stmt = $conn->prepare($user_query);
    $user_stmt->bind_param("i", $_SESSION['user_id']);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    $user = $user_result->fetch_assoc();

    if (!$user) {
        throw new Exception('User details not found');
    }

    // Create a PaymentIntent
    $intent = \Stripe\PaymentIntent::create([
        'amount' => (int)($data['amount']), // amount in cents
        'currency' => 'usd',
        'automatic_payment_methods' => [
            'enabled' => true,
        ],
        'description' => "Booking for Hotel ID: {$data['hotel_id']}, Room ID: {$data['room_id']}"
    ]);
    
    error_log('Created PaymentIntent: ' . $intent->id);

    // Return client secret
    echo json_encode([
        'clientSecret' => $intent->client_secret
    ]);

} catch (\Stripe\Exception\CardException $e) {
    // Card was declined
    error_log('Card declined: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'error' => $e->getMessage()
    ]);
} catch (\Exception $e) {
    error_log('Error in payment processing: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage()
    ]);
}
?> 