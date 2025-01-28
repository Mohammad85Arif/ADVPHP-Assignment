<?php
session_start(); // Start the session

include '../config/db.php'; // Include the database connection

// Check if the user is already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error_message = ''; // To store any login errors

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username_or_email = $_POST['username_or_email'];
    $password = $_POST['password'];

    // Validate inputs
    if (empty($username_or_email) || empty($password)) {
        $error_message = "Please enter both username/email and password.";
    } else {
        // Prepare SQL to fetch user by username or email
        $query = $conn->prepare("SELECT * FROM users WHERE username = :username_or_email OR email = :username_or_email");
        $query->bindParam(':username_or_email', $username_or_email);
        $query->execute();
        
        $user = $query->fetch(PDO::FETCH_ASSOC); // Fetch user data

        if ($user && password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            
            // Redirect to user dashboard
            header('Location: dashboard.php');
            exit;
        } else {
            $error_message = "Invalid username/email or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login</title>
    <link rel="stylesheet" href="../public/css/styles.css">
</head>
<body>

<?php include '../includes/header.php'; // Include header ?>

<div class="login-container">
    <h2>User Login</h2>
    <?php if ($error_message): ?>
        <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>
    
    <form method="POST" action="login.php">
        <div class="form-group">
            <label for="username_or_email">Username or Email</label>
            <input type="text" id="username_or_email" name="username_or_email" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit">Login</button>
    </form>
    
    <p>Don't have an account? <a href="register.php">Register here</a></p>
</div>

<?php include '../includes/footer.php'; // Include footer ?>

</body>
</html>
