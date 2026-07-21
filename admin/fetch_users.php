<?php
// Database connection
$host = 'localhost';
$db = 'agriculture';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;
$offset = ($page - 1) * $limit;

// Fetch users with pagination
$sql = "SELECT * FROM users WHERE full_name LIKE ? OR email LIKE ? OR phone LIKE ? LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$searchTerm = "%$search%";
$stmt->bind_param("sssii", $searchTerm, $searchTerm, $searchTerm, $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

// Get total number of users for pagination
$totalSql = "SELECT COUNT(*) as total FROM users WHERE full_name LIKE ? OR email LIKE ? OR phone LIKE ?";
$totalStmt = $conn->prepare($totalSql);
$totalStmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
$totalStmt->execute();
$totalResult = $totalStmt->get_result();
$totalUsers = $totalResult->fetch_assoc()['total'];

echo json_encode([
    'status' => 'success',
    'data' => $users,
    'total' => $totalUsers
]);

$stmt->close();
$totalStmt->close();
$conn->close();
?>