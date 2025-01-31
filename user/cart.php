<?php
session_start();
include '../config/db.php';

// Redirect if user is not logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: ../user/login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

// Add product to cart
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_to_cart"])) {
    $product_id = $_POST["product_id"];
    $quantity = isset($_POST["quantity"]) ? intval($_POST["quantity"]) : 1;

    $stmt = $conn->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    $existingItem = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingItem) {
        $stmt = $conn->prepare("UPDATE cart SET quantity = quantity + ? WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$quantity, $user_id, $product_id]);
    } else {
        $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $product_id, $quantity]);
    }

    $_SESSION["message"] = "Product added to cart!";
    header("Location: cart.php");
    exit();
}

// Remove product from cart
if (isset($_GET["remove"])) {
    $cart_id = $_GET["remove"];
    $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    $stmt->execute([$cart_id, $user_id]);

    $_SESSION["message"] = "Product removed from cart!";
    header("Location: cart.php");
    exit();
}

// Update cart quantity
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update_cart"])) {
    $cart_id = $_POST["cart_id"];
    $new_quantity = $_POST["quantity"];

    if ($new_quantity > 0) {
        $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$new_quantity, $cart_id, $user_id]);
    } else {
        $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
        $stmt->execute([$cart_id, $user_id]);
    }

    $_SESSION["message"] = "Cart updated!";
    header("Location: cart.php");
    exit();
}

// Fetch cart items
$stmt = $conn->prepare("SELECT cart.id AS cart_id, products.id AS product_id, products.name, products.price, products.image, cart.quantity 
                        FROM cart 
                        JOIN products ON cart.product_id = products.id 
                        WHERE cart.user_id = ?");
$stmt->execute([$user_id]);
$cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <link rel="stylesheet" href="../public/css/styles.css">
</head>
<body>

<?php include '../includes/header.php'; ?>

<div class="container">
    <h2>Your Shopping Cart</h2>

    <?php if (!empty($_SESSION["message"])): ?>
        <p class="message"><?php echo $_SESSION["message"]; unset($_SESSION["message"]); ?></p>
    <?php endif; ?>

    <div class="cart-items">
        <?php if (count($cartItems) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $totalPrice = 0; ?>
                    <?php foreach ($cartItems as $item): ?>
                        <?php $subtotal = $item["price"] * $item["quantity"]; ?>
                        <tr>
                            <td><img src="../public/images/<?php echo htmlspecialchars($item['image']); ?>" width="50"></td>
                            <td><?php echo htmlspecialchars($item["name"]); ?></td>
                            <td>$<?php echo number_format($item["price"], 2); ?></td>
                            <td>
                                <form method="POST" action="">
                                    <input type="hidden" name="cart_id" value="<?php echo $item["cart_id"]; ?>">
                                    <input type="number" name="quantity" value="<?php echo $item["quantity"]; ?>" min="1">
                                    <button type="submit" name="update_cart">Update</button>
                                </form>
                            </td>
                            <td>$<?php echo number_format($subtotal, 2); ?></td>
                            <td><a href="cart.php?remove=<?php echo $item["cart_id"]; ?>" class="btn-remove">Remove</a></td>
                        </tr>
                        <?php $totalPrice += $subtotal; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <h3>Total: $<?php echo number_format($totalPrice, 2); ?></h3>
            <a href="checkout.php" class="btn-checkout">Proceed to Checkout</a>
        <?php else: ?>
            <p>Your cart is empty.</p>
            <!-- Back to User Page Button -->
            <a href="../user/index.php" class="btn-back">Back to Homepage</a> <!-- Modify path if necessary -->
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

</body>
</html>
