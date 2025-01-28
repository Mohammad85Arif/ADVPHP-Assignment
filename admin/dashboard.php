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

// Fetch all products from the database
$sql = "SELECT * FROM products";
$stmt = $conn->prepare($sql);
$stmt->execute();
$products = $stmt->fetchAll();

// Close the database connection
unset($stmt);
unset($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../public/css/styles.css">
</head>
<body>
    <div class="container">
        <!-- Admin Header -->
        <?php include('../includes/header.php'); ?>

        <div class="dashboard">
            <h2>Welcome, Admin!</h2>
            <p>Manage your products here.</p>

            <div class="dashboard-actions">
                <a href="add_product.php" class="btn">Add Product</a>
            </div>

            <h3>Product List</h3>
            <table class="product-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Price</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Check if there are any products and display them
                    if ($products) {
                        foreach ($products as $product) {
                            echo "<tr>";
                            echo "<td>" . $product['id'] . "</td>";
                            echo "<td>" . $product['name'] . "</td>";
                            echo "<td>" . $product['description'] . "</td>";
                            echo "<td>" . $product['price'] . "</td>";
                            echo "<td>";
                            echo "<a href='edit_product.php?id=" . $product['id'] . "' class='btn'>Edit</a>";
                            echo " | ";
                            echo "<a href='delete_product.php?id=" . $product['id'] . "' class='btn' onclick='return confirm(\"Are you sure you want to delete this product?\");'>Delete</a>";
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5'>No products found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- Admin Footer -->
        <?php include('../includes/footer.php'); ?>
    </div>
</body>
</html>
