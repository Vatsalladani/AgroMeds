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

    // Begin transaction
    $conn->begin_transaction();

    try {
        // Delete order items
        $stmt = $conn->prepare("DELETE FROM order_items WHERE order_id = ?");
        $stmt->bind_param('i', $orderId);
        $stmt->execute();
        $stmt->close();

        // Delete order
        $stmt = $conn->prepare("DELETE FROM orders WHERE order_id = ?");
        $stmt->bind_param('i', $orderId);
        $stmt->execute();
        
        if ($stmt->affected_rows === 0) {
            throw new Exception('Order not found');
        }
        
        $stmt->close();

        // Commit transaction
        $conn->commit();

        $response = [
            'status' => 'success',
            'message' => 'Order deleted successfully'
        ];
        
        echo json_encode($response);

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        throw $e;
    }

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