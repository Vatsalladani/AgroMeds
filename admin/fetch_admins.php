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

// Verify admin session
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    die(json_encode(['status' => 'error', 'message' => 'Unauthorized access']));
}

// Get parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = isset($_GET['limit']) ? max(1, intval($_GET['limit'])) : 5;
$offset = ($page - 1) * $limit;

// Base query - select admin data
$query = "SELECT admin_id, name, email, role, created_at FROM admin";

// Add search conditions if provided
if (!empty($search)) {
    $search = $conn->real_escape_string($search);
    $query .= " WHERE (name LIKE '%$search%' OR 
                      email LIKE '%$search%' OR 
                      role LIKE '%$search%')";
}

// Count total records
$countQuery = "SELECT COUNT(*) as total FROM admin";
if (!empty($search)) {
    $countQuery .= " WHERE (name LIKE '%$search%' OR 
                           email LIKE '%$search%' OR 
                           role LIKE '%$search%')";
}
$countResult = $conn->query($countQuery);
$total = $countResult->fetch_assoc()['total'];

// Add pagination
$query .= " ORDER BY created_at DESC LIMIT $limit OFFSET $offset";

// Execute query
$result = $conn->query($query);

if ($result) {
    $admins = [];
    while ($row = $result->fetch_assoc()) {
        // Format the created_at date if needed
        $row['created_at'] = date('Y-m-d H:i:s', strtotime($row['created_at']));
        $admins[] = $row;
    }
    
    echo json_encode([
        'status' => 'success',
        'data' => $admins,
        'total' => $total,
        'page' => $page,
        'limit' => $limit
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch admin data',
        'error' => $conn->error
    ]);
}

$conn->close();
?>