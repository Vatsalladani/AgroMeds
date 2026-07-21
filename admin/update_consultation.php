<?php
header('Content-Type: application/json');
require_once 'send_email.php'; // For email notifications
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "agriculture";

// Create a new MySQLi connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check for connection errors
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]));
}


$consultation_id = $_POST['consultation_id'];
$status = $_POST['status'];
$expert_id = $_POST['expert_id'] ?: null;
$admin_notes = $_POST['admin_notes'];
$send_email = $_POST['send_email'];
$user_email = $_POST['user_email'];
$user_name = $_POST['user_name'];

try {
    // Update consultation
    $stmt = $conn->prepare("
        UPDATE consultations 
        SET status = ?, expert_id = ?, admin_notes = ?, updated_at = NOW() 
        WHERE id = ?
    ");
    $stmt->bind_param("sisi", $status, $expert_id, $admin_notes, $consultation_id);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        // Send email notification if requested
        if ($send_email) {
            $subject = "Your Consultation Status Update";
            $message = "
                <h2>Hello $user_name,</h2>
                <p>Your consultation request has been updated:</p>
                <p><strong>Status:</strong> " . strtoupper($status) . "</p>
                " . ($admin_notes ? "<p><strong>Admin Notes:</strong> $admin_notes</p>" : "") . "
                <p>Thank you for using our service.</p>
            ";
            
            sendEmail($user_email, $subject, $message);
        }
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Consultation updated successfully'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'No changes made to the consultation'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}

$conn->close();
?>