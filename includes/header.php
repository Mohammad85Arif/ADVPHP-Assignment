<?php
// session_start() is not needed here anymore if it's already called in other pages.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-commerce Website</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <header>
        <div class="header-container">
            <h1><a href="index.php">My E-Commerce</a></h1>
            <nav>
                <ul class="nav-tabs">
                    <li><a href="index.php">Home</a></li>
                    <!-- Link the Products tab to user/products.php -->
                    <li><a href="user/products.php">Products</a></li>  <!-- Updated link -->
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="user/cart.php">Cart</a></li>
                        <li><a href="user/dashboard.php">Dashboard</a></li>
                        <li><a href="user/logout.php">Logout</a></li>
                    <?php else: ?>
                        <li><a href="user/login.php">Login</a></li>
                        <li><a href="user/register.php">Register</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
</body>
</html>
