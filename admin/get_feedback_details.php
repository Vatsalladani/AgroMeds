<?php
error_reporting(E_ALL); // Report all PHP errors
ini_set('display_errors', 1); // Display errors to the browser
ini_set('display_startup_errors', 1); // Display startup errors

// Function to return a JSON response
function jsonResponse($status, $message, $data = []) {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => $status,
        'message' => $message,
        'data' => $data
    ]);
    exit();
}

session_start();

// Redirect if the admin is not logged in
if (!isset($_SESSION['admin_logged_in'])) {
    jsonResponse('error', 'Admin not logged in.');
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "agriculture"; // Adjust the database name as needed

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    jsonResponse('error', 'Database connection failed: ' . $conn->connect_error);
}

// Get feedback ID from the request
$feedback_id = isset($_POST['feedback_id']) ? intval($_POST['feedback_id']) : 0;

if ($feedback_id === 0) {
    jsonResponse('error', 'Invalid feedback ID.');
}

// Fetch feedback details
$sql = "
    SELECT 
        f.feedback_id, 
        f.user_id, 
        f.product_id, 
        f.rating, 
        f.comment, 
        f.created_at,
        u.full_name,  
        p.product_name
    FROM feedback f
    JOIN users u ON f.user_id = u.user_id
    JOIN products p ON f.product_id = p.product_id
    WHERE f.feedback_id = ?
";

try {
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        jsonResponse('error', 'Failed to prepare the SQL statement: ' . $conn->error);
    }

    $stmt->bind_param("i", $feedback_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        jsonResponse('error', 'Feedback not found.');
    }

    // Fetch the feedback details
    $feedback = $result->fetch_assoc();

    // Debugging: Log the fetched feedback data
    error_log(print_r($feedback, true));

    // Close the statement and connection
    $stmt->close();
    $conn->close();

    // Return the feedback details as JSON
    jsonResponse('success', 'Feedback details fetched successfully.', [
        'feedback_id' => $feedback['feedback_id'],
        'user_id' => $feedback['user_id'],
        'full_name' => $feedback['full_name'], // Ensure this matches the SQL query
        'product_id' => $feedback['product_id'],
        'product_name' => $feedback['product_name'],
        'rating' => $feedback['rating'],
        'comment' => $feedback['comment'],
        'created_at' => $feedback['created_at']
    ]);
} catch (mysqli_sql_exception $e) {
    jsonResponse('error', 'Database error: ' . $e->getMessage());
}
?>