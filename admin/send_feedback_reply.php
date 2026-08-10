<?php
session_start();

// Redirect if the admin is not logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: adminlogin.php");
    exit();
}

require 'vendor/autoload.php'; // Include PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "agriculture"; // Adjust the database name as needed

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get form data
$feedback_id = isset($_POST['feedback_id']) ? intval($_POST['feedback_id']) : 0;
$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
$reply_message = isset($_POST['reply_message']) ? $_POST['reply_message'] : '';

if ($feedback_id === 0 || $user_id === 0 || empty($reply_message)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input.']);
    exit();
}

// Fetch user email
$sql = "SELECT email FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'User not found.']);
    exit();
}

$user = $result->fetch_assoc();
$user_email = $user['email'];

// Send email using PHPMailer
$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com'; // Replace with your SMTP server
    $mail->SMTPAuth = true;
 $mail->Username   = 'agromeds.official.1@gmail.com'; // Your Gmail address
        $mail->Password   = 'YOUR_GMAIL_APP_PASSWORD'; // Your Gmail App Password
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    // Recipients
    $mail->setFrom('Agromeds.official.1@gmail.com', 'Admin');
    $mail->addAddress($user_email);

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Reply to Your Feedback';
    $mail->Body = "<p>Thank you for your feedback. Here is our reply:</p><p>$reply_message</p>";

    $mail->send();
    echo json_encode(['status' => 'success', 'message' => 'Reply sent successfully.']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to send reply.']);
}

$stmt->close();
$conn->close();
?>