<?php
session_start();
require 'vendor/autoload.php';

// Database connection
$host = 'localhost';
$dbname = 'agriculture';
$username = 'root';
$password = '';
$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Unauthorized");
}

$user_id = $_SESSION['user_id'];
$order_id = intval($_GET['order_id']);

// Verify user owns this order
$verify_sql = "SELECT user_id FROM orders WHERE order_id = $order_id";
$verify_result = $conn->query($verify_sql);

if ($verify_result->num_rows > 0) {
    $order = $verify_result->fetch_assoc();
    if ($order['user_id'] == $user_id) {
        // Get order details
        $order_sql = "SELECT o.*, p.payment_method, p.payment_status as payment_table_status, 
                      p.transaction_id, p.created_at as payment_date
                      FROM orders o
                      LEFT JOIN payments p ON o.order_id = p.order_id
                      WHERE o.order_id = $order_id";
        $order_result = $conn->query($order_sql);
        $order = $order_result->fetch_assoc();
        
        // Get order items
        $items_sql = "SELECT oi.*, p.product_name, p.image_url, p.description 
                     FROM order_items oi 
                     JOIN products p ON oi.product_id = p.product_id
                     WHERE oi.order_id = $order_id";
        $items_result = $conn->query($items_sql);
        
        // Display order details
        echo '<div class="row">';
        echo '<div class="col-md-6">';
        echo '<h6>Order Information</h6>';
        echo '<p><strong>Order ID:</strong> #' . $order['order_id'] . '</p>';
        echo '<p><strong>Order Date:</strong> ' . date('d M Y, h:i A', strtotime($order['order_date'])) . '</p>';
        echo '<p><strong>Status:</strong> <span class="' . getStatusClass($order['status']) . '">' . $order['status'] . '</span></p>';
        
        echo '<h6 class="mt-4">Payment Details</h6>';
        echo '<p><strong>Method:</strong> ' . ($order['payment_method'] ?? 'N/A') . '</p>';
        echo '<p><strong>Status:</strong> <span class="' . getPaymentStatusClass($order['payment_table_status']) . '">' . ($order['payment_table_status'] ?? 'Pending') . '</span></p>';
        if ($order['transaction_id']) {
            echo '<p><strong>Transaction ID:</strong> ' . $order['transaction_id'] . '</p>';
        }
        echo '<p><strong>Total Amount:</strong> ₹' . number_format($order['total_amount'], 2) . '</p>';
        
        echo '</div>';
        echo '<div class="col-md-6">';
        echo '<h6>Shipping Address</h6>';
        echo '<p>' . nl2br(htmlspecialchars($order['address'])) . '</p>';
        echo '<p>' . htmlspecialchars($order['pincode']) . '</p>';
        echo '</div>';
        echo '</div>';
        
        // Display order items
        echo '<div class="mt-4">';
        echo '<h6>Order Items</h6>';
        echo '<table class="order-items-table">';
        echo '<thead><tr><th>Product</th><th>Price</th><th>Qty</th><th>Total</th></tr></thead>';
        echo '<tbody>';
        
        while ($item = $items_result->fetch_assoc()) {
            echo '<tr>';
            echo '<td>';
            echo '<div class="d-flex align-items-center">';
            echo '<img src="' . htmlspecialchars($item['image_url']) . '" class="me-3" width="60">';
            echo '<div>';
            echo '<h6 class="mb-1">' . htmlspecialchars($item['product_name']) . '</h6>';
            echo '<small class="text-muted">' . substr(htmlspecialchars($item['description']), 0, 50) . '...</small>';
            echo '</div>';
            echo '</div>';
            echo '</td>';
            echo '<td>₹' . number_format($item['price'], 2) . '</td>';
            echo '<td>' . $item['quantity'] . '</td>';
            echo '<td>₹' . number_format($item['price'] * $item['quantity'], 2) . '</td>';
            echo '</tr>';
        }
        
        echo '</tbody>';
        echo '</table>';
        echo '</div>';
    } else {
        echo '<div class="alert alert-danger">You are not authorized to view this order</div>';
    }
} else {
    echo '<div class="alert alert-danger">Order not found</div>';
}

// Reuse functions from orders.php
function getStatusClass($status) {
    switch ($status) {
        case 'Processing': return 'status-processing';
        case 'Shipped': return 'status-shipped';
        case 'Delivered': return 'status-delivered';
        case 'Cancelled': return 'status-cancelled';
        default: return 'status-pending';
    }
}

function getPaymentStatusClass($status) {
    switch ($status) {
        case 'Completed': return 'status-delivered';
        case 'Failed': return 'status-cancelled';
        default: return 'status-pending';
    }
}
?>