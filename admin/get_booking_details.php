<?php
require_once("../restrict_access.php");
restrictAccess(['admin']);
require_once("../db_connect.php");

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode(['error' => 'Booking ID is required']);
    exit;
}

$booking_id = intval($_GET['id']);

// Fetch booking details
$sql = "SELECT b.*, r.name as room_name, h.name as hotel_name 
        FROM bookings b 
        JOIN rooms r ON b.room_id = r.id
        JOIN hotels h ON b.hotel_id = h.id
        WHERE b.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $booking = $result->fetch_assoc();
    
    // Calculate nights
    $check_in = new DateTime($booking['check_in_date']);
    $check_out = new DateTime($booking['check_out_date']);
    $nights = $check_in->diff($check_out)->days;
    
    $booking['nights'] = $nights;
    
    echo json_encode($booking);
} else {
    echo json_encode(['error' => 'Booking not found']);
}

$stmt->close();
$conn->close();
?> 