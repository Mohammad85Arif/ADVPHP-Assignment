<?php
session_start();
include '../includes/header.php'; // Include the header

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Database connection
include '../config/db.php';

try {
    // Pagination logic
    $limit = 20; // Number of orders per page
    $page = isset($_GET['page']) ? $_GET['page'] : 1;
    $start = ($page - 1) * $limit;

    // Fetch orders and related user information with pagination
    $query = "
        SELECT 
            orders.id AS order_id, 
            orders.total_price, 
            orders.order_date, 
            orders.order_status, 
            users.username AS user_name, 
            users.email AS user_email
        FROM orders
        JOIN users ON orders.user_id = users.id
        ORDER BY orders.order_date DESC
        LIMIT :start, :limit
    ";

    $stmt = $conn->prepare($query);
    $stmt->bindParam(':start', $start, PDO::PARAM_INT);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch the total number of orders to calculate the number of pages
    $count_query = "SELECT COUNT(*) FROM orders";
    $count_stmt = $conn->prepare($count_query);
    $count_stmt->execute();
    $total_orders = $count_stmt->fetchColumn();
    $total_pages = ceil($total_orders / $limit);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    exit;
}
?>

<div class="view-orders">
    <h2>Order List</h2>
    <?php if (count($orders) > 0): ?>
        <table class="orders-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>User</th>
                    <th>Email</th>
                    <th>Total Price</th>
                    <th>Order Status</th>
                    <th>Order Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($order['order_id']); ?></td>
                        <td><?php echo htmlspecialchars($order['user_name']); ?></td>
                        <td><?php echo htmlspecialchars($order['user_email']); ?></td>
                        <td>$<?php echo number_format($order['total_price'], 2); ?></td>
                        <td><?php echo htmlspecialchars($order['order_status']); ?></td>
                        <td><?php echo htmlspecialchars($order['order_date']); ?></td>
                        <td><a href="order_details.php?id=<?php echo $order['order_id']; ?>">View</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="pagination">
            <ul>
                <?php if ($page > 1): ?>
                    <li><a href="?page=<?php echo $page - 1; ?>">Prev</a></li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li><a href="?page=<?php echo $i; ?>" <?php if ($i == $page) echo 'class="active"'; ?>><?php echo $i; ?></a></li>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <li><a href="?page=<?php echo $page + 1; ?>">Next</a></li>
                <?php endif; ?>
            </ul>
        </div>
    <?php else: ?>
        <p>No orders found.</p>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; // Include the footer ?>
