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
    $query = "SELECT contact_id, first_name, last_name, email, subject, query FROM contactus WHERE contact_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $contact_id);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $data = $result->fetch_assoc();
            // Ensure data is properly encoded for both plain text and JSON
            $data['query'] = htmlspecialchars($data['query']); // For plain text
            echo json_encode(["status" => "success", "data" => $data]);
        } else {
            echo json_encode(["status" => "error", "message" => "Contact not found."]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Error fetching contact: " . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "Invalid contact ID."]);
}

$conn->close();
?>
