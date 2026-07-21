<?php
header('Content-Type: application/json');
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "agriculture";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
    exit();
}

try {
    $id = intval($_GET['id'] ?? 0);
    
    if ($id <= 0) {
        throw new Exception("Invalid testimonial ID");
    }

    $query = "SELECT t.*, e.name as expert_name 
              FROM testimonials t
              LEFT JOIN experts e ON t.expert_id = e.expert_id
              WHERE t.id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Testimonial not found");
    }

    $testimonial = $result->fetch_assoc();
    
    echo json_encode([
        'status' => 'success',
        'testimonial' => $testimonial
    ]);
} catch (Exception $e) {
    http_response_code(404);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>