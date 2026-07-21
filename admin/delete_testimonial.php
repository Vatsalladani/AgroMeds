<?php
header('Content-Type: application/json');
$host = 'localhost';
$db = 'agriculture';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if request is DELETE
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

try {
    // Get input data
    $input = json_decode(file_get_contents('php://input'), true) ?? $_GET;
    
    if (empty($input['id'])) {
        throw new Exception("Missing testimonial ID");
    }

    $id = intval($input['id']);

    // Delete testimonial
    $query = "DELETE FROM testimonials WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        throw new Exception("Testimonial not found or already deleted");
    }
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Testimonial deleted successfully'
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>