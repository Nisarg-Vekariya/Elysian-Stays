<?php
header('Content-Type: application/json');
ini_set('display_errors', 0);
ini_set('log_errors', 1);

try {
    require_once 'db_connect.php';
    
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Unauthorized');
    }
    
    // Get JSON POST data
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    if (!$data) {
        throw new Exception('Invalid request data');
    }
    
    // Get user details from session or database (adjust query based on your table structure)
    $user_query = "SELECT name, email, phone FROM users WHERE id = ?";
    $user_stmt = $conn->prepare($user_query);
    $user_stmt->bind_param("i", $_SESSION['user_id']);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    $user = $user_result->fetch_assoc();
    
    if (!$user) {
        throw new Exception('User details not found');
    }
    
    // Insert booking into database
    $query = "INSERT INTO bookings (
        hotel_id,
        room_id,
        guest_name,
        guest_email,
        guest_phone,
        check_in_date,
        check_out_date,
        total_price,
        status
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'confirmed')";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param(
        "iisssssd", 
        $data['hotel_id'],
        $data['room_id'],
        $user['name'],
        $user['email'],
        $user['phone'],
        $data['check_in'],
        $data['check_out'],
        $data['amount']
    );
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to save booking to database: ' . $stmt->error);
    }
    
    $booking_id = $conn->insert_id;
    
    echo json_encode([
        'success' => true,
        'booking_id' => $booking_id
    ]);

} catch (Exception $e) {
    error_log('Error in save_booking.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage()
    ]);
} 