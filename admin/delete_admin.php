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

// Prevent admin from deleting themselves
if ($admin_id == $_SESSION['admin_id']) {
    die(json_encode(['status' => 'error', 'message' => 'You cannot delete your own account']));
}

// Prepare and execute delete query
$stmt = $conn->prepare("DELETE FROM admin WHERE admin_id = ?");
$stmt->bind_param("i", $admin_id);

if ($stmt->execute()) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Admin deleted successfully'
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to delete admin',
        'error' => $conn->error
    ]);
}

$stmt->close();
$conn->close();
?>