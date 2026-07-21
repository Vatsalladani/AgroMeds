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

// Get input data
$input = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (!isset($input['order_id'], $input['payment_method'])) {
    http_response_code(400);
    die(json_encode(['status' => 'error', 'message' => 'Missing required fields']));
}

$order_id = $conn->real_escape_string($input['order_id']);
$user_id = $_SESSION['user_id'];
$payment_method = $conn->real_escape_string($input['payment_method']);
$transaction_id = isset($input['transaction_id']) ? $conn->real_escape_string($input['transaction_id']) : null;

// Verify order belongs to user
$order_check = $conn->query("SELECT * FROM orders WHERE order_id = '$order_id' AND user_id = '$user_id'");
if ($order_check->num_rows == 0) {
    http_response_code(404);
    die(json_encode(['status' => 'error', 'message' => 'Order not found']));
}

// Process different payment methods
try {
    $conn->begin_transaction();
    
    // For Cash on Delivery
    if ($payment_method === 'cod') {
        $status = 'completed'; // Or 'pending' if you want to confirm later
        $update_sql = "UPDATE orders SET payment_status = '$status', payment_method = '$payment_method' WHERE order_id = '$order_id'";
    } 
    // For other payment methods
    else {
        $status = 'completed'; // Change based on actual payment verification
        $update_sql = "UPDATE orders SET payment_status = '$status', payment_method = '$payment_method', transaction_id = '$transaction_id' WHERE order_id = '$order_id'";
    }
    
    if (!$conn->query($update_sql)) {
        throw new Exception("Failed to update order: " . $conn->error);
    }
    
    // Insert into payments table (if you have one)
    $amount = $order_check->fetch_assoc()['total_amount'];
    $payment_sql = "INSERT INTO payments (order_id, user_id, amount, payment_method, payment_status, transaction_id) 
                    VALUES ('$order_id', '$user_id', '$amount', '$payment_method', '$status', '$transaction_id')";
                    
    if (!$conn->query($payment_sql)) {
        throw new Exception("Failed to record payment: " . $conn->error);
    }
    
    $conn->commit();
    echo json_encode(['status' => 'success', 'message' => 'Payment recorded successfully']);
    
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
} finally {
    $conn->close();
}