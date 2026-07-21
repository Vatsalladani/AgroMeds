<?php
header('Content-Type: application/json');

// Database connection
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
    // Get JSON input
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON input');
    }

    // Validate required fields
    $requiredFields = ['expert_id', 'name', 'specialization', 'description'];
    foreach ($requiredFields as $field) {
        if (empty($data[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    // Set default values
    $email = $data['email'] ?? '';
    $contact_no = $data['contact_no'] ?? '';
    $image_url = $data['image_path'] ?? $data['current_image'] ?? '';
    
    // Fix image path if it starts with backslashes
    if (strpos($image_url, '\\\\') === 0) {
        $image_url = str_replace('\\\\', '/', $image_url);
    }

    // Prepare and execute update
    $stmt = $conn->prepare("UPDATE experts SET 
        name = ?,
        Experts_email = ?,
        Contact_no = ?,
        specialization = ?,
        description = ?,
        image_url = ?
        WHERE expert_id = ?");
    
    if (!$stmt) {
        throw new Exception('Database preparation failed: ' . $conn->error);
    }
    
    $stmt->bind_param("ssssssi", 
        $data['name'],
        $email,
        $contact_no,
        $data['specialization'],
        $data['description'],
        $image_url,
        $data['expert_id']
    );
    
    if (!$stmt->execute()) {
        throw new Exception('Database execution failed: ' . $stmt->error);
    }

    $response = [
        'status' => 'success',
        'message' => $stmt->affected_rows > 0 
            ? 'Expert updated successfully' 
            : 'No changes detected',
        'expert_id' => $data['expert_id']
    ];
    
    echo json_encode($response);
    
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