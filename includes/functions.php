<?php
// Database connection function
function getDbConnection() {
    require_once('config/db.php');
    return $conn;
}

// Function to check if the user is logged in (for user-specific pages)
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to check if the admin is logged in (for admin-specific pages)
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

// Function to handle user login
function loginUser($email, $password) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            return true;
        }
    }
    return false;
}

// Function to handle admin login
function loginAdmin($email, $password) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT * FROM admins WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $admin = $result->fetch_assoc();
        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['name'];
            return true;
        }
    }
    return false;
}

// Function to register a new user
function registerUser($name, $email, $password) {
    $conn = getDbConnection();
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $hashedPassword);
    return $stmt->execute();
}

// Function to add a product to the database (Admin use)
function addProduct($name, $description, $price, $image) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("INSERT INTO products (name, description, price, image) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssis", $name, $description, $price, $image);
    return $stmt->execute();
}

// Function to get all products
function getAllProducts() {
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT * FROM products");
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Function to get a product by ID
function getProductById($id) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Function to update a product (Admin use)
function updateProduct($id, $name, $description, $price, $image) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ?, image = ? WHERE id = ?");
    $stmt->bind_param("ssisi", $name, $description, $price, $image, $id);
    return $stmt->execute();
}

// Function to delete a product (Admin use)
function deleteProduct($id) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

// Function to add an item to the shopping cart
function addToCart($user_id, $product_id, $quantity) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
    $stmt->bind_param("iii", $user_id, $product_id, $quantity);
    return $stmt->execute();
}

// Function to get the user's cart
function getUserCart($user_id) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT c.id, p.name, p.price, c.quantity FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Function to delete an item from the cart
function removeFromCart($cart_id) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("DELETE FROM cart WHERE id = ?");
    $stmt->bind_param("i", $cart_id);
    return $stmt->execute();
}

// Function to get the total price of the items in the user's cart
function getCartTotal($user_id) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT SUM(p.price * c.quantity) AS total FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['total'];
}
?>
