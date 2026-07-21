<?php
// Database connection parameters (UPDATE THESE WITH YOUR ACTUAL CREDENTIALS)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "agriculture";

// Create a new MySQLi connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check for connection errors
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]));
}

$contact_id = isset($_POST['contact_id']) ? intval($_POST['contact_id']) : 0;

if ($contact_id > 0) {
    $query = "DELETE FROM contactus WHERE contact_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $contact_id);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Contact deleted successfully."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error deleting contact: " . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "Invalid contact ID."]);
}

$conn->close();
?>
