<?php
session_start();
header('Content-Type: application/json'); // Return JSON response

if (isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_dashboard.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$database = "agriculture";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed!']);
    exit();
}

// Handle AJAX request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check if email exists
    $stmt = $conn->prepare("SELECT admin_id, name, password FROM admin WHERE email = ?");
    if (!$stmt) {
        echo json_encode(['status' => 'error', 'message' => 'Error preparing statement.']);
        exit();
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 1) {
        $stmt->bind_result($admin_id, $name, $hashed_password);
        $stmt->fetch();
        $stmt->close();

        // Verify password
        if (password_verify($password, $hashed_password)) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin_id;
            $_SESSION['admin_name'] = $name;
            
            echo json_encode(['status' => 'success', 'message' => 'Login successful! Redirecting...']);
            exit();
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid email or password.']);
            exit();
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid email or password.']);
        exit();
    } 
}
$conn->close();
?>



