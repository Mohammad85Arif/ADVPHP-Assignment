<?php
include '../config/db.php';

// Check if a product ID is provided
if (isset($_GET['id'])) {
    $product_id = $_GET['id'];

    // Fetch the product details
    $query = "SELECT * FROM products WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        echo "Product not found.";
        exit;
    }
} else {
    echo "No product ID provided.";
    exit;
}

// Handle form submission for updating the product
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $image = $product['image']; // Keep the old image by default

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image_name = $_FILES['image']['name'];
        $image_tmp_name = $_FILES['image']['tmp_name'];
        $image_size = $_FILES['image']['size'];
        $image_type = $_FILES['image']['type'];

        // Check if file is an image
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array($image_type, $allowed_types)) {
            // Check file size (5MB limit)
            if ($image_size <= 5000000) {
                // Generate a unique filename to avoid overwriting
                $image = 'images/' . uniqid() . '_' . $image_name;

                // Move the uploaded file to the public/images/ folder
                move_uploaded_file($image_tmp_name, "../public/" . $image);
            } else {
                $error = "Image size is too large. Please upload a file smaller than 5MB.";
            }
        } else {
            $error = "Invalid image format. Only JPG, PNG, and GIF are allowed.";
        }
    }

    if (empty($name) || empty($price)) {
        $error = "Product name and price are required.";
    } else {
        // Update the product in the database
        $update_query = "UPDATE products SET name = ?, price = ?, description = ?, image = ? WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $result = $stmt->execute([$name, $price, $description, $image, $product_id]);

        if ($result) {
            $success = "Product updated successfully.";
            // Refresh the product data
            $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $error = "Failed to update the product. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <link rel="stylesheet" href="../public/css/styles.css">
</head>
<body>
    <div class="container">
        <h2>Edit Product</h2>

        <?php if (!empty($error)): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <p class="success"><?php echo $success; ?></p>
        <?php endif; ?>

        <form method="POST" action="" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Product Name:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="price">Price:</label>
                <input type="number" id="price" name="price" step="0.01" value="<?php echo htmlspecialchars($product['price']); ?>" required>
            </div>
            <div class="form-group">
                <label for="description">Description:</label>
                <textarea id="description" name="description" required><?php echo htmlspecialchars($product['description']); ?></textarea>
            </div>
            <!-- Image Upload -->
            <div class="form-group">
                <label for="image">Product Image (optional):</label>
                <input type="file" name="image" id="image" class="form-control">
                <?php if (!empty($product['image'])): ?>
                    <p>Current Image: <img src="../public/<?php echo htmlspecialchars($product['image']); ?>" alt="Product Image" width="100"></p>
                <?php endif; ?>
            </div>
            <button type="submit">Update Product</button>
        </form>
        <a href="dashboard.php">Back to Dashboard</a>
    </div>
</body>
</html>
