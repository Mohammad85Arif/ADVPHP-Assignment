<?php
session_start();
include('../config/db.php');

// Redirect if not logged in
if (!isset($_SESSION["user_id"])) {
    header("location: ../user/login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

// Fetch cart items
$sql = "SELECT c.id AS cart_id, p.id AS product_id, p.name AS product_name, c.quantity, p.price 
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate total price
$total_price = 0;
foreach ($cart_items as $item) {
    $total_price += $item['price'] * $item['quantity'];
}

// Handle checkout
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $shipping_address = trim($_POST['address']);
    $payment_method = $_POST['payment_method'];

    if (empty($cart_items)) {
        echo "<script>alert('Your cart is empty! Add items before checkout.'); window.location.href='cart.php';</script>";
        exit();
    }

    try {
        $conn->beginTransaction();

        // Insert order into `orders` table
        $stmt = $conn->prepare("INSERT INTO orders (user_id, total_price, shipping_address, payment_method, order_date) 
                                VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$user_id, $total_price, $shipping_address, $payment_method]);
        $order_id = $conn->lastInsertId();

        // Insert order items
        $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        foreach ($cart_items as $item) {
            $stmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);
        }

        // Clear cart
        $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->execute([$user_id]);

        $conn->commit();

        header("location: order_confirmation.php?order_id=$order_id");
        exit();
    } catch (Exception $e) {
        $conn->rollBack();
        die("Error processing order: " . $e->getMessage());
    }
}
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

<?php include 'includes/header.php'; ?>

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
            <?php if (!empty($cart_items)): ?>
                <?php foreach ($cart_items as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td>$<?php echo number_format($item['price'], 2); ?></td>
                        <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4">Your cart is empty.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <h3>Total: $<?php echo number_format($total_price, 2); ?></h3>

    <h3>Shipping Address</h3>
    <form action="checkout.php" method="POST">
        <textarea name="address" required placeholder="Enter your shipping address..." rows="4" cols="50"></textarea>

        <label for="payment_method">Select Payment Method:</label>
        <select name="payment_method" id="payment_method" required>
            <option value="Credit Card">Credit Card</option>
            <option value="PayPal">PayPal</option>
            <option value="Bank Transfer">Bank Transfer</option>
            <option value="Cash on Delivery">Cash on Delivery</option>
        </select>

        <button type="submit" class="btn btn-primary">Place Order</button>
    </form>
</div>

<?php include('../includes/footer.php'); ?>

</body>
</html>
