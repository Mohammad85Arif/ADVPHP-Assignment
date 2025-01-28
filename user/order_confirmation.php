<?php
// Start the session
session_start();

// Check if the user is logged in, otherwise redirect to the login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Include the database connection file
include('../config/db.php');

// Check if order_id is passed in the URL
if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
    echo "Invalid Order ID.";
    exit;
}

// Get the order_id from the URL
$order_id = $_GET['order_id'];

// Fetch the order details from the orders table
$sql = "SELECT o.id, o.total_price, o.shipping_address, o.payment_method, o.order_date, u.username
        FROM orders o
        JOIN users u ON o.user_id = u.id
        WHERE o.id = :order_id";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bindParam(":order_id", $order_id, PDO::PARAM_INT);
    $stmt->execute();
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    // If no order found with the given order_id
    if (!$order) {
        echo "Order not found.";
        exit;
    }
} else {
    echo "Error fetching order details.";
    exit;
}

// Fetch order items for the specific order
$sql = "SELECT oi.quantity, oi.price, p.name AS product_name
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = :order_id";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bindParam(":order_id", $order_id, PDO::PARAM_INT);
    $stmt->execute();
    $order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    echo "Error fetching order items.";
    exit;
}

// Close connection
unset($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
    <link rel="stylesheet" href="../public/css/styles.css">
</head>
<body>

    <?php include('../includes/header.php'); ?>

    <div class="container">
        <h2>Order Confirmation</h2>

        <h3>Order #<?php echo htmlspecialchars($order['id']); ?> - Placed on <?php echo htmlspecialchars($order['order_date']); ?></h3>

        <h3>Order Summary</h3>
        <table class="order-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($order_items) > 0): ?>
                    <?php foreach ($order_items as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td><?php echo number_format($item['price'], 2); ?> USD</td>
                            <td><?php echo number_format($item['price'] * $item['quantity'], 2); ?> USD</td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">No items found in the order.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <h3>Shipping Address</h3>
        <p><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>

        <h3>Payment Method</h3>
        <p><?php echo htmlspecialchars($order['payment_method']); ?></p>

        <h3>Total Price: <?php echo number_format($order['total_price'], 2); ?> USD</h3>

        <a href="dashboard.php" class="btn btn-primary">Back to Dashboard</a>
    </div>

    <?php include('../includes/footer.php'); ?>

</body>
</html>
