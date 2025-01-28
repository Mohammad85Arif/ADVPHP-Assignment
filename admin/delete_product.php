<?php
// Start the session
session_start();

// Include database connection
include('../config/db.php');

// Check if the user is logged in, else redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Check if the product ID is passed in the URL
if (isset($_GET['id'])) {
    $product_id = $_GET['id'];

    // Prepare a delete query
    $sql = "DELETE FROM products WHERE id = :id";

    if ($stmt = $conn->prepare($sql)) {
        // Bind the ID parameter
        $stmt->bindParam(':id', $product_id, PDO::PARAM_INT);

        // Attempt to execute the query
        if ($stmt->execute()) {
            // Redirect to the products page with a success message
            header("location: dashboard.php?status=success&message=Product deleted successfully");
            exit;
        } else {
            // Redirect to the products page with an error message
            header("location: dashboard.php?status=error&message=Failed to delete the product");
            exit;
        }
    } else {
        // Redirect to the products page with an error message
        header("location: dashboard.php?status=error&message=Failed to prepare the delete query");
        exit;
    }
} else {
    // If no ID is provided, redirect back to the products page with an error message
    header("location: dashboard.php?status=error&message=Product ID missing");
    exit;
}

// Close the database connection
unset($conn);
?>
