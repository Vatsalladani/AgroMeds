<?php
session_start();

// Check if the user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
// Fetch theme and language from session
$theme = isset($_SESSION['theme']) ? $_SESSION['theme'] : 'light';
$language = isset($_SESSION['language']) ? $_SESSION['language'] : 'en';

// Database Connection
$conn = new mysqli('localhost', 'root', '', 'agriculture');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all products
$sqlProducts = "SELECT * FROM products";
$resultProducts = $conn->query($sqlProducts);
$products = [];
if ($resultProducts->num_rows > 0) {
    while ($row = $resultProducts->fetch_assoc()) {
        $row['product_name'] = limitWords($row['product_name'], 7);
        $row['description'] = limitWords($row['description'], 10);
        $products[] = $row;
    }
}

// Pagination settings
$itemsPerPage = 6; // Number of items per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $itemsPerPage;

// Function to limit words in a string
function limitWords($text, $limit = 10) {
    $words = explode(' ', $text);
    return count($words) > $limit ? implode(' ', array_slice($words, 0, $limit)) . '...' : $text;
}

// Fetch total number of products for pagination
$sqlCount = "SELECT COUNT(*) as total FROM products";
$resultCount = $conn->query($sqlCount);
$totalItems = $resultCount->fetch_assoc()['total'];
$totalPages = ceil($totalItems / $itemsPerPage);

// Fetch products with pagination
$sqlProducts = "SELECT * FROM products LIMIT $offset, $itemsPerPage";
$resultProducts = $conn->query($sqlProducts);
$products = [];
if ($resultProducts->num_rows > 0) {
    while ($row = $resultProducts->fetch_assoc()) {
        $row['product_name'] = limitWords($row['product_name'], 7);
        $row['description'] = limitWords($row['description'], 10);
        $products[] = $row;
    }
}


// Function to get current language
function getCurrentLanguage() {
    // Check Google Translate first
    if(isset($_COOKIE['googtrans']) && !empty($_COOKIE['googtrans'])) {
        $gt = $_COOKIE['googtrans'];
        if(strpos($gt, 'en|hi') !== false) return 'hi';
        if(strpos($gt, 'en|gu') !== false) return 'gu';
    }
    
    // Fall back to session language
    return isset($_SESSION['language']) ? $_SESSION['language'] : 'en';
}

// Set the current language
$language = getCurrentLanguage();

// Modify your translation function to check Google first
function translateText($text, $targetLang) {
    // If using Google Translate, return original (Google will handle client-side)
    if(isset($_COOKIE['googtrans']) && !empty($_COOKIE['googtrans'])) {
        return $text;
    }
}


// Fetch best rated products (rating >= 4, limit 4)
// Fetch best selling products (top 4) - corrected
$sqlBestSelling = "SELECT p.* FROM products p 
                  JOIN (SELECT product_id, COUNT(*) as sales 
                        FROM order_items 
                        GROUP BY product_id 
                        ORDER BY sales DESC 
                        LIMIT 4) as t
                  ON p.product_id = t.product_id";
$resultBestSelling = $conn->query($sqlBestSelling);
$bestSellingProducts = [];
if ($resultBestSelling->num_rows > 0) {
    while ($row = $resultBestSelling->fetch_assoc()) {
        $row['product_name'] = limitWords($row['product_name'], 7);
        $row['description'] = limitWords($row['description'], 10);
        $bestSellingProducts[] = $row;
    }
}
// Fetch best rated products (rating >= 4, limit 4)
$sqlBestRated = "SELECT p.*, c.category_name, AVG(r.rating) as avg_rating FROM products p
                LEFT JOIN feedback r ON p.product_id = r.product_id
                LEFT JOIN category c ON p.category_id = c.category_id
                GROUP BY p.product_id
                HAVING avg_rating >= 4
                ORDER BY avg_rating DESC
                LIMIT 4";
$resultBestRated = $conn->query($sqlBestRated);
$bestRatedProducts = [];
if ($resultBestRated->num_rows > 0) {
    while ($row = $resultBestRated->fetch_assoc()) {
        $row['product_name'] = limitWords($row['product_name'], 7);
        $row['description'] = limitWords($row['description'], 10);
        $bestRatedProducts[] = $row;
    }
}


// Fetch categories for filtering
$sqlCategories = "SELECT DISTINCT category_id, category_name FROM category";
$resultCategories = $conn->query($sqlCategories);
$categories = [];
if ($resultCategories->num_rows > 0) {
    while ($row = $resultCategories->fetch_assoc()) {
        $categories[$row['category_id']] = $row['category_name'];
    }
}

// Handle add to cart request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (!$isLoggedIn) {
        echo json_encode(['status' => 'error', 'message' => 'Please login to add items to cart']);
        exit;
    }
    
    $productId = intval($_POST['product_id']);
    $userId = $_SESSION['user_id'];
    
    // Check if product already in cart
    $checkSql = "SELECT * FROM cart WHERE user_id = ? AND product_id = ?";
    $stmt = $conn->prepare($checkSql);
    $stmt->bind_param("ii", $userId, $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Update quantity
        $updateSql = "UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?";
        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param("ii", $userId, $productId);
    } else {
        // Insert new item
        $insertSql = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)";
        $stmt = $conn->prepare($insertSql);
        $stmt->bind_param("ii", $userId, $productId);
    }
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Product added to cart']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to add product to cart']);
    }
    exit;
}

// Handle add to favorites request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_favorites'])) {
    if (!$isLoggedIn) {
        echo json_encode(['status' => 'error', 'message' => 'Please login to add favorites']);
        exit;
    }
    
    $productId = intval($_POST['product_id']);
    $userId = $_SESSION['user_id'];
    
    // Check if already in favorites
$checkSql = "SELECT * FROM favorites WHERE user_id = ? AND item_id = ? AND item_type = 'product'";

    $stmt = $conn->prepare($checkSql);
    $stmt->bind_param("ii", $userId, $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode(['status' => 'info', 'message' => 'Product already in favorites']);
    } else {
        $insertSql = "INSERT INTO favorites (user_id, item_id, item_type) VALUES (?, ?, 'product')";
        $stmt = $conn->prepare($insertSql);
        $stmt->bind_param("ii", $userId, $productId);
        
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Added to favorites']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to add to favorites']);
        }
    }
    exit;
}
?>


<!DOCTYPE html>
<html lang="en" data-theme="<?php echo $theme; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title data-lang-key="products_title">Products - AgroMeds</title>
    <link rel="stylesheet" href="home.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <!-- Animate.css -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <!-- AOS (Animate On Scroll) -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet">
    <style>
        /* Custom Styles */
        :root {
            --primary-color: #28a745;
            --secondary-color: #218838;
            --background-color: #f8f9fa;
            --text-color: #000000;
        }

        body {
            background-color: var(--background-color);
            color: var(--text-color);
        }
        .header {
    background: url('Images/bg7.jpg') no-repeat center center;
    background-size: cover; /* Ensures the entire image fits */
    background-position: center;
    justify-content: center;
    /* width: 1400px; /* Explicitly set width */
    /* height: 600px; Explicitly set height */ 
    color: white;
    padding: 177px 0;
    text-align: center;
}

        .header h1 {
            font-size: 3rem;
            font-weight: bold;
            animation: fadeInDown 1s;
        }

        .header p {
            font-size: 1.2rem;
            animation: fadeInUp 1s;
        }

        .navbar .settings-icon {
            font-size: 1.5rem;
            color: #555;
        }
        /* Default navbar background color */
.navbar {
    background-color: var(--primary-color) !important;
}

/* Override navbar background color for light theme */
[data-theme="light"] .navbar {
    background-color: white !important;
}


        .navbar {
            font-size: 1.039rem;
        }
        .navbar .nav-link {
    color: var(--text-color);
}
/* Navbar Styles */
.navbar {
    background-color: var(--primary-color); /* Use primary color for background */
    color: var(--text-color); /* Use text color for font */
    padding: 10px 0; /* Adjust padding as needed */
    transition: background-color 0.3s ease, color 0.3s ease; /* Smooth transition */
}

.navbar-brand {
    color: var(--text-color) !important; /* Ensure brand text uses theme text color */
    font-weight: bold;
}

.navbar-nav .nav-link {
    color: var(--text-color) !important; /* Ensure nav links use theme text color */
    transition: color 0.3s ease; /* Smooth transition */
}

.navbar-nav .nav-link:hover {
    color: var(--secondary-color) !important; /* Change color on hover */
}

.navbar-toggler {
    border-color: var(--text-color); /* Toggler border color */
}

.navbar-toggler-icon {
    background-image: url("data:image/svg+xml;charset=utf8,%3Csvg viewBox='0 0 30 30' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath stroke='%23000' stroke-width='2' stroke-linecap='round' stroke-miterlimit='10' d='M4 7h22M4 15h22M4 23h22'/%3E%3C/svg%3E");
    filter: invert(1); /* Invert toggler icon color for better visibility */
}

/* Ensure dropdown menu adapts to theme */
.navbar .dropdown-menu {
    background-color: var(--primary-color);
    border: 1px solid var(--secondary-color);
}

.navbar .dropdown-item {
    color: var(--text-color);
}

.navbar .dropdown-item:hover {
    background-color: var(--secondary-color);
    color: var(--text-color);
}

/* Product Cards Container */
.product-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 30px;
    padding: 20px;
}

/* Modern Card Design */
.product-card {
    perspective: 1000px;
    transform-style: preserve-3d;
    transition: all 0.5s cubic-bezier(0.25, 0.8, 0.25, 1);
    position: relative;
    z-index: 1;
}

.product-card:hover {
    transform: translateY(-10px) scale(1.02);
    z-index: 2;
}

.product-card .card {
    border: none;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    transition: all 0.5s cubic-bezier(0.25, 0.8, 0.25, 1);
    background: linear-gradient(145deg, var(--card-bg-color-1), var(--card-bg-color-2));
    color: var(--text-color);
    height: 100%;
    display: flex;
    flex-direction: column;
}

.product-card:hover .card {
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
    transform: translateZ(20px);
}

/* Card Image */
.card-img-container {
    height: 200px;
    overflow: hidden;
    position: relative;
}

.card-img-top {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.8s cubic-bezier(0.25, 0.8, 0.25, 1);
}

.product-card:hover .card-img-top {
    transform: scale(1.1);
}

/* Badges */
.product-badge {
    position: absolute;
    top: 15px;
    right: 15px;
    background-color: var(--primary-color);
    color: white;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: bold;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
    z-index: 3;
}

/* Card Body */
.card-body {
    padding: 20px;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
}

.card-title {
    font-size: 1.2rem;
    font-weight: 700;
    margin-bottom: 10px;
    color: var(--primary-color);
    transition: color 0.3s ease;
}

.product-card:hover .card-title {
    color: var(--secondary-color);
}

.card-text {
    color: var(--text-color);
    flex-grow: 1;
    margin-bottom: 15px;
    line-height: 1.5;
}

.price-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.current-price {
    font-size: 1.3rem;
    font-weight: bold;
    color: var(--primary-color);
}

.original-price {
    text-decoration: line-through;
    color: #999;
    font-size: 0.9rem;
}

.discount-badge {
    background-color: #ff4d4d;
    color: white;
    padding: 3px 8px;
    border-radius: 5px;
    font-size: 0.8rem;
    font-weight: bold;
}

/* Rating Stars */
.rating {
    margin-bottom: 15px;
    display: flex;
    align-items: center;
}

.rating-stars {
    color: #FFD700;
    margin-right: 5px;
}

.rating-count {
    font-size: 0.8rem;
    color: #777;
}

/* Action Buttons */
.card-actions {
    display: flex;
    justify-content: space-between;
    margin-top: auto;
}

.btn-action {
    flex: 1;
    margin: 0 5px;
    padding: 8px 0;
    border-radius: 8px;
    border: none;
    font-weight: 600;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}

.btn-primary-action {
    background-color: var(--primary-color);
    color: white;
}

.btn-primary-action:hover {
    background-color: var(--secondary-color);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.btn-secondary-action {
    background-color: rgba(0, 0, 0, 0.05);
    color: var(--text-color);
}

.btn-secondary-action:hover {
    background-color: rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.btn-action i {
    margin-right: 5px;
}

/* Hover Effects */
.product-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: radial-gradient(circle at center, rgba(255,255,255,0.2) 0%, rgba(255,255,255,0) 70%);
    opacity: 0;
    transition: opacity 0.5s ease;
    pointer-events: none;
}

.product-card:hover::before {
    opacity: 1;
}

/* Theme-specific card colors */
.theme-light {
    --card-bg-color-1: #ffffff;
    --card-bg-color-2: #f8f9fa;
}

.theme-dark {
    --card-bg-color-1: #2d2d2d;
    --card-bg-color-2: #252525;
}

.theme-blue {
    --card-bg-color-1: #e9f5ff;
    --card-bg-color-2: #d4e9ff;
}

.theme-green {
    --card-bg-color-1: #e8f5e9;
    --card-bg-color-2: #d8edd9;
}

.theme-pink {
    --card-bg-color-1: #fff0f6;
    --card-bg-color-2: #ffe0ed;
}

.theme-ocean {
    --card-bg-color-1: #e3f2fd;
    --card-bg-color-2: #d0e7ff;
}

.theme-sunset {
    --card-bg-color-1: #fff3e0;
    --card-bg-color-2: #ffe8cc;
}

.theme-forest {
    --card-bg-color-1: #e8f5e9;
    --card-bg-color-2: #d8edd9;
}

.theme-violet {
    --card-bg-color-1: #f3e5f5;
    --card-bg-color-2: #ead5ee;
}

/* Floating Animation */
@keyframes float {
    0% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
    100% { transform: translateY(0px); }
}

.product-card {
    animation: float 6s ease-in-out infinite;
}

.product-card:nth-child(2n) {
    animation-delay: 0.5s;
}

.product-card:nth-child(3n) {
    animation-delay: 1s;
}

/* Quick View Popup */
.quick-view {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
    z-index: 4;
}

.product-card:hover .quick-view {
    opacity: 1;
}

.quick-view-btn {
    background-color: white;
    color: var(--primary-color);
    border: none;
    padding: 10px 20px;
    border-radius: 30px;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
}

.quick-view-btn:hover {
    background-color: var(--primary-color);
    color: white;
    transform: scale(1.05);
}

/* Ribbon for Featured Products */
.ribbon {
    position: absolute;
    top: 10px;
    left: -5px;
    background-color: var(--primary-color);
    color: white;
    padding: 5px 15px;
    font-size: 0.8rem;
    font-weight: bold;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
    z-index: 3;
}

.ribbon::before {
    content: '';
    position: absolute;
    left: 0;
    bottom: -5px;
    border-left: 5px solid transparent;
    border-right: 5px solid darken(var(--primary-color), 20%);
    border-top: 5px solid darken(var(--primary-color), 20%);
    border-bottom: 5px solid transparent;
}

        .pagination {
    display: flex;
    justify-content: center;
    margin-top: 20px;
}

.pagination button {
    margin: 0 5px;
    padding: 5px 10px;
    border: 1px solid var(--primary-color);
    border-radius: 5px;
    background-color: var(--background-color);
    color: var(--text-color);
    cursor: pointer;
    transition: background-color 0.3s ease, transform 0.2s ease, box-shadow 0.3s ease;
}

.pagination button:hover {
    background-color: var(--primary-color);
    color: white;
    transform: scale(1.1);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.pagination button.active {
    background-color: var(--primary-color);
    color: white;
    transform: scale(1.1);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
}

/* Footer */
footer {
    background-color: var(--primary-color);
    color: var(--text-color);
}
footer {
    background-color: var(--primary-color); /* Use primary color for background */
    color: var(--text-color); /* Use text color for font */
    padding: 80px 0; /* Add padding for spacing */
    text-align: center; /* Center-align content */
    border-top: 1px solid var(--secondary-color); /* Add a border for separation */
}

footer a {
    color: var(--text-color); /* Use text color for links */
    text-decoration: none; /* Remove underline */
    transition: color 0.3s ease; /* Smooth transition */
}

footer a:hover {
    color: var(--secondary-color); /* Change link color on hover */
}

.theme-light footer {
    background-color: #28a745; /* Green background for light theme */
    color: #ffffff; /* White text for better contrast */
    border-top: 1px solid #218838; /* Darker green border */
}

.theme-light footer a {
    color: #ffffff; /* White text for links */
}

.theme-light footer a:hover {
    color: #e8f5e9; /* Light green for hover state */
}

#products,
#bestSelling,
#bestRated {
    background-color: var(--section-background-color);
    padding: 20px;
    border-radius: 10px;
}
/* Theme-Specific Section Backgrounds */
.theme-light {
    --section-background-color: #f8f9fa; /* Light gray */
}

.theme-dark {
    --section-background-color: #343a40; /* Dark gray */
}

.theme-blue {
    --section-background-color: #e9f5ff; /* Light blue */
}

.theme-green {
    --section-background-color: #e8f5e9; /* Light green */
}

.theme-pink {
    --section-background-color: #fff0f6; /* Light pink */
}

.theme-ocean {
    --section-background-color: #e3f2fd; /* Ocean blue */
}

.theme-sunset {
    --section-background-color: #fff3e0; /* Sunset orange */
}

.theme-forest {
    --section-background-color: #e8f5e9; /* Forest green */
}

.theme-violet {
    --section-background-color: #f3e5f5; /* Light violet */
}



/* Theme-Specific Styles */
.theme-light {
    --primary-color: #28a745;
    --secondary-color: #218838;
    --background-color: #ffffff;
    --text-color: #000000;
}

.theme-dark {
    --primary-color: #343a40;
    --secondary-color: #121212;
    --background-color: #1e1e1e;
    --text-color: #ffffff;
}

.theme-blue {
    --primary-color: #007bff;
    --secondary-color: #0056b3;
    --background-color: #e9f5ff;
    --text-color: #000000;
}

.theme-green {
    --primary-color: #28a745;
    --secondary-color: #218838;
    --background-color: #e8f5e9;
    --text-color: #000000;
}

.theme-pink {
    --primary-color: #e83e8c;
    --secondary-color: #d63384;
    --background-color: #fff0f6;
    --text-color: #000000;
}

.theme-ocean {
    --primary-color: #17a2b8;
    --secondary-color: #138496;
    --background-color: #e3f2fd;
    --text-color: #000000;
}

.theme-sunset {
    --primary-color: #ff7f50;
    --secondary-color: #ff6347;
    --background-color: #fff3e0;
    --text-color: #000000;
}

.theme-forest {
    --primary-color: #228b22;
    --secondary-color: #1e7e34;
    --background-color: #e8f5e9;
    --text-color: #000000;
}

.theme-violet {
    --primary-color: #8a2be2;
    --secondary-color: #7b1fa2;
    --background-color: #f3e5f5;
    --text-color: #000000;
}


/* Best Selling/Rated Sections */
.best-products-section {
    position: relative;
    overflow: hidden;
    padding: 60px 0;
    margin: 60px 0;
    background: linear-gradient(135deg, rgba(var(--primary-rgb), 0.1) 0%, rgba(var(--primary-rgb), 0.05) 100%);
}

.best-products-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 5px;
    background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
}

.section-title {
    position: relative;
    display: inline-block;
    margin-bottom: 40px;
    font-size: 2.2rem;
    font-weight: 700;
    color: var(--primary-color);
}

.section-title::after {
    content: '';
    position: absolute;
    bottom: -10px;
    left: 0;
    width: 50%;
    height: 4px;
    background: var(--secondary-color);
    border-radius: 2px;
}

.best-product-card {
    border: none;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    transition: all 0.5s ease;
    background: white;
    position: relative;
}

.best-product-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(var(--primary-rgb), 0.1) 0%, rgba(var(--primary-rgb), 0.05) 100%);
    z-index: 1;
}

.best-product-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
}

.best-product-badge {
    position: absolute;
    top: 15px;
    right: 15px;
    background: var(--primary-color);
    color: white;
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: bold;
    z-index: 2;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
}

.best-product-img {
    height: 180px;
    object-fit: cover;
    transition: transform 0.8s ease;
}

.best-product-card:hover .best-product-img {
    transform: scale(1.1);
}

.best-product-body {
    position: relative;
    z-index: 2;
    padding: 20px;
    background: white;
}

.best-product-title {
    font-size: 1.2rem;
    font-weight: 700;
    margin-bottom: 10px;
    color: var(--primary-color);
}

.best-product-rating {
    display: flex;
    align-items: center;
    margin-bottom: 10px;
}

.best-product-stars {
    color: #FFD700;
    margin-right: 5px;
}

.best-product-price {
    font-size: 1.3rem;
    font-weight: bold;
    color: var(--primary-color);
    margin-bottom: 15px;
}

.best-product-actions {
    display: flex;
    gap: 10px;
}

.best-product-btn {
    flex: 1;
    padding: 8px 0;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}

.best-product-btn-primary {
    background: var(--primary-color);
    color: white;
}

.best-product-btn-primary:hover {
    background: var(--secondary-color);
    transform: translateY(-2px);
}

.best-product-btn-secondary {
    background: rgba(0, 0, 0, 0.05);
    color: var(--text-color);
}

.best-product-btn-secondary:hover {
    background: rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

/* Special badge for best rated */
.best-rated-badge {
    background: linear-gradient(135deg, #FFD700, #FFA500);
    color: #333;
    font-weight: bold;
}

/* Special badge for best selling */
.best-selling-badge {
    background: linear-gradient(135deg, #FF4D4D, #FF0000);
    color: white;
    font-weight: bold;
}

/* Google Translate Widget Styling */
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
<div class="helpline-container text-center py-3">
        <h2 id="helpline" class="animate__animated animate__fadeIn"></h2>
    </div>

    <style>
        .helpline-container {
            background-color: rgb(101, 107, 102);
            color: white;
            font-size: 1.5rem;
            font-weight: bold;
        }
    </style>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const helplineElement = document.getElementById("helpline");
            const helplines = [
           "📞 Helpline: +91 99999 99999", // English
"📞 सहायता केंद्र: +९१ ९९९९९ ९९९९९", // Hindi
"📞 હેલ્પલાઈન: +૯૧ ૯૯૯૯૯ ૯૯૯૯૯" // Gujarati
            ];
            let index = 0;

            function updateHelpline() {
                helplineElement.classList.remove("animate__fadeIn");
                void helplineElement.offsetWidth; // Trigger reflow for animation reset
                helplineElement.classList.add("animate__fadeIn");
                helplineElement.textContent = helplines[index];
                index = (index + 1) % helplines.length;
            }

            updateHelpline(); // Initialize with first helpline
            setInterval(updateHelpline, 3000); // Rotate every 3 seconds
        });
    </script>
    <div id="theme-wrapper" class="theme-light">
    <!-- Header Section -->
    <header class="header">
        <div class="container">
            <h1 class="animate__animated animate__fadeInDown" data-lang-key="products_header_title">Our Products</h1>
            <p class="animate__animated animate__fadeInUp" data-lang-key="products_header_desc">Explore our wide range of high-quality agricultural products.</p>
        </div>
    </header>

    <!-- Navbar -->
    <!-- Navbar -->
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
  

    <!-- Products Section -->
<section id="products" class="py-5">
    <div class="container">
        <h2 class="text-center mb-5" data-aos="fade-down" data-lang-key="products_header_title">
            <i class="fas fa-seedling me-2"></i> Our Premium Products
        </h2>
        
        <div class="search-filter mb-5">
            <div class="input-group">
                <span class="input-group-text bg-primary text-white">
                    <i class="fas fa-search"></i>
                </span>
                <input type="text" id="search" class="form-control form-control-lg" placeholder="Search by name..." aria-label="Search products">
                <button class="btn btn-primary" type="button">
                    <i class="fas fa-filter me-1"></i> Filters
                </button>
            </div>
            
            <div class="advanced-filters mt-3" id="advancedFilters">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="categoryFilter" class="form-label">Category</label>
                        <select id="categoryFilter" class="form-select">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $id => $name) : ?>
                                <option value="<?php echo $id; ?>"><?php echo htmlspecialchars($name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="minPrice" class="form-label">Min Price</label>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="number" id="minPrice" class="form-control" placeholder="Min">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label for="maxPrice" class="form-label">Max Price</label>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="number" id="maxPrice" class="form-control" placeholder="Max">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label for="sortBy" class="form-label">Sort By</label>
                        <select id="sortBy" class="form-select">
                            <option value="popular">Most Popular</option>
                            <option value="newest">Newest First</option>
                            <option value="price-low">Price: Low to High</option>
                            <option value="price-high">Price: High to Low</option>
                            <option value="rating">Highest Rated</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="product-grid" id="productList">
            <?php if (!empty($products)) : ?>
                <?php foreach ($products as $index => $product) : ?>
                   <div class="product-card"
     data-product-id="<?php echo $product['product_id']; ?>"
     data-category="<?php echo $product['category_id']; ?>"
     data-price="<?php echo $product['price']; ?>"
     data-rating="<?php echo rand(3, 5); ?>"
     data-popularity="<?php echo rand(50, 100); ?>"
     data-date="<?php echo date('Y-m-d', strtotime('-'.rand(0, 30).' days')); ?>">
                        <!-- Featured Ribbon (for every 3rd product) -->
                        <?php if ($index % 3 == 0) : ?>
                            <div class="ribbon">Featured</div>
                        <?php endif; ?>
                        
                        <div class="card h-100">
                            <!-- Image Container -->
                            <div class="card-img-container">
                                <img src="<?php echo $product['image_url']; ?>" class="card-img-top" alt="<?php echo $product['product_name']; ?>">
                                <!-- Quick View Button (shown on hover) -->
                                <div class="quick-view">
                                    <button class="quick-view-btn" onclick="quickView(<?php echo $product['product_id']; ?>)">
                                        <i class="fas fa-eye me-1"></i> Quick View
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Product Badge -->
                            <div class="product-badge">
                                <?php 
                                $badges = ['Organic', 'Premium', 'Limited', 'Sale', 'New'];
                                echo $badges[array_rand($badges)]; 
                                ?>
                            </div>
                            
                            <!-- Card Body -->
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($product['product_name']); ?></h5>
                                
                                <!-- Rating -->
                                <div class="rating">
                                    <div class="rating-stars">
                                        <?php 
                                        $rating = rand(3, 5);
                                        for ($i = 1; $i <= 5; $i++) {
                                            echo $i <= $rating ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
                                        }
                                        ?>
                                    </div>
                                    <span class="rating-count">(<?php echo rand(10, 100); ?>)</span>
                                </div>
                                
                                <p class="card-text"><?php echo limitWords(htmlspecialchars($product['description'])); ?></p>
                                
                                <!-- Price -->
                                <div class="price-container">
                                    <span class="current-price">₹<?php echo $product['price']; ?></span>
                                    <?php if (rand(0, 1)) : ?>
                                        <span class="original-price">₹<?php echo $product['price'] + rand(50, 200); ?></span>
                                        <span class="discount-badge"><?php echo rand(10, 30); ?>% OFF</span>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Action Buttons -->
                                <div class="card-actions">
                                    <button class="btn-action btn-primary-action" onclick="addToCart(<?php echo $product['product_id']; ?>)">
                                        <i class="fas fa-shopping-cart"></i> Add
                                    </button>
                                    <!-- To this -->
                                    <button class="btn-action btn-secondary-action" onclick="addToFavorites(<?php echo $product['product_id']; ?>)">
                                        <i class="fas fa-heart"></i> Favorites
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <div class="col-12 text-center py-5">
                    <div class="empty-state">
                        <i class="fas fa-box-open fa-4x mb-3 text-muted"></i>
                        <h4 data-lang-key="no_products">No products available at the moment.</h4>
                        <p class="text-muted">Check back later for our latest products</p>
                        <button class="btn btn-primary mt-3" onclick="window.location.reload()">
                            <i class="fas fa-sync-alt me-1"></i> Refresh
                        </button>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Pagination -->
<nav aria-label="Product pagination" class="mt-5">
    <ul class="pagination justify-content-center">
        <!-- Previous button -->
        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
            <a class="page-link" href="?page=<?php echo $page - 1; ?>" tabindex="-1" aria-disabled="<?php echo $page <= 1 ? 'true' : 'false'; ?>">
                <i class="fas fa-chevron-left"></i>
            </a>
        </li>
        
        <!-- Page numbers -->
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
            </li>
        <?php endfor; ?>
        
        <!-- Next button -->
        <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
            <a class="page-link" href="?page=<?php echo $page + 1; ?>" aria-disabled="<?php echo $page >= $totalPages ? 'true' : 'false'; ?>">
                <i class="fas fa-chevron-right"></i>
            </a>
        </li>
    </ul>
</nav>
    </div>
</section>

    <!-- Best Selling Products Section -->
<section id="bestSelling" class="best-products-section">
    <div class="container">
        <h2 class="section-title text-center" data-aos="fade-down">
            <i class="fas fa-fire me-2"></i> Best Selling Products
        </h2>
        <div class="row">
            <?php if (!empty($bestSellingProducts)) : ?>
                <?php foreach ($bestSellingProducts as $product) : ?>
                    <div class="col-md-3 mb-4">
                        <div class="best-product-card h-100">
                            <div class="best-product-badge best-selling-badge">
                                <i class="fas fa-bolt me-1"></i> Hot Seller
                            </div>
                            <img src="<?php echo $product['image_url']; ?>" class="card-img-top best-product-img" alt="<?php echo $product['product_name']; ?>">
                            <div class="best-product-body">
                                <h5 class="best-product-title"><?php echo htmlspecialchars($product['product_name']); ?></h5>
                                <div class="best-product-rating">
                                    <div class="best-product-stars">
                                        <?php 
                                        $rating = rand(4, 5);
                                        for ($i = 1; $i <= 5; $i++) {
                                            echo $i <= $rating ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
                                        }
                                        ?>
                                    </div>
                                    <span class="rating-count">(<?php echo rand(50, 200); ?>)</span>
                                </div>
                                <div class="best-product-price">₹<?php echo $product['price']; ?></div>
                                <div class="best-product-actions">
                                    <button class="best-product-btn best-product-btn-primary" onclick="addToCart(<?php echo $product['product_id']; ?>)">
                                        <i class="fas fa-shopping-cart me-1"></i> Add
                                    </button>
                                    <button class="best-product-btn best-product-btn-secondary" onclick="addToFavorites(<?php echo $product['product_id']; ?>)">
                                        <i class="fas fa-heart me-1"></i> Fav
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <div class="col-12 text-center py-4">
                    <div class="empty-state">
                        <i class="fas fa-box-open fa-3x mb-3 text-muted"></i>
                        <h5>No best selling products available yet</h5>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Best Rated Products Section -->
<section id="bestRated" class="best-products-section">
    <div class="container">
        <h2 class="section-title text-center" data-aos="fade-down">
            <i class="fas fa-star me-2"></i> Top Rated Products
        </h2>
        <div class="row">
            <?php if (!empty($bestRatedProducts)) : ?>
                <?php foreach ($bestRatedProducts as $product) : ?>
                    <div class="col-md-3 mb-4">
                        <div class="best-product-card h-100">
                            <div class="best-product-badge best-rated-badge">
                                <i class="fas fa-crown me-1"></i> Top Rated
                            </div>
                            <img src="<?php echo $product['image_url']; ?>" class="card-img-top best-product-img" alt="<?php echo $product['product_name']; ?>">
                            <div class="best-product-body">
                                <h5 class="best-product-title"><?php echo htmlspecialchars($product['product_name']); ?></h5>
                                <div class="best-product-rating">
                                    <div class="best-product-stars">
                                        <?php 
                                        $rating = round($product['avg_rating']);
                                        for ($i = 1; $i <= 5; $i++) {
                                            echo $i <= $rating ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
                                        }
                                        ?>
                                    </div>
                                    <span class="rating-count">(<?php echo rand(50, 200); ?>)</span>
                                </div>
                                <div class="best-product-price">₹<?php echo $product['price']; ?></div>
                                <div class="best-product-actions">
                                    <button class="best-product-btn best-product-btn-primary" onclick="addToCart(<?php echo $product['product_id']; ?>)">
                                        <i class="fas fa-shopping-cart me-1"></i> Add
                                    </button>
                                    <button class="best-product-btn best-product-btn-secondary" onclick="addToFavorites(<?php echo $product['product_id']; ?>)">
                                        <i class="fas fa-heart me-1"></i> Fav
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <div class="col-12 text-center py-4">
                    <div class="empty-state">
                        <i class="fas fa-star fa-3x mb-3 text-muted"></i>
                        <h5>No top rated products available yet</h5>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

   <!-- Footer Section -->
   <footer>
    <div class="container">
        <div class="row">
            <!-- About Us Section -->
            <div class="col-md-4">
                <h5 data-lang-key="aboutUs">About Us</h5>
                <p data-lang-key="aboutDescription">AgroMeds is your trusted partner in agriculture, providing high-quality products and expert advice.</p>
                <p data-lang-key="missionStatement">Our mission is to empower farmers with innovative solutions for sustainable agriculture.</p>
            </div>
            
            <!-- Contact Info Section -->
            <div class="col-md-4">
                <h5 data-lang-key="contactInfo">Contact Info</h5>
                <ul class="list-unstyled">
                    <li>
                        <i class="fas fa-map-marker-alt"></i> 
                        <span data-lang-key="addressLabel">Address:</span> 
                        <span data-lang-key="addressText">123 Farm Road, Agro City</span>
                    </li>
                    <li>
                        <i class="fas fa-phone"></i> 
                        <span data-lang-key="phoneLabel">Phone:</span> 
                        <span data-lang-key="phoneNumber">+91 99999 99999</span>
                    </li>
                    <li>
                        <i class="fas fa-envelope"></i> 
                        <span data-lang-key="emailLabel">Email:</span> 
                        <span data-lang-key="emailText">info@agromeds.com</span>
                    </li>
                </ul>

         <!-- Follow Us Section -->
<h5 data-lang-key="followUs">Follow Us</h5>
<ul class="list-inline">
    <!-- LinkedIn -->
    <li class="list-inline-item">
        <a href="https://www.linkedin.com/in/vatsalladani"
           target="_blank"
           rel="noopener noreferrer">
            <i class="fab fa-linkedin"></i>
        </a>
    </li>

    <!-- GitHub -->
    <li class="list-inline-item">
        <a href="https://github.com/Vatsalladani/AgroMeds"
           target="_blank"
           rel="noopener noreferrer">
            <i class="fab fa-github"></i>
        </a>
    </li>

    <!-- Portfolio (Coming Soon) -->
    <li class="list-inline-item">
        <a href="https://vatsalladani.me/"
           title="Portfolio Coming Soon">
            <i class="fas fa-globe"></i>
        </a>
    </li>

</ul>
</div>

            <!-- Quick Links Section -->
            <div class="col-md-4">
                <h5 data-lang-key="quickLinks">Quick Links</h5>
                <ul class="list-unstyled">
                    <li><a href="home.php" data-lang-key="homeLink">Home</a></li>
                    <li><a href="products.php" data-lang-key="productsLink">Products</a></li>
                    <li><a href="features.php" data-lang-key="featuresLink">Features</a></li>
                    <li><a href="contactUs.php" data-lang-key="contactUsLink">Contact Us</a></li>
                </ul>
            </div>
        </div>

        <!-- Copyright Section -->
        <hr />
        <div class="row">
            <div class="col-12 text-center">
              <p class="mb-0" data-lang-key="copyright">
    © 2025 AgroMeds. All rights reserved.
</p>
            </div>
        </div>
    </div>
</footer>
</div>

<!-- Add this right before your closing </body> tag -->
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
<script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
   

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
<script>
// Initialize AOS
AOS.init();

// Quick view redirect
function quickView(productId) {
    window.location.href = `product_details.php?id=${productId}`;
}

// Enhanced notification function
function showNotification(message, type = 'success') {
    // Remove any existing notifications first
    document.querySelectorAll('.product-notification').forEach(el => el.remove());
    
    const colors = {
        success: 'var(--primary-color)',
        error: '#dc3545',
        info: '#17a2b8',
        warning: '#ffc107'
    };
    
    const notification = document.createElement('div');
    notification.className = `product-notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'times-circle' : 'info-circle'}"></i>
            <span>${message}</span>
        </div>
    `;
    
    notification.style.position = 'fixed';
    notification.style.bottom = '20px';
    notification.style.right = '20px';
    notification.style.backgroundColor = colors[type] || colors['success'];
    notification.style.color = 'white';
    notification.style.padding = '15px 25px';
    notification.style.borderRadius = '5px';
    notification.style.boxShadow = '0 5px 15px rgba(0,0,0,0.2)';
    notification.style.zIndex = '10000';
    notification.style.transform = 'translateY(100px)';
    notification.style.opacity = '0';
    notification.style.transition = 'all 0.3s ease';
    notification.style.display = 'flex';
    notification.style.alignItems = 'center';
    notification.style.gap = '10px';
    
    document.body.appendChild(notification);
    
    // Force reflow to enable animation
    void notification.offsetWidth;
    
    // Show notification
    notification.style.transform = 'translateY(0)';
    notification.style.opacity = '1';
    
    // Hide after 3 seconds
    setTimeout(() => {
        notification.style.transform = 'translateY(100px)';
        notification.style.opacity = '0';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Universal response handler
async function handleResponse(response) {
    try {
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            const data = await response.json();
            return data;
        } else {
            const text = await response.text();
            try {
                // Try to parse as JSON in case content-type was wrong
                return JSON.parse(text);
            } catch {
                // If not JSON, return as plain text
                return { 
                    status: response.ok ? 'success' : 'error',
                    message: text || (response.ok ? 'Action completed successfully' : 'An error occurred')
                };
            }
        }
    } catch (error) {
        console.error('Error handling response:', error);
        return {
            status: 'error',
            message: 'Failed to process server response'
        };
    }
}

// Add to cart function with improved error handling
async function addToCart(productId) {
    try {
        const response = await fetch('products.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `add_to_cart=1&product_id=${productId}`
        });
        
        const data = await handleResponse(response);
        
        // Animation for add to cart
        const card = document.querySelector(`.product-card[data-product-id="${productId}"]`);
        if (card) {
            const cartIcon = document.querySelector('.fa-shopping-cart');
            if (cartIcon) {
                const cardRect = card.getBoundingClientRect();
                const cartRect = cartIcon.getBoundingClientRect();
                
                // Create flying item animation
                const flyingItem = document.createElement('div');
                flyingItem.className = 'flying-item';
                flyingItem.innerHTML = '<i class="fas fa-seedling"></i>';
                flyingItem.style.position = 'fixed';
                flyingItem.style.left = `${cardRect.left + cardRect.width/2}px`;
                flyingItem.style.top = `${cardRect.top}px`;
                flyingItem.style.fontSize = '20px';
                flyingItem.style.color = 'var(--primary-color)';
                flyingItem.style.zIndex = '10000';
                flyingItem.style.transition = 'all 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275)';
                document.body.appendChild(flyingItem);
                
                // Animate to cart
                setTimeout(() => {
                    flyingItem.style.left = `${cartRect.left}px`;
                    flyingItem.style.top = `${cartRect.top}px`;
                    flyingItem.style.transform = 'scale(0.5)';
                    flyingItem.style.opacity = '0.5';
                }, 50);
                
                // Remove after animation
                setTimeout(() => {
                    flyingItem.remove();
                    showNotification(data.message || 'Product added to cart successfully', data.status || 'success');
                }, 800);
            }
        }
        
        if (data.status !== 'success') {
            showNotification(data.message || 'Failed to add to cart', data.status || 'error');
            if (data.message && data.message.toLowerCase().includes('login')) {
                setTimeout(() => window.location.href = 'login.php', 1500);
            }
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Failed to add to cart. Please try again.', 'error');
    }
}

// Unified favorites/wishlist function
async function addToFavorites(productId, buttonElement = null) {
    try {
        const response = await fetch('products.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `add_to_favorites=1&product_id=${productId}`
        });
        
        const data = await handleResponse(response);
        
        // Update heart icon if button element is provided
        if (buttonElement) {
            const heartIcon = buttonElement.querySelector('i') || 
                            document.querySelector(`.product-card[data-product-id="${productId}"] .fa-heart`);
            
            if (heartIcon) {
                if (data.status === 'success') {
                    heartIcon.classList.remove('far');
                    heartIcon.classList.add('fas', 'text-danger');
                    heartIcon.style.transform = 'scale(1.5)';
                    setTimeout(() => {
                        heartIcon.style.transform = 'scale(1)';
                    }, 300);
                } else if (data.status === 'removed') {
                    heartIcon.classList.remove('fas', 'text-danger');
                    heartIcon.classList.add('far');
                }
            }
        }
        
        showNotification(data.message || 
                       (data.status === 'success' ? 'Added to favorites' : 
                        data.status === 'removed' ? 'Removed from favorites' : 
                        'Failed to update favorites'), 
                       data.status === 'error' ? 'error' : 'success');
        
        if (data.message && data.message.toLowerCase().includes('login')) {
            setTimeout(() => window.location.href = 'login.php', 1500);
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Failed to update favorites. Please try again.', 'error');
    }
}

// Update your HTML buttons to pass 'this' to the function:
// <button onclick="addToFavorites(123, this)">

// Filter and sort functions remain the same
function filterProducts() {
    const searchTerm = document.getElementById('search').value.toLowerCase();
    const minPrice = parseFloat(document.getElementById('minPrice').value) || 0;
    const maxPrice = parseFloat(document.getElementById('maxPrice').value) || Infinity;
    const category = document.getElementById('categoryFilter').value;
    
    document.querySelectorAll('.product-card').forEach(card => {
        const name = card.querySelector('.card-title').textContent.toLowerCase();
        const price = parseFloat(card.getAttribute('data-price'));
        const cardCategory = card.getAttribute('data-category');
        
        const matchesSearch = name.includes(searchTerm);
        const matchesPrice = price >= minPrice && price <= maxPrice;
        const matchesCategory = category === '' || cardCategory === category;
        
        card.style.display = matchesSearch && matchesPrice && matchesCategory ? 'block' : 'none';
    });
}

function sortProducts() {
    const sortBy = document.getElementById('sortBy').value;
    const container = document.getElementById('productList');
    const cards = Array.from(document.querySelectorAll('.product-card'));
    
    cards.sort((a, b) => {
        switch(sortBy) {
            case 'price-low': return parseFloat(a.getAttribute('data-price')) - parseFloat(b.getAttribute('data-price'));
            case 'price-high': return parseFloat(b.getAttribute('data-price')) - parseFloat(a.getAttribute('data-price'));
            case 'rating': return parseFloat(b.getAttribute('data-rating')) - parseFloat(a.getAttribute('data-rating'));
            case 'popular': return parseFloat(b.getAttribute('data-popularity')) - parseFloat(a.getAttribute('data-popularity'));
            case 'newest': return new Date(b.getAttribute('data-date')) - new Date(a.getAttribute('data-date'));
            default: return 0;
        }
    });
    
    cards.forEach(card => container.appendChild(card));
}

// Initialize event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Search and filter
    document.getElementById('search').addEventListener('input', filterProducts);
    document.getElementById('minPrice').addEventListener('input', filterProducts);
    document.getElementById('maxPrice').addEventListener('input', filterProducts);
    document.getElementById('categoryFilter').addEventListener('change', filterProducts);
    document.getElementById('sortBy').addEventListener('change', sortProducts);
    
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Initialize floating animation delays
    document.querySelectorAll('.product-card').forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
    });
    
    // 3D card hover effects
    document.querySelectorAll(".card").forEach(card => {
        card.addEventListener("mousemove", (e) => {
            const rect = card.getBoundingClientRect();
            const x = (e.clientX - rect.left) / rect.width - 0.5;
            const y = (e.clientY - rect.top) / rect.height - 0.5;
            const tiltX = y * -15;
            const tiltY = x * 15;
            card.style.transform = `rotateX(${tiltX}deg) rotateY(${tiltY}deg) scale(1.05)`;
        });
        
        card.addEventListener("mouseleave", () => {
            card.style.transform = "rotateX(0deg) rotateY(0deg) scale(1)";
        });
    });


// Check on page load
document.addEventListener('DOMContentLoaded', function() {
    checkGoogleTranslate();
    
    // Also check periodically in case user changes language
    setInterval(checkGoogleTranslate, 2000);
});

    // Theme and language handling
    const themeWrapper = document.getElementById('theme-wrapper');
    const theme = localStorage.getItem('theme') || 'light';
    themeWrapper.className = `theme-${theme}`;
    
    // Load translations
    const language = localStorage.getItem('language') || 'en';
    fetch('translation_f.json')
        .then(response => response.json())
        .then(translations => {
            const translation = translations[language] || translations['en'];
            document.querySelectorAll('[data-lang-key]').forEach(element => {
                const key = element.getAttribute('data-lang-key');
                if (translation[key]) {
                    element.textContent = translation[key];
                }
            });
        })
        .catch(error => console.error("Error loading translations:", error));
});
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

// AJAX pagination
document.querySelectorAll('.pagination .page-link').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        const url = this.getAttribute('href');
        
        // Show loading indicator
        document.getElementById('productList').innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
        
        // Load content via AJAX
        fetch(url)
            .then(response => response.text())
            .then(html => {
                // Extract just the product grid from the response
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newContent = doc.getElementById('productList').innerHTML;
                
                // Update the page
                document.getElementById('productList').innerHTML = newContent;
                window.history.pushState({}, '', url);
                
                // Scroll to top
                window.scrollTo({top: 0, behavior: 'smooth'});
            })
            .catch(error => {
                console.error('Error:', error);
                window.location.href = url; // Fallback to normal navigation
            });
    });
});
</script>
</body>
</html>