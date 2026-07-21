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

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

try {
    // Get input data
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    
    // Validate required fields
    $required = ['expert_id', 'client_name', 'profession', 'city', 'rating', 'testimonial'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    // Prepare data
    $expert_id = intval($input['expert_id']);
    $client_name = $conn->real_escape_string($input['client_name']);
    $profession = $conn->real_escape_string($input['profession']);
    $city = $conn->real_escape_string($input['city']);
    $client_image = isset($input['client_image']) ? $conn->real_escape_string($input['client_image']) : null;
    $rating = intval($input['rating']);
    $testimonial = $conn->real_escape_string($input['testimonial']);

    // Insert testimonial
    $query = "INSERT INTO testimonials (expert_id, client_name, profession, city, client_image, rating, testimonial)
              VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("issssis", $expert_id, $client_name, $profession, $city, $client_image, $rating, $testimonial);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        throw new Exception("Failed to add testimonial");
    }

    $newId = $stmt->insert_id;
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Testimonial added successfully',
        'id' => $newId
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>