<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}


$isLoggedIn = isset($_SESSION['user_id']);
$theme = isset($_SESSION['theme']) ? $_SESSION['theme'] : 'light';
$language = isset($_SESSION['language']) ? $_SESSION['language'] : 'en';
$user_id = $_SESSION['user_id'];

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

// Handle Add to Favorites
if (isset($_GET['add_to_favorites'])) {
    $product_id = $_GET['product_id'];
    $user_id = $_SESSION['user_id'];

    try {
        $stmt = $pdo->prepare("INSERT INTO favorites (user_id, item_id, item_type) VALUES (:user_id, :item_id, :item_type)");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':item_id', $product_id, PDO::PARAM_INT);
        $stmt->bindValue(':item_type', 'product', PDO::PARAM_STR);
        $stmt->execute();
        echo json_encode(['status' => 'success', 'message' => 'Product added to favorites']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to add to favorites: ' . $e->getMessage()]);
    }
    exit;
}

// Handle Remove from Cart
if (isset($_GET['remove_from_cart'])) {
    $product_id = $_GET['product_id'];
    $user_id = $_SESSION['user_id'];

    try {
        $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = :user_id AND product_id = :product_id");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $stmt->execute();
        echo json_encode(['status' => 'success', 'message' => 'Product removed from cart']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to remove from cart']);
    }
    exit;
}

// Handle Update Quantity with stock validation
if (isset($_POST['update_quantity'])) {
    $product_id = $_POST['product_id'];
    $user_id = $_SESSION['user_id'];
    $quantity = $_POST['quantity'];

    try {
        // First get current stock quantity
        $stmt = $pdo->prepare("SELECT quantity FROM products WHERE product_id = :product_id");
        $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $stmt->execute();
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$product) {
            echo json_encode(['status' => 'error', 'message' => 'Product not found']);
            exit;
        }
        
        // Validate against available stock
        if ($quantity > $product['quantity']) {
            echo json_encode([
                'status' => 'error', 
                'message' => 'Quantity exceeds available stock',
                'max_quantity' => $product['quantity']
            ]);
            exit;
        }

        // Update quantity if validation passes
        $stmt = $pdo->prepare("UPDATE cart SET quantity = :quantity WHERE user_id = :user_id AND product_id = :product_id");
        $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $stmt->execute();
        
        // Re-fetch the current stock quantity in case it changed
        $stmt = $pdo->prepare("SELECT quantity FROM products WHERE product_id = :product_id");
        $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $stmt->execute();
        $updatedProduct = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'status' => 'success', 
            'message' => 'Quantity updated',
            'max_quantity' => $updatedProduct['quantity']
        ]);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update quantity']);
    }
    exit;
}

// Fetch cart items for the logged-in user
$user_id = $_SESSION['user_id'];
// Fetch cart items with product stock information
try {
        $stmt = $pdo->prepare("
        SELECT cart.*, 
            cart.quantity as cart_quantity,
            products.product_name, 
            products.description, 
            products.price, 
            products.image_url, 
            products.category_id, 
            products.quantity as product_quantity,  
            category.category_name
        FROM cart
        INNER JOIN products ON cart.product_id = products.product_id
        INNER JOIN category ON products.category_id = category.category_id
        WHERE cart.user_id = :user_id
    ");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching cart items: " . $e->getMessage());
}

// Calculate total number of items and total price
$totalItems = 0;
$totalPrice = 0;
foreach ($cartItems as $item) {
    $totalItems += $item['cart_quantity'];
    $totalPrice += $item['price'] * $item['cart_quantity'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart | AgroMeds</title>
    
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

        .navbar {
            background-color: var(--primary-color) !important;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: var(--transition);
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: white !important;
        }

        .navbar-brand i {
            margin-right: 8px;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
            font-weight: 500;
            padding: 8px 15px !important;
            border-radius: 4px;
            transition: var(--transition);
            margin: 0 5px;
        }

        .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.15);
            color: white !important;
            transform: translateY(-2px);
        }

        .nav-link.active {
            background-color: rgba(255, 255, 255, 0.25);
            font-weight: 600;
        }

        /* Cart Header */
        .cart-header {
            background: linear-gradient(135deg, var(--primary-color), var(--dark-color));
            color: white;
            padding: 3rem 0;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }

        .cart-header::before {
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

        .cart-header h1 {
            font-weight: 700;
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            text-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .cart-header p {
            font-weight: 300;
            opacity: 0.9;
        }

        /* Summary Cards */
        .summary-card {
            background-color: var(--card-bg);
            border-radius: 12px;
            box-shadow: var(--shadow);
            padding: 1.5rem;
            margin-bottom: 2rem;
            transition: var(--transition);
            border: none;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .summary-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        }

        .summary-card::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
            transition: var(--transition);
        }

        .summary-card:hover::after {
            height: 6px;
        }

        .summary-card i {
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .summary-card h3 {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .summary-card .value {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        /* Cart Items */
        .cart-container {
            background-color: var(--card-bg);
            border-radius: 12px;
            box-shadow: var(--shadow);
            padding: 2rem;
            margin-bottom: 3rem;
        }

        .cart-item {
            display: flex;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            background-color: var(--card-bg);
            border-radius: 10px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .cart-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .cart-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(to bottom, var(--primary-color), var(--accent-color));
            transition: var(--transition);
        }

        .cart-item:hover::before {
            width: 6px;
        }

        .cart-item-image {
            width: 120px;
            height: 120px;
            border-radius: 8px;
            overflow: hidden;
            flex-shrink: 0;
            margin-right: 1.5rem;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            transition: var(--transition);
        }

        .cart-item:hover .cart-item-image {
            transform: scale(1.03);
        }

        .cart-item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: var(--transition);
        }

        .cart-item-content {
            flex-grow: 1;
        }

        .cart-item-title {
            font-weight: 600;
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
            color: var(--text-color);
        }

        .cart-item-category {
            display: inline-block;
            background-color: var(--light-color);
            color: var(--dark-color);
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .cart-item-description {
            color: var(--text-light);
            font-size: 0.9rem;
            margin-bottom: 1rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .cart-item-price {
            font-weight: 700;
            font-size: 1.2rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .cart-item-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .quantity-control {
            display: flex;
            align-items: center;
            background-color: #f5f5f5;
            border-radius: 30px;
            padding: 0.25rem;
        }

        .quantity-btn {
            width: 30px;
            height: 30px;
            border: none;
            background-color: var(--primary-color);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
        }

        .quantity-btn:hover {
            background-color: var(--dark-color);
            transform: scale(1.1);
        }

        .quantity-input {
            width: 40px;
            text-align: center;
            border: none;
            background: transparent;
            font-weight: 600;
        }

        .action-btn {
            width: 40px;
            height: 40px;
            border: none;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
            color: white;
            font-size: 1rem;
        }

        .action-btn.favorite {
            background-color: #FF4081;
        }

        .action-btn.remove {
            background-color: #F44336;
        }

        .action-btn:hover {
            transform: translateY(-3px) scale(1.1);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        }

        /* Empty Cart */
        .empty-cart {
            text-align: center;
            padding: 3rem;
            background-color: var(--card-bg);
            border-radius: 12px;
            box-shadow: var(--shadow);
        }

        .empty-cart i {
            font-size: 4rem;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            opacity: 0.7;
        }

        .empty-cart h3 {
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .empty-cart p {
            color: var(--text-light);
            margin-bottom: 2rem;
        }

        /* Checkout Section */
        .checkout-section {
            background-color: var(--card-bg);
            border-radius: 12px;
            box-shadow: var(--shadow);
            padding: 2rem;
            margin-bottom: 3rem;
        }

        .checkout-total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem 0;
            border-bottom: 1px solid #eee;
        }

        .checkout-total:last-child {
            border-bottom: none;
        }

        .checkout-total .label {
            font-weight: 500;
            color: var(--text-light);
        }

        .checkout-total .value {
            font-weight: 700;
            font-size: 1.2rem;
        }

        .checkout-total.grand-total .value {
            font-size: 1.5rem;
            color: var(--primary-color);
        }

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

        .checkout-btn:active {
            transform: translateY(0);
        }

        /* Footer */
        .footer {
            background-color: var(--dark-color);
            color: white;
            padding: 2rem 0;
            margin-top: 3rem;
        }

        .footer p {
            margin: 0;
            text-align: center;
            opacity: 0.8;
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
            .cart-item {
                flex-direction: column;
            }
            
            .cart-item-image {
                width: 100%;
                height: 200px;
                margin-right: 0;
                margin-bottom: 1rem;
            }
            
            .summary-card {
                margin-bottom: 1rem;
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
        }

        .fab:hover {
            transform: translateY(-5px) scale(1.1);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        }

        /* Badge for cart items */
        .cart-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: #FF4081;
            color: white;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 600;
        }

        /* Theme switcher */
        .theme-switcher {
            position: fixed;
            bottom: 30px;
            left: 30px;
            z-index: 1000;
        }

        .theme-btn {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: none;
            margin: 5px;
            cursor: pointer;
            transition: var(--transition);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .theme-btn:hover {
            transform: scale(1.1);
        }

        .theme-btn.light {
            background: linear-gradient(135deg, #f5f5f5, #e0e0e0);
        }

        .theme-btn.dark {
            background: linear-gradient(135deg, #424242, #212121);
        }

        .theme-btn.green {
            background: linear-gradient(135deg, #4CAF50, #2E7D32);
        }

        /* Floating cart icon */
        .floating-cart {
            position: fixed;
            top: 100px;
            right: 20px;
            background-color: var(--primary-color);
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            cursor: pointer;
            transition: var(--transition);
        }

        .floating-cart:hover {
            transform: translateY(-3px) scale(1.1);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        }

        .floating-cart .badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: #FF4081;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            font-weight: 600;
        }

        /* Google Translate Widget Styling */
.goog-te-banner-frame {
    display: none !important;
}

.goog-te-gadget {
    font-family: inherit !important;
    color: transparent !important;
}

.goog-te-gadget-simple {
    background-color: transparent !important;
    border: none !important;
    padding: 0 !important;
}

.goog-te-menu-value {
    display: none !important;
}

.goog-te-gadget-icon {
    display: none !important;
}

/* Language selector dropdown styling */
.navbar .dropdown-menu {
    min-width: 150px;
}

.language-selector {
    display: flex;
    align-items: center;
    padding: 8px 16px;
}

.language-selector:hover {
    background-color: var(--secondary-color);
    color: white;
}
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-light sticky-top shadow">
            <div class="container">
                <a class="navbar-brand" href="home.php"><i class="fas fa-leaf"></i> <span data-lang-key="logo">AgroMeds</span></a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item"><a class="nav-link" href="home.php" data-lang-key="home">Home</a></li>
                        <li class="nav-item"><a class="nav-link" href="Features.php" data-lang-key="features">Features</a></li>
                        <li class="nav-item"><a class="nav-link" href="products.php" data-lang-key="products">Products</a></li>
                        <li class="nav-item"><a class="nav-link" href="contactUs.php" data-lang-key="contact">Contact</a></li>
                        <?php if ($isLoggedIn): ?>
                            <!-- Profile Dropdown Menu -->
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Profile
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                                    <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                                    <li><a class="dropdown-item" href="favorites.php">Favorites</a></li>
                                    <li><a class="dropdown-item" href="cart.php">Cart</a></li>
                                    <li><a class="dropdown-item" href="orders.php">Orders</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
                                </ul>
                            </li>
                        <?php else: ?>
                            <!-- Login Link -->
                            <li class="nav-item"><a class="nav-link btn btn-outline-primary px-3 ms-2" href="login.php">Login</a></li>
                        <?php endif; ?>
                        
                        <!-- Language Selector Dropdown -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="languageDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-language"></i> <span data-lang-key="language">Language</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="languageDropdown">
                                <li><a class="dropdown-item language-selector" href="#" data-lang="en">English</a></li>
                                <li><a class="dropdown-item language-selector" href="#" data-lang="hi">हिंदी (Hindi)</a></li>
                                <li><a class="dropdown-item language-selector" href="#" data-lang="gu">ગુજરાતી (Gujarati)</a></li>
                            </ul>
                        </li>
                    </ul>
                    <a href="settings.html" class="ms-3 settings-icon"><i class="fas fa-cog"></i> <span data-lang-key="settings"></span></a>
                </div>
            </div>
        </nav>
    <!-- Floating Cart Icon -->
    <div class="floating-cart animate__animated animate__fadeInRight" onclick="window.location.href='cart.php'">
        <i class="fas fa-shopping-cart"></i>
        <span class="badge"><?php echo $totalItems; ?></span>
    </div>

    <!-- Theme Switcher -->
    <div class="theme-switcher animate__animated animate__fadeInLeft">
        <button class="theme-btn light" onclick="changeTheme('light')" title="Light Theme"></button>
        <button class="theme-btn dark" onclick="changeTheme('dark')" title="Dark Theme"></button>
        <button class="theme-btn green" onclick="changeTheme('green')" title="Green Theme"></button>
    </div>

    <!-- Cart Header -->
    <div class="cart-header text-center animate__animated animate__fadeIn">
        <div class="container">
            <h1 class="animate__animated animate__fadeInDown">Your Shopping Cart</h1>
            <p class="animate__animated animate__fadeInUp animate__delay-1s">Review and manage your selected products</p>
        </div>
    </div>

    <div class="container">
        <div class="row">
            <!-- Summary Cards -->
            <div class="col-md-4 animate__animated animate__fadeInLeft">
                <div class="summary-card" onclick="showCartItems()" data-aos="fade-up" data-aos-delay="100">
                    <i class="fas fa-box-open"></i>
                    <h3>Total Items</h3>
                    <div class="value"><?php echo $totalItems; ?></div>
                </div>
                
                <div class="summary-card" onclick="showPriceBreakdown()" data-aos="fade-up" data-aos-delay="200">
                    <i class="fas fa-receipt"></i>
                    <h3>Total Price</h3>
                    <div class="value">₹<?php echo number_format($totalPrice, 2); ?></div>
                </div>
                
                <div class="summary-card" onclick="window.location.href='products.php'" data-aos="fade-up" data-aos-delay="300">
                    <i class="fas fa-store"></i>
                    <h3>Continue Shopping</h3>
                    <p>Add more items to your cart</p>
                </div>
            </div>

            <!-- Cart Items -->
            <div class="col-md-8">
                <div class="cart-container animate__animated animate__fadeInRight">
                    <?php if (!empty($cartItems)): ?>
                        <h2 class="mb-4">Your Selected Products</h2>
                        
                        <?php foreach ($cartItems as $item): ?>
                            <div class="cart-item animate-fadeInUp" data-aos="zoom-in" data-product-id="<?php echo $item['product_id']; ?>">
                                <div class="cart-item-image">
                                    <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                                </div>
                                <div class="cart-item-content">
                                    <h3 class="cart-item-title"><?php echo htmlspecialchars($item['product_name']); ?></h3>
                                    <span class="cart-item-category"><?php echo htmlspecialchars($item['category_name']); ?></span>
                                    <p class="cart-item-description"><?php echo htmlspecialchars($item['description']); ?></p>
                                    <div class="cart-item-price">₹<?php echo number_format($item['price'], 2); ?></div>
                                    <div class="cart-item-stock" style="font-size: 0.8rem; color: #666; margin-bottom: 0.5rem;">
                                        Available: <?php echo $item['quantity']; ?>
                                    </div>
                                    <div class="cart-item-actions">
                                        <div class="quantity-control">
                                            <button class="quantity-btn decrease" onclick="updateQuantity(<?php echo $item['product_id']; ?>, -1)">
                                                <i class="fas fa-minus"></i>
                                            </button>
                                            <input type="number" class="quantity-input" 
                                                value="<?php echo $item['cart_quantity']; ?>" 
                                                min="1" 
                                                max="<?php echo $item['product_quantity']; ?>"
                                                onchange="updateQuantityInput(<?php echo $item['product_id']; ?>, this.value)">
                                            <button class="quantity-btn increase" onclick="updateQuantity(<?php echo $item['product_id']; ?>, 1)">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                        <button class="action-btn favorite" onclick="addToFavorites(<?php echo $item['product_id']; ?>)">
                                            <i class="fas fa-heart"></i>
                                        </button>
                                        <button class="action-btn remove" onclick="removeFromCart(<?php echo $item['product_id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-cart animate__animated animate__fadeIn">
                            <i class="fas fa-shopping-cart"></i>
                            <h3>Your cart is empty</h3>
                            <p>Looks like you haven't added any items to your cart yet.</p>
                            <a href="products.php" class="btn btn-primary btn-lg">
                                <i class="fas fa-store"></i> Start Shopping
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (!empty($cartItems)): ?>
                    <!-- Checkout Section -->
                    <div class="checkout-section animate__animated animate__fadeInUp">
                        <h2 class="mb-4">Order Summary</h2>
                        
                        <div class="checkout-total">
                            <span class="label">Subtotal (<?php echo $totalItems; ?> items)</span>
                            <span class="value">₹<?php echo number_format($totalPrice, 2); ?></span>
                        </div>
                        
                        <div class="checkout-total">
                            <span class="label">Shipping</span>
                            <span class="value">FREE</span>
                        </div>
                        
                        <div class="checkout-total">
                            <span class="label">Tax</span>
                            <span class="value">₹0.00</span>
                        </div>
                        
                        <div class="checkout-total grand-total">
                            <span class="label">Total</span>
                            <span class="value">₹<?php echo number_format($totalPrice, 2); ?></span>
                        </div>
                        
                        <button class="checkout-btn animate-pulse" onclick="checkout()">
                            <i class="fas fa-lock"></i> Proceed to Checkout
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer animate__animated animate__fadeInUp">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> AgroMeds. All rights reserved.</p>
        </div>
    </footer>

    <!-- Floating Action Button -->
    <div class="fab animate__animated animate__bounceInUp animate__delay-1s" onclick="window.scrollTo({top: 0, behavior: 'smooth'})">
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

    // Theme functionality
    function changeTheme(theme) {
        document.body.className = theme;
        localStorage.setItem('theme', theme);
        
        // Safely update navbar color if it exists
        const navbar = document.querySelector('.navbar');
        if (navbar) {
            if (theme === 'dark') {
                navbar.style.backgroundColor = '#343a40';
            } else if (theme === 'green') {
                navbar.style.backgroundColor = '#4CAF50';
            } else {
                navbar.style.backgroundColor = '#f8f9fa';
            }
        }
    }

    // Apply saved theme on page load
    document.addEventListener('DOMContentLoaded', function() {
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.body.className = savedTheme;
        
        // Initialize button states
        document.querySelectorAll('.cart-item').forEach(item => {
            const productId = item.dataset.productId;
            const input = item.querySelector('.quantity-input');
            if (input) {
                const currentQuantity = parseInt(input.value);
                const maxQuantity = parseInt(input.getAttribute('max'));
                updateQuantityButtonStates(productId, currentQuantity, maxQuantity);
            }
        });
    });

    // Show cart items popup
    function showCartItems() {
        Swal.fire({
            title: 'Your Cart Items',
            html: `<?php foreach ($cartItems as $item): ?>
                <div style="display: flex; align-items: center; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #eee;">
                    <img src="<?php echo htmlspecialchars($item['image_url']); ?>" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px; margin-right: 15px;">
                    <div>
                        <h5 style="margin: 0 0 5px 0;"><?php echo htmlspecialchars($item['product_name']); ?></h5>
                        <p style="margin: 0; color: #777;">Quantity: <?php echo $item['quantity']; ?></p>
                        <p style="margin: 0; font-weight: bold; color: #4CAF50;">₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>`,
            showConfirmButton: false,
            showCloseButton: true,
            width: 600
        });
    }

    // Show price breakdown popup
    function showPriceBreakdown() {
        Swal.fire({
            title: 'Price Breakdown',
            html: `<table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 1px solid #eee;">
                        <th style="text-align: left; padding: 10px;">Product</th>
                        <th style="text-align: center; padding: 10px;">Qty</th>
                        <th style="text-align: right; padding: 10px;">Price</th>
                        <th style="text-align: right; padding: 10px;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cartItems as $item): ?>
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 10px;"><?php echo htmlspecialchars(substr($item['product_name'], 0, 20)); ?>...</td>
                            <td style="text-align: center; padding: 10px;"><?php echo $item['quantity']; ?></td>
                            <td style="text-align: right; padding: 10px;">₹<?php echo number_format($item['price'], 2); ?></td>
                            <td style="text-align: right; padding: 10px; font-weight: bold;">₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" style="text-align: right; padding: 10px; font-weight: bold;">Subtotal:</td>
                        <td style="text-align: right; padding: 10px; font-weight: bold;">₹<?php echo number_format($totalPrice, 2); ?></td>
                    </tr>
                    <tr>
                        <td colspan="3" style="text-align: right; padding: 10px; font-weight: bold;">Shipping:</td>
                        <td style="text-align: right; padding: 10px; font-weight: bold;">FREE</td>
                    </tr>
                    <tr>
                        <td colspan="3" style="text-align: right; padding: 10px; font-weight: bold;">Grand Total:</td>
                        <td style="text-align: right; padding: 10px; font-weight: bold; color: #4CAF50;">₹<?php echo number_format($totalPrice, 2); ?></td>
                    </tr>
                </tfoot>
            </table>`,
            showConfirmButton: false,
            showCloseButton: true,
            width: 600
        });
    }

    // Show stock limit alert
    function showStockLimitAlert(maxQuantity) {
        Swal.fire({
            icon: 'warning',
            title: 'Stock Limit Reached',
            text: `Maximum available quantity is ${maxQuantity}`,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000
        });
    }

    // Update all totals in real-time
    function updateAllTotals() {
        let subtotal = 0;
        let totalItems = 0;
        
        document.querySelectorAll('.cart-item').forEach(item => {
            const priceElement = item.querySelector('.cart-item-price');
            const quantityInput = item.querySelector('.quantity-input');
            
            if (priceElement && quantityInput) {
                const price = parseFloat(priceElement.textContent.replace(/[^0-9.]/g, ''));
                const quantity = parseInt(quantityInput.value);
                
                subtotal += price * quantity;
                totalItems += quantity;
            }
        });
        
        // Update summary cards
        const summaryValues = document.querySelectorAll('.summary-card .value');
        if (summaryValues.length >= 2) {
            summaryValues[0].textContent = totalItems;
            summaryValues[1].textContent = `₹${subtotal.toFixed(2)}`;
        }
        
        // Update checkout section
        const checkoutValues = document.querySelectorAll('.checkout-total .value');
        if (checkoutValues.length >= 4) {
            checkoutValues[0].textContent = `₹${subtotal.toFixed(2)}`;
            checkoutValues[3].textContent = `₹${subtotal.toFixed(2)}`;
        }
        
        // Update floating cart badge
        const badge = document.querySelector('.floating-cart .badge');
        if (badge) {
            badge.textContent = totalItems;
        }
    }

    // Update quantity with buttons
    function updateQuantity(productId, change) {
        const input = document.querySelector(`.cart-item[data-product-id="${productId}"] .quantity-input`);
        if (!input) return;
        
        const maxQuantity = parseInt(input.getAttribute('max'));
        let newQuantity = parseInt(input.value) + change;
        
        // Validate quantity
        if (newQuantity < 1) newQuantity = 1;
        if (newQuantity > maxQuantity) {
            newQuantity = maxQuantity;
            showStockLimitAlert(maxQuantity);
        }
        
        input.value = newQuantity;
        updateQuantityButtonStates(productId, newQuantity, maxQuantity);
        updateAllTotals();
        updateQuantityInDatabase(productId, newQuantity);
    }

    // Update quantity with direct input
    function updateQuantityInput(productId, value) {
        const input = document.querySelector(`.cart-item[data-product-id="${productId}"] .quantity-input`);
        if (!input) return;
        
        const maxQuantity = parseInt(input.getAttribute('max'));
        let quantity = parseInt(value) || 1;
        
        // Validate quantity
        if (quantity < 1) quantity = 1;
        if (quantity > maxQuantity) {
            quantity = maxQuantity;
            showStockLimitAlert(maxQuantity);
        }
        
        input.value = quantity;
        updateQuantityButtonStates(productId, quantity, maxQuantity);
        updateAllTotals();
        updateQuantityInDatabase(productId, quantity);
    }

    // Update button states based on quantity
    function updateQuantityButtonStates(productId, currentQuantity, maxQuantity) {
        const increaseBtn = document.querySelector(`.cart-item[data-product-id="${productId}"] .increase`);
        const decreaseBtn = document.querySelector(`.cart-item[data-product-id="${productId}"] .decrease`);
        
        if (increaseBtn) {
            increaseBtn.disabled = currentQuantity >= maxQuantity;
            increaseBtn.style.opacity = currentQuantity >= maxQuantity ? '0.5' : '1';
            increaseBtn.style.cursor = currentQuantity >= maxQuantity ? 'not-allowed' : 'pointer';
        }
        
        if (decreaseBtn) {
            decreaseBtn.disabled = currentQuantity <= 1;
            decreaseBtn.style.opacity = currentQuantity <= 1 ? '0.5' : '1';
            decreaseBtn.style.cursor = currentQuantity <= 1 ? 'not-allowed' : 'pointer';
        }
    }

    // Sync quantity with server
    function updateQuantityInDatabase(productId, quantity) {
        $.ajax({
            url: 'cart.php',
            method: 'POST',
            data: {
                update_quantity: 1,
                product_id: productId,
                quantity: quantity
            },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    const cartItem = document.querySelector(`.cart-item[data-product-id="${productId}"]`);
                    if (cartItem) {
                        cartItem.classList.add('animate__animated', 'animate__pulse');
                        setTimeout(() => {
                            cartItem.classList.remove('animate__animated', 'animate__pulse');
                        }, 1000);
                    }
                    
                    if (response.max_quantity) {
                        const input = document.querySelector(`.cart-item[data-product-id="${productId}"] .quantity-input`);
                        if (input) {
                            input.setAttribute('max', response.max_quantity);
                            updateQuantityButtonStates(productId, parseInt(input.value), response.max_quantity);
                        }
                    }
                } else {
                    Swal.fire('Error', response.message || 'Failed to update quantity', 'error');
                    updateAllTotals(); // Revert to correct values
                }
            },
            error: function() {
                Swal.fire('Error', 'Failed to connect to server', 'error');
                updateAllTotals(); // Revert to correct values
            }
        });
    }

    // Add to favorites
    function addToFavorites(productId) {
        $.ajax({
            url: `cart.php?add_to_favorites=1&product_id=${productId}`,
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    const btn = document.querySelector(`.cart-item[data-product-id="${productId}"] .favorite`);
                    if (btn) {
                        btn.innerHTML = '<i class="fas fa-check"></i>';
                        btn.style.backgroundColor = '#4CAF50';
                        
                        Swal.fire({
                            position: 'top-end',
                            icon: 'success',
                            title: response.message,
                            showConfirmButton: false,
                            timer: 1500,
                            toast: true
                        });
                        
                        setTimeout(() => {
                            btn.innerHTML = '<i class="fas fa-heart"></i>';
                            btn.style.backgroundColor = '#FF4081';
                        }, 2000);
                    }
                } else {
                    Swal.fire('Error', response.message || 'Failed to add to favorites', 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Failed to add to favorites', 'error');
            }
        });
    }

    // Remove from cart
    function removeFromCart(productId) {
        Swal.fire({
            title: 'Remove Item',
            text: 'Are you sure you want to remove this item from your cart?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#4CAF50',
            cancelButtonColor: '#F44336',
            confirmButtonText: 'Yes, remove it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `cart.php?remove_from_cart=1&product_id=${productId}`,
                    method: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            const cartItem = document.querySelector(`.cart-item[data-product-id="${productId}"]`);
                            if (cartItem) {
                                cartItem.classList.add('animate__animated', 'animate__fadeOutRight');
                                setTimeout(() => {
                                    cartItem.remove();
                                    updateAllTotals();
                                }, 500);
                            }
                            
                            Swal.fire('Removed!', response.message, 'success');
                        } else {
                            Swal.fire('Error', response.message || 'Failed to remove item', 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Failed to remove item', 'error');
                    }
                });
            }
        });
    }

    // Checkout function
    function checkout() {
        Swal.fire({
            title: 'Proceed to Checkout',
            html: `You're about to checkout with <strong>${document.querySelector('.summary-card:nth-child(1) .value').textContent} items</strong> for a total of <strong>₹${document.querySelector('.summary-card:nth-child(2) .value').textContent.replace('₹', '')}</strong>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#4CAF50',
            cancelButtonColor: '#F44336',
            confirmButtonText: 'Continue to Payment',
            cancelButtonText: 'Review Cart',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                return new Promise((resolve) => {
                    setTimeout(() => {
                        window.location.href = 'checkout.php';
                        resolve();
                    }, 1000);
                });
            }
        });
    }

    // Floating button animation
    document.addEventListener('scroll', function() {
        const fab = document.querySelector('.fab');
        if (fab) {
            if (window.scrollY > 300) {
                fab.style.opacity = '1';
                fab.style.visibility = 'visible';
                fab.classList.add('animate__fadeIn');
                fab.classList.remove('animate__fadeOut');
            } else {
                fab.classList.add('animate__fadeOut');
                fab.classList.remove('animate__fadeIn');
                setTimeout(() => {
                    fab.style.visibility = 'hidden';
                }, 500);
            }
        }
    });
</script>
<script type="text/javascript">
function googleTranslateElementInit() {
    new google.translate.TranslateElement({
        pageLanguage: 'en',
        includedLanguages: 'en,hi,gu', // Only show English, Hindi, Gujarati
        layout: google.translate.TranslateElement.InlineLayout.SIMPLE,
        autoDisplay: false
    }, 'google_translate_element');
    
    // Restore language selection if previously chosen
    if(localStorage.getItem('googtrans') !== null) {
        var iframe = document.querySelector('.goog-te-menu-frame');
        iframe.onload = function() {
            var select = iframe.contentWindow.document.querySelector('select');
            if(select) {
                select.value = localStorage.getItem('googtrans');
                select.dispatchEvent(new Event('change'));
            }
        };
    }
    
    // Store language selection
    document.addEventListener('click', function(e) {
        if(e.target.closest('.goog-te-menu-value')) {
            setTimeout(function() {
                var iframe = document.querySelector('.goog-te-menu-frame');
                iframe.onload = function() {
                    var select = iframe.contentWindow.document.querySelector('select');
                    if(select) {
                        select.addEventListener('change', function() {
                            localStorage.setItem('googtrans', this.value);
                        });
                    }
                };
            }, 500);
        }
    });
}
</script>
<script type="text/javascript">
// Custom Google Translate implementation
function googleTranslateElementInit() {
    // Create a hidden div for Google Translate
    const translateDiv = document.createElement('div');
    translateDiv.id = 'google_translate_element';
    translateDiv.style.display = 'none';
    document.body.appendChild(translateDiv);
    
    // Initialize Google Translate
    new google.translate.TranslateElement({
        pageLanguage: 'en',
        includedLanguages: 'en,hi,gu',
        layout: google.translate.TranslateElement.InlineLayout.HORIZONTAL,
        autoDisplay: false
    }, 'google_translate_element');
    
    // Handle our custom language selector
    document.querySelectorAll('.language-selector').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const lang = this.getAttribute('data-lang');
            
            // Set the Google Translate cookie
            document.cookie = `googtrans=/en/${lang}; path=/; domain=.${window.location.hostname}`;
            
            // For immediate effect, reload the page
            window.location.reload();
        });
    });
    
    // Remove the Google Translate branding
    const removeBranding = setInterval(() => {
        const branding = document.querySelector('.goog-te-banner-frame');
        if (branding) {
            branding.remove();
            clearInterval(removeBranding);
        }
    }, 100);
    
    // Remove the Google Translate footer
    const removeFooter = setInterval(() => {
        const footer = document.querySelector('.goog-te-footer');
        if (footer) {
            footer.remove();
            clearInterval(removeFooter);
        }
    }, 100);
}

// Load Google Translate script
function loadGoogleTranslate() {
    const script = document.createElement('script');
    script.type = 'text/javascript';
    script.src = '//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit';
    document.body.appendChild(script);
}

// Check if translation is active
function checkTranslation() {
    const googleTransCookie = document.cookie.split(';').find(c => c.trim().startsWith('googtrans='));
    if (googleTransCookie) {
        const langValue = googleTransCookie.split('=')[1];
        if (langValue && langValue !== '/en/en') {
            // Translation is active
            console.log('Translation active:', langValue);
        }
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    loadGoogleTranslate();
    checkTranslation();
});
</script>
<script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
</body>
</html>