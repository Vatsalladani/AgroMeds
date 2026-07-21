<?php
header('Content-Type: application/json');

session_start();
require 'vendor/autoload.php';

// Database connection
$host = 'localhost';
$dbname = 'agriculture';
$username = 'root';
$password = '';
$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    die(json_encode(['success' => false, 'message' => 'Please login first']));
}

$order_id = $_GET['order_id'] ?? 0;
$user_id = $_SESSION['user_id'];

// Validate order_id
if (!is_numeric($order_id) || $order_id <= 0) {
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'Invalid order ID']));
}

// Verify the order belongs to the user
$order_sql = "SELECT * FROM orders WHERE order_id = ? AND user_id = ?";
$stmt = $conn->prepare($order_sql);
if (!$stmt) {
    http_response_code(500);
    die(json_encode(['success' => false, 'message' => 'Database error']));
}

$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    http_response_code(404);
    die(json_encode(['success' => false, 'message' => 'Order not found']));
}

// Get order items with current stock information
$items_sql = "SELECT oi.*, p.product_name, p.price, p.stock_quantity, p.image_url as product_image 
              FROM order_items oi
              JOIN products p ON oi.product_id = p.product_id
              WHERE oi.order_id = ?";
$stmt = $conn->prepare($items_sql);
if (!$stmt) {
    http_response_code(500);
    die(json_encode(['success' => false, 'message' => 'Database error']));
}

$stmt->bind_param("i", $order_id);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Filter out products with zero stock
$available_products = array_filter($items, function($item) {
    return $item['stock_quantity'] > 0;
});

if (empty($available_products)) {
    http_response_code(200);
    die(json_encode([
        'success' => false, 
        'message' => 'None of the products from this order are currently available'
    ]));
}

// Prepare response
$response = [
    'success' => true,
    'order_id' => $order_id,
    'products' => array_values($available_products) // reindex array
];

http_response_code(200);
echo json_encode($response);
?>