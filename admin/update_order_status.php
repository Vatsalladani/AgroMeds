<?php
session_start();
require 'vendor/autoload.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    die(json_encode(['status' => 'error', 'message' => 'Unauthorized access']));
}

// Database connection
$host = 'localhost';
$dbname = 'agriculture';
$username = 'root';
$password = '';
$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['status' => 'error', 'message' => 'Database connection failed']));
}

// Get input data
$order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
$status = isset($_POST['status']) ? $conn->real_escape_string($_POST['status']) : '';

if ($order_id <= 0 || empty($status)) {
    die(json_encode(['status' => 'error', 'message' => 'Invalid input data']));
}

// Update order status
$stmt = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
$stmt->bind_param("si", $status, $order_id);
$stmt->execute();

if ($stmt->affected_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'No changes made to order']);
} else {
    echo json_encode(['status' => 'success', 'message' => 'Order status updated successfully']);
}

$stmt->close();
$conn->close();
?>