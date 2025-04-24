<?php
// Database connection
$servername = "localhost";
$username = "root";  // Change if needed
$password = "";      // Change if needed
$database = "elysian_stays";

$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>