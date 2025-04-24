<?php
require_once("db.php");

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("DELETE FROM hotels WHERE id = :id");
    $stmt->execute([':id' => $id]);
    echo json_encode(['success' => true]);
}
?>