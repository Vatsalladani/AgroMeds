<?php
$host = 'localhost';
$db = 'agriculture';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if client accepts JSON
$acceptsJson = strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if ($acceptsJson) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    } else {
        header('Content-Type: text/plain');
        echo "Error: Invalid request method";
    }
    exit;
}

// Get input data
$input = file_get_contents('php://input');
if (strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
    $data = json_decode($input, true);
} else {
    // Handle form-data or x-www-form-urlencoded
    parse_str($input, $data);
}

try {
    $stmt = $conn->prepare("UPDATE testimonials SET 
                          expert_id = ?, 
                          client_name = ?, 
                          profession = ?, 
                          city = ?, 
                          testimonial = ?, 
                          rating = ? 
                          WHERE id = ?");
    
    $stmt->bind_param("issssii", 
        $data['expert_id'],
        $data['client_name'],
        $data['profession'],
        $data['city'],
        $data['testimonial'],
        $data['rating'],
        $data['id']
    );
    
    if ($stmt->execute()) {
        if ($acceptsJson) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'message' => 'Testimonial updated successfully']);
        } else {
            header('Content-Type: text/plain');
            echo "Success: Testimonial updated successfully";
        }
    } else {
        if ($acceptsJson) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Failed to update testimonial']);
        } else {
            header('Content-Type: text/plain');
            echo "Error: Failed to update testimonial";
        }
    }
} catch (Exception $e) {
    if ($acceptsJson) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
    } else {
        header('Content-Type: text/plain');
        echo "Error: " . $e->getMessage();
    }
}
?>