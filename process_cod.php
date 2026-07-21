<?php
session_start();
require 'db_connection.php';

// Database Connection
$conn = new mysqli('localhost', 'root', '', 'agriculture');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$order_id = $_POST['order_id'] ?? 0;
$user_id = $_SESSION['user_id'] ?? 0;

// Verify order belongs to user
$stmt = $conn->prepare("SELECT * FROM orders WHERE order_id = ? AND user_id = ?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid order']);
    exit;
}

// Update order status to "confirmed" with COD payment
$update = $conn->prepare("UPDATE orders SET payment_status = 'confirmed', payment_method = 'COD' WHERE order_id = ?");
$update->bind_param("i", $order_id);
$update->execute();

// Get order details for email
$order = $result->fetch_assoc();
$user_stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();

// Send confirmation email (using PHPMailer as before)
// ... [email sending code similar to previous examples]

echo json_encode(['success' => true]);
?>