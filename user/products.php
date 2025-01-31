<?php
session_start();
include '../config/db.php';
include 'includes/header.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $product_id = $_POST['product_id'];
    $quantity = 1;

    try {
        // Check if the product already exists in the cart
        $check_cart = $conn->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
        $check_cart->execute([$user_id, $product_id]);
        $cart_item = $check_cart->fetch(PDO::FETCH_ASSOC);

        if ($cart_item) {
            // Update quantity if the product already exists in the cart
            $update_cart = $conn->prepare("UPDATE cart SET quantity = quantity + ? WHERE user_id = ? AND product_id = ?");
            $update_cart->execute([$quantity, $user_id, $product_id]);
        } else {
            // Insert new item into the cart
            $insert_cart = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
            $insert_cart->execute([$user_id, $product_id, $quantity]);
        }

        $_SESSION['message'] = "Product added to cart successfully.";
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

// Fetch all products
try {
    $query = $conn->prepare("SELECT * FROM products");
    $query->execute();
    $products = $query->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

?>

<div class="container">
    <h2 class="text-center">Products</h2>
    <div class="products-grid">
        <?php foreach ($products as $product) : ?>
            <div class="product-card">
                <img src="../public/images/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                <p><?php echo htmlspecialchars($product['description']); ?></p>
                <p>Price: $<?php echo htmlspecialchars($product['price']); ?></p>
                <form method="POST" action="">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    <button type="submit" name="add_to_cart" class="btn btn-primary">Add to Cart</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
