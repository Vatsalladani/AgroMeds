<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Check if order ID is provided
if (!isset($_GET['order_id'])) {
    header("Location: cart.php");
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

// Get order details
$order_id = $_GET['order_id'];
$user_id = $_SESSION['user_id'];

$order_sql = "SELECT o.*, GROUP_CONCAT(oi.product_id) as product_ids, 
              GROUP_CONCAT(oi.quantity) as quantities, 
              GROUP_CONCAT(oi.price) as prices,
              GROUP_CONCAT(oi.total) as item_totals
              FROM orders o
              JOIN order_items oi ON o.order_id = oi.order_id
              WHERE o.order_id = $order_id AND o.user_id = $user_id
              GROUP BY o.order_id";
              
$order_result = $conn->query($order_sql);

if ($order_result->num_rows == 0) {
    header("Location: cart.php");
    exit;
}

$order = $order_result->fetch_assoc();

// Get products details
$product_ids = explode(',', $order['product_ids']);
$quantities = explode(',', $order['quantities']);
$prices = explode(',', $order['prices']);
$item_totals = explode(',', $order['item_totals']);

$products = [];
foreach ($product_ids as $index => $product_id) {
    $product_sql = "SELECT product_name FROM products WHERE product_id = $product_id";
    $product_result = $conn->query($product_sql);
    if ($product_result->num_rows > 0) {
        $product = $product_result->fetch_assoc();
        $products[] = [
            'name' => $product['product_name'],
            'quantity' => $quantities[$index],
            'price' => $prices[$index],
            'total' => $item_totals[$index]
        ];
    }
}

// Handle cancel order request
if (isset($_POST['cancel_order'])) {
    // Delete order items first
    $delete_items_sql = "DELETE FROM order_items WHERE order_id = $order_id";
    $conn->query($delete_items_sql);
    
    // Then delete the order
    $delete_order_sql = "DELETE FROM orders WHERE order_id = $order_id AND user_id = $user_id";
    if ($conn->query($delete_order_sql)) {
        $_SESSION['message'] = "Your order #$order_id has been cancelled.";
        header("Location: products.php");
        exit;
    } else {
        $error = "Failed to cancel order. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            background-size: 200% 200%;
            animation: gradientAnimation 10s ease infinite;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }

        @keyframes gradientAnimation {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .confirmation-box {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            padding: 30px;
            width: 100%;
            max-width: 800px;
            text-align: center;
        }

        .confirmation-icon {
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

        .order-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }

        .order-total {
            font-weight: bold;
            font-size: 1.2rem;
            margin-top: 10px;
            text-align: right;
            color: #28a745;
        }

        .btn-continue {
            background-color: #28a745;
            border: none;
            color: white;
            padding: 10px 30px;
            border-radius: 5px;
            font-size: 16px;
            transition: background-color 0.3s ease;
            margin: 5px;
        }

        .btn-payment {
            background-color: #007bff;
            border: none;
            color: white;
            padding: 10px 30px;
            border-radius: 5px;
            font-size: 16px;
            transition: background-color 0.3s ease;
            margin: 5px;
        }

        .btn-cancel {
            background-color: #dc3545;
            border: none;
            color: white;
            padding: 10px 30px;
            border-radius: 5px;
            font-size: 16px;
            transition: background-color 0.3s ease;
            margin: 5px;
        }

        .btn-continue:hover, .btn-payment:hover, .btn-cancel:hover {
            opacity: 0.9;
        }

        .modal-content {
            border-radius: 15px;
        }
    </style>
</head>
<body>
    <div class="confirmation-box">
        <div class="confirmation-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" fill="currentColor" class="bi bi-check-circle" viewBox="0 0 16 16">
                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                <path d="M10.97 4.97a.235.235 0 0 0-.02.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05z"/>
            </svg>
        </div>
        
        <h2>Thank You for Your Order!</h2>
        <p>Your order has been confirmed and will be processed after payment.</p>
        <p>Order ID: <strong>#<?php echo $order['order_id']; ?></strong></p>
        
        <div class="order-details">
            <h4>Order Summary</h4>
            <?php foreach ($products as $product): ?>
                <div class="order-item">
                    <span><?php echo $product['name']; ?> (x<?php echo $product['quantity']; ?>)</span>
                    <span>₹<?php echo number_format($product['total'], 2); ?></span>
                </div>
            <?php endforeach; ?>
            <div class="order-total">
                Total Amount: ₹<?php echo number_format($order['total_amount'], 2); ?>
            </div>
        </div>
        
        <p>Please complete your payment to proceed with your order.</p>
        
        <div class="action-buttons">
            <a href="products.php" class="btn btn-continue">Continue Shopping</a>
            <a href="payment.php?order_id=<?php echo $order_id; ?>&amount=<?php echo $order['total_amount']; ?>" class="btn btn-payment">Proceed to Payment</a>
            <button type="button" class="btn btn-cancel" data-bs-toggle="modal" data-bs-target="#cancelModal">
                Cancel Order
            </button>
        </div>
    </div>

    <!-- Cancel Order Modal -->
    <div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cancelModalLabel">Cancel Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Your payment is still pending. If you cancel now, your order will be permanently deleted.</p>
                    <p>Are you sure you want to cancel order #<?php echo $order_id; ?>?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Go Back</button>
                    <form method="post">
                        <button type="submit" name="cancel_order" class="btn btn-danger">Yes, Cancel Order</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>