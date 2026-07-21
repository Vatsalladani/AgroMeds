<?php
header('Content-Type: application/json');

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "agriculture";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit;
}

try {
    $paymentId = isset($_POST['payment_id']) ? intval($_POST['payment_id']) : 0;
    
    if ($paymentId <= 0) {
        throw new Exception("Invalid payment ID");
    }

    // Get payment details
    $paymentQuery = "SELECT * FROM payments WHERE payment_id = ?";
    $stmt = $conn->prepare($paymentQuery);
    $stmt->bind_param("i", $paymentId);
    $stmt->execute();
    $paymentResult = $stmt->get_result();
    
    if ($paymentResult->num_rows === 0) {
        throw new Exception("Payment not found");
    }
    
    $payment = $paymentResult->fetch_assoc();
    
    // Get associated order details
    $orderQuery = "SELECT * FROM orders WHERE order_id = ?";
    $stmt = $conn->prepare($orderQuery);
    $stmt->bind_param("i", $payment['order_id']);
    $stmt->execute();
    $orderResult = $stmt->get_result();
    $order = $orderResult->num_rows > 0 ? $orderResult->fetch_assoc() : null;

    echo json_encode([
        'status' => 'success',
        'payment' => $payment,
        'order' => $order
    ]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
} finally {
    $conn->close();
}
?>