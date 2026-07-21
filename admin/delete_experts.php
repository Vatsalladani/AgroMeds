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

// Get POST data
$expertId = $_POST['expert_id'];

// Delete expert
$sql = "DELETE FROM experts WHERE expert_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $expertId);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Expert deleted successfully']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to delete expert']);
}

$stmt->close();
$conn->close();
?>