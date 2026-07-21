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

// Get feedback ID
$feedback_id = isset($_POST['feedback_id']) ? intval($_POST['feedback_id']) : 0;

if ($feedback_id === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid feedback ID.']);
    exit();
}

// Delete feedback
$sql = "DELETE FROM feedback WHERE feedback_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $feedback_id);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Feedback deleted successfully.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to delete feedback.']);
}

$stmt->close();
$conn->close();
?>