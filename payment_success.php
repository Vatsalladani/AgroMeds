
<?php
session_start();

// Database Connection
$conn = new mysqli('localhost', 'root', '', 'agriculture');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Check if order ID and payment ID are provided
if (!isset($_GET['order_id']) || !isset($_GET['payment_id'])) {
    header("Location: cart.php");
    exit;
}

// Verify order belongs to user
$order_id = $_GET['order_id'];
$payment_id = $_GET['payment_id'];
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM orders WHERE order_id = ? AND user_id = ?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: cart.php");
    exit;
}

// Update order status to paid
$update = $conn->prepare("UPDATE orders SET payment_status = 'completed', payment_id = ? WHERE order_id = ?");
$update->bind_param("si", $payment_id, $order_id);
$update->execute();

// Get order details for display
$order = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Payment Successful</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .success-container {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            max-width: 600px;
            width: 100%;
            text-align: center;
        }
        .success-icon {
            color: #28a745;
            font-size: 5rem;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" fill="currentColor" class="bi bi-check-circle-fill" viewBox="0 0 16 16">
                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
            </svg>
        </div>
        <h2>Payment Successful!</h2>
        <p>Thank you for your payment. Your order is now being processed.</p>
        <p>Order ID: <strong>#<?php echo $order_id; ?></strong></p>
        <p>Payment ID: <strong><?php echo $payment_id; ?></strong></p>
        <p>Amount Paid: <strong>₹<?php echo number_format($order['total_amount'], 2); ?></strong></p>
        
        <a href="order_details.php?order_id=<?php echo $order_id; ?>" class="btn btn-primary mt-3">View Order Details</a>
        <a href="products.php" class="btn btn-secondary mt-3">Continue Shopping</a>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>