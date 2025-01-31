<?php
// Start session only if it's not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in as an admin
if (isset($_SESSION['admin_loggedin']) && $_SESSION['admin_loggedin'] === true) {
    // Destroy the session for the admin
    session_unset();  // Unset all session variables
    session_destroy();  // Destroy the session completely
}

// Redirect to the admin login page after logout
header("Location: /PHP-Assignment/admin/login.php");
exit;
?>
