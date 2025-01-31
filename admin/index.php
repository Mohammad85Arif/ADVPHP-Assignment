<?php
session_start();
include '../config/db.php'; // Include the database connection
include '../includes/header.php'; // Include the header

try {
    // Fetch products from the database
    $query = "SELECT * FROM products";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC); // Fetch all products as an associative array
} catch (PDOException $e) {
    die("Error fetching products: " . $e->getMessage());
}
?>

<div class="product-list">
    <h1>Our Products</h1>
    <div class="products-container">
        <?php foreach ($products as $product): ?>
            <div class="product-item">
                <img src="../public/images/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                <p>Price: $<?php echo htmlspecialchars($product['price']); ?></p>
                <a href="../user/cart.php?add=<?php echo $product['id']; ?>" class="btn">Add to Cart</a>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include '../includes/footer.php'; // Include the footer ?>
