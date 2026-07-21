<?php
session_start();

// Redirect if the admin is not logged in
if (!isset($_SESSION['admin_logged_in'])) {
    echo json_encode(['status' => 'error', 'message' => 'Admin not logged in.']);
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "agriculture";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
    exit();
}

// Get search term, page, and limit from the request
$search = isset($_GET['search']) ? $_GET['search'] : '';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 5;
$offset = ($page - 1) * $limit;

// Build the SQL query
$sql = "SELECT 
            product_id, 
            product_name, 
            description, 
            price, 
            quantity, 
            category_id, 
            image_url 
        FROM products";

$countSql = "SELECT COUNT(*) as total FROM products";

if (!empty($search)) {
    $search = "%$search%";
    $sql .= " WHERE product_name LIKE ? OR description LIKE ?";
    $countSql .= " WHERE product_name LIKE ? OR description LIKE ?";
}

$sql .= " LIMIT ? OFFSET ?";

// Prepare and execute the query
$stmt = $conn->prepare($sql);
$countStmt = $conn->prepare($countSql);

if (!empty($search)) {
    $stmt->bind_param("ssii", $search, $search, $limit, $offset);
    $countStmt->bind_param("ss", $search, $search);
} else {
    $stmt->bind_param("ii", $limit, $offset);
}

$stmt->execute();
$result = $stmt->get_result();
$products = [];

while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

$countStmt->execute();
$totalResult = $countStmt->get_result();
$total = $totalResult->fetch_assoc()['total'];

$stmt->close();
$countStmt->close();
$conn->close();

// Return JSON response
echo json_encode([
    'status' => 'success',
    'data' => $products,
    'total' => $total
]);
?>