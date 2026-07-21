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
    
    if (!isset($data['order_id'])) {
        throw new Exception('Order ID is required');
    }

    $orderId = $conn->real_escape_string($data['order_id']);

    // Fetch order details
    $stmt = $conn->prepare("SELECT * FROM orders WHERE order_id = ?");
    $stmt->bind_param('i', $orderId);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$order) {
        throw new Exception('Order not found');
    }

    // Fetch order items
    $stmt = $conn->prepare("
        SELECT oi.*, p.product_name 
        FROM order_items oi
        JOIN products p ON oi.product_id = p.product_id
        WHERE oi.order_id = ?
    ");
    $stmt->bind_param('i', $orderId);
    $stmt->execute();
    $orderItems = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $response = [
        'status' => 'success',
        'order' => $order,
        'order_items' => $orderItems
    ];
    
    echo json_encode($response);

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