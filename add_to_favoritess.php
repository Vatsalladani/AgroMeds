<?php
session_start();
$host = 'localhost';
$db = 'agriculture';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to add favorites']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $expertId = filter_input(INPUT_POST, 'expert_id', FILTER_VALIDATE_INT);
    $action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_STRING);
    $userId = $_SESSION['user_id'];

    if (!$expertId) {
        echo json_encode(['success' => false, 'message' => 'Invalid expert']);
        exit;
    }

    if ($action === 'add') {
        // Check if already favorited
        $stmt = $conn->prepare("SELECT * FROM user_favorites WHERE user_id = ? AND expert_id = ?");
        $stmt->bind_param("ii", $userId, $expertId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            // Add to favorites
            $insert = $conn->prepare("INSERT INTO user_favorites (user_id, expert_id, created_at) VALUES (?, ?, NOW())");
            $insert->bind_param("ii", $userId, $expertId);
            
            if ($insert->execute()) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Database error']);
            }
        } else {
            echo json_encode(['success' => true, 'message' => 'Already in favorites']);
        }
    } elseif ($action === 'remove') {
        // Remove from favorites
        $delete = $conn->prepare("DELETE FROM user_favorites WHERE user_id = ? AND expert_id = ?");
        $delete->bind_param("ii", $userId, $expertId);
        $delete->execute();
        echo json_encode(['success' => true]);
    }
}
?>