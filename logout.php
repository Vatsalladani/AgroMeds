<?php
session_start();

// Database connection
$conn = new mysqli('localhost', 'root', '', 'agriculture');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Update logged_in status to 1
$updateSql = "UPDATE users SET logged_in = 0 WHERE user_id = " . $_SESSION['user_id'];
$conn->query($updateSql);

// Destroy session
session_destroy();

// Redirect to login page
header("Location: login.php");
exit();

$conn->close();
?>
