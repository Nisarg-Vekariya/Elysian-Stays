<?php
// Include database connection
require_once 'db_connect.php';

// Set proper content type
header('Content-Type: application/json');

// Get all hotel names
$hotelNames = array();
$result = $conn->query("SELECT id, name FROM hotels ORDER BY name");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $hotelNames[] = $row['name'];
    }
}

// Return as JSON
echo json_encode($hotelNames);

// Close connection
$conn->close();
?> 