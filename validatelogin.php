<?php
session_start();

// Database connection
$conn = new mysqli('localhost', 'root', '', 'agriculture');
if ($conn->connect_error) {
    header('Content-Type: text/plain');
    echo "Connection failed: " . $conn->connect_error;
    exit();
}

// Get input values
$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'] ?? null;
$password = $data['password'] ?? null;

if (!$email || !$password) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Email and password are required']);
    exit();
}

// Prepared statement to check credentials
$stmt = $conn->prepare("
SELECT user_id, password
FROM users
WHERE email = ?
");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    if (password_verify($password, $row['password'])) {
        // Update logged_in status to 0
        $updateStmt = $conn->prepare("UPDATE users SET logged_in = 1 WHERE user_id = ?");
        $updateStmt->bind_param("i", $row['user_id']);
        $updateStmt->execute();

        // Set session variables
        $_SESSION['user_id'] = $row['user_id'];

        // Return success response
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'redirect' => 'home.php']);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Invalid password']);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode([
    'status'=>'error',
    'message'=>'Invalid email or password'
]);
}

$stmt->close();
$conn->close();
?>
