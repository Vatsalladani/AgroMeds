<?php
session_start();
require 'vendor/autoload.php'; // For PHPMailer

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Check if order ID and amount are provided
if (!isset($_GET['order_id']) || !isset($_GET['amount'])) {
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
$amount = floatval($_GET['amount']);

// Verify order belongs to user
$order_sql = "SELECT * FROM orders WHERE order_id = $order_id AND user_id = $user_id";
$order_result = $conn->query($order_sql);

if ($order_result->num_rows == 0) {
    header("Location: cart.php");
    exit;
}

// Check if payment already exists
$payment_sql = "SELECT * FROM payments WHERE order_id = $order_id";
$payment_result = $conn->query($payment_sql);
$payment_exists = $payment_result->num_rows > 0;

// UPI Configuration
$upi_id = "demo@upi";  // Replace with your UPI ID
$payee_name = "AgroMeds"; // Your name or business name

// Generate unique transaction IDs
$transaction_id = 'TXN' . bin2hex(random_bytes(4));

// Create UPI payment URL
$payment_url = "upi://pay?pa=".urlencode($upi_id).
               "&pn=".urlencode($payee_name).
               "&am=".number_format($amount, 2, '.', '').
               "&cu=INR".
               "&tn=".urlencode("Payment for Order $order_id").
               "&tr=".urlencode($transaction_id);

// Generate QR code
$qrcode_url = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($payment_url);

// Payment methods configuration
$payment_methods = [
    'qr' => [
        'name' => 'QR Code',
        'icon' => 'bi-qr-code-scan',
        'url' => $qrcode_url,
        'type' => 'modal',
        'content' => '<img src="'.$qrcode_url.'" alt="UPI QR Code" class="img-fluid">
                     <p class="mt-3">Scan this QR code with any UPI app to complete payment</p>
                     <div class="text-center mt-3">
                        <button onclick="confirmAdminPayment(\'upi\')" class="btn btn-primary">Confirm Payment</button>
                     </div>'
    ],
    'google_pay' => [
        'name' => 'Google Pay',
        'icon' => 'bi-google',
        'url' => "https://pay.google.com/send?pa=$upi_id&am=$amount&pn=$payee_name",
        'type' => 'link'
    ],
    'phonepe' => [
        'name' => 'PhonePe',
        'icon' => 'bi-phone',
        'url' => "phonepe://pay?pa=$upi_id&pn=$payee_name&am=$amount",
        'type' => 'link'
    ],
    'paytm' => [
        'name' => 'Paytm',
        'icon' => 'bi-wallet2',
        'url' => "paytmmp://pay?pa=$upi_id&pn=$payee_name&am=$amount",
        'type' => 'link'
    ],
    'netbanking' => [
        'name' => 'Net Banking',
        'icon' => 'bi-bank',
        'type' => 'modal',
        'content' => '
            <form id="netbankingForm">
                <div class="mb-3">
                    <label class="form-label">Select Bank</label>
                    <select class="form-select" name="bank" required>
                        <option value="">Select your bank</option>
                        <option value="sbi">State Bank of India</option>
                        <option value="hdfc">HDFC Bank</option>
                        <option value="icici">ICICI Bank</option>
                        <option value="axis">Axis Bank</option>
                        <option value="other">Other Bank</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Account Number</label>
                    <input type="text" class="form-control" name="account_number" placeholder="Enter account number" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">IFSC Code</label>
                    <input type="text" class="form-control" name="ifsc_code" placeholder="Enter IFSC code" required>
                </div>
                <button type="button" onclick="processPayment(\'netbanking\')" class="btn btn-primary w-100">Proceed to Payment</button>
            </form>'
    ],
    'cards' => [
        'name' => 'Credit/Debit Card',
        'icon' => 'bi-credit-card',
        'type' => 'modal',
        'content' => '
            <form id="cardPaymentForm">
                <div class="mb-3">
                    <label class="form-label">Card Number</label>
                    <input type="text" class="form-control" name="card_number" placeholder="1234 5678 9012 3456" required>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Expiry Date</label>
                        <input type="text" class="form-control" name="expiry" placeholder="MM/YY" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">CVV</label>
                        <input type="text" class="form-control" name="cvv" placeholder="123" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Cardholder Name</label>
                    <input type="text" class="form-control" name="card_name" placeholder="Name on card" required>
                </div>
                <button type="button" onclick="processPayment(\'card\')" class="btn btn-primary w-100">Pay ₹'.number_format($amount, 2).'</button>
            </form>'
    ],
    'cod' => [
        'name' => 'Cash on Delivery',
        'icon' => 'bi-cash',
        'type' => 'modal',
        'content' => '
            <div class="text-center">
                <i class="bi bi-truck" style="font-size: 3rem; color: #28a745;"></i>
                <h4 class="my-3">Cash on Delivery</h4>
                <p>Pay when you receive your order</p>
                <p class="fw-bold">Order Total: ₹'.number_format($amount, 2).'</p>
                <button onclick="confirmCOD()" class="btn btn-success">Confirm Order</button>
            </div>'
    ]
];

// Handle COD confirmation
if (isset($_POST['confirm_cod'])) {
    $payment_method = 'cod';
    $payment_status = 'completed';
    $transaction_id = 'COD-' . bin2hex(random_bytes(4));
    
    // Insert payment record
    $insert_sql = "INSERT INTO payments (order_id, user_id, amount, payment_method, payment_status, transaction_id, payment_details) 
                   VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_sql);
    $payment_details = json_encode(['method' => 'cod']);
    $stmt->bind_param("iidssss", $order_id, $user_id, $amount, $payment_method, $payment_status, $transaction_id, $payment_details);
    
    if ($stmt->execute()) {
        // Update order status
        $update_sql = "UPDATE orders SET payment_status = 'completed', status = 'processing' WHERE order_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("i", $order_id);
        $update_stmt->execute();
        
        header("Location: order_success.php?order_id=$order_id&payment_method=cod");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Payment | AgroMeds</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        :root {
            --primary-color: #4a6bff;
            --secondary-color: #6c5ce7;
            --success-color: #00b894;
            --danger-color: #ff7675;
            --warning-color: #fdcb6e;
            --dark-color: #2d3436;
            --light-color: #f5f6fa;
            --gradient-bg: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --card-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--gradient-bg);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            color: var(--dark-color);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
        }
        
        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .payment-container {
            background: rgba(255, 255, 255, 0.97);
            border-radius: 20px;
            box-shadow: var(--card-shadow);
            padding: 40px;
            width: 100%;
            max-width: 800px;
            text-align: center;
            position: relative;
            overflow: hidden;
            transform: translateY(0);
            transition: var(--transition);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .payment-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
        
        .payment-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 8px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
        }
        
        .payment-header {
            margin-bottom: 30px;
            position: relative;
        }
        
        .payment-header h2 {
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 10px;
            position: relative;
            display: inline-block;
        }
        
        .payment-header h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background: var(--primary-color);
            border-radius: 3px;
        }
        
        .order-summary {
            background: rgba(74, 107, 255, 0.1);
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-left: 4px solid var(--primary-color);
        }
        
        .order-summary .amount {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .payment-methods {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        
        .payment-method {
            border: 1px solid rgba(0, 0, 0, 0.05);
            border-radius: 12px;
            padding: 20px 15px;
            text-align: center;
            cursor: pointer;
            transition: var(--transition);
            background: white;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }
        
        .payment-method::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--primary-color);
            transition: var(--transition);
            opacity: 0;
        }
        
        .payment-method:hover {
            transform: translateY(-8px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            border-color: rgba(74, 107, 255, 0.3);
        }
        
        .payment-method:hover::before {
            opacity: 1;
        }
        
        .payment-method i {
            font-size: 2.5rem;
            margin-bottom: 15px;
            display: block;
            transition: var(--transition);
        }
        
        .payment-method:hover i {
            transform: scale(1.1);
        }
        
        .payment-method h5 {
            font-weight: 600;
            margin-bottom: 5px;
            transition: var(--transition);
        }
        
        .payment-method:hover h5 {
            color: var(--primary-color);
        }
        
        .payment-method .method-desc {
            font-size: 0.8rem;
            color: #666;
            opacity: 0;
            height: 0;
            transition: var(--transition);
        }
        
        .payment-method:hover .method-desc {
            opacity: 1;
            height: auto;
            margin-top: 5px;
        }
        
        .cod-badge {
            background-color: var(--success-color);
            color: white;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            margin-left: 5px;
            display: inline-block;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: var(--transition);
            backdrop-filter: blur(5px);
        }
        
        .modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }
        
        .payment-modal {
            background: white;
            border-radius: 15px;
            padding: 30px;
            width: 90%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
            transform: translateY(20px);
            transition: var(--transition);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
        }
        
        .modal-overlay.active .payment-modal {
            transform: translateY(0);
        }
        
        .close-modal {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 1.5rem;
            cursor: pointer;
            background: none;
            border: none;
            color: #666;
            transition: var(--transition);
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }
        
        .close-modal:hover {
            background: #f5f5f5;
            color: var(--danger-color);
        }
        
        .admin-confirm-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.8);
            z-index: 1001;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(5px);
        }
        
        .admin-confirm-modal.active {
            display: flex;
            animation: fadeIn 0.3s;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .admin-confirm-content {
            background: white;
            padding: 30px;
            border-radius: 15px;
            width: 90%;
            max-width: 450px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
            transform: scale(0.9);
            transition: var(--transition);
        }
        
        .admin-confirm-modal.active .admin-confirm-content {
            transform: scale(1);
        }
        
        .payment-status-btn {
            background: white;
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            font-weight: 600;
            padding: 10px 20px;
            border-radius: 50px;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .payment-status-btn:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(74, 107, 255, 0.3);
        }
        
        .status-result {
            margin-top: 20px;
            padding: 15px;
            border-radius: 10px;
            transition: var(--transition);
        }
        
        .qr-code-container {
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
            position: relative;
        }
        
        .qr-code-container::before {
            content: '';
            position: absolute;
            top: 10px;
            left: 10px;
            right: 10px;
            bottom: 10px;
            border: 2px dashed rgba(74, 107, 255, 0.2);
            border-radius: 5px;
            pointer-events: none;
        }
        
        .payment-instructions {
            background: rgba(74, 107, 255, 0.05);
            border-radius: 10px;
            padding: 15px;
            margin-top: 20px;
            text-align: left;
        }
        
        .payment-instructions ol {
            padding-left: 20px;
        }
        
        .payment-instructions li {
            margin-bottom: 8px;
        }
        
        .payment-success-animation {
            display: flex;
            justify-content: center;
            margin: 30px 0;
        }
        
        .checkmark {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: block;
            stroke-width: 5;
            stroke: var(--success-color);
            stroke-miterlimit: 10;
            box-shadow: 0 0 0 rgba(0, 180, 148, 0.4);
            animation: checkmark-fill 0.4s ease-in-out 0.4s forwards, checkmark-scale 0.3s ease-in-out 0.9s both;
        }
        
        .checkmark-circle {
            stroke-dasharray: 166;
            stroke-dashoffset: 166;
            stroke-width: 5;
            stroke-miterlimit: 10;
            stroke: var(--success-color);
            fill: none;
            animation: checkmark-stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
        }
        
        .checkmark-check {
            transform-origin: 50% 50%;
            stroke-dasharray: 48;
            stroke-dashoffset: 48;
            animation: checkmark-stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards;
        }
        
        @keyframes checkmark-stroke {
            100% { stroke-dashoffset: 0; }
        }
        
        @keyframes checkmark-scale {
            0%, 100% { transform: none; }
            50% { transform: scale3d(1.1, 1.1, 1); }
        }
        
        @keyframes checkmark-fill {
            100% { box-shadow: inset 0 0 0 100px rgba(0, 180, 148, 0.1); }
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .payment-container {
                padding: 30px 20px;
                margin: 20px;
            }
            
            .payment-methods {
                grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
                gap: 15px;
            }
            
            .payment-method i {
                font-size: 2rem;
            }
        }
        
        @media (max-width: 480px) {
            .payment-methods {
                grid-template-columns: 1fr 1fr;
            }
            
            .payment-header h2 {
                font-size: 1.5rem;
            }
            
            .order-summary {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="payment-container animate__animated animate__fadeIn">
        <div class="payment-header">
            <h2><i class="bi bi-lock-fill"></i> Secure Payment</h2>
            <p>Complete your purchase with a secure payment method</p>
        </div>
        
        <div class="order-summary animate__animated animate__fadeInUp">
            <div>
                <span class="text-muted">Order Reference:</span>
                <strong>#<?php echo $order_id; ?></strong>
            </div>
            <div class="amount">₹<?php echo number_format($amount, 2); ?></div>
        </div>
        
        <?php if ($payment_exists): ?>
            <div class="alert alert-info animate__animated animate__fadeIn">
                <i class="bi bi-info-circle-fill"></i> Payment for this order has already been recorded. Please wait for confirmation.
            </div>
            <div class="text-center mt-4">
                <a href="orders.php" class="btn btn-primary">
                    <i class="bi bi-box-seam"></i> View Your Orders
                </a>
            </div>
        <?php else: ?>
            <h5 class="text-start mb-3 animate__animated animate__fadeInUp" style="animation-delay: 0.1s;">
                <i class="bi bi-credit-card"></i> Choose Payment Method
            </h5>
            
            <div class="payment-methods">
                <!-- QR Code -->
                <div class="payment-method animate__animated animate__fadeInUp" style="animation-delay: 0.2s;" onclick="showPaymentModal('qr')">
                    <i class="bi bi-qr-code-scan text-primary"></i>
                    <h5>UPI QR Code</h5>
                    <div class="method-desc">Scan & pay with any UPI app</div>
                </div>
                
                <!-- Google Pay -->
                <div class="payment-method animate__animated animate__fadeInUp" style="animation-delay: 0.3s;" onclick="launchPayment('google_pay')">
                    <i class="bi bi-google" style="color: #4285F4;"></i>
                    <h5>Google Pay</h5>
                    <div class="method-desc">Fast UPI payments</div>
                </div>
                
                <!-- PhonePe -->
                <div class="payment-method animate__animated animate__fadeInUp" style="animation-delay: 0.4s;" onclick="launchPayment('phonepe')">
                    <i class="bi bi-phone" style="color: #5F259F;"></i>
                    <h5>PhonePe</h5>
                    <div class="method-desc">Secure UPI payments</div>
                </div>
                
                <!-- Paytm -->
                <div class="payment-method animate__animated animate__fadeInUp" style="animation-delay: 0.5s;" onclick="launchPayment('paytm')">
                    <i class="bi bi-wallet2" style="color: #00BAF2;"></i>
                    <h5>Paytm</h5>
                    <div class="method-desc">Wallet & UPI</div>
                </div>
                
                <!-- Net Banking -->
                <div class="payment-method animate__animated animate__fadeInUp" style="animation-delay: 0.6s;" onclick="showPaymentModal('netbanking')">
                    <i class="bi bi-bank" style="color: #2E86C1;"></i>
                    <h5>Net Banking</h5>
                    <div class="method-desc">All major banks</div>
                </div>
                
                <!-- Cards -->
                <div class="payment-method animate__animated animate__fadeInUp" style="animation-delay: 0.7s;" onclick="showPaymentModal('cards')">
                    <i class="bi bi-credit-card" style="color: #239B56;"></i>
                    <h5>Credit/Debit Card</h5>
                    <div class="method-desc">Visa, Mastercard, RuPay</div>
                </div>
                
                <!-- Cash on Delivery -->
                <div class="payment-method animate__animated animate__fadeInUp" style="animation-delay: 0.8s;" onclick="showPaymentModal('cod')">
                    <i class="bi bi-truck" style="color: #28a745;"></i>
                    <h5>Cash on Delivery <span class="cod-badge">Available</span></h5>
                    <div class="method-desc">Pay when you receive</div>
                </div>
            </div>
            
            <!-- Payment Security Badge -->
            <div class="d-flex justify-content-center align-items-center mt-4 gap-3 animate__animated animate__fadeIn" style="animation-delay: 1s;">
                <div class="text-center">
                    <i class="bi bi-shield-lock-fill text-primary" style="font-size: 1.5rem;"></i>
                    <p class="small text-muted mb-0">256-bit SSL Secure</p>
                </div>
                <div class="text-center">
                    <i class="bi bi-credit-card-2-back-fill text-primary" style="font-size: 1.5rem;"></i>
                    <p class="small text-muted mb-0">PCI DSS Compliant</p>
                </div>
                <div class="text-center">
                    <i class="bi bi-arrow-repeat text-primary" style="font-size: 1.5rem;"></i>
                    <p class="small text-muted mb-0">Easy Refunds</p>
                </div>
            </div>
            
            <!-- Payment Status Check -->
            <div class="mt-4 text-center animate__animated animate__fadeIn" style="animation-delay: 1.1s;" id="payment-status-check">
                <button class="payment-status-btn" onclick="checkPaymentStatus()">
                    <i class="bi bi-arrow-repeat"></i> Check Payment Status
                </button>
                <div id="status-result" class="status-result"></div>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Payment Modal -->
    <div class="modal-overlay" id="paymentModal">
        <div class="payment-modal">
            <button class="close-modal" onclick="hidePaymentModal()">&times;</button>
            <div id="modalContent"></div>
        </div>
    </div>
    
    <!-- Admin Confirmation Modal -->
    <div class="admin-confirm-modal" id="adminConfirmModal">
        <div class="admin-confirm-content">
            <div class="text-center mb-4">
                <i class="bi bi-shield-check" style="font-size: 2.5rem; color: var(--primary-color);"></i>
                <h4 class="mt-3">Confirm Payment</h4>
                <p class="text-muted">Please verify payment details with the customer</p>
            </div>
            
            <div class="mb-3">
                <label class="form-label fw-bold">Payment Method</label>
                <select class="form-select" id="confirmPaymentMethod">
                    <option value="upi">UPI (Google Pay/PhonePe/Paytm)</option>
                    <option value="card">Credit/Debit Card</option>
                    <option value="netbanking">Net Banking</option>
                    <option value="cod">Cash on Delivery</option>
                    <option value="other">Other Method</option>
                </select>
            </div>
            
            <div class="mb-4">
                <label class="form-label fw-bold">Transaction ID (if any)</label>
                <input type="text" class="form-control" id="confirmTransactionId" placeholder="Enter transaction ID">
                <small class="text-muted">Leave blank if not applicable</small>
            </div>
            
            <div class="d-flex justify-content-between mt-4">
                <button class="btn btn-outline-secondary" onclick="hideAdminConfirmModal()">
                    <i class="bi bi-x-circle"></i> Cancel
                </button>
                <button class="btn btn-primary" onclick="submitAdminConfirmation()">
                    <i class="bi bi-check-circle"></i> Confirm Payment
                </button>
            </div>
        </div>
    </div>
    
    <!-- COD Form (hidden) -->
    <form id="codForm" method="post" action="">
        <input type="hidden" name="confirm_cod" value="1">
    </form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Payment methods configuration
    const paymentMethods = <?php echo json_encode($payment_methods); ?>;
    const orderId = <?php echo $order_id; ?>;
    const userId = <?php echo $user_id; ?>;
    const amount = <?php echo $amount; ?>;
    
    // Show payment modal with animation
    function showPaymentModal(method) {
        const config = paymentMethods[method];
        if (!config) return;
        
        document.getElementById('modalContent').innerHTML = `
            <div class="animate__animated animate__fadeIn">
                <h4 class="text-center mb-4">
                    <i class="bi ${config.icon}"></i> ${config.name}
                </h4>
                ${config.content || ''}
            </div>
        `;
        
        // Add specific content for QR code modal
        if (method === 'qr') {
            document.getElementById('modalContent').innerHTML += `
                <div class="payment-instructions animate__animated animate__fadeIn" style="animation-delay: 0.3s;">
                    <h6><i class="bi bi-info-circle"></i> How to pay:</h6>
                    <ol>
                        <li>Open any UPI payment app on your phone</li>
                        <li>Tap on 'Scan QR Code'</li>
                        <li>Point your camera at the QR code above</li>
                        <li>Enter the exact amount and complete payment</li>
                        <li>Click 'Confirm Payment' after successful transaction</li>
                    </ol>
                </div>
            `;
        }
        
        document.getElementById('paymentModal').classList.add('active');
    }
    
    // Hide payment modal with animation
    function hidePaymentModal() {
        const modal = document.getElementById('paymentModal');
        modal.classList.remove('active');
    }
    
    // Show admin confirmation modal
    function showAdminConfirmModal() {
        document.getElementById('adminConfirmModal').classList.add('active');
    }
    
    // Hide admin confirmation modal
    function hideAdminConfirmModal() {
        document.getElementById('adminConfirmModal').classList.remove('active');
    }
    
    // Launch payment apps with user feedback
    function launchPayment(method) {
        const config = paymentMethods[method];
        if (!config || config.type !== 'link') return;
        
        // Open the payment app
        window.location.href = config.url;
        
        // Fallback after delay
        setTimeout(() => {
            if (confirm(`${config.name} didn't open. Would you like to install it?`)) {
                window.location.href = `https://play.google.com/store/search?q=${encodeURIComponent(config.name)}`;
            } else {
                // Show QR code as fallback
                showPaymentModal('qr');
            }
        }, 500);
    }
    
    // Confirm Cash on Delivery with animation
    function confirmCOD() {
        // Show confirmation animation
        const modalContent = document.getElementById('modalContent');
        modalContent.innerHTML = `
            <div class="text-center animate__animated animate__fadeIn">
                <div class="payment-success-animation">
                    <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                        <circle class="checkmark-circle" cx="26" cy="26" r="25" fill="none"/>
                        <path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                    </svg>
                </div>
                <h4 class="text-success">COD Confirmed!</h4>
                <p>Your order will be processed shortly.</p>
                <p class="fw-bold">Order Total: ₹${amount.toFixed(2)}</p>
                <div class="spinner-border text-success mt-3" role="status"></div>
                <p class="mt-2">Redirecting...</p>
            </div>
        `;
        
        // Submit the COD form after animation
        setTimeout(() => {
            document.getElementById('codForm').submit();
        }, 2000);
    }
    
    // Confirm payment with admin
    function confirmAdminPayment(method) {
        // Set the payment method in the confirmation modal
        document.getElementById('confirmPaymentMethod').value = method;
        
        // Generate a random transaction ID if UPI
        if (method === 'upi') {
            document.getElementById('confirmTransactionId').value = 
                'UPI-' + Math.random().toString(36).substr(2, 8).toUpperCase();
        } else {
            document.getElementById('confirmTransactionId').value = '';
        }
        
        // Hide the payment modal and show admin confirmation
        hidePaymentModal();
        setTimeout(() => {
            showAdminConfirmModal();
        }, 300);
    }
    
    // Submit admin confirmation
    function submitAdminConfirmation() {
        const method = document.getElementById('confirmPaymentMethod').value;
        const transactionId = document.getElementById('confirmTransactionId').value || 
                            'ADMIN-' + Math.random().toString(36).substr(2, 8).toUpperCase();
        
        // Show loading animation
        document.getElementById('adminConfirmModal').querySelector('.admin-confirm-content').innerHTML = `
            <div class="text-center py-4 animate__animated animate__fadeIn">
                <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status"></div>
                <h5 class="mt-3">Recording Payment...</h5>
                <p class="text-muted">Please wait while we process your confirmation</p>
            </div>
        `;
        
        // Prepare payment details
        let paymentDetails = {
            method: method,
            confirmed_by: 'admin',
            confirmed_at: new Date().toISOString()
        };
        
        if (transactionId) {
            paymentDetails.transaction_id = transactionId;
        }
        
        // Send data to server
        fetch('record_payment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                order_id: orderId,
                user_id: userId,
                amount: amount,
                payment_method: method,
                payment_status: 'completed',
                transaction_id: transactionId,
                payment_details: JSON.stringify(paymentDetails)
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success animation
                document.getElementById('adminConfirmModal').querySelector('.admin-confirm-content').innerHTML = `
                    <div class="text-center animate__animated animate__fadeIn">
                        <div class="payment-success-animation">
                            <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                                <circle class="checkmark-circle" cx="26" cy="26" r="25" fill="none"/>
                                <path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                            </svg>
                        </div>
                        <h4 class="text-success">Payment Recorded!</h4>
                        <p>Transaction ID: ${transactionId}</p>
                        <div class="spinner-border text-success mt-3" role="status"></div>
                        <p class="mt-2">Redirecting to order details...</p>
                    </div>
                `;
                
                // Redirect to success page after animation
                setTimeout(() => {
                    window.location.href = `order_success.php?order_id=${orderId}&payment_method=${method}`;
                }, 2000);
            } else {
                // Show error
                document.getElementById('adminConfirmModal').querySelector('.admin-confirm-content').innerHTML = `
                    <div class="animate__animated animate__fadeIn">
                        <div class="alert alert-danger">
                            <i class="bi bi-x-circle-fill"></i> <strong>Error:</strong> ${data.message || 'Failed to record payment'}
                        </div>
                        <button onclick="hideAdminConfirmModal()" class="btn btn-secondary w-100">
                            <i class="bi bi-arrow-left"></i> Go Back
                        </button>
                    </div>
                `;
            }
        })
        .catch(error => {
            document.getElementById('adminConfirmModal').querySelector('.admin-confirm-content').innerHTML = `
                <div class="animate__animated animate__fadeIn">
                    <div class="alert alert-danger">
                        <i class="bi bi-x-circle-fill"></i> <strong>Network Error:</strong> Please check your connection and try again
                    </div>
                    <button onclick="hideAdminConfirmModal()" class="btn btn-secondary w-100">
                        <i class="bi bi-arrow-left"></i> Go Back
                    </button>
                </div>
            `;
        });
    }
    
    // Process payment (for card/netbanking)
    function processPayment(method) {
        const modalContent = document.getElementById('modalContent');
        modalContent.innerHTML = `
            <div class="text-center py-4 animate__animated animate__fadeIn">
                <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status"></div>
                <h4 class="mt-3">Processing Payment...</h4>
                <div class="progress mt-3" style="height: 8px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 45%"></div>
                </div>
                <p class="text-muted mt-2">Please wait while we process your ${method} payment</p>
            </div>
        `;

        // Simulate processing delay
        setTimeout(() => {
            document.querySelector('.progress-bar').style.width = '80%';
        }, 1000);
        
        // After processing, show admin confirmation
        setTimeout(() => {
            hidePaymentModal();
            confirmAdminPayment(method);
        }, 2500);
    }
    
    // Check payment status with animation
    function checkPaymentStatus() {
        const statusResult = document.getElementById('status-result');
        statusResult.innerHTML = `
            <div class="animate__animated animate__fadeIn">
                <div class="d-flex justify-content-center align-items-center gap-3">
                    <div class="spinner-border text-primary" role="status"></div>
                    <span>Checking payment status...</span>
                </div>
            </div>
        `;
        
        fetch(`check_payment.php?order_id=${orderId}`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'completed') {
                    statusResult.innerHTML = `
                        <div class="alert alert-success animate__animated animate__fadeIn">
                            <div class="d-flex align-items-center gap-3">
                                <i class="bi bi-check-circle-fill" style="font-size: 1.5rem;"></i>
                                <div>
                                    <h5 class="mb-1">Payment Confirmed!</h5>
                                    <p class="mb-0">Transaction ID: ${data.transaction_id || 'N/A'}</p>
                                </div>
                            </div>
                            <div class="progress mt-2" style="height: 4px;">
                                <div class="progress-bar bg-success" style="width: 100%"></div>
                            </div>
                            <p class="mt-2 mb-0">Redirecting to order details...</p>
                        </div>
                    `;
                    setTimeout(() => {
                        window.location.href = `order_success.php?order_id=${orderId}`;
                    }, 2000);
                } else {
                    statusResult.innerHTML = `
                        <div class="alert alert-warning animate__animated animate__fadeIn">
                            <div class="d-flex align-items-center gap-3">
                                <i class="bi bi-exclamation-triangle-fill" style="font-size: 1.5rem;"></i>
                                <div>
                                    <h5 class="mb-1">Payment Not Received</h5>
                                    <p class="mb-0">Please complete your payment to proceed</p>
                                </div>
                            </div>
                        </div>
                    `;
                }
            })
            .catch(error => {
                statusResult.innerHTML = `
                    <div class="alert alert-danger animate__animated animate__fadeIn">
                        <div class="d-flex align-items-center gap-3">
                            <i class="bi bi-x-circle-fill" style="font-size: 1.5rem;"></i>
                            <div>
                                <h5 class="mb-1">Connection Error</h5>
                                <p class="mb-0">Failed to check payment status</p>
                            </div>
                        </div>
                    </div>
                `;
            });
    }
    
    // Auto-check every 30 seconds if still on page
    setInterval(checkPaymentStatus, 30000);
    
    // Add animation to payment methods on hover
    document.querySelectorAll('.payment-method').forEach(method => {
        method.addEventListener('mouseenter', () => {
            method.classList.add('animate__pulse');
        });
        method.addEventListener('mouseleave', () => {
            method.classList.remove('animate__pulse');
        });
    });
    </script>
</body>
</html>