<?php
session_start();
require 'vendor/autoload.php';

// Database connection
$host = 'localhost';
$dbname = 'agriculture';
$username = 'root';
$password = '';
$conn = new mysqli($host, $username, $password, $dbname);

// Set header for plain text response
header('Content-Type: text/plain');

if ($conn->connect_error) {
    die("Error: Database connection failed");
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Error: Please login first");
}

// Get order_id from POST or GET
$order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : (isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0);
$user_id = (int)$_SESSION['user_id'];

// Validate order_id
if ($order_id <= 0) {
    die("Error: Invalid order ID");
}

// Verify the order belongs to the user
$order_sql = "SELECT * FROM orders WHERE order_id = ? AND user_id = ?";
$stmt = $conn->prepare($order_sql);

if (!$stmt) {
    die("Error: Database preparation failed");
}

$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    die("Error: Order not found or doesn't belong to you");
}

// Get order items with current stock
$items_sql = "SELECT oi.*, p.stock_quantity, p.product_name, p.price, p.image_url as product_image 
              FROM order_items oi
              JOIN products p ON oi.product_id = p.product_id
              WHERE oi.order_id = ?";
$stmt = $conn->prepare($items_sql);

if (!$stmt) {
    die("Error: Database preparation failed");
}

$stmt->bind_param("i", $order_id);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$added_count = 0;
$messages = [];

foreach ($items as $item) {
    $product_id = (int)$item['product_id'];
    
    // Check if product is available
    if ($item['stock_quantity'] <= 0) {
        $messages[] = "Product '{$item['product_name']}' is out of stock";
        continue;
    }
    
    // Determine quantity to add (not exceeding available stock)
    $quantity_to_add = min($item['quantity'], $item['stock_quantity']);
    
    // Add to cart or update quantity
    if (isset($_SESSION['cart'][$product_id])) {
        $new_quantity = $_SESSION['cart'][$product_id]['quantity'] + $quantity_to_add;
        if ($new_quantity > $item['stock_quantity']) {
            $messages[] = "Only {$item['stock_quantity']} of '{$item['product_name']}' available (reduced from requested {$new_quantity})";
            $quantity_to_add = $item['stock_quantity'] - $_SESSION['cart'][$product_id]['quantity'];
            if ($quantity_to_add <= 0) continue;
        }
        $_SESSION['cart'][$product_id]['quantity'] += $quantity_to_add;
    } else {
        $_SESSION['cart'][$product_id] = [
            'product_id' => $product_id,
            'name' => $item['product_name'],
            'price' => (float)$item['price'],
            'quantity' => $quantity_to_add,
            'image' => $item['product_image'] ?? 'default.jpg'
        ];
    }
    
    $added_count++;
}

// Calculate total cart count
$cart_count = array_sum(array_column($_SESSION['cart'], 'quantity'));

// Prepare response
if ($added_count > 0) {
    echo "Success: Added $added_count items to your cart\n";
    echo "Total items in cart: $cart_count\n";
} else {
    echo "Notice: No items were added to your cart\n";
}

// Output any messages
foreach ($messages as $message) {
    echo "$message\n";
}

// Close connection
$conn->close();
?>