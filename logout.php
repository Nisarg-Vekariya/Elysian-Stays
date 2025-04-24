<?php
// Start the session to access it
session_start();

// Clear all session variables
session_unset();

// Destroy the session completely
session_destroy();

// Redirect to the login page
header("Location: login.php");
exit();
?>