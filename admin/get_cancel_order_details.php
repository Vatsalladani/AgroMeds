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
    die(json_encode(['status' => 'error', 'message' => 'Database connection failed']));
}

// Get cancellation ID
$cancellation_id = isset($_POST['cancellation_id']) ? intval($_POST['cancellation_id']) : 0;

if ($cancellation_id <= 0) {
    die(json_encode(['status' => 'error', 'message' => 'Invalid cancellation ID']));
}

// Get cancellation details
$cancelQuery = "SELECT co.*, o.customer_name, o.email, o.phone, o.address, o.pincode, 
                       o.total_amount, o.order_date, o.payment_method, o.payment_status
                FROM cancel_orders co
                JOIN orders o ON co.order_id = o.order_id
                WHERE co.cancellation_id = $cancellation_id";

$cancelResult = $conn->query($cancelQuery);

if ($cancelResult && $cancelResult->num_rows > 0) {
    $cancelOrder = $cancelResult->fetch_assoc();
    
    // Get order items
    $order_id = $cancelOrder['order_id'];
    $itemsQuery = "SELECT oi.*, p.product_name 
                   FROM order_items oi
                   JOIN products p ON oi.product_id = p.product_id
                   WHERE oi.order_id = $order_id";
    
    $itemsResult = $conn->query($itemsQuery);
    $orderItems = [];
    
    if ($itemsResult) {
        while ($item = $itemsResult->fetch_assoc()) {
            $orderItems[] = $item;
        }
    }
    
    echo json_encode([
        'status' => 'success',
        'cancel_order' => $cancelOrder,
        'order' => [
            'order_id' => $order_id,
            'customer_name' => $cancelOrder['customer_name'],
            'email' => $cancelOrder['email'],
            'phone' => $cancelOrder['phone'],
            'address' => $cancelOrder['address'],
            'pincode' => $cancelOrder['pincode'],
            'total_amount' => $cancelOrder['total_amount'],
            'order_date' => $cancelOrder['order_date'],
            'payment_method' => $cancelOrder['payment_method'],
            'payment_status' => $cancelOrder['payment_status']
        ],
        'order_items' => $orderItems
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Cancel order not found'
    ]);
}

$conn->close();
?>