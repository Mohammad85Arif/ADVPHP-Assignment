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
$sql_products = "SELECT * FROM products";
$stmt_products = $conn->prepare($sql_products);
$stmt_products->execute();
$products = $stmt_products->fetchAll();

// Fetch all orders from the database
$sql_orders = "
    SELECT 
        orders.id AS order_id, 
        orders.total_price, 
        orders.order_date, 
        orders.order_status,
        users.username AS user_name, 
        users.email AS user_email
    FROM orders
    JOIN users ON orders.user_id = users.id
    ORDER BY orders.order_date DESC
";
$stmt_orders = $conn->prepare($sql_orders);
$stmt_orders->execute();
$orders = $stmt_orders->fetchAll();

// Close the database connection
unset($stmt_products);
unset($stmt_orders);
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
            <p>Manage your products and orders here.</p>

            <div class="dashboard-actions">
                <a href="add_product.php" class="btn">Add Product</a>
            </div>

            <!-- Product List -->
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

            <!-- Order List -->
            <h3>Order List</h3>
            <table class="order-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>User</th>
                        <th>Email</th>
                        <th>Total Price</th>
                        <th>Order Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Check if there are any orders and display them
                    if ($orders) {
                        foreach ($orders as $order) {
                            echo "<tr>";
                            echo "<td>" . $order['order_id'] . "</td>";
                            echo "<td>" . $order['user_name'] . "</td>";
                            echo "<td>" . $order['user_email'] . "</td>";
                            echo "<td>$" . number_format($order['total_price'], 2) . "</td>";
                            echo "<td>" . $order['order_date'] . "</td>";
                            echo "<td>" . $order['order_status'] . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6'>No orders found</td></tr>";
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
