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
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';
$role = 'admin'; // Default role

// Validate input
if (empty($name) || empty($email) || empty($password)) {
    die(json_encode(['status' => 'error', 'message' => 'All fields are required']));
}

if (strlen($password) < 8) {
    die(json_encode(['status' => 'error', 'message' => 'Password must be at least 8 characters']));
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die(json_encode(['status' => 'error', 'message' => 'Invalid email format']));
}

// Check if email already exists
$stmt = $conn->prepare("SELECT admin_id FROM admin WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    die(json_encode(['status' => 'error', 'message' => 'Email already exists']));
}

// Hash password
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Insert new admin
$stmt = $conn->prepare("INSERT INTO admin (name, email, password, role) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $name, $email, $password_hash, $role);

if ($stmt->execute()) {
    $admin_id = $stmt->insert_id;
    echo json_encode([
        'status' => 'success',
        'message' => 'Admin added successfully',
        'admin_id' => $admin_id
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to add admin',
        'error' => $conn->error
    ]);
}

$stmt->close();
$conn->close();
?>