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

// Fetch user information from the session
$user_id = $_SESSION["id"];

// Fetch cart items for the logged-in user
$sql = "SELECT c.id, p.name AS product_name, c.quantity, p.price 
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = :user_id";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    echo "Error fetching cart items.";
    exit;
}

// Calculate total price
$total_price = 0;
foreach ($cart_items as $item) {
    $total_price += $item['price'] * $item['quantity'];
}

// Handle form submission for order
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $shipping_address = $_POST['address'];
    $payment_method = $_POST['payment_method'];

    // Insert the order into the orders table
    $sql = "INSERT INTO orders (user_id, total_price, shipping_address, payment_method) 
            VALUES (:user_id, :total_price, :shipping_address, :payment_method)";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
        $stmt->bindParam(":total_price", $total_price, PDO::PARAM_STR);
        $stmt->bindParam(":shipping_address", $shipping_address, PDO::PARAM_STR);
        $stmt->bindParam(":payment_method", $payment_method, PDO::PARAM_STR);
        $stmt->execute();

        // Get the last inserted order ID
        $order_id = $conn->lastInsertId();

        // Insert the order details into the order_items table
        foreach ($cart_items as $item) {
            $sql = "INSERT INTO order_items (order_id, product_id, quantity, price) 
                    VALUES (:order_id, :product_id, :quantity, :price)";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bindParam(":order_id", $order_id, PDO::PARAM_INT);
                $stmt->bindParam(":product_id", $item['id'], PDO::PARAM_INT);
                $stmt->bindParam(":quantity", $item['quantity'], PDO::PARAM_INT);
                $stmt->bindParam(":price", $item['price'], PDO::PARAM_STR);
                $stmt->execute();
            }
        }

        // Clear the user's cart after the order is placed
        $sql = "DELETE FROM cart WHERE user_id = :user_id";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
            $stmt->execute();
        }

        // Redirect to the order confirmation page
        header("location: order_confirmation.php?order_id=" . $order_id);
        exit;
    } else {
        echo "Error placing the order.";
    }
}

// Close connection
unset($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link rel="stylesheet" href="../public/css/styles.css">
</head>
<body>

    <?php include('../includes/header.php'); ?>

    <div class="container">
        <h2>Checkout</h2>

        <h3>Your Cart</h3>
        <table class="cart-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($cart_items) > 0): ?>
                    <?php foreach ($cart_items as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td><?php echo number_format($item['price'], 2); ?> USD</td>
                            <td><?php echo number_format($item['price'] * $item['quantity'], 2); ?> USD</td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">Your cart is empty.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <h3>Total: <?php echo number_format($total_price, 2); ?> USD</h3>

        <h3>Shipping Address</h3>
        <form action="checkout.php" method="POST">
            <textarea name="address" required placeholder="Enter your shipping address..." rows="4" cols="50"></textarea>

            <h3>Payment Method</h3>
            <select name="payment_method" required>
                <option value="credit_card">Credit Card</option>
                <option value="paypal">PayPal</option>
                <option value="bank_transfer">Bank Transfer</option>
            </select>

            <button type="submit" class="btn btn-primary">Place Order</button>
        </form>
    </div>

    <?php include('../includes/footer.php'); ?>

</body>
</html>
