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

// Get admin ID
$admin_id = isset($_POST['admin_id']) ? intval($_POST['admin_id']) : 0;

if ($admin_id <= 0) {
    die(json_encode(['status' => 'error', 'message' => 'Invalid admin ID']));
}

// Prepare and execute query
$stmt = $conn->prepare("SELECT admin_id, name, email, role, created_at FROM admin WHERE admin_id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $admin = $result->fetch_assoc();
    // Format the created_at date
    $admin['created_at'] = date('Y-m-d H:i:s', strtotime($admin['created_at']));
    
    echo json_encode([
        'status' => 'success',
        'data' => $admin
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Admin not found'
    ]);
}

$stmt->close();
$conn->close();
?>