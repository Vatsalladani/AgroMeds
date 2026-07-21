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

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Fetch user details
$user_id = $_SESSION['user_id'];
$user_stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$user_stmt->execute([$user_id]);
$user = $user_stmt->fetch(PDO::FETCH_ASSOC);

// Fetch cart items
$cart_stmt = $pdo->prepare("
    SELECT cart.*, 
           cart.quantity as cart_quantity,
           products.*, 
           category.category_name 
    FROM cart 
    INNER JOIN products ON cart.product_id = products.product_id
    INNER JOIN category ON products.category_id = category.category_id
    WHERE cart.user_id = ?
");
$cart_stmt->execute([$user_id]);
$cartItems = $cart_stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate totals
$subtotal = 0;
$totalItems = 0;
foreach ($cartItems as $item) {
    $subtotal += $item['price'] * $item['cart_quantity'];
    $totalItems += $item['cart_quantity'];
}
$shipping = 0; // Free shipping
$tax = 0; // No tax
$grandTotal = $subtotal + $shipping + $tax;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate form data
    $errors = [];
    $requiredFields = ['name', 'email', 'phone', 'address', 'pincode', 'payment_method'];
    
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            $errors[$field] = "This field is required";
        }
    }
    
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format";
    }
    
    if (!preg_match('/^[0-9]{10}$/', $_POST['phone'])) {
        $errors['phone'] = "Invalid phone number";
    }
    
    if (!preg_match('/^[0-9]{6}$/', $_POST['pincode'])) {
        $errors['pincode'] = "Invalid pincode";
    }
    
    // If no errors, process order
if (empty($errors)) {
    try {
        $pdo->beginTransaction();
        
        // Find the next available order ID
        $order_id = 1;
        $stmt = $pdo->query("SELECT MAX(order_id) as max_id FROM orders");
        $max_id = $stmt->fetch(PDO::FETCH_ASSOC)['max_id'];
        
        if ($max_id) {
            // Check for gaps in order IDs
            $stmt = $pdo->query("SELECT order_id FROM orders ORDER BY order_id");
            $all_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            for ($i = 1; $i <= $max_id; $i++) {
                if (!in_array($i, $all_ids)) {
                    $order_id = $i;
                    break;
                }
            }
            
            if ($order_id == 1) {
                $order_id = $max_id + 1;
            }
        }
        
        // Insert order
        $order_stmt = $pdo->prepare("
            INSERT INTO orders (
                order_id, user_id, customer_name, email, phone, pincode, address, 
                total_amount, status, payment_method, payment_status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Processing', ?, 'Pending')
        ");
        
        $order_stmt->execute([
            $order_id,
            $user_id,
            $_POST['name'],
            $_POST['email'],
            $_POST['phone'],
            $_POST['pincode'],
            $_POST['address'],
            $grandTotal,
            $_POST['payment_method']
        ]);
        
        // Insert order items
        $order_item_stmt = $pdo->prepare("
            INSERT INTO order_items (
                order_id, product_id, quantity, price, total
            ) VALUES (?, ?, ?, ?, ?)
        ");
        
        // Prepare the update statement outside the loop
        $update_stmt = $pdo->prepare("
            UPDATE products SET quantity = quantity - ? WHERE product_id = ?
        ");
        
        foreach ($cartItems as $item) {
            $order_item_stmt->execute([
                $order_id,
                $item['product_id'],
                $item['cart_quantity'],
                $item['price'],
                $item['price'] * $item['cart_quantity']
            ]);
            
            // Update product stock
            $update_stmt->execute([$item['cart_quantity'], $item['product_id']]);
        }
        
        // Clear cart
        $pdo->prepare("DELETE FROM cart WHERE user_id = ?")->execute([$user_id]);
        
        $pdo->commit();
        
        // Redirect to order confirmation
        header("Location: order_confirmation.php?order_id=$order_id");
        exit;
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error_message = "Order processing failed: " . $e->getMessage();
    }
}
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout | AgroMeds</title>
    
    <!-- Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #4CAF50;
            --secondary-color: #388E3C;
            --accent-color: #8BC34A;
            --dark-color: #2E7D32;
            --light-color: #C8E6C9;
            --text-color: #333;
            --text-light: #777;
            --bg-color: #f9f9f9;
            --card-bg: #fff;
            --shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            overflow-x: hidden;
        }

        /* Checkout Header */
        .checkout-header {
            background: linear-gradient(135deg, var(--primary-color), var(--dark-color));
            color: white;
            padding: 3rem 0;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }

        .checkout-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="none"><path fill="rgba(255,255,255,0.05)" d="M0,0 L100,0 L100,100 L0,100 Z" /></svg>');
            background-size: cover;
            opacity: 0.1;
        }

        .checkout-header h1 {
            font-weight: 700;
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            text-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .checkout-header p {
            font-weight: 300;
            opacity: 0.9;
        }

        /* Checkout Steps */
        .checkout-steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
            position: relative;
        }

        .checkout-steps::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 0;
            width: 100%;
            height: 3px;
            background-color: #e0e0e0;
            z-index: 1;
        }

        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 2;
        }

        .step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #e0e0e0;
            color: #777;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin-bottom: 0.5rem;
            transition: var(--transition);
        }

        .step.active .step-number {
            background-color: var(--primary-color);
            color: white;
            transform: scale(1.1);
            box-shadow: 0 4px 10px rgba(76, 175, 80, 0.3);
        }

        .step.completed .step-number {
            background-color: var(--dark-color);
            color: white;
        }

        .step.completed .step-number::after {
            content: '\f00c';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
        }

        .step-label {
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--text-light);
            transition: var(--transition);
        }

        .step.active .step-label {
            color: var(--primary-color);
            font-weight: 600;
        }

        .step.completed .step-label {
            color: var(--dark-color);
        }

        /* Checkout Container */
        .checkout-container {
            background-color: var(--card-bg);
            border-radius: 12px;
            box-shadow: var(--shadow);
            padding: 2rem;
            margin-bottom: 3rem;
            transition: var(--transition);
        }

        .checkout-container:hover {
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        }

        .section-title {
            font-weight: 600;
            margin-bottom: 1.5rem;
            position: relative;
            padding-bottom: 0.5rem;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
        }

        /* Form Styles */
        .form-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
        }

        .form-label i {
            margin-right: 8px;
            color: var(--primary-color);
        }

        .form-control {
            border-radius: 8px;
            padding: 0.75rem 1rem;
            border: 1px solid #ddd;
            transition: var(--transition);
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(76, 175, 80, 0.25);
        }

        .is-invalid {
            border-color: #dc3545;
        }

        .invalid-feedback {
            color: #dc3545;
            font-size: 0.85rem;
            margin-top: 0.25rem;
        }

        /* Payment Methods */
        .payment-method {
            display: none;
        }

        .payment-option {
            display: flex;
            align-items: center;
            padding: 1rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 1rem;
            cursor: pointer;
            transition: var(--transition);
        }

        .payment-option:hover {
            border-color: var(--primary-color);
            background-color: rgba(76, 175, 80, 0.05);
        }

        .payment-option.active {
            border-color: var(--primary-color);
            background-color: rgba(76, 175, 80, 0.1);
        }

        .payment-option input {
            margin-right: 1rem;
        }

        .payment-icon {
            width: 40px;
            height: 40px;
            background-color: #f5f5f5;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            color: var(--primary-color);
            font-size: 1.25rem;
        }

        .payment-details {
            flex-grow: 1;
        }

        .payment-title {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .payment-description {
            font-size: 0.85rem;
            color: var(--text-light);
        }

        /* Order Summary */
        .order-summary {
            background-color: #f9f9f9;
            border-radius: 12px;
            padding: 1.5rem;
            position: sticky;
            top: 20px;
        }

        .order-item {
            display: flex;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #eee;
        }

        .order-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .order-item-image {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            overflow: hidden;
            margin-right: 1rem;
        }

        .order-item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .order-item-details {
            flex-grow: 1;
        }

        .order-item-title {
            font-weight: 600;
            margin-bottom: 0.25rem;
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .order-item-meta {
            display: flex;
            justify-content: space-between;
            font-size: 0.85rem;
            color: var(--text-light);
        }

        .order-item-price {
            font-weight: 600;
            color: var(--primary-color);
        }

        .order-total {
            padding: 1.5rem 0;
            border-top: 1px solid #eee;
            border-bottom: 1px solid #eee;
            margin: 1.5rem 0;
        }

        .order-total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }

        .order-total-row:last-child {
            margin-bottom: 0;
        }

        .order-total-label {
            color: var(--text-light);
        }

        .order-total-value {
            font-weight: 600;
        }

        .grand-total .order-total-value {
            font-size: 1.2rem;
            color: var(--primary-color);
        }

        /* Checkout Button */
        .checkout-btn {
            width: 100%;
            padding: 1rem;
            border: none;
            background: linear-gradient(135deg, var(--primary-color), var(--dark-color));
            color: white;
            font-weight: 600;
            font-size: 1.1rem;
            border-radius: 8px;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .checkout-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(76, 175, 80, 0.4);
        }

        .checkout-btn:disabled {
            background: #ddd;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fadeInUp {
            animation: fadeInUp 0.6s ease-out forwards;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
            100% {
                transform: scale(1);
            }
        }

        .animate-pulse {
            animation: pulse 2s infinite;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .checkout-steps {
                flex-wrap: wrap;
                gap: 1rem;
            }
            
            .step {
                flex: 1 0 30%;
            }
            
            .order-summary {
                position: static;
                margin-top: 2rem;
            }
        }

        /* Floating action button */
        .fab {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary-color), var(--dark-color));
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            cursor: pointer;
            z-index: 1000;
            transition: var(--transition);
            opacity: 0;
            visibility: hidden;
        }

        .fab.visible {
            opacity: 1;
            visibility: visible;
        }

        .fab:hover {
            transform: translateY(-5px) scale(1.1);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        }

        /* Loading spinner */
        .spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <!-- Checkout Header -->
    <div class="checkout-header text-center animate__animated animate__fadeIn">
        <div class="container">
            <h1 class="animate__animated animate__fadeInDown">Secure Checkout</h1>
            <p class="animate__animated animate__fadeInUp animate__delay-1s">Complete your purchase with confidence</p>
        </div>
    </div>

    <div class="container">
        <!-- Checkout Steps -->
        <div class="checkout-steps animate__animated animate__fadeIn">
            <div class="step active" data-step="1">
                <div class="step-number">1</div>
                <div class="step-label">Shipping</div>
            </div>
            <div class="step" data-step="2">
                <div class="step-number">2</div>
                <div class="step-label">Payment</div>
            </div>
            <div class="step" data-step="3">
                <div class="step-number">3</div>
                <div class="step-label">Confirmation</div>
            </div>
        </div>

        <div class="row">
            <!-- Left Column - Shipping & Payment -->
            <div class="col-lg-8">
                <form id="checkoutForm" method="POST" action="checkout.php">
                    <!-- Shipping Information -->
                    <div class="checkout-container animate__animated animate__fadeInLeft" data-aos="fade-right">
                        <h3 class="section-title">
                            <i class="fas fa-truck me-2"></i>Shipping Information
                        </h3>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">
                                    <i class="fas fa-user"></i>Full Name
                                </label>
                                <input type="text" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" 
                                    id="name" name="name" 
                                    value="<?= htmlspecialchars($_POST['name'] ?? $user['name'] ?? '') ?>"
                                    required>
                                <?php if (isset($errors['name'])): ?>
                                    <div class="invalid-feedback"><?= $errors['name'] ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope"></i>Email Address
                                </label>
                                <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                                    id="email" name="email" 
                                    value="<?= htmlspecialchars($_POST['email'] ?? $user['email'] ?? '') ?>"
                                    required>
                                <?php if (isset($errors['email'])): ?>
                                    <div class="invalid-feedback"><?= $errors['email'] ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">
                                    <i class="fas fa-phone"></i>Phone Number
                                </label>
                                <input type="tel" class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>" 
                                    id="phone" name="phone" 
                                    value="<?= htmlspecialchars($_POST['phone'] ?? $user['phone'] ?? '') ?>"
                                    required>
                                <?php if (isset($errors['phone'])): ?>
                                    <div class="invalid-feedback"><?= $errors['phone'] ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="pincode" class="form-label">
                                    <i class="fas fa-map-marker-alt"></i>Pincode
                                </label>
                                <input type="text" class="form-control <?= isset($errors['pincode']) ? 'is-invalid' : '' ?>" 
                                    id="pincode" name="pincode" 
                                    value="<?= htmlspecialchars($_POST['pincode'] ?? $user['pincode'] ?? '') ?>"
                                    required>
                                <?php if (isset($errors['pincode'])): ?>
                                    <div class="invalid-feedback"><?= $errors['pincode'] ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="address" class="form-label">
                                <i class="fas fa-home"></i>Shipping Address
                            </label>
                            <textarea class="form-control <?= isset($errors['address']) ? 'is-invalid' : '' ?>" 
                                id="address" name="address" rows="3" required><?= htmlspecialchars($_POST['address'] ?? $user['address'] ?? '') ?></textarea>
                            <?php if (isset($errors['address'])): ?>
                                <div class="invalid-feedback"><?= $errors['address'] ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Payment Method -->
                    <div class="checkout-container animate__animated animate__fadeInLeft animate__delay-1s" data-aos="fade-right" data-aos-delay="100">
                        <h3 class="section-title">
                            <i class="fas fa-credit-card me-2"></i>Payment Method
                        </h3>
                        
                        <div class="mb-3">
                            <div class="payment-option" onclick="selectPayment('upi')">
                                <input type="radio" id="upi" name="payment_method" value="UPI" 
                                    <?= ($_POST['payment_method'] ?? '') === 'UPI' ? 'checked' : '' ?> required>
                                <div class="payment-icon">
                                    <i class="fas fa-mobile-screen"></i>
                                </div>
                                <div class="payment-details">
                                    <div class="payment-title">UPI Payment</div>
                                    <div class="payment-description">Pay using UPI apps like Google Pay, PhonePe, Paytm</div>
                                </div>
                            </div>
                            
                            <div class="payment-option" onclick="selectPayment('card')">
                                <input type="radio" id="card" name="payment_method" value="Card" 
                                    <?= ($_POST['payment_method'] ?? '') === 'Card' ? 'checked' : '' ?>>
                                <div class="payment-icon">
                                    <i class="far fa-credit-card"></i>
                                </div>
                                <div class="payment-details">
                                    <div class="payment-title">Credit/Debit Card</div>
                                    <div class="payment-description">Pay using Visa, Mastercard, Rupay, or other cards</div>
                                </div>
                            </div>
                            
                            <div class="payment-option" onclick="selectPayment('netbanking')">
                                <input type="radio" id="netbanking" name="payment_method" value="Netbanking" 
                                    <?= ($_POST['payment_method'] ?? '') === 'Netbanking' ? 'checked' : '' ?>>
                                <div class="payment-icon">
                                    <i class="fas fa-university"></i>
                                </div>
                                <div class="payment-details">
                                    <div class="payment-title">Net Banking</div>
                                    <div class="payment-description">Pay directly from your bank account</div>
                                </div>
                            </div>
                            
                            <div class="payment-option" onclick="selectPayment('cod')">
                                <input type="radio" id="cod" name="payment_method" value="COD" 
                                    <?= ($_POST['payment_method'] ?? '') === 'COD' ? 'checked' : '' ?>>
                                <div class="payment-icon">
                                    <i class="fas fa-money-bill-wave"></i>
                                </div>
                                <div class="payment-details">
                                    <div class="payment-title">Cash on Delivery</div>
                                    <div class="payment-description">Pay when you receive your order</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Payment Method Details (shown based on selection) -->
                        <div id="paymentDetails" class="mt-4">
                            <!-- Dynamically loaded based on payment method -->
                        </div>
                    </div>
                    
                    <!-- Order Button -->
                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="checkout-btn animate-pulse" id="submitBtn">
                            <i class="fas fa-lock"></i> Complete Order (₹<?= number_format($grandTotal, 2) ?>)
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Right Column - Order Summary -->
            <div class="col-lg-4">
                <div class="order-summary animate__animated animate__fadeInRight" data-aos="fade-left">
                    <h3 class="section-title">
                        <i class="fas fa-shopping-bag me-2"></i>Order Summary
                    </h3>
                    
                    <div class="order-items">
                        <?php foreach ($cartItems as $item): ?>
                            <div class="order-item animate-fadeInUp">
                                <div class="order-item-image">
                                    <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['product_name']) ?>">
                                </div>
                                <div class="order-item-details">
                                    <div class="order-item-title"><?= htmlspecialchars($item['product_name']) ?></div>
                                    <div class="order-item-meta">
                                        <span><?= $item['quantity'] ?> × ₹<?= number_format($item['price'], 2) ?></span>
                                        <span class="order-item-price">₹<?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="order-total">
                        <div class="order-total-row">
                            <span class="order-total-label">Subtotal (<?= $totalItems ?> items)</span>
                            <span class="order-total-value">₹<?= number_format($subtotal, 2) ?></span>
                        </div>
                        <div class="order-total-row">
                            <span class="order-total-label">Shipping</span>
                            <span class="order-total-value">FREE</span>
                        </div>
                        <div class="order-total-row">
                            <span class="order-total-label">Tax</span>
                            <span class="order-total-value">₹0.00</span>
                        </div>
                    </div>
                    
                    <div class="order-total-row grand-total">
                        <span class="order-total-label">Total</span>
                        <span class="order-total-value">₹<?= number_format($grandTotal, 2) ?></span>
                    </div>
                    
                    <div class="d-flex align-items-center mt-3">
                        <i class="fas fa-shield-alt text-success me-2"></i>
                        <small class="text-muted">Secure SSL encrypted payment</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Floating Action Button -->
    <div class="fab" id="fab" onclick="window.scrollTo({top: 0, behavior: 'smooth'})">
        <i class="fas fa-arrow-up"></i>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Initialize AOS animations
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });

        // Payment method selection
        function selectPayment(method) {
            // Update radio button
            document.getElementById(method).checked = true;
            
            // Update UI for selected option
            document.querySelectorAll('.payment-option').forEach(option => {
                option.classList.remove('active');
            });
            event.currentTarget.classList.add('active');
            
            // Load payment details
            loadPaymentDetails(method);
        }

        // Load payment details based on selected method
        function loadPaymentDetails(method) {
            const container = document.getElementById('paymentDetails');
            let html = '';
            
            switch(method) {
                case 'upi':
                    html = `
                        <div class="payment-method upi-method">
                            <div class="mb-3">
                                <label for="upi_id" class="form-label">
                                    <i class="fas fa-mobile-alt"></i>UPI ID
                                </label>
                                <input type="text" class="form-control" id="upi_id" placeholder="yourname@upi">
                            </div>
                            <div class="d-flex align-items-center text-muted">
                                <i class="fas fa-info-circle me-2"></i>
                                <small>You'll be redirected to your UPI app to complete payment</small>
                            </div>
                        </div>
                    `;
                    break;
                    
                case 'card':
                    html = `
                        <div class="payment-method card-method">
                            <div class="mb-3">
                                <label for="card_number" class="form-label">
                                    <i class="far fa-credit-card"></i>Card Number
                                </label>
                                <input type="text" class="form-control" id="card_number" placeholder="1234 5678 9012 3456">
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="expiry" class="form-label">Expiry Date</label>
                                    <input type="text" class="form-control" id="expiry" placeholder="MM/YY">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="cvv" class="form-label">CVV</label>
                                    <input type="text" class="form-control" id="cvv" placeholder="123">
                                </div>
                            </div>
                            
                            <div class="d-flex align-items-center text-muted">
                                <i class="fas fa-lock me-2"></i>
                                <small>Your payment details are securely encrypted</small>
                            </div>
                        </div>
                    `;
                    break;
                    
                case 'netbanking':
                    html = `
                        <div class="payment-method netbanking-method">
                            <div class="mb-3">
                                <label for="bank" class="form-label">
                                    <i class="fas fa-university"></i>Select Bank
                                </label>
                                <select class="form-select" id="bank">
                                    <option value="">Select your bank</option>
                                    <option value="sbi">State Bank of India</option>
                                    <option value="hdfc">HDFC Bank</option>
                                    <option value="icici">ICICI Bank</option>
                                    <option value="axis">Axis Bank</option>
                                    <option value="pnb">Punjab National Bank</option>
                                </select>
                            </div>
                            <div class="d-flex align-items-center text-muted">
                                <i class="fas fa-info-circle me-2"></i>
                                <small>You'll be redirected to your bank's secure payment page</small>
                            </div>
                        </div>
                    `;
                    break;
                    
                case 'cod':
                    html = `
                        <div class="payment-method cod-method">
                            <div class="alert alert-success">
                                <i class="fas fa-info-circle me-2"></i>
                                Pay with cash when your order is delivered
                            </div>
                        </div>
                    `;
                    break;
                    
                default:
                    html = '';
            }
            
            container.innerHTML = html;
            
            // Animate the payment details
            container.classList.add('animate__animated', 'animate__fadeIn');
            setTimeout(() => {
                container.classList.remove('animate__animated', 'animate__fadeIn');
            }, 500);
        }

        // Initialize payment method if one is already selected
        document.addEventListener('DOMContentLoaded', function() {
            const selectedMethod = document.querySelector('input[name="payment_method"]:checked');
            if (selectedMethod) {
                selectPayment(selectedMethod.id);
            }
            
            // Set first payment method as default if none selected
            if (!selectedMethod && document.getElementById('upi')) {
                document.getElementById('upi').checked = true;
                selectPayment('upi');
            }
        });

        // Form submission
        document.getElementById('checkoutForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner"></span> Processing your order...';
            
            // You could add additional validation here if needed
            
            // If everything is valid, the form will submit normally
            // For demo purposes, we'll simulate a delay
            setTimeout(() => {
                submitBtn.innerHTML = '<i class="fas fa-check"></i> Order Complete! Redirecting...';
            }, 1500);
        });

        // Floating button visibility
        window.addEventListener('scroll', function() {
            const fab = document.getElementById('fab');
            if (window.scrollY > 300) {
                fab.classList.add('visible', 'animate__fadeIn');
                fab.classList.remove('animate__fadeOut');
            } else {
                fab.classList.add('animate__fadeOut');
                fab.classList.remove('animate__fadeIn');
                setTimeout(() => {
                    fab.classList.remove('visible');
                }, 500);
            }
        });

        // Show error message if there was a problem
        <?php if (isset($error_message)): ?>
            Swal.fire({
                icon: 'error',
                title: 'Order Failed',
                text: '<?= addslashes($error_message) ?>',
                confirmButtonColor: '#4CAF50'
            });
        <?php endif; ?>
    </script>
</body>
</html>