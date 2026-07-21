<?php
session_start();
require 'vendor/autoload.php';

// Database Connection
$conn = new mysqli('localhost', 'root', '', 'agriculture');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$order_id = $_GET['order_id'] ?? 0;
$user_id = $_SESSION['user_id'] ?? 0;

// Get order details
$order_stmt = $conn->prepare("SELECT * FROM orders WHERE order_id = ? AND user_id = ?");
$order_stmt->bind_param("ii", $order_id, $user_id);
$order_stmt->execute();
$order = $order_stmt->get_result()->fetch_assoc();

if (!$order) {
    header("Location: orders.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank You for Your Order | AgroMeds</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        :root {
            --primary-color: #2e8b57;
            --secondary-color: #3cb371;
            --accent-color: #ff7f50;
            --light-bg: #f8f9fa;
            --dark-text: #343a40;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light-bg);
            color: var(--dark-text);
            overflow-x: hidden;
        }
        
        .thank-you-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
        }
        
        .thank-you-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 10px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color), var(--accent-color));
        }
        
        .confirmation-icon {
            font-size: 5rem;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            animation: bounceIn 1s both;
        }
        
        .order-details {
            background: rgba(46, 139, 87, 0.05);
            border-left: 4px solid var(--primary-color);
            padding: 1.5rem;
            border-radius: 8px;
            margin: 2rem 0;
            transition: transform 0.3s ease;
        }
        
        .order-details:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .detail-item {
            display: flex;
            align-items: center;
            margin-bottom: 0.8rem;
        }
        
        .detail-icon {
            font-size: 1.2rem;
            color: var(--primary-color);
            margin-right: 1rem;
            min-width: 25px;
        }
        
        .progress-tracker {
            position: relative;
            margin: 3rem 0;
            padding: 0;
            list-style: none;
            display: flex;
            justify-content: space-between;
        }
        
        .progress-tracker::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 0;
            width: 100%;
            height: 4px;
            background: #e9ecef;
            z-index: 1;
        }
        
        .progress-step {
            text-align: center;
            position: relative;
            z-index: 2;
            width: 25%;
        }
        
        .step-icon {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: #e9ecef;
            color: #6c757d;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.5rem;
            font-size: 1.2rem;
        }
        
        .step-icon.active {
            background: var(--primary-color);
            color: white;
            animation: pulse 2s infinite;
        }
        
        .step-label {
            font-size: 0.85rem;
            color: #6c757d;
        }
        
        .step-label.active {
            color: var(--primary-color);
            font-weight: 600;
        }
        
        .action-buttons .btn {
            margin: 0.5rem;
            padding: 0.8rem 1.5rem;
            border-radius: 50px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: #267749;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(46, 139, 87, 0.3);
        }
        
        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            color: white;
        }
        
        .floating-icons {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
            pointer-events: none;
        }
        
        .floating-icon {
            position: absolute;
            opacity: 0.1;
            color: var(--primary-color);
            animation: float 15s infinite linear;
        }
        
        @keyframes float {
            0% {
                transform: translateY(0) rotate(0deg);
                opacity: 0.1;
            }
            50% {
                opacity: 0.15;
            }
            100% {
                transform: translateY(-1000px) rotate(720deg);
                opacity: 0;
            }
        }
        
        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(46, 139, 87, 0.4);
            }
            70% {
                box-shadow: 0 0 0 15px rgba(46, 139, 87, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(46, 139, 87, 0);
            }
        }
        
        @keyframes bounceIn {
            0% {
                opacity: 0;
                transform: scale(0.3);
            }
            50% {
                opacity: 1;
                transform: scale(1.05);
            }
            70% {
                transform: scale(0.9);
            }
            100% {
                transform: scale(1);
            }
        }
        
        .coupon-box {
            background: linear-gradient(135deg, #f6d365 0%, #fda085 100%);
            border-radius: 10px;
            padding: 1.5rem;
            color: white;
            text-align: center;
            margin: 2rem 0;
            position: relative;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            animation: fadeInUp 1s both;
        }
        
        .coupon-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="none"><path d="M0,0 L100,0 L100,100 L0,100 Z" fill="none" stroke="white" stroke-width="2" stroke-dasharray="5,5" opacity="0.2"/></svg>');
            background-size: 20px 20px;
        }
        
        .coupon-code {
            font-size: 1.8rem;
            font-weight: bold;
            letter-spacing: 3px;
            margin: 1rem 0;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.2);
        }
        
        .coupon-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        
        .animate-delay-1 {
            animation-delay: 0.3s;
        }
        
        .animate-delay-2 {
            animation-delay: 0.6s;
        }
        
        .animate-delay-3 {
            animation-delay: 0.9s;
        }
        
        @media (max-width: 768px) {
            .thank-you-container {
                margin: 1rem;
                padding: 1.5rem;
            }
            
            .confirmation-icon {
                font-size: 4rem;
            }
            
            .progress-step {
                width: 33%;
            }
        }
    </style>
</head>
<body>
    <div class="floating-icons">
        <?php 
        $icons = ['bi-check-circle', 'bi-truck', 'bi-gift', 'bi-coin', 'bi-shop', 'bi-basket'];
        for ($i = 0; $i < 15; $i++): 
            $randomIcon = $icons[array_rand($icons)];
            $size = rand(20, 40);
            $left = rand(0, 100);
            $animationDuration = rand(10, 25);
        ?>
        <i class="bi <?php echo $randomIcon; ?> floating-icon" 
           style="font-size: <?php echo $size; ?>px; 
                  left: <?php echo $left; ?>%; 
                  animation-duration: <?php echo $animationDuration; ?>s;
                  animation-delay: <?php echo rand(0, 5); ?>s;"></i>
        <?php endfor; ?>
    </div>
    
    <div class="container py-5">
        <div class="thank-you-container animate__animated animate__fadeIn">
            <div class="text-center">
                <i class="bi bi-check-circle-fill confirmation-icon"></i>
                <h1 class="mb-3 animate__animated animate__fadeInDown">Thank You for Your Order!</h1>
                <p class="lead animate__animated animate__fadeIn animate-delay-1">
                    Your order #<?php echo $order_id; ?> has been confirmed.
                </p>
                <?php if ($order['payment_method'] === 'cod'): ?>
                <div class="alert alert-info animate__animated animate__fadeIn animate-delay-1">
                    <i class="bi bi-info-circle"></i> Please have ₹<?php echo number_format($order['total_amount'], 2); ?> ready for our delivery partner.
                </div>
                <?php else: ?>
                <p class="animate__animated animate__fadeIn animate-delay-1">
                    We've sent a confirmation to your email.
                </p>
                <?php endif; ?>
            </div>
            
            <div class="order-details animate__animated animate__fadeIn animate-delay-2">
                <h4><i class="bi bi-receipt"></i> Order Summary</h4>
                <div class="detail-item">
                    <i class="bi bi-hash detail-icon"></i>
                    <span>Order Number: <strong>#<?php echo $order_id; ?></strong></span>
                </div>
                <div class="detail-item">
                    <i class="bi bi-calendar-check detail-icon"></i>
                    <span>Order Date: <strong><?php echo date('F j, Y', strtotime($order['order_date'])); ?></strong></span>
                </div>
                <div class="detail-item">
                    <i class="bi bi-credit-card detail-icon"></i>
                    <span>Payment Method: <strong><?php echo strtoupper($order['payment_method']); ?></strong></span>
                </div>
                <div class="detail-item">
                    <i class="bi bi-currency-rupee detail-icon"></i>
                    <span>Total Amount: <strong>₹<?php echo number_format($order['total_amount'], 2); ?></strong></span>
                </div>
            </div>
            
            <div class="progress-tracker animate__animated animate__fadeIn animate-delay-2">
                <div class="progress-step">
                    <div class="step-icon active">
                        <i class="bi bi-cart-check"></i>
                    </div>
                    <div class="step-label active">Order Placed</div>
                </div>
                <div class="progress-step">
                    <div class="step-icon <?php echo $order['payment_method'] === 'cod' ? '' : 'active'; ?>">
                        <i class="bi bi-credit-card"></i>
                    </div>
                    <div class="step-label <?php echo $order['payment_method'] === 'cod' ? '' : 'active'; ?>">Payment <?php echo $order['payment_method'] === 'cod' ? 'On Delivery' : 'Processed'; ?></div>
                </div>
                <div class="progress-step">
                    <div class="step-icon">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <div class="step-label">Processing</div>
                </div>
                <div class="progress-step">
                    <div class="step-icon">
                        <i class="bi bi-truck"></i>
                    </div>
                    <div class="step-label">Delivered</div>
                </div>
            </div>
            
            <div class="coupon-box animate__animated animate__fadeIn animate-delay-3">
                <i class="bi bi-percent coupon-icon"></i>
                <h4>Special Discount for Next Order!</h4>
                <p>Use this code at checkout to get 10% off</p>
                <div class="coupon-code">AGROMEDS10</div>
                <small>Valid for 30 days</small>
            </div>
            
            <div class="text-center action-buttons animate__animated animate__fadeIn animate-delay-3">
                <a href="orders.php" class="btn btn-primary">
                    <i class="bi bi-list-check"></i> View My Orders
                </a>
                <a href="products.php" class="btn btn-outline-primary">
                    <i class="bi bi-arrow-left"></i> Continue Shopping
                </a>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add some interactive animations
        document.addEventListener('DOMContentLoaded', function() {
            // Animate the coupon box on hover
            const couponBox = document.querySelector('.coupon-box');
            if (couponBox) {
                couponBox.addEventListener('mouseenter', () => {
                    couponBox.style.transform = 'scale(1.02)';
                });
                couponBox.addEventListener('mouseleave', () => {
                    couponBox.style.transform = 'scale(1)';
                });
            }
            
            // Copy coupon code to clipboard
            const couponCode = document.querySelector('.coupon-code');
            if (couponCode) {
                couponCode.addEventListener('click', () => {
                    navigator.clipboard.writeText(couponCode.textContent.trim());
                    const originalText = couponCode.textContent;
                    couponCode.textContent = 'Copied!';
                    couponCode.style.color = '#fff';
                    setTimeout(() => {
                        couponCode.textContent = originalText;
                    }, 2000);
                });
            }
        });
    </script>
</body>
</html>