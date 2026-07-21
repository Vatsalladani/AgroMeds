<?php
header('Content-Type: application/json');

// Database connection
$host = 'localhost';
$db = 'agriculture';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get product ID from POST
$productId = $_POST['product_id'];

// Delete product
$sql = "DELETE FROM products WHERE product_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $productId);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Product deleted successfully']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to delete product']);
}

$stmt->close();
$conn->close();
?>