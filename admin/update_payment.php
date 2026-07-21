<?php
header('Content-Type: application/json');
session_start();

// Redirect if the admin is not logged in
if (!isset($_SESSION['admin_logged_in'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

require 'vendor/autoload.php'; // Include PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "agriculture";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit();
}

try {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true) ?: $_POST;
    
    if (!isset($data['payment_id'], $data['payment_status'])) {
        throw new Exception('Missing required fields');
    }

    $paymentId = intval($data['payment_id']);
    $paymentStatus = $conn->real_escape_string($data['payment_status']);
    $transactionId = isset($data['transaction_id']) ? $conn->real_escape_string($data['transaction_id']) : null;
    $updateOrder = isset($data['update_order']) ? boolval($data['update_order']) : false;
    $notifyUser = isset($data['notify_user']) ? boolval($data['notify_user']) : false;

    // Start transaction
    $conn->begin_transaction();

    try {
        // Update payment
        $stmt = $conn->prepare("UPDATE payments SET payment_status = ?, transaction_id = ?, updated_at = NOW() WHERE payment_id = ?");
        $stmt->bind_param('ssi', $paymentStatus, $transactionId, $paymentId);
        $stmt->execute();
        
        if ($stmt->affected_rows === 0) {
            throw new Exception('No changes made or payment not found');
        }
        
        // Get payment details for order update
        $paymentQuery = "SELECT order_id FROM payments WHERE payment_id = ?";
        $stmt = $conn->prepare($paymentQuery);
        $stmt->bind_param("i", $paymentId);
        $stmt->execute();
        $paymentResult = $stmt->get_result();
        $payment = $paymentResult->fetch_assoc();
        
        // Update associated order if requested
        if ($updateOrder && $payment) {
            $orderStmt = $conn->prepare("UPDATE orders SET payment_status = ?, transaction_id = ? WHERE order_id = ?");
            $orderStmt->bind_param('ssi', $paymentStatus, $transactionId, $payment['order_id']);
            $orderStmt->execute();
            
            // Get user details for notification
            $userQuery = "SELECT email, customer_name FROM orders WHERE order_id = ?";
            $stmt = $conn->prepare($userQuery);
            $stmt->bind_param("i", $payment['order_id']);
            $stmt->execute();
            $userResult = $stmt->get_result();
            $user = $userResult->fetch_assoc();
            
            // Send notification email if requested
            if ($notifyUser && $user) {
                $mail = new PHPMailer(true);

                try {
                    // Server settings
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'agromeds.official.1@gmail.com'; // Your Gmail
                    $mail->Password = 'REMOVED_GMAIL_APP_PASSWORD'; // Your App Password
                    $mail->SMTPSecure = 'tls';
                    $mail->Port = 587;

                    // Recipients
                    $mail->setFrom('agromeds.official.1@gmail.com', 'AgroMeds');
                    $mail->addAddress($user['email'], $user['customer_name']);

                    // Content
                    $mail->isHTML(true);
                    $mail->Subject = 'Payment Status Update';
                    
                    // Email body with styling
                    $mail->Body = "
                        <html>
                        <head>
                            <title>Payment Status Update</title>
                            <style>
                                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                                .header { background-color: #4e73df; color: white; padding: 15px; text-align: center; }
                                .content { padding: 20px; background-color: #f8f9fa; }
                                .footer { margin-top: 20px; text-align: center; font-size: 12px; color: #777; }
                                .status { font-weight: bold; }
                                .completed { color: #28a745; }
                                .pending { color: #ffc107; }
                                .failed { color: #dc3545; }
                            </style>
                        </head>
                        <body>
                            <div class='container'>
                                <div class='header'>
                                    <h2>Payment Status Update</h2>
                                </div>
                                <div class='content'>
                                    <p>Hello {$user['customer_name']},</p>
                                    <p>The status of your payment for Order #{$payment['order_id']} has been updated:</p>
                                    <p><strong>New Status:</strong> <span class='status {$paymentStatus}'>$paymentStatus</span></p>
                                    <p><strong>Transaction ID:</strong> " . ($transactionId ?: 'N/A') . "</p>
                                    <p>If you have any questions, please contact our support team.</p>
                                </div>
                                <div class='footer'>
                                    <p>© " . date('Y') . " AgroMeds. All rights reserved.</p>
                                </div>
                            </div>
                        </body>
                        </html>
                    ";

                    $mail->send();
                } catch (Exception $e) {
                    // Log the error but don't fail the entire operation
                    error_log("Mailer Error: " . $mail->ErrorInfo);
                }
            }
        }
        
        // Commit transaction
        $conn->commit();
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Payment updated successfully'
        ]);
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
} finally {
    $conn->close();
}
?>