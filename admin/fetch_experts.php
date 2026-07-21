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
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 4;
$offset = ($page - 1) * $limit;

// Fetch experts with pagination
$sql = "SELECT * FROM experts WHERE name LIKE ? OR specialization LIKE ? LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$searchTerm = "%$search%";
$stmt->bind_param("ssii", $searchTerm, $searchTerm, $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();

$experts = [];
while ($row = $result->fetch_assoc()) {
    $experts[] = $row;
}

// Get total number of experts for pagination
$totalSql = "SELECT COUNT(*) as total FROM experts WHERE name LIKE ? OR specialization LIKE ?";
$totalStmt = $conn->prepare($totalSql);
$totalStmt->bind_param("ss", $searchTerm, $searchTerm);
$totalStmt->execute();
$totalResult = $totalStmt->get_result();
$totalExperts = $totalResult->fetch_assoc()['total'];

echo json_encode([
    'status' => 'success',
    'data' => $experts,
    'total' => $totalExperts
]);

$stmt->close();
$totalStmt->close();
$conn->close();
?>