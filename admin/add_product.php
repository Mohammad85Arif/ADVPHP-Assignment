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

// Define variables and initialize with empty values
$name = $description = $price = $image = "";
$name_err = $description_err = $price_err = $image_err = "";

// Process form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validate product name
    if (empty(trim($_POST["name"]))) {
        $name_err = "Please enter a product name.";
    } else {
        $name = trim($_POST["name"]);
    }

    // Validate product description
    if (empty(trim($_POST["description"]))) {
        $description_err = "Please enter a product description.";
    } else {
        $description = trim($_POST["description"]);
    }

    // Validate product price
    if (empty(trim($_POST["price"]))) {
        $price_err = "Please enter a product price.";
    } elseif (!is_numeric($_POST["price"])) {
        $price_err = "Please enter a valid price.";
    } else {
        $price = trim($_POST["price"]);
    }

    // Validate image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image_name = $_FILES['image']['name'];
        $image_tmp_name = $_FILES['image']['tmp_name'];
        $image_size = $_FILES['image']['size'];
        $image_type = $_FILES['image']['type'];

        // Check if file is an image
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array($image_type, $allowed_types)) {
            // Check the file size (5MB limit)
            if ($image_size <= 5000000) {
                // Generate a unique file name to avoid overwrite
                $image = 'uploads/' . uniqid() . '_' . $image_name;
                move_uploaded_file($image_tmp_name, "../" . $image);
            } else {
                $image_err = "Image size is too large. Please upload a file smaller than 5MB.";
            }
        } else {
            $image_err = "Invalid image format. Only JPG, PNG, and GIF are allowed.";
        }
    } else {
        $image_err = "Please upload an image.";
    }

    // Check if there are no errors
    if (empty($name_err) && empty($description_err) && empty($price_err) && empty($image_err)) {
        // Prepare an insert statement
        $sql = "INSERT INTO products (name, description, price, image) VALUES (:name, :description, :price, :image)";

        if ($stmt = $conn->prepare($sql)) {
            // Bind parameters
            $stmt->bindParam(":name", $param_name);
            $stmt->bindParam(":description", $param_description);
            $stmt->bindParam(":price", $param_price);
            $stmt->bindParam(":image", $param_image);

            // Set parameters
            $param_name = $name;
            $param_description = $description;
            $param_price = $price;
            $param_image = $image;

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Redirect to the product list page
                header("location: dashboard.php");
                exit();
            } else {
                echo "Something went wrong. Please try again later.";
            }
        }

        // Close the statement
        unset($stmt);
    }

    // Close the connection
    unset($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product</title>
    <link rel="stylesheet" href="../public/css/styles.css">
</head>
<body>
    <div class="container">
        <!-- Admin Header -->
        <?php include('../includes/header.php'); ?>

        <div class="add-product">
            <h2>Add New Product</h2>
            <p>Please fill in the form to add a new product.</p>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
                <!-- Product Name -->
                <div class="form-group">
                    <label for="name">Product Name</label>
                    <input type="text" name="name" id="name" class="form-control" value="<?php echo $name; ?>">
                    <span class="error"><?php echo $name_err; ?></span>
                </div>

                <!-- Product Description -->
                <div class="form-group">
                    <label for="description">Product Description</label>
                    <textarea name="description" id="description" class="form-control"><?php echo $description; ?></textarea>
                    <span class="error"><?php echo $description_err; ?></span>
                </div>

                <!-- Product Price -->
                <div class="form-group">
                    <label for="price">Product Price</label>
                    <input type="text" name="price" id="price" class="form-control" value="<?php echo $price; ?>">
                    <span class="error"><?php echo $price_err; ?></span>
                </div>

                <!-- Product Image -->
                <div class="form-group">
                    <label for="image">Product Image</label>
                    <input type="file" name="image" id="image" class="form-control">
                    <span class="error"><?php echo $image_err; ?></span>
                </div>

                <!-- Submit Button -->
                <div class="form-group">
                    <input type="submit" class="btn" value="Add Product">
                </div>
            </form>
        </div>

        <!-- Admin Footer -->
        <?php include('../includes/footer.php'); ?>
    </div>
</body>
</html>
