<?php
session_start();

// Check if the user is logged in
$isLoggedIn = isset($_SESSION['user_id']); // Replace 'user_id' with your session variable for logged-in users
// Fetch theme and language from session
$theme = isset($_SESSION['theme']) ? $_SESSION['theme'] : 'light';
$language = isset($_SESSION['language']) ? $_SESSION['language'] : 'en';


// Database connection details
$host = 'localhost';
$dbname = 'agriculture';
$username = 'root';
$password = '';

// Number of products per page
$productsPerPage = 4;

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Content-Type: text/plain");
    echo "User not logged in";
    exit;
}

$user_id = $_SESSION['user_id'];

// Get total number of products
try {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM favorites
        WHERE user_id = :user_id AND item_type = 'product'
    ");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $totalProducts = $stmt->fetchColumn();
    $totalPages = ceil($totalProducts / $productsPerPage);
} catch (PDOException $e) {
    header("Content-Type: text/plain");
    echo "Error fetching total products: " . $e->getMessage();
    exit;
}

// Get current page number
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) {
    $page = 1;
} elseif ($page > $totalPages) {
    $page = $totalPages;
}

// Calculate offset for SQL query
$offset = ($page - 1) * $productsPerPage;

// Fetch favorites for the current page
try {
    $stmt = $pdo->prepare("
    SELECT
        favorites.*,
        products.product_name,
        products.description,
        products.price,
        products.image_url,
        products.quantity 
    FROM
        favorites
    INNER JOIN
        products ON favorites.item_id = products.product_id
    WHERE
        favorites.user_id = :user_id AND favorites.item_type = 'product'
    LIMIT :limit OFFSET :offset
");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':limit', $productsPerPage, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    header("Content-Type: text/plain");
    echo "Error fetching favorites: " . $e->getMessage();
    exit;
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?php echo $theme; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Favorites | AgroMeds</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <!-- Animate.css -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <!-- Glide.js for carousel -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Glide.js/3.6.0/css/glide.core.min.css">
    <style>
        /* Theme Variables */
        :root {
            --primary-color: #6c5ce7;
            --secondary-color: #a29bfe;
            --accent-color: #fd79a8;
            --background-color: #f8f9fa;
            --text-color: #2d3436;
            --card-bg: #ffffff;
            --border-color: #e0e0e0;
        }

        [data-theme="dark"] {
            --primary-color: #6c5ce7;
            --secondary-color: #a29bfe;
            --accent-color: #fd79a8;
            --background-color: #121212;
            --text-color: #f8f9fa;
            --card-bg: #1e1e1e;
            --border-color: #333;
        }

        [data-theme="blue"] {
            --primary-color: #0984e3;
            --secondary-color: #74b9ff;
            --accent-color: #00cec9;
            --background-color: #f5f6fa;
            --text-color: #2d3436;
            --card-bg: #ffffff;
            --border-color: #e0e0e0;
        }

        [data-theme="green"] {
            --primary-color: #00b894;
            --secondary-color: #55efc4;
            --accent-color: #00cec9;
            --background-color: #f5f6fa;
            --text-color: #2d3436;
            --card-bg: #ffffff;
            --border-color: #e0e0e0;
        }

        [data-theme="pink"] {
            --primary-color: #e84393;
            --secondary-color: #fd79a8;
            --accent-color: #fab1a0;
            --background-color: #fdf2f5;
            --text-color: #2d3436;
            --card-bg: #ffffff;
            --border-color: #e0e0e0;
        }

        [data-theme="ocean"] {
            --primary-color: #0984e3;
            --secondary-color: #00cec9;
            --accent-color: #55efc4;
            --background-color: #f0f8ff;
            --text-color: #2d3436;
            --card-bg: #ffffff;
            --border-color: #e0e0e0;
        }

        [data-theme="sunset"] {
            --primary-color: #e17055;
            --secondary-color: #fab1a0;
            --accent-color: #fdcb6e;
            --background-color: #fff5e6;
            --text-color: #2d3436;
            --card-bg: #ffffff;
            --border-color: #e0e0e0;
        }

        [data-theme="forest"] {
            --primary-color: #00b894;
            --secondary-color: #55efc4;
            --accent-color: #00cec9;
            --background-color: #f0fff4;
            --text-color: #2d3436;
            --card-bg: #ffffff;
            --border-color: #e0e0e0;
        }

        [data-theme="violet"] {
            --primary-color: #6c5ce7;
            --secondary-color: #a29bfe;
            --accent-color: #fd79a8;
            --background-color: #f8f9fa;
            --text-color: #2d3436;
            --card-bg: #ffffff;
            --border-color: #e0e0e0;
        }

        /* Base Styles */
        body {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            transition: all 0.3s ease;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Floating Background Animation */
        .floating-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            opacity: 0.05;
            background: radial-gradient(circle at center, var(--primary-color) 0%, transparent 70%);
            animation: float 15s infinite alternate ease-in-out;
        }

        @keyframes float {
            0% { transform: translate(0, 0); }
            50% { transform: translate(50px, 50px); }
            100% { transform: translate(-50px, -50px); }
        }

        /* Main Container */
        .main-container {
            flex: 1;
            padding: 20px;
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
        }

        /* Header */
        .page-header {
            text-align: center;
            margin: 30px 0;
            position: relative;
        }

        .page-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 15px;
            position: relative;
            display: inline-block;
        }

        .page-header h1::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
            border-radius: 2px;
        }

        /* Product Container */
        #productContainer {
            display: flex;
            flex-wrap: wrap;
            gap: 25px;
            justify-content: center;
            margin: 30px 0;
        }

        /* Product Card */
        .productCard {
            width: 300px;
            height: 420px;
            background-color: var(--card-bg);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            position: relative;
            border: 1px solid var(--border-color);
        }

        .productCard:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        }

        .cardImage {
            width: 100%;
            height: 200px;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .productCard:hover .cardImage {
            transform: scale(1.05);
        }

        .cardBody {
            padding: 20px;
            display: flex;
            flex-direction: column;
            height: calc(100% - 200px);
        }

        .cardTitle {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 10px;
            color: var(--text-color);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .cardText {
            font-size: 0.9rem;
            color: var(--text-color);
            opacity: 0.8;
            margin-bottom: 15px;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            flex-grow: 1;
        }

        .cardPrice {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 15px;
        }

        /* Card Hover Actions */
        .card-actions {
            position: absolute;
            top: 10px;
            right: 10px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            opacity: 0;
            transform: translateY(10px);
            transition: all 0.3s ease;
        }

        .productCard:hover .card-actions {
            opacity: 1;
            transform: translateY(0);
        }

        .action-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: rgba(255, 255, 255, 0.9);
            color: var(--primary-color);
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .action-btn:hover {
            background-color: var(--primary-color);
            color: white;
            transform: scale(1.1);
        }

        /* Pagination */
        .pagination-container {
            display: flex;
            justify-content: center;
            margin: 40px 0;
        }

        .pagination {
            display: flex;
            gap: 10px;
        }

        .page-item {
            list-style: none;
        }

        .page-link {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--card-bg);
            color: var(--text-color);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border: 1px solid var(--border-color);
        }

        .page-item.active .page-link {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .page-item.disabled .page-link {
            opacity: 0.5;
            pointer-events: none;
        }

        .page-link:hover:not(.active) {
            background-color: var(--secondary-color);
            color: white;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 50px 0;
        }

        .empty-state i {
            font-size: 5rem;
            color: var(--secondary-color);
            margin-bottom: 20px;
            opacity: 0.7;
        }

        .empty-state h3 {
            font-size: 1.5rem;
            margin-bottom: 15px;
            color: var(--text-color);
        }

        .empty-state p {
            font-size: 1rem;
            color: var(--text-color);
            opacity: 0.7;
            margin-bottom: 25px;
        }

        .empty-state .btn {
            background-color: var(--primary-color);
            color: white;
            padding: 10px 25px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .empty-state .btn:hover {
            background-color: var(--secondary-color);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        /* Cart Popup */
        .cart-popup {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(5px);
        }

        .cart-popup-content {
            background-color: var(--card-bg);
            padding: 30px;
            border-radius: 20px;
            width: 90%;
            max-width: 400px;
            text-align: center;
            position: relative;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            border: 3px solid var(--primary-color);
            animation: borderAnimation 3s infinite;
        }

        @keyframes borderAnimation {
            0% { border-color: var(--primary-color); }
            25% { border-color: var(--secondary-color); }
            50% { border-color: var(--accent-color); }
            75% { border-color: #ffc107; }
            100% { border-color: var(--primary-color); }
        }

        .close-popup {
            position: absolute;
            top: 15px;
            right: 15px;
            cursor: pointer;
            font-size: 24px;
            color: var(--text-color);
            transition: all 0.3s ease;
        }

        .close-popup:hover {
            color: var(--accent-color);
            transform: rotate(90deg);
        }

        .popup-image {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 15px;
            margin-bottom: 20px;
            border: 3px solid var(--secondary-color);
        }

        .quantity-selector {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 20px 0;
        }

        .quantity-selector button {
            background-color: var(--primary-color);
            color: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            font-size: 1.2rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .quantity-selector button:hover {
            background-color: var(--secondary-color);
            transform: scale(1.1);
        }

        .quantity-selector input {
            width: 60px;
            text-align: center;
            margin: 0 15px;
            border: 2px solid var(--border-color);
            border-radius: 10px;
            padding: 8px;
            font-size: 1.1rem;
            background-color: var(--card-bg);
            color: var(--text-color);
        }

        #confirmAddToCart {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 30px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: all 0.3s ease;
            margin-top: 10px;
            width: 100%;
        }

        #confirmAddToCart:hover {
            background-color: var(--secondary-color);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .success-message {
            margin-top: 15px;
            color: var(--secondary-color);
            font-weight: 600;
            font-size: 1rem;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .success-message.show {
            opacity: 1;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .productCard {
                width: 100%;
                max-width: 350px;
            }

            .page-header h1 {
                font-size: 2rem;
            }

            .cart-popup-content {
                width: 95%;
                padding: 20px;
            }
        }

        /* Theme-specific adjustments */
        [data-theme="dark"] .productCard {
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        [data-theme="dark"] .action-btn {
            background-color: rgba(30, 30, 30, 0.9);
            color: var(--secondary-color);
        }

        [data-theme="dark"] .action-btn:hover {
            background-color: var(--secondary-color);
            color: white;
        }

        /* Floating animation for cards */
        @keyframes floatCard {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }

        .productCard {
            animation: floatCard 6s ease-in-out infinite;
        }

        .productCard:nth-child(2n) {
            animation-delay: 0.5s;
        }

        .productCard:nth-child(3n) {
            animation-delay: 1s;
        }

        /* Gradient text for some elements */
        .gradient-text {
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Navbar Styles */
        .navbar {
            background-color: var(--primary-color) !important;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: var(--transition);
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .navbar-brand i {
            font-size: 1.8rem;
        }

        .navbar .nav-link {
            font-weight: 500;
            padding: 0.5rem 1rem;
            position: relative;
        }

        .navbar .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 0;
            background-color: var(--text-color);
            transition: var(--transition);
        }

        .navbar .nav-link:hover::after {
            width: 100%;
        }

        .navbar .settings-icon {
            font-size: 1.5rem;
            color: var(--text-color);
            transition: transform 0.3s ease;
        }

        .navbar .settings-icon:hover {
            transform: rotate(30deg);
        }
    </style>
</head>
<body>
    <div class="floating-bg"></div>
    
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top shadow">
        <div class="container">
            <a class="navbar-brand" href="home.php"><i class="fas fa-leaf"></i> AgroMeds</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="home.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="products.php">Products</a></li>
                    <li class="nav-item"><a class="nav-link" href="contactUs.php">Contact</a></li>
                    <?php if ($isLoggedIn): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle"></i> Profile
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
                                <li><a class="dropdown-item" href="favorites.php"><i class="fas fa-heart"></i> Favorites</a></li>
                                <li><a class="dropdown-item" href="cart.php"><i class="fas fa-shopping-cart"></i> Cart</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="settings.html"><i class="fas fa-cog"></i></a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-container">
        <div class="page-header animate__animated animate__fadeIn">
            <h1 class="gradient-text"><i class="fas fa-heart"></i> Your Favorites</h1>
            <p>All your loved products in one place</p>
        </div>

        <?php if (!empty($favorites)) : ?>
            <div id="productContainer" class="animate__animated animate__fadeInUp">
                <?php foreach ($favorites as $favorite) : ?>
                    <div class="productCard">
                        <img src="<?php echo htmlspecialchars($favorite['image_url']); ?>" class="cardImage" alt="<?php echo htmlspecialchars($favorite['product_name']); ?>">
                        <div class="cardBody">
                            <h5 class="cardTitle"><?php echo htmlspecialchars($favorite['product_name']); ?></h5>
                            <p class="cardText"><?php echo htmlspecialchars($favorite['description']); ?></p>
                            <p class="cardPrice">₹<?php echo htmlspecialchars(number_format($favorite['price'], 2)); ?></p>
                        </div>
                        <div class="card-actions">
                            <button class="action-btn" onclick="viewDetails(<?php echo htmlspecialchars($favorite['item_id']); ?>)">
                                <i class="fas fa-info-circle"></i>
                            </button>
                            <button class="action-btn" onclick="openAddToCartPopup(
                                <?php echo htmlspecialchars($favorite['item_id']); ?>,
                                '<?php echo htmlspecialchars($favorite['product_name']); ?>',
                                '<?php echo htmlspecialchars($favorite['image_url']); ?>',
                                <?php echo htmlspecialchars($favorite['price']); ?>,
                                '<?php echo htmlspecialchars($favorite['weight'] ?? 'N/A'); ?>',
                                <?php echo htmlspecialchars($favorite['quantity']); ?>
                            )">
                                <i class="fas fa-shopping-cart"></i>
                            </button>
                            <button class="action-btn" onclick="removeFromFavorites(<?php echo htmlspecialchars($favorite['favorite_id']); ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <div class="pagination-container animate__animated animate__fadeIn">
                <ul class="pagination">
                    <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?>"><i class="fas fa-chevron-left"></i></a>
                    </li>
                    <?php for ($i = 1; $i <= $totalPages; $i++) : ?>
                        <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?>"><i class="fas fa-chevron-right"></i></a>
                    </li>
                </ul>
            </div>
        <?php else : ?>
            <div class="empty-state animate__animated animate__fadeIn">
                <i class="fas fa-heart-broken"></i>
                <h3>Your favorites list is empty</h3>
                <p>Start adding products to your favorites to see them here</p>
                <a href="products.php" class="btn">Browse Products</a>
            </div>
        <?php endif; ?>
    </div>

        <!-- Cart Popup -->
        <div id="cartPopup" class="cart-popup">
        <div class="cart-popup-content">
            <span class="close-popup" onclick="closeCartPopup()">&times;</span>
            <img id="popupImage" src="" class="popup-image" alt="Product Image">
            <h3 id="popupTitle"></h3>
            <p id="popupPrice"></p>
            <p id="popupWeight"></p>
            
            <div class="quantity-selector">
                <button onclick="decrementQuantity()"><i class="fas fa-minus"></i></button>
                <input type="number" id="quantityInput" value="1" min="1" max="100">
                <button onclick="incrementQuantity()"><i class="fas fa-plus"></i></button>
            </div>
            
            <button id="confirmAddToCart" onclick="addToCart()">
                <i class="fas fa-cart-plus"></i> Add to Cart
            </button>
            
            <div id="successMessage" class="success-message">
                <i class="fas fa-check-circle"></i> Added to cart successfully!
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5><i class="fas fa-leaf"></i> AgroMeds</h5>
                    <p>Your trusted source for high-quality agricultural and medical products.</p>
                </div>
                <div class="col-md-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="home.php" class="text-white">Home</a></li>
                        <li><a href="products.php" class="text-white">Products</a></li>
                        <li><a href="contactUs.php" class="text-white">Contact Us</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Connect With Us</h5>
                    <div class="social-links">
                    <a href="https://www.linkedin.com/in/vatsalladani"
target="_blank"
class="text-white me-2">
<i class="fab fa-linkedin-in"></i>
</a>

<a href="https://github.com/Vatsalladani"
target="_blank"
class="text-white me-2">
<i class="fab fa-github"></i>
</a>

<a href="javascript:void(0);"
title="Portfolio Coming Soon"
class="text-white">
<i class="fas fa-globe"></i>
</a>
                    </div>
                </div>
            </div>
            <hr>
            <div class="text-center">
                <p class="mb-0">&copy; 2025 AgroMeds. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Glide.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Glide.js/3.6.0/glide.min.js"></script>
    
    <script>
        // Current product being added to cart
        let currentProduct = null;
        
        // Open add to cart popup
        function openAddToCartPopup(itemId, productName, imageUrl, price, weight, stock) {
            currentProduct = {
                id: itemId,
                name: productName,
                image: imageUrl,
                price: price,
                weight: weight,
                stock: stock
            };
            
            document.getElementById('popupImage').src = imageUrl;
            document.getElementById('popupTitle').textContent = productName;
            document.getElementById('popupPrice').textContent = '₹' + price.toFixed(2);
            document.getElementById('popupWeight').textContent = 'Weight: ' + weight;
            document.getElementById('quantityInput').value = 1;
            document.getElementById('quantityInput').max = stock;
            document.getElementById('successMessage').classList.remove('show');
            
            document.getElementById('cartPopup').style.display = 'flex';
        }
        
        // Close cart popup
        function closeCartPopup() {
            document.getElementById('cartPopup').style.display = 'none';
        }
        
        // Increment quantity
        function incrementQuantity() {
            const input = document.getElementById('quantityInput');
            let value = parseInt(input.value);
            if (value < parseInt(input.max)) {
                input.value = value + 1;
            }
        }
        
        // Decrement quantity
        function decrementQuantity() {
            const input = document.getElementById('quantityInput');
            let value = parseInt(input.value);
            if (value > 1) {
                input.value = value - 1;
            }
        }
        
        // Add to cart function
        function addToCart() {
            if (!currentProduct) return;
            
            const quantity = parseInt(document.getElementById('quantityInput').value);
            
            // Here you would typically make an AJAX call to add to cart
            // For demonstration, we'll just show the success message
            document.getElementById('successMessage').classList.add('show');
            
            // Simulate AJAX call
            setTimeout(() => {
                console.log(`Added ${quantity} of ${currentProduct.name} to cart`);
                // In a real application, you would handle the response here
            }, 500);
        }
        
        // Remove from favorites
        function removeFromFavorites(favoriteId) {
            if (confirm('Are you sure you want to remove this item from your favorites?')) {
                // Here you would make an AJAX call to remove the favorite
                console.log(`Removing favorite with ID: ${favoriteId}`);
                // In a real application, you would handle the response and update the UI
                
                // For demonstration, we'll just reload the page
                window.location.reload();
            }
        }
        
        // View product details
        function viewDetails(itemId) {
            // Redirect to product details page
            window.location.href = `product_details.php?id=${itemId}`;
        }
        
        // Close popup when clicking outside
        window.onclick = function(event) {
            const popup = document.getElementById('cartPopup');
            if (event.target === popup) {
                closeCartPopup();
            }
        }
        
        // Initialize tooltips
        document.addEventListener('DOMContentLoaded', function() {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
</body>
</html>