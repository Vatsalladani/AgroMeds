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
    $search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 5;
    $offset = ($page - 1) * $limit;

    // Count total orders
    $countQuery = "SELECT COUNT(*) as total FROM orders";
    $whereClause = '';
    $params = [];
    
    if (!empty($search)) {
        $whereClause = " WHERE customer_name LIKE ? OR email LIKE ? OR order_id LIKE ?";
        $searchParam = "%$search%";
        $params = array_fill(0, 3, $searchParam);
    }
    
    $countStmt = $conn->prepare($countQuery . $whereClause);
    if (!empty($params)) {
        $types = str_repeat('s', count($params));
        $countStmt->bind_param($types, ...$params);
    }
    $countStmt->execute();
    $countResult = $countStmt->get_result();
    $total = $countResult->fetch_assoc()['total'];
    $countStmt->close();

    // Fetch orders
    $query = "SELECT * FROM orders" . $whereClause . " ORDER BY order_date DESC LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($query);
    
    if (!empty($params)) {
        $types = str_repeat('s', count($params)) . 'ii';
        $params[] = $limit;
        $params[] = $offset;
        $stmt->bind_param($types, ...$params);
    } else {
        $stmt->bind_param('ii', $limit, $offset);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $orders = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $response = [
        'status' => 'success',
        'data' => $orders,
        'total' => $total
    ];
    
    echo json_encode($response);

} catch (Exception $e) {
    $response = [
        'status' => 'error',
        'message' => 'Error: ' . $e->getMessage()
    ];
    echo json_encode($response);
} finally {
    $conn->close();
}
?>