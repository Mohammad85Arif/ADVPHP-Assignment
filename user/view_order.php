<?php
include('config/db.php');
session_start();

if (!isset($_SESSION['user_id']) || !isset($_GET['order_id'])) {
    header('Location: login.php');  // Redirect to login if user is not logged in or order ID is not set
    exit();
}

$order_id = $_GET['order_id'];
$user_id = $_SESSION['user_id'];

// Fetch order details
$query = "SELECT * FROM orders WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$order_result = $stmt->get_result();

if ($order_result->num_rows == 0) {
    echo "Order not found.";
    exit();
}

$order = $order_result->fetch_assoc();

// Fetch order items
$query = "SELECT products.name, order_items.price, order_items.quantity 
          FROM order_items 
          JOIN products ON order_items.product_id = products.id 
          WHERE order_items.order_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order_items_result = $stmt->get_result();

?>

<h2>Order Details</h2>
<p><strong>Order ID:</strong> <?php echo $order['id']; ?></p>
<p><strong>Order Date:</strong> <?php echo $order['order_date']; ?></p>
<p><strong>Total Amount:</strong> $<?php echo number_format($order['total_amount'], 2); ?></p>
<p><strong>Shipping Address:</strong> <?php echo $order['shipping_address']; ?></p>

<h3>Order Items:</h3>
<table>
    <thead>
        <tr>
            <th>Product</th>
            <th>Price</th>
            <th>Quantity</th>
        </tr>
    </thead>
    <tbody>
        <?php
        while ($item = $order_items_result->fetch_assoc()) {
            echo "<tr>
                    <td>{$item['name']}</td>
                    <td>\${$item['price']}</td>
                    <td>{$item['quantity']}</td>
                  </tr>";
        }
        ?>
    </tbody>
</table>

<a href="dashboard.php">Back to Dashboard</a>

<?php
// Include footer
include('includes/footer.php');
?>
