<?php
// Include Composer's autoloader
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Database connection parameters (UPDATE THESE WITH YOUR ACTUAL CREDENTIALS)
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

$contact_id = isset($_POST['contact_id']) ? intval($_POST['contact_id']) : 0;
$first_name = isset($_POST['first_name']) ? $_POST['first_name'] : '';
$last_name = isset($_POST['last_name']) ? $_POST['last_name'] : '';
$email = isset($_POST['email']) ? $_POST['email'] : '';
$subject = isset($_POST['subject']) ? $_POST['subject'] : '';
$reply_message = isset($_POST['reply_message']) ? $_POST['reply_message'] : '';

if ($contact_id > 0 && !empty($email) && !empty($reply_message)) {
    $mail = new PHPMailer(true);

    try {
        // SMTP configuration
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
 $mail->Username   = 'agromeds.official.1@gmail.com'; // Your Gmail address
        $mail->Password   = 'YOUR_GMAIL_APP_PASSWORD'; // Your Gmail App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Use TLS
        $mail->Port       = 587; // TCP port to connect to

        // Sender and recipient settings
        $mail->setFrom('agromeds.official.1@gmail.com', 'AgroMeds');
        $mail->addAddress($email, $first_name . ' ' . $last_name);

        // Email content settings
        $mail->isHTML(true);
        $mail->Subject = 'Reply: ' . $subject;
        $emailBody = "
            <h2>Reply from AgroMeds</h2>
            <p>Dear {$first_name} {$last_name},</p>
            <p>{$reply_message}</p>
            <p>Best Regards,<br>AgroMeds Team</p>
        ";
        $mail->Body    = $emailBody;
        $mail->AltBody = "Dear {$first_name} {$last_name},\n\n{$reply_message}\n\nBest Regards,\nAgroMeds Team";

        $mail->send();

        echo json_encode(["status" => "success", "message" => "Reply email sent successfully!"]);
    } catch (Exception $e) {
        error_log("Mailer Error: {$mail->ErrorInfo}");
        echo json_encode(["status" => "error", "message" => "Error sending the reply email."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid data."]);
}

$conn->close();
?>
