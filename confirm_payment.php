<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Database connection
$host = 'localhost';
$dbname = 'agriculture';
$username = 'root';
$password = '';
$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get submitted data
$order_id = $_POST['order_id'] ?? '';
$amount = $_POST['amount'] ?? 0;
$upi_reference = $_POST['upi_reference'] ?? '';
$payer_name = $_POST['payer_name'] ?? 'Anonymous';

// Basic validation
if (empty($order_id) || empty($upi_reference) || $amount <= 0) {
    die("Invalid payment confirmation data");
}

// Verify order belongs to user
$user_id = $_SESSION['user_id'];
$order_sql = "SELECT * FROM orders WHERE order_id = $order_id AND user_id = $user_id";
$order_result = $conn->query($order_sql);

if ($order_result->num_rows == 0) {
    die("Order not found or doesn't belong to you");
}

// Update order status
$update_sql = "UPDATE orders SET 
               payment_status = 'completed',
               payment_method = 'UPI',
               payment_reference = '$upi_reference',
               updated_at = NOW()
               WHERE order_id = $order_id";

if ($conn->query($update_sql)) {
    // Show confirmation
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Payment Confirmed</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body {
                font-family: 'Poppins', sans-serif;
                background: linear-gradient(135deg, #6a11cb, #2575fc);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0;
            }
            .success-container {
                background: rgba(255, 255, 255, 0.95);
                border-radius: 15px;
                box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
                padding: 30px;
                width: 100%;
                max-width: 600px;
                text-align: center;
            }
            .success-icon {
                font-size: 5rem;
                color: #28a745;
                margin-bottom: 20px;
            }
            .order-details {
                background-color: #f8f9fa;
                border-radius: 10px;
                padding: 20px;
                margin: 20px 0;
                text-align: left;
            }
            .btn-continue {
                background-color: #28a745;
                color: white;
                margin-top: 15px;
            }
        </style>
    </head>
    <body>
        <div class="success-container">
            <div class="success-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" fill="currentColor" class="bi bi-check-circle" viewBox="0 0 16 16">
                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                    <path d="M10.97 4.97a.235.235 0 0 0-.02.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05z"/>
                </svg>
            </div>
            
            <h2>Payment Successful!</h2>
            <p>Thank you for your payment. Your order is now being processed.</p>
            
            <div class="order-details">
                <h4>Payment Details</h4>
                <p><strong>Order ID:</strong> #<?php echo $order_id; ?></p>
                <p><strong>Amount Paid:</strong> ₹<?php echo number_format($amount, 2); ?></p>
                <p><strong>UPI Reference:</strong> <?php echo htmlspecialchars($upi_reference); ?></p>
                <p><strong>Payer Name:</strong> <?php echo htmlspecialchars($payer_name); ?></p>
            </div>
            
            <p>We've sent a confirmation to your email with the order details.</p>
            <a href="products.php" class="btn btn-continue">Continue Shopping</a>
        </div>
    </body>
    </html>
    <?php
} else {
    echo "Error updating order: " . $conn->error;
}

$conn->close();
?>