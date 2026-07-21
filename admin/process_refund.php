<?php
header('Content-Type: application/json');

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "agriculture";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit;
}

try {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true) ?: $_POST;
    
    if (!isset($data['payment_id'], $data['amount'], $data['reason'])) {
        throw new Exception('Missing required fields');
    }

    $paymentId = intval($data['payment_id']);
    $amount = floatval($data['amount']);
    $reason = $conn->real_escape_string($data['reason']);
    $notifyUser = isset($data['notify_user']) ? boolval($data['notify_user']) : false;

    // Start transaction
    $conn->begin_transaction();

    try {
        // Verify payment exists and is completed
        $paymentQuery = "SELECT p.*, o.customer_name, o.email, o.order_id 
                         FROM payments p
                         LEFT JOIN orders o ON p.order_id = o.order_id
                         WHERE p.payment_id = ? AND p.payment_status = 'Completed'";
        $stmt = $conn->prepare($paymentQuery);
        $stmt->bind_param("i", $paymentId);
        $stmt->execute();
        $paymentResult = $stmt->get_result();
        
        if ($paymentResult->num_rows === 0) {
            throw new Exception('Payment not found or not eligible for refund');
        }
        
        $payment = $paymentResult->fetch_assoc();
        
        // Verify refund amount doesn't exceed payment amount
        if ($amount > floatval($payment['amount'])) {
            throw new Exception('Refund amount cannot exceed original payment amount');
        }
        
        // Generate unique refund ID
        $refundId = 'RFND-' . strtoupper(bin2hex(random_bytes(4)));
        $currentDate = date('Y-m-d H:i:s');
        
        // Insert refund record
        $insertRefund = "INSERT INTO refunds (refund_id, payment_id, order_id, amount, reason, status, created_at)
                         VALUES (?, ?, ?, ?, ?, 'Processing', ?)";
        $stmt = $conn->prepare($insertRefund);
        $stmt->bind_param("siidss", $refundId, $paymentId, $payment['order_id'], $amount, $reason, $currentDate);
        $stmt->execute();
        
        // Update payment status to Refunded if full amount is being refunded
        if ($amount == floatval($payment['amount'])) {
            $updatePayment = "UPDATE payments SET payment_status = 'Refunded', updated_at = ? WHERE payment_id = ?";
            $stmt = $conn->prepare($updatePayment);
            $stmt->bind_param("si", $currentDate, $paymentId);
            $stmt->execute();
            
            // Also update order payment status
            $updateOrder = "UPDATE orders SET payment_status = 'Refunded' WHERE order_id = ?";
            $stmt = $conn->prepare($updateOrder);
            $stmt->bind_param("i", $payment['order_id']);
            $stmt->execute();
        } else {
            // For partial refunds, mark as Partially Refunded
            $updatePayment = "UPDATE payments SET payment_status = 'Partially Refunded', updated_at = ? WHERE payment_id = ?";
            $stmt = $conn->prepare($updatePayment);
            $stmt->bind_param("si", $currentDate, $paymentId);
            $stmt->execute();
        }
        
        // Send notification email if requested
        if ($notifyUser && $payment['email']) {
            $subject = "Your Refund Request Has Been Processed";
            $message = "
                <html>
                <head>
                    <title>Refund Processed</title>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                        .header { background-color: #4e73df; color: white; padding: 15px; text-align: center; }
                        .content { padding: 20px; background-color: #f8f9fa; }
                        .footer { margin-top: 20px; text-align: center; font-size: 12px; color: #777; }
                        .amount { font-size: 18px; font-weight: bold; color: #28a745; }
                        .details { background-color: #fff; border: 1px solid #ddd; padding: 15px; margin: 15px 0; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <h2>Refund Processed</h2>
                        </div>
                        <div class='content'>
                            <p>Hello {$payment['customer_name']},</p>
                            <p>Your refund request for Order #{$payment['order_id']} has been processed successfully.</p>
                            
                            <div class='details'>
                                <p><strong>Refund Amount:</strong> <span class='amount'>₹{$amount}</span></p>
                                <p><strong>Refund ID:</strong> {$refundId}</p>
                                <p><strong>Reason:</strong> {$reason}</p>
                                <p><strong>Original Payment ID:</strong> {$paymentId}</p>
                            </div>
                            
                            <p>The refund amount should appear in your original payment method within 5-7 business days.</p>
                            <p>If you have any questions, please contact our support team.</p>
                        </div>
                        <div class='footer'>
                            <p>© " . date('Y') . " AgroMeds. All rights reserved.</p>
                        </div>
                    </div>
                </body>
                </html>
            ";
            
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= "From: AgroMeds <noreply@agromeds.com>" . "\r\n";
            
            mail($payment['email'], $subject, $message, $headers);
        }
        
        // Commit transaction
        $conn->commit();
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Refund processed successfully',
            'refund_id' => $refundId,
            'amount' => $amount
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