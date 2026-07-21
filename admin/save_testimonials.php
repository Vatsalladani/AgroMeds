<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "agriculture"; // Adjust the database name as needed

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit();
}

if (!isset($_SESSION['admin_logged_in'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

$data = [
    'id' => isset($_POST['id']) ? intval($_POST['id']) : null,
    'expert_id' => isset($_POST['expert_id']) ? intval($_POST['expert_id']) : null,
    'client_name' => isset($_POST['client_name']) ? trim($_POST['client_name']) : '',
    'profession' => isset($_POST['profession']) ? trim($_POST['profession']) : '',
    'city' => isset($_POST['city']) ? trim($_POST['city']) : '',
    'rating' => isset($_POST['rating']) ? intval($_POST['rating']) : 5,
    'testimonial' => isset($_POST['testimonial']) ? trim($_POST['testimonial']) : ''
];

// Validate required fields
if (empty($data['expert_id']) || empty($data['client_name']) || 
    empty($data['profession']) || empty($data['city']) || 
    empty($data['testimonial'])) {
    echo json_encode(['status' => 'error', 'message' => 'All fields are required']);
    exit();
}

try {
    if ($data['id']) {
        // Update existing testimonial
        $stmt = $conn->prepare("UPDATE testimonials SET 
            expert_id = ?, client_name = ?, profession = ?, city = ?, 
            rating = ?, testimonial = ?, updated_at = NOW() 
            WHERE id = ?");
        $stmt->bind_param("isssisi", 
            $data['expert_id'], $data['client_name'], $data['profession'],
            $data['city'], $data['rating'], $data['testimonial'], $data['id']);
    } else {
        // Insert new testimonial
        $stmt = $conn->prepare("INSERT INTO testimonials 
            (expert_id, client_name, profession, city, rating, testimonial, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("isssis", 
            $data['expert_id'], $data['client_name'], $data['profession'],
            $data['city'], $data['rating'], $data['testimonial']);
    }
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $stmt->error]);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>