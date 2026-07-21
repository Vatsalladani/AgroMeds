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

// Get input data
$admin_id = isset($_POST['admin_id']) ? intval($_POST['admin_id']) : 0;
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : null;

// Validate input
if ($admin_id <= 0) {
    die(json_encode(['status' => 'error', 'message' => 'Invalid admin ID']));
}

if (empty($name) || empty($email)) {
    die(json_encode(['status' => 'error', 'message' => 'Name and email are required']));
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die(json_encode(['status' => 'error', 'message' => 'Invalid email format']));
}

if ($password !== null && strlen($password) < 8) {
    die(json_encode(['status' => 'error', 'message' => 'Password must be at least 8 characters']));
}

// Check if email is being changed to one that already exists
$stmt = $conn->prepare("SELECT admin_id FROM admin WHERE email = ? AND admin_id != ?");
$stmt->bind_param("si", $email, $admin_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    die(json_encode(['status' => 'error', 'message' => 'Email already exists']));
}

// Prepare update query
if ($password) {
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE admin SET name = ?, email = ?, password = ? WHERE admin_id = ?");
    $stmt->bind_param("sssi", $name, $email, $password_hash, $admin_id);
} else {
    $stmt = $conn->prepare("UPDATE admin SET name = ?, email = ? WHERE admin_id = ?");
    $stmt->bind_param("ssi", $name, $email, $admin_id);
}

if ($stmt->execute()) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Admin updated successfully'
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to update admin',
        'error' => $conn->error
    ]);
}

$stmt->close();
$conn->close();
?>