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

// Get parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = isset($_GET['limit']) ? max(1, intval($_GET['limit'])) : 5;
$offset = ($page - 1) * $limit;

// Base query
$query = "SELECT co.*, o.customer_name, o.total_amount as amount 
          FROM cancel_orders co
          JOIN orders o ON co.order_id = o.order_id";

// Add search conditions if provided
if (!empty($search)) {
    $search = $conn->real_escape_string($search);
    $query .= " WHERE (co.order_id LIKE '%$search%' OR 
                      o.customer_name LIKE '%$search%' OR 
                      co.reason LIKE '%$search%' OR 
                      co.refund_preference LIKE '%$search%')";
}

// Count total records
$countQuery = "SELECT COUNT(*) as total FROM ($query) as counted";
$countResult = $conn->query($countQuery);
$total = $countResult->fetch_assoc()['total'];

// Add pagination
$query .= " ORDER BY co.cancellation_date DESC LIMIT $limit OFFSET $offset";

// Execute query
$result = $conn->query($query);

if ($result) {
    $cancelOrders = [];
    while ($row = $result->fetch_assoc()) {
        $cancelOrders[] = $row;
    }
    
    echo json_encode([
        'status' => 'success',
        'data' => $cancelOrders,
        'total' => $total
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch cancel orders'
    ]);
}

$conn->close();
?>