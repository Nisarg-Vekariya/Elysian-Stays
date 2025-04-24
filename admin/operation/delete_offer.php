<?php
require '../../db_connect.php'; // Include your database connection file

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: ../platform_settings.php?error=Invalid offer ID");
    exit();
}

$id = (int)$_GET['id'];

// Prepare and execute delete statement
$stmt = $conn->prepare("DELETE FROM sliders WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    $stmt->close();
    $conn->close();
    header("Location: ../platform_settings.php?success=Offer deleted successfully");
    exit();
} else {
    $stmt->close();
    $conn->close();
    header("Location: ../platform_settings.php?error=Failed to delete offer");
    exit();
}
?>