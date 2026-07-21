<div class="qrcode-container">
            <!-- Add error handling for the image -->
            <img src="<?php echo $qrcode_url; ?>" alt="UPI QR Code" 
                 onerror="this.onerror=null; this.src='https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=upi://pay?pa=demo@upi&pn=Demo&am=1&cu=INR'">




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

// UPI Configuration
$upi_id = "udayboda147@axl";  // Replace with your UPI ID
$payee_name = "AgroMeds"; // Your name or business name

// Generate unique transaction IDs
$transaction_id = 'TXN' . bin2hex(random_bytes(4));

// Create UPI payment URL
// Create UPI payment URL with all parameters
$payment_url = "upi://pay?pa=".urlencode($upi_id).
               "&pn=".urlencode($payee_name).
               "&am=".number_format($amount, 2, '.', '').
               "&cu=INR".
               "&tn=".urlencode("Payment for Order $order_id").
               "&tr=".urlencode($transaction_id);

// Alternative URL that shows app chooser
$intent_url = "intent://pay/".urlencode("?pa=$upi_id&pn=$payee_name&am=$amount&tn=Order$order_id").
              "#Intent;scheme=upi;package=;end";

// Generate QR code using an alternative API (QRServer)
$qrcode_url = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($payment_url);

// Debugging output - add this temporarily
echo "<!-- DEBUG: Payment URL: $payment_url -->";
echo "<!-- DEBUG: QR Code URL: $qrcode_url -->";

// Add this after generating payment URLs
$payment_methods = [
    'qr' => [
        'name' => 'QR Code',
        'icon' => 'bi-qr-code-scan',
        'url' => $qrcode_url,
        'type' => 'scan'
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
        'url' => "netbanking.php?order_id=$order_id",
        'type' => 'page'
    ],
    'cards' => [
        'name' => 'Credit/Debit Card',
        'icon' => 'bi-credit-card',
        'url' => "cards.php?order_id=$order_id",
        'type' => 'page'
    ]
];

$payment_methods['cod'] = [
    'name' => 'Cash on Delivery',
    'icon' => 'bi-cash',
    'url' => '#',
    'type' => 'cod'
];

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UPI Payment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }
        .payment-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            padding: 30px;
            width: 100%;
            max-width: 600px;
            text-align: center;
        }
        .qrcode-container {
            margin: 20px 0;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background: white;
        }
        .instructions {
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            text-align: left;
        }
        .payment-button {
            display: inline-block;
            margin: 15px 0;
            padding: 10px 20px;
            background: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
        .confirmation-form {
            margin-top: 30px;
            padding: 20px;
            border-top: 1px solid #eee;
        }
        .btn-back {
            background-color: #6c757d;
            color: white;
            margin-top: 15px;
        }
         /* Add new styles for payment methods */
         .payment-methods {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .payment-method {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        .payment-method:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .payment-method i {
            font-size: 2rem;
            margin-bottom: 10px;
            display: block;
        }
        .payment-method.qr {
            grid-column: span 2;
        }
        .payment-method.qr img {
            max-width: 100%;
            height: auto;
        }
        /* Add modal styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            display: none;
        }
        .modal-content {
            background: white;
            padding: 20px;
            border-radius: 10px;
            max-width: 90%;
            text-align: center;
            position: relative;
        }
        .close-modal {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 1.5rem;
            cursor: pointer;
        }
        /* COD specific styles */
        .cod-instructions {
            display: none;
            margin-top: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="payment-container">
        <h2>UPI Payment</h2>
        <p>Please scan the QR code below to complete your payment of ₹<?php echo number_format($amount, 2); ?></p>
        <p>Order ID: #<?php echo $order_id; ?></p>
        
        
            
                 <div class="payment-methods">
            <!-- QR Code Method -->
            <div class="payment-method qr" onclick="showQR()">
            <div class="payment-method qr" onclick="showQRModal()">
                <i class="bi bi-qr-code-scan"></i>
                <h5>Scan QR Code</h5>
            </div>
            
            <!-- UPI Apps -->
            <div class="payment-method" onclick="launchPayment('google_pay')">
                <i class="bi bi-google" style="color: #4285F4;"></i>
                <h5>Google Pay</h5>
            </div>
            
            <div class="payment-method" onclick="launchPayment('phonepe')">
                <i class="bi bi-phone" style="color: #5F259F;"></i>
                <h5>PhonePe</h5>
            </div>
            
            <div class="payment-method" onclick="launchPayment('paytm')">
                <i class="bi bi-wallet2" style="color: #00BAF2;"></i>
                <h5>Paytm</h5>
            </div>
            
            <!-- Net Banking -->
            <div class="payment-method" onclick="showNetBankingInfo()">
                <i class="bi bi-bank" style="color: #2E86C1;"></i>
                <h5>Net Banking</h5>
            </div>
            
            <!-- Cards -->
            <div class="payment-method" onclick="showCardInfo()">
                <i class="bi bi-credit-card" style="color: #239B56;"></i>
                <h5>Credit/Debit Card</h5>
            </div>
            
            <!-- Cash on Delivery -->
            <div class="payment-method" onclick="selectCOD()">
                <i class="bi bi-cash" style="color: #28a745;"></i>
                <h5>Cash on Delivery</h5>
            </div>
        </div>
        
        <!-- Manual UPI Details -->
        <div class="alert alert-info mt-4">
            <h5><i class="bi bi-info-circle"></i> Manual UPI Payment</h5>
            <p>Send payment to:</p>
            <p><strong>UPI ID:</strong> <?php echo $upi_id; ?></p>
            <p><strong>Amount:</strong> ₹<?php echo number_format($amount, 2); ?></p>
            <p><strong>Note:</strong> Order #<?php echo $order_id; ?></p>
        </div>
        
        <!-- Payment Status Check -->
        <div class="mt-4" id="payment-status-check">
            <button class="btn btn-outline-primary" onclick="checkPaymentStatus()">
                <i class="bi bi-arrow-repeat"></i> Check Payment Status
            </button>
            <div id="status-result" class="mt-2"></div>
        </div>

        <!-- COD Confirmation (hidden by default) -->
        <div id="cod-confirm" class="cod-instructions">
            <h4><i class="bi bi-check-circle"></i> Cash on Delivery Selected</h4>
            <p>Your order will be processed and delivered to your address.</p>
            <p>Please pay ₹<?php echo number_format($amount, 2); ?> in cash when you receive your order.</p>
            <button class="btn btn-success" onclick="confirmCOD()">
                <i class="bi bi-check"></i> Confirm Order
            </button>
        </div>
    </div>


    <div class="modal-overlay" id="qr-modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal('qr-modal')">&times;</span>
            <h4>Scan QR Code to Pay</h4>
            <img src="<?php echo $qrcode_url; ?>" alt="UPI QR Code" style="max-width: 100%;">
            <p class="mt-3">Scan this QR code with any UPI app to complete payment</p>
            <p><strong>Amount:</strong> ₹<?php echo number_format($amount, 2); ?></p>
        </div>
    </div>
    

     <!-- Net Banking Info Modal -->
     <div class="modal-overlay" id="netbanking-modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal('netbanking-modal')">&times;</span>
            <h4>Net Banking Payment</h4>
            <p>You will be redirected to our secure payment gateway to complete your transaction.</p>
            <div class="alert alert-warning">
                <h5>Requirements:</h5>
                <ul class="text-start">
                    <li>Active net banking account with any Indian bank</li>
                    <li>Your internet banking credentials</li>
                    <li>Mobile number registered with your bank for OTP</li>
                </ul>
            </div>
            <button class="btn btn-primary" onclick="window.location.href='netbanking.php?order_id=<?php echo $order_id; ?>'">
                Proceed to Net Banking
            </button>
        </div>
    </div>

    <!-- Card Payment Info Modal -->
    <div class="modal-overlay" id="card-modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal('card-modal')">&times;</span>
            <h4>Card Payment</h4>
            <p>You will be redirected to our secure payment gateway to complete your transaction.</p>
            <div class="alert alert-warning">
                <h5>Requirements:</h5>
                <ul class="text-start">
                    <li>Credit or Debit card from any bank</li>
                    <li>Card number, expiry date, and CVV</li>
                    <li>Mobile number registered with your bank for OTP</li>
                </ul>
            </div>
            <button class="btn btn-primary" onclick="window.location.href='cards.php?order_id=<?php echo $order_id; ?>'">
                Proceed to Card Payment
            </button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Payment methods configuration
    const paymentMethods = <?php echo json_encode($payment_methods); ?>;
    
    function showQR() {
        const qrContainer = document.getElementById('qr-container');
        qrContainer.style.display = qrContainer.style.display === 'none' ? 'block' : 'none';
    }
    
    function launchPayment(method) {
        const config = paymentMethods[method];
        if (!config) return;
        
        if (config.type === 'link') {
            // Try direct app link first
            window.location.href = config.url;
            
            // Fallback after delay
            setTimeout(() => {
                if (confirm(`${config.name} didn't open. Install it?`)) {
                    window.location.href = `https://play.google.com/store/search?q=${config.name}`;
                }
            }, 500);
        } else {
            window.location.href = config.url;
        }
    }
    
    function checkPaymentStatus() {
        const statusResult = document.getElementById('status-result');
        statusResult.innerHTML = '<div class="spinner-border text-primary" role="status"></div> Checking...';
        
        fetch(`check_payment.php?order_id=<?php echo $order_id; ?>`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'paid') {
                    statusResult.innerHTML = `
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle"></i> Payment confirmed! Redirecting...
                        </div>
                    `;
                    setTimeout(() => {
                        window.location.href = `order_success.php?order_id=<?php echo $order_id; ?>`;
                    }, 2000);
                } else {
                    statusResult.innerHTML = `
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i> Payment not received yet
                        </div>
                    `;
                }
            })
            .catch(error => {
                statusResult.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-x-circle"></i> Error checking status
                    </div>
                `;
            });
    }
    
    // Auto-check every 30 seconds if still on page
    setInterval(checkPaymentStatus, 30000);
    </script>
    <script>
    // Payment methods configuration
    const paymentMethods = <?php echo json_encode($payment_methods); ?>;
    
    // Modal functions
    function showQRModal() {
        document.getElementById('qr-modal').style.display = 'flex';
    }
    
    function showNetBankingInfo() {
        document.getElementById('netbanking-modal').style.display = 'flex';
    }
    
    function showCardInfo() {
        document.getElementById('card-modal').style.display = 'flex';
    }
    
    function closeModal(id) {
        document.getElementById(id).style.display = 'none';
    }
    
    // Close modal when clicking outside
    window.onclick = function(event) {
        if (event.target.className === 'modal-overlay') {
            event.target.style.display = 'none';
        }
    }
    
    // COD functions
    function selectCOD() {
        document.getElementById('payment-status-check').style.display = 'none';
        document.getElementById('cod-confirm').style.display = 'block';
    }
    
    function confirmCOD() {
        // Show loading state
        document.getElementById('cod-confirm').innerHTML = `
            <div class="spinner-border text-primary" role="status"></div>
            <p>Confirming your order...</p>
        `;
        
        // Process COD order
        fetch('process_cod.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `order_id=<?php echo $order_id; ?>`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = `order_success.php?order_id=<?php echo $order_id; ?>&method=COD`;
            } else {
                alert('Error: ' + data.message);
                location.reload();
            }
        });
    }
    </script>
</body>
</html>  make the styling good in the box  also organize the code