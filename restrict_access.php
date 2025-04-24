<?php
// Start session only if it’s not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
    ]);
}

function restrictAccess($allowed_roles = []) {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        header("Location: ../login.php");
        exit();
    }
    if (!in_array($_SESSION['role'], $allowed_roles)) {
        header("Location: ../unauthorized.php");
        exit();
    }
}
?>