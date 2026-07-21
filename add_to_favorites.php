<?php
session_start();
header("Content-Type: application/json");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "User not logged in"]);
    exit;
}

// Check the Content-Type header
$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';

// Initialize variables
$id = null;
$type = null;

// Handle JSON input
if ($contentType === "application/json") {
    $data = json_decode(file_get_contents("php://input"), true);
    if (json_last_error() === JSON_ERROR_NONE) {
        $id = $data['id'];
        $type = $data['type'];
    } else {
        echo json_encode(["success" => false, "message" => "Invalid JSON input"]);
        exit;
    }
}
// Handle plain text input (e.g., form data)
else {
    $id = isset($_POST['id']) ? $_POST['id'] : null;
    $type = isset($_POST['type']) ? $_POST['type'] : null;
}

// Validate input
if (empty($id) || empty($type)) {
    echo json_encode(["success" => false, "message" => "Missing required fields"]);
    exit;
}

// Add to favorites logic (e.g., insert into database)
try {
    $pdo = new PDO("mysql:host=localhost;dbname=agriculture", "root", "");
    $stmt = $pdo->prepare("INSERT INTO favorites (user_id, item_id, item_type) VALUES (:user_id, :item_id, :item_type)");
    $stmt->execute([
        ":user_id" => $_SESSION['user_id'],
        ":item_id" => $id,
        ":item_type" => $type,
    ]);
    echo json_encode(["success" => true]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>