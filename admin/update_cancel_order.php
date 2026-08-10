<?php
session_start();
require 'vendor/autoload.php';

// Database connection
$host = 'localhost';
$dbname = 'agriculture';
$username = 'root';
$password = '';
$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['status' => 'error', 'message' => 'Database connection failed']));
}

// Get input data
$cancellation_id = isset($_POST['cancellation_id']) ? intval($_POST['cancellation_id']) : 0;
$status = isset($_POST['status']) ? $conn->real_escape_string($_POST['status']) : '';
$rejection_reason = isset($_POST['rejection_reason']) ? $conn->real_escape_string($_POST['rejection_reason']) : null;
$refund_date = isset($_POST['refund_date']) ? $conn->real_escape_string($_POST['refund_date']) : null;

if ($cancellation_id <= 0 || empty($status)) {
    die(json_encode(['status' => 'error', 'message' => 'Invalid input data']));
}

// Start transaction
$conn->begin_transaction();

try {
    // Update cancel order status
    $updateQuery = "UPDATE cancel_orders SET status = ?";
    $params = [$status];
    $types = "s";
    
    if ($status === 'Rejected' && $rejection_reason) {
        $updateQuery .= ", admin_notes = ?";
        $params[] = $rejection_reason;
        $types .= "s";
    }
    
    $updateQuery .= " WHERE cancellation_id = ?";
    $params[] = $cancellation_id;
    $types .= "i";
    
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    
    if ($stmt->affected_rows === 0) {
        throw new Exception("No changes made to cancel order");
    }
    
    // Get cancel order details for email
    $cancelQuery = "SELECT co.*, o.customer_name, o.email, o.total_amount 
                    FROM cancel_orders co
                    JOIN orders o ON co.order_id = o.order_id
                    WHERE co.cancellation_id = $cancellation_id";
    $cancelResult = $conn->query($cancelQuery);
    $cancelOrder = $cancelResult->fetch_assoc();
    
    // Get order items for email
    $order_id = $cancelOrder['order_id'];
    $itemsQuery = "SELECT oi.*, p.product_name 
                   FROM order_items oi
                   JOIN products p ON oi.product_id = p.product_id
                   WHERE oi.order_id = $order_id";
    $itemsResult = $conn->query($itemsQuery);
    $orderItems = [];
    
    while ($item = $itemsResult->fetch_assoc()) {
        $orderItems[] = $item;
    }
    
    // Commit transaction
    $conn->commit();
    
    // Send email to customer
    if (in_array($status, ['Processed', 'Rejected'])) {
        sendCancellationStatusEmail($cancelOrder, $orderItems, $status, $rejection_reason, $refund_date);
    }
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Cancel order status updated successfully'
    ]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

$conn->close();

function sendCancellationStatusEmail($cancelOrder, $orderItems, $status, $rejection_reason = null, $refund_date = null) {
    $to = $cancelOrder['email'];
    $subject = "Your Order Cancellation Status - AgroMeds";
    
    // Build email body
    $body = "<h2>Order Cancellation Update</h2>";
    $body .= "<p>Dear " . htmlspecialchars($cancelOrder['customer_name']) . ",</p>";
    
    if ($status === 'Processed') {
        $body .= "<p>Your cancellation request for Order #" . $cancelOrder['order_id'] . " has been processed.</p>";
        $body .= "<h3>Refund Details:</h3>";
        $body .= "<p><strong>Refund Method:</strong> " . htmlspecialchars($cancelOrder['refund_preference']) . "</p>";
        $body .= "<p><strong>Amount to be Refunded:</strong> ₹" . number_format($cancelOrder['total_amount'], 2) . "</p>";
        
        if ($refund_date) {
            $formattedDate = date('F j, Y', strtotime($refund_date));
            $body .= "<p><strong>Expected Refund Date:</strong> " . $formattedDate . "</p>";
            $body .= "<p>Please note it may take 3-5 business days for the refund to reflect in your account after this date.</p>";
        }
        
        $body .= "<h3>Order Items:</h3>";
        $body .= "<table border='1' cellpadding='5' cellspacing='0' width='100%'>";
        $body .= "<tr><th>Product</th><th>Quantity</th><th>Price</th><th>Total</th></tr>";
        
        foreach ($orderItems as $item) {
            $body .= "<tr>";
            $body .= "<td>" . htmlspecialchars($item['product_name']) . "</td>";
            $body .= "<td>" . $item['quantity'] . "</td>";
            $body .= "<td>₹" . number_format($item['price'], 2) . "</td>";
            $body .= "<td>₹" . number_format($item['price'] * $item['quantity'], 2) . "</td>";
            $body .= "</tr>";
        }
        
        $body .= "</table>";
        $body .= "<p><strong>Total Amount Refunded:</strong> ₹" . number_format($cancelOrder['total_amount'], 2) . "</p>";
    } else {
        $body .= "<p>Your cancellation request for Order #" . $cancelOrder['order_id'] . " has been rejected.</p>";
        $body .= "<p><strong>Reason:</strong> " . htmlspecialchars($rejection_reason) . "</p>";
    }
    
    $body .= "<p>If you have any questions, please contact our customer support.</p>";
    $body .= "<p>Thank you,<br>AgroMeds Team</p>";
    
    // Create PHPMailer instance
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Replace with your SMTP server
        $mail->SMTPAuth = true;
 $mail->Username   = 'agromeds.official.1@gmail.com'; // Your Gmail address
        $mail->Password   = 'YOUR_GMAIL_APP_PASSWORD'; // Your Gmail App Password
        $mail->SMTPSecure = 'tls';
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        // Recipients
        $mail->setFrom('Agromeds.official.1@gmail.com', 'AgroMeds');
        $mail->addAddress($to, $cancelOrder['customer_name']);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = strip_tags($body);
        
        $mail->send();
    } catch (Exception $e) {
        error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
    }
}