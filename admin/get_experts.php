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

try {
    // Get expert ID from POST data
    $expert_id = $_POST['expert_id'] ?? null;
    
    if (!$expert_id) {
        throw new Exception('Expert ID is required');
    }

    // Prepare and execute query
    $stmt = $conn->prepare("SELECT * FROM experts WHERE expert_id = ?");
    $stmt->bind_param("i", $expert_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Expert not found');
    }

    $expert = $result->fetch_assoc();
    
    // Fix image URL if it starts with backslashes
    if (isset($expert['image_url']) && strpos($expert['image_url'], '\\\\') === 0) {
        $expert['image_url'] = str_replace('\\\\', '/', $expert['image_url']);
    }

    echo json_encode([
        'status' => 'success',
        'data' => $expert
    ]);
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>