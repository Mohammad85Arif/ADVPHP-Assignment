<?php
// Database credentials
$host = 'localhost'; // Database host
$dbname = 'ecommerce'; // Database name
$username = 'root'; // Database username (use 'root' for local development)
$password = 'ccbst@123'; // Database password (empty for local XAMPP installations)

// Create a PDO instance to connect to the database
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // If connection fails, display an error message
    echo "Connection failed: " . $e->getMessage();
    exit();
}
?>
