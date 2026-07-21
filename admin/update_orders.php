<?php
header('Content-Type: application/json');

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "agriculture";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    $response = [
        'status' => 'error',
        'message' => 'Database connection failed: ' . $conn->connect_error
    ];
    echo json_encode($response);
    exit;
}

try {
    // Get input data (works with both POST and JSON input)
    $input = file_get_contents('php://input');
    $data = json_decode($input, true) ?: $_POST;
    
    if (!isset($data['order_id'], $data['status'], $data['payment_status'])) {
        throw new Exception('Missing required fields');
    }

    $orderId = $conn->real_escape_string($data['order_id']);
    $status = $conn->real_escape_string($data['status']);
    $paymentStatus = $conn->real_escape_string($data['payment_status']);

    $stmt = $conn->prepare("UPDATE orders SET status = ?, payment_status = ? WHERE order_id = ?");
    $stmt->bind_param('ssi', $status, $paymentStatus, $orderId);
    $stmt->execute();
    
    if ($stmt->affected_rows === 0) {
        throw new Exception('No changes made or order not found');
    }
    
    $response = [
        'status' => 'success',
        'message' => 'Order updated successfully'
    ];
    
    echo json_encode($response);
    $stmt->close();

} catch (Exception $e) {
    $response = [
        'status' => 'error',
        'message' => $e->getMessage()
    ];
    echo json_encode($response);
} finally {
    $conn->close();
}
?>