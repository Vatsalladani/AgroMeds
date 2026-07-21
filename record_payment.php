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
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

// Get input data
$input = json_decode(file_get_contents('php://input'), true);

$order_id = $input['order_id'] ?? 0;
$user_id = $input['user_id'] ?? 0;
$amount = $input['amount'] ?? 0;
$payment_method = $input['payment_method'] ?? '';
$payment_status = $input['payment_status'] ?? 'completed';
$transaction_id = $input['transaction_id'] ?? '';
$payment_details = $input['payment_details'] ?? '';

// Insert payment record
$insert_sql = "INSERT INTO payments (order_id, user_id, amount, payment_method, payment_status, transaction_id, payment_details) 
               VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($insert_sql);
$stmt->bind_param("iidssss", $order_id, $user_id, $amount, $payment_method, $payment_status, $transaction_id, $payment_details);

if ($stmt->execute()) {
    // Update order status
    $update_sql = "UPDATE orders SET payment_status = 'completed', status = 'processing' WHERE order_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("i", $order_id);
    $update_stmt->execute();
    
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to record payment']);
}
?>