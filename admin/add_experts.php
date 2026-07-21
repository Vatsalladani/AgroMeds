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
    
    // Validate input
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON input');
    }
    
    $requiredFields = ['name', 'specialization', 'description', 'image_path'];
    foreach ($requiredFields as $field) {
        if (empty($data[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }
    
    // Validate image path format
    if (!preg_match('/^\\\\Farming_meds\/Uploads\/Experts\/[a-zA-Z0-9_\-\.]+$/', $data['image_path'])) {
        throw new Exception('Invalid image path format');
    }
    
    // Set default values for optional fields
    $email = $data['email'] ?? '';
    $contact_no = $data['contact_no'] ?? '';
    
    // Prepare SQL statement
    $stmt = $conn->prepare("INSERT INTO experts 
        (name, Experts_email, Contact_no, specialization, description, image_url) 
        VALUES (?, ?, ?, ?, ?, ?)");
    
    if (!$stmt) {
        throw new Exception('Database preparation failed: ' . $conn->error);
    }
    
    $stmt->bind_param("ssssss", 
        $data['name'],
        $email,
        $contact_no,
        $data['specialization'],
        $data['description'],
        $data['image_path']
    );
    
    if ($stmt->execute()) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Expert added successfully',
            'expert_id' => $stmt->insert_id
        ]);
    } else {
        throw new Exception('Database execution failed: ' . $stmt->error);
    }
    
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