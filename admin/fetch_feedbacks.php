<?php
session_start();

// Redirect if the admin is not logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: adminlogin.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "agriculture"; // Adjust the database name as needed

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get search term, page, and limit from the request
$search = isset($_GET['search']) ? $_GET['search'] : '';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
$offset = ($page - 1) * $limit;

// Build the SQL query
$sql = "SELECT feedback_id, user_id, product_id, rating, comment, created_at FROM feedback";
$countSql = "SELECT COUNT(*) as total FROM feedback";

if (!empty($search)) {
    $search = "%$search%";
    $sql .= " WHERE user_id LIKE ? OR product_id LIKE ? OR comment LIKE ?";
    $countSql .= " WHERE user_id LIKE ? OR product_id LIKE ? OR comment LIKE ?";
}

$sql .= " LIMIT ? OFFSET ?";

// Prepare and execute the query
$stmt = $conn->prepare($sql);
$countStmt = $conn->prepare($countSql);

if (!empty($search)) {
    $stmt->bind_param("sssii", $search, $search, $search, $limit, $offset);
    $countStmt->bind_param("sss", $search, $search, $search);
} else {
    $stmt->bind_param("ii", $limit, $offset);
}

$stmt->execute();
$result = $stmt->get_result();
$feedbacks = [];

while ($row = $result->fetch_assoc()) {
    $feedbacks[] = $row;
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
    'data' => $feedbacks,
    'total' => $total
]);
?>