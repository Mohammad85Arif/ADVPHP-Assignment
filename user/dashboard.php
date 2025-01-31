<?php
session_start();
include 'includes/header.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Database connection
include '../config/db.php';


$userId = $_SESSION['user_id'];

// Fetch user details
try {
    $query = "SELECT name, email, username FROM users WHERE id = :id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception("User not found.");
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    exit;
}

// Fetch user's orders
try {
    $orderQuery = "
        SELECT orders.id, orders.total_price, orders.order_date
        FROM orders
        WHERE orders.user_id = :id
        ORDER BY orders.order_date DESC
    ";
    $orderStmt = $conn->prepare($orderQuery);
    $orderStmt->bindParam(':id', $userId, PDO::PARAM_INT);
    $orderStmt->execute();
    $orders = $orderStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    $orders = [];
}
?>

<div class="dashboard">
    <h2>Welcome, <?php echo htmlspecialchars($user['name']); ?></h2>
    
    <div class="profile-section">
        <h3>Your Profile</h3>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
        <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
        <a href="edit_profile.php" class="btn">Edit Profile</a>
    </div>

    <div class="orders-section">
        <h3>Your Orders</h3>
        <?php if (count($orders) > 0): ?>
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Total Price</th>
                        <th>Order Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($order['id']); ?></td>
                            <td>$<?php echo number_format($order['total_price'], 2); ?></td>
                            <td><?php echo htmlspecialchars($order['order_date']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>You have not placed any orders yet.</p>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; // Include the footer ?>
