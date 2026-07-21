<?php
header('Content-Type: application/json');
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

$consultation_id = $_POST['consultation_id'];

try {
    $stmt = $conn->prepare("
        SELECT c.*, e.name as expert_name 
        FROM consultations c 
        LEFT JOIN experts e ON c.expert_id = e.expert_id 
        WHERE c.id = ?
    ");
    $stmt->bind_param("i", $consultation_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $consultation = $result->fetch_assoc();
        echo json_encode([
            'status' => 'success',
            'consultation' => $consultation
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Consultation not found'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}

$conn->close();
?>