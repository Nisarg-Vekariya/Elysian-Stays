<?php
require_once("../db_connect.php"); // Adjust this to your database connection file

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Hotel ID is required'
    ]);
    exit;
}

$hotel_id = intval($_GET['id']);

// Prepare and execute query
$query = "SELECT * FROM hotels WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $hotel_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $hotel = $result->fetch_assoc();
    echo json_encode([
        'success' => true,
        'hotel' => $hotel
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Hotel not found'
    ]);
}

$stmt->close();
$conn->close();
?>