<?php
session_start();
require 'vendor/autoload.php';

// Database connection
$host = 'localhost';
$dbname = 'agriculture';
$username = 'root';
$password = '';
$conn = new mysqli($host, $username, $password, $dbname);

// Determine response type based on Accept header
$accept = isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : '';
$is_json = strpos($accept, 'application/json') !== false;
$is_text = strpos($accept, 'text/plain') !== false || !$is_json;

// Default to JSON if no preference specified
$response_type = $is_text ? 'text' : 'json';

function sendResponse($success, $message, $data = [], $response_type = 'json') {
    if ($response_type === 'json') {
        header('Content-Type: application/json');
        $response = ['success' => $success, 'message' => $message];
        if (!empty($data)) {
            $response = array_merge($response, $data);
        }
        echo json_encode($response);
    } else {
        header('Content-Type: text/plain');
        echo $success ? "SUCCESS: $message" : "ERROR: $message";
        if (!empty($data)) {
            echo "\n\nData:\n";
            print_r($data);
        }
    }
    exit();
}

if ($conn->connect_error) {
    sendResponse(false, 'Database connection failed', [], $response_type);
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    sendResponse(false, 'Please login first', [], $response_type);
}

// Get order_id from request
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
$user_id = $_SESSION['user_id'];

// Verify the order belongs to the user
$order_sql = "SELECT * FROM orders WHERE order_id = ? AND user_id = ?";
$stmt = $conn->prepare($order_sql);
if (!$stmt) {
    sendResponse(false, 'Database error: ' . $conn->error, [], $response_type);
}

$stmt->bind_param("ii", $order_id, $user_id);
if (!$stmt->execute()) {
    sendResponse(false, 'Database error: ' . $stmt->error, [], $response_type);
}

$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    sendResponse(false, 'Order not found or unauthorized access', [], $response_type);
}

// Get order items with current product info
$items_sql = "SELECT oi.*, p.product_name, p.price, p.image_url, p.quantity as stock_quantity 
              FROM order_items oi
              JOIN products p ON oi.product_id = p.product_id
              WHERE oi.order_id = ?";
$stmt = $conn->prepare($items_sql);
if (!$stmt) {
    sendResponse(false, 'Database error: ' . $conn->error, [], $response_type);
}

$stmt->bind_param("i", $order_id);
if (!$stmt->execute()) {
    sendResponse(false, 'Database error: ' . $stmt->error, [], $response_type);
}

$items_result = $stmt->get_result();
$items = [];
$total_price = 0;

while ($item = $items_result->fetch_assoc()) {
    // Only include items that are still available
    if ($item['stock_quantity'] > 0) {
        $items[] = [
            'product_id' => $item['product_id'],
            'product_name' => $item['product_name'],
            'quantity' => $item['quantity'],
            'price' => $item['price'],
            'image_url' => $item['image_url']
        ];
        $total_price += $item['price'] * $item['quantity'];
    }
}

if (empty($items)) {
    sendResponse(false, 'No available items in this order', [], $response_type);
}

// Prepare response data
$response_data = [
    'items' => $items,
    'total_price' => $total_price
];

sendResponse(true, 'Order details retrieved successfully', $response_data, $response_type);
?>