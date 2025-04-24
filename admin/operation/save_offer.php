<?php
require '../../db_connect.php'; // Include database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $title = $_POST['title'];
    $coupon_code = $_POST['coupon_code'];
    $discount = $_POST['discount'];
    $is_active = $_POST['is_active'];

    // Validate input (optional but recommended)
    if (empty($title) || empty($coupon_code) || !is_numeric($discount) || $discount < 0 || $discount > 100) {
        die("Invalid input data.");
    }

    // Insert into database
    $query = "INSERT INTO sliders (title, coupon_code, discount, is_active) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssii", $title, $coupon_code, $discount, $is_active);

    if ($stmt->execute()) {
        // Redirect back to the settings page with a success message
        header("Location: ../platform_settings.php?status=success");
        exit();
    } else {
        // Redirect back with an error message
        header("Location: ../platform_settings.php?status=error");
        exit();
    }
} else {
    // Redirect if accessed directly
    header("Location: ../platform_settings.php");
    exit();
}
?>