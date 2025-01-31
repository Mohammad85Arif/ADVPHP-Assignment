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
            <h1><a href="dashboard.php">My E-Commerce</a></h1> <!-- Updated link to public/index.php -->
            <nav>
                <ul class="nav-tabs">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <!-- Home tab should link to admin dashboard if logged in -->
                        <li><a href="dashboard.php">Home</a></li>
                    <?php else: ?>
                        <!-- Home tab links to the admin Login page if not logged in -->
                        <li><a href="login.php">Home</a></li> 
                    <?php endif; ?>
                    <!-- Link the Products tab to user/products.php -->
                    <li><a href="add_product.php">Add Products</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        
                        <li><a href="dashboard.php">Dashboard</a></li>
                        <li><a href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="register.php">Register</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
</body>
</html>
