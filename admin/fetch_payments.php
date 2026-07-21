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
    // Get parameters
    $search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 8;
    $offset = ($page - 1) * $limit;

    // Base query
    $query = "SELECT p.*, o.customer_name 
              FROM payments p
              LEFT JOIN orders o ON p.order_id = o.order_id
              WHERE 1=1";

    // Add search conditions
    if (!empty($search)) {
        $query .= " AND (p.payment_id LIKE '%$search%' 
                        OR p.order_id LIKE '%$search%'
                        OR p.transaction_id LIKE '%$search%'
                        OR o.customer_name LIKE '%$search%')";
    }

    // Count total records
    $countQuery = "SELECT COUNT(*) as total FROM ($query) as temp";
    $countResult = $conn->query($countQuery);
    $total = $countResult->fetch_assoc()['total'];

    // Add pagination
    $query .= " ORDER BY p.created_at DESC LIMIT $limit OFFSET $offset";

    $result = $conn->query($query);

    if ($result) {
        $payments = [];
        while ($row = $result->fetch_assoc()) {
            $payments[] = $row;
        }

        echo json_encode([
            'status' => 'success',
            'data' => $payments,
            'total' => $total
        ]);
    } else {
        throw new Exception("Failed to fetch payments");
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
} finally {
    $conn->close();
}
?>