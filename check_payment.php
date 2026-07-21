<?php
session_start();
header('Content-Type: application/json');

// Check if order ID is provided
if (!isset($_GET['order_id'])) {
    http_response_code(400);
    die(json_encode(['status' => 'error', 'message' => 'Order ID is required']));
}

// Database connection
$host = 'localhost';
$dbname = 'agriculture';
$username = 'root';
$password = '';
$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode(['status' => 'error', 'message' => 'Database connection failed']));
}

$order_id = $_GET['order_id'];
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Use prepared statement for security
$sql = "SELECT o.payment_status, o.status as order_status, 
               p.payment_method, p.transaction_id, p.payment_status as payment_table_status
        FROM orders o
        LEFT JOIN payments p ON o.order_id = p.order_id
        WHERE o.order_id = ?";
        
if ($user_id) {
    $sql .= " AND o.user_id = ?";
}

$stmt = $conn->prepare($sql);

if ($user_id) {
    $stmt->bind_param("ii", $order_id, $user_id);
} else {
    $stmt->bind_param("i", $order_id);
}

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'Order not found or access denied']);
} else {
    $order = $result->fetch_assoc();
    
    // Determine which payment status to use (prefer payments table if available)
    $payment_status = $order['payment_table_status'] ?? $order['payment_status'];
    
    $response = [
        'status' => 'success',
        'payment_status' => $payment_status,
        'order_status' => $order['order_status'],
        'payment_method' => $order['payment_method'] ?? null,
        'transaction_id' => $order['transaction_id'] ?? null
    ];
    
    // Add appropriate message based on status
    switch ($payment_status) {
        case 'completed':
            $response['message'] = 'Payment completed successfully';
            break;
        case 'failed':
            $response['message'] = 'Payment failed. Please try another method';
            break;
        case 'pending':
            $response['message'] = 'Payment is still pending';
            break;
        default:
            $response['message'] = 'Payment status unknown';
    }
    
    echo json_encode($response);
}

$stmt->close();
$conn->close();
?>