<?php
session_start();
require 'vendor/autoload.php'; // PHPMailer

// Database Connection
$conn = new mysqli('localhost', 'root', '', 'agriculture');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$order_id = $_GET['order_id'] ?? 0;
$user_id = $_SESSION['user_id'] ?? 0;
$payment_method = $_GET['method'] ?? 'UPI';

// First get the order amount before updating
$order_stmt = $conn->prepare("SELECT total_amount FROM orders WHERE order_id = ? AND user_id = ?");
$order_stmt->bind_param("ii", $order_id, $user_id);
$order_stmt->execute();
$order = $order_stmt->get_result()->fetch_assoc();

if (!$order) {
    die("Order not found or doesn't belong to you");
}

$amount = $order['total_amount']; // Now we have the amount

// Verify order and mark as paid
$stmt = $conn->prepare("UPDATE orders SET payment_status = 'paid', payment_method = ? WHERE order_id = ? AND user_id = ?");
$stmt->bind_param("sii", $payment_method, $order_id, $user_id);
$stmt->execute();

// Get user email
$user_stmt = $conn->prepare("SELECT email FROM users WHERE user_id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();

// Send confirmation email
$mail = new PHPMailer\PHPMailer\PHPMailer();
try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'agromeds.official.1@gmail.com';
    $mail->Password = 'REMOVED_GMAIL_APP_PASSWORD';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('Agromeds.official.1@gmail.com', 'AgroMeds');
    $mail->addAddress($user['email']);
    $mail->isHTML(true);
    $mail->Subject = 'Payment Confirmation - Order #'.$order_id;
    
    $mail->Body = "<h1>Thank you for your order!</h1>
                  <p>Your order #{$order_id} has been confirmed.</p>
                  <p>Payment Method: {$payment_method}</p>
                  <p>Total Amount: ₹" . number_format($amount, 2) . "</p>";
                  
    if ($payment_method === 'cod') {
        $mail->Body .= "<p>Please have the exact amount ready when our delivery partner arrives.</p>";
    } else {
        $mail->Body .= "<p>We'll notify you when your order ships.</p>";
    }

    $mail->send();
    
    // Redirect to thank you page or show success message
    header("Location: thank_you.php?order_id={$order_id}");
    exit;
    
} catch (Exception $e) {
    // Log error but don't show to user
    error_log("Mailer Error: " . $mail->ErrorInfo);
    // Continue to show success page even if email fails
    header("Location: thank_you.php?order_id={$order_id}");
    exit;
}
// Display success page
?>
