<?php
session_start(); // Start session to check login status

// Check if the user is logged in
$isLoggedIn = isset($_SESSION['user_id']); // Replace 'user_id' with your session variable for logged-in users

// Fetch theme and language from session
$theme = isset($_SESSION['theme']) ? $_SESSION['theme'] : 'light';
$language = isset($_SESSION['language']) ? $_SESSION['language'] : 'en';

// Database Connection
$conn = new mysqli('localhost', 'root', '', 'agriculture');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to limit words in descriptions
function limitWords($text, $limit = 10) {
    $words = explode(' ', $text);
    return count($words) > $limit ? implode(' ', array_slice($words, 0, $limit)) . '...' : $text;
}

// Fetch Seeds (Category ID = 1)
$sqlSeeds = "SELECT * FROM products WHERE category_id = 1 LIMIT 10";
$resultSeeds = $conn->query($sqlSeeds);
$seeds = [];
if ($resultSeeds->num_rows > 0) {
    while ($row = $resultSeeds->fetch_assoc()) {
        $seeds[] = $row;
    }
}

// Fetch Fertilizers (Category ID = 3)
$sqlFertilizers = "SELECT * FROM products WHERE category_id = 3 LIMIT 10";
$resultFertilizers = $conn->query($sqlFertilizers);
$fertilizers = [];
if ($resultFertilizers->num_rows > 0) {
    while ($row = $resultFertilizers->fetch_assoc()) {
        $fertilizers[] = $row;
    }
}

// Fetch Pesticides (Category ID = 5)
$sqlPesticides = "SELECT * FROM products WHERE category_id = 5 LIMIT 10";
$resultPesticides = $conn->query($sqlPesticides);
$pesticides = [];
if ($resultPesticides->num_rows > 0) {
    while ($row = $resultPesticides->fetch_assoc()) {
        $pesticides[] = $row;
    }
}

// Fetch Experts
$sqlExperts = "SELECT * FROM experts LIMIT 10";
$resultExperts = $conn->query($sqlExperts);
$experts = [];
if ($resultExperts->num_rows > 0) {
    while ($row = $resultExperts->fetch_assoc()) {
        $experts[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en" data-theme="<?php echo $theme; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title data-lang-key="features_title">Features - AgroMeds</title>
    <link rel="stylesheet" href="home.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <!-- Animate.css -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <!-- AOS (Animate On Scroll) -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet">
    <!-- Owl Carousel -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Modern CSS Variables */
        :root {
            --primary: #2e7d32;
            --primary-light: #4caf50;
            --primary-dark: #1b5e20;
            --secondary: #ff9800;
            --accent: #ff5722;
            --text-primary: #212121;
            --text-secondary: #757575;
            --background: #f5f5f5;
            --surface: #ffffff;
            --error: #d32f2f;
            --border-radius: 12px;
            --box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        /* Base Styles */
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--background);
            color: var(--text-primary);
            line-height: 1.6;
            overflow-x: hidden;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
        }

        a {
            text-decoration: none;
            transition: var(--transition);
        }

        /* Theme System */
        [data-theme="light"] {
            --primary: #2e7d32;
            --primary-light: #4caf50;
            --primary-dark: #1b5e20;
            --text-primary: #212121;
            --text-secondary: #757575;
            --background: #f5f5f5;
            --surface: #ffffff;
            --card-bg: #ffffff;
            --card-border: rgba(0, 0, 0, 0.1);
        }

        [data-theme="dark"] {
            --primary: #4caf50;
            --primary-light: #66bb6a;
            --primary-dark: #388e3c;
            --text-primary: #f5f5f5;
            --text-secondary: #bdbdbd;
            --background: #121212;
            --surface: #1e1e1e;
            --card-bg: #2d2d2d;
            --card-border: rgba(255, 255, 255, 0.1);
        }

        [data-theme="blue"] {
            --primary: #1976d2;
            --primary-light: #2196f3;
            --primary-dark: #0d47a1;
            --text-primary: #212121;
            --text-secondary: #757575;
            --background: #e3f2fd;
            --surface: #ffffff;
            --card-bg: #e9f5ff;
            --card-border: rgba(25, 118, 210, 0.2);
        }

        [data-theme="green"] {
            --primary: #388e3c;
            --primary-light: #4caf50;
            --primary-dark: #2e7d32;
            --text-primary: #212121;
            --text-secondary: #757575;
            --background: #e8f5e9;
            --surface: #ffffff;
            --card-bg: #f1f8e9;
            --card-border: rgba(56, 142, 60, 0.2);
        }

        [data-theme="sunset"] {
            --primary: #ff7043;
            --primary-light: #ff8a65;
            --primary-dark: #e64a19;
            --text-primary: #212121;
            --text-secondary: #757575;
            --background: #fff3e0;
            --surface: #ffffff;
            --card-bg: #ffecb3;
            --card-border: rgba(255, 112, 67, 0.2);
        }

        /* Header Styles */
        .header {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('Images/bg3.jpeg') center/cover no-repeat;
            color: white;
            padding: 180px 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(ellipse at center, rgba(46, 125, 50, 0.3) 0%, transparent 70%);
            z-index: 0;
        }

        .header h1 {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            position: relative;
            z-index: 1;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .header p {
            font-size: 1.3rem;
            max-width: 700px;
            margin: 0 auto 2rem;
            position: relative;
            z-index: 1;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
        }

        /* Feature Cards */
        .feature-card {
            background: var(--surface);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
            transition: var(--transition);
            height: 100%;
            display: flex;
            flex-direction: column;
            border: 1px solid var(--card-border);
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }

        .feature-card .card-img-top {
            height: 200px;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .feature-card:hover .card-img-top {
            transform: scale(1.05);
        }

        .feature-card .card-body {
            padding: 1.5rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .feature-card .card-title {
            color: var(--primary);
            font-weight: 600;
            margin-bottom: 0.75rem;
        }

        .feature-card .card-text {
            color: var(--text-secondary);
            margin-bottom: 1.25rem;
            flex-grow: 1;
        }

        /* Action Buttons */
        .action-buttons {
            position: absolute;
            top: 15px;
            right: 15px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            opacity: 0;
            transform: translateX(20px);
            transition: all 0.3s ease;
            z-index: 10;
        }

        .feature-card:hover .action-buttons {
            opacity: 1;
            transform: translateX(0);
        }

        .btn-favorite, .btn-details {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.9);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            color: var(--primary);
            border: none;
            cursor: pointer;
        }

        .btn-favorite:hover, .btn-details:hover {
            transform: scale(1.1);
            background: var(--primary);
            color: white;
        }

        .btn-favorite.active {
            color: #ff4081;
        }

        /* Main Button Styles */
        .btn-view-details {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 50px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: var(--transition);
            align-self: flex-start;
            position: relative;
            overflow: hidden;
        }

        .btn-view-details:hover {
            background-color: var(--primary-dark);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .btn-view-details::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 5px;
            height: 5px;
            background: rgba(255, 255, 255, 0.5);
            opacity: 0;
            border-radius: 100%;
            transform: scale(1, 1) translate(-50%);
            transform-origin: 50% 50%;
        }

        .btn-view-details:focus:not(:active)::after {
            animation: ripple 1s ease-out;
        }

        @keyframes ripple {
            0% {
                transform: scale(0, 0);
                opacity: 0.5;
            }
            20% {
                transform: scale(25, 25);
                opacity: 0.3;
            }
            100% {
                opacity: 0;
                transform: scale(40, 40);
            }
        }

        /* Section Styles */
        section {
            padding: 80px 0;
            position: relative;
        }

        section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(46, 125, 50, 0.03) 0%, transparent 100%);
            z-index: -1;
        }

        .section-title {
            text-align: center;
            margin-bottom: 60px;
            position: relative;
        }

        .section-title h2 {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 1rem;
            display: inline-block;
            position: relative;
        }

        .section-title h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: var(--primary);
            border-radius: 2px;
        }

        .section-title p {
            color: var(--text-secondary);
            max-width: 700px;
            margin: 0 auto;
            font-size: 1.1rem;
        }

        /* Feature Overview Cards */
        .feature-overview-card {
            background: var(--surface);
            border-radius: var(--border-radius);
            padding: 30px;
            text-align: center;
            transition: var(--transition);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            height: 100%;
            border: 1px solid var(--card-border);
        }

        .feature-overview-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }

        .feature-overview-icon {
            font-size: 3.5rem;
            color: var(--primary);
            margin-bottom: 20px;
            transition: var(--transition);
        }

        .feature-overview-card:hover .feature-overview-icon {
            transform: scale(1.1);
            color: var(--primary-light);
        }

        .feature-overview-card h4 {
            font-weight: 600;
            margin-bottom: 15px;
            color: var(--primary);
        }

        .feature-overview-card p {
            color: var(--text-secondary);
            margin-bottom: 20px;
        }

        /* Carousel Styles */
        .owl-carousel {
            position: relative;
        }

        .owl-nav {
            position: absolute;
            top: 50%;
            width: 100%;
            display: flex;
            justify-content: space-between;
            transform: translateY(-50%);
            pointer-events: none;
        }

        .owl-prev, .owl-next {
            pointer-events: auto;
            background-color: var(--primary) !important;
            width: 50px;
            height: 50px;
            border-radius: 50% !important;
            color: white !important;
            font-size: 24px !important;
            display: flex !important;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .owl-prev:hover, .owl-next:hover {
            background-color: var(--primary-dark) !important;
            transform: scale(1.1);
        }

        .owl-prev {
            margin-left: -25px;
        }

        .owl-next {
            margin-right: -25px;
        }

        /* Custom Carousel Effects */
        .seeds-carousel .owl-item:not(.center) {
            transform: scale(0.9);
            opacity: 0.7;
            transition: all 0.4s ease;
        }

        .seeds-carousel .owl-item.center {
            transform: scale(1.05);
            opacity: 1;
        }

        .fertilizers-carousel .owl-item:not(.center) {
            transform: scale(0.9) translateY(20px);
            opacity: 0.7;
            transition: all 0.4s ease;
        }

        .fertilizers-carousel .owl-item.center {
            transform: scale(1.05) translateY(0);
            opacity: 1;
            animation: float 3s ease-in-out infinite;
        }

        .pesticides-carousel .owl-item:not(.center) {
            transform: scale(0.9) rotate(-5deg);
            opacity: 0.7;
            transition: all 0.4s ease;
        }

        .pesticides-carousel .owl-item.center {
            transform: scale(1.05) rotate(0deg);
            opacity: 1;
        }

        .experts-carousel .owl-item:not(.center) {
            transform: scale(0.9);
            opacity: 0.7;
            transition: all 0.4s ease;
        }

        .experts-carousel .owl-item.center {
            transform: scale(1.05);
            opacity: 1;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: scale(1.05) translateY(0); }
            50% { transform: scale(1.07) translateY(-10px); }
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1.05); }
            50% { transform: scale(1.07); }
        }

        /* Floating Particles Background */
        .particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: -1;
        }

        .particle {
            position: absolute;
            background: rgba(46, 125, 50, 0.2);
            border-radius: 50%;
            animation: float-particle linear infinite;
        }

        @keyframes float-particle {
            0% {
                transform: translateY(0) translateX(0);
                opacity: 0;
            }
            50% {
                opacity: 0.5;
            }
            100% {
                transform: translateY(-100vh) translateX(100px);
                opacity: 0;
            }
        }

        /* Helpline Banner */
        .helpline-container {
            background: linear-gradient(90deg, var(--primary), var(--primary-light));
            color: white;
            padding: 15px 0;
            position: relative;
            overflow: hidden;
        }

        .helpline-container h2 {
            font-size: 1.3rem;
            font-weight: 500;
            margin: 0;
            position: relative;
            z-index: 1;
        }

        .helpline-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, 
                transparent 0%, 
                rgba(255, 255, 255, 0.2) 50%, 
                transparent 100%);
            animation: shine 3s infinite;
        }

        @keyframes shine {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        /* Responsive Adjustments */
        @media (max-width: 992px) {
            .header {
                padding: 120px 0;
            }
            
            .header h1 {
                font-size: 2.5rem;
            }
            
            .header p {
                font-size: 1.1rem;
            }
            
            .section-title h2 {
                font-size: 2rem;
            }
        }

        @media (max-width: 768px) {
            .header {
                padding: 100px 0;
            }
            
            .header h1 {
                font-size: 2rem;
            }
            
            .section-title h2 {
                font-size: 1.8rem;
            }
            
            .owl-prev, .owl-next {
                width: 40px;
                height: 40px;
                font-size: 18px !important;
            }
        }

        @media (max-width: 576px) {
            .header {
                padding: 80px 0;
            }
            
            .feature-overview-card {
                padding: 20px;
            }
            
            .feature-overview-icon {
                font-size: 2.5rem;
            }
        }

        /* Theme Transition */
        body, .navbar, .feature-card, .feature-overview-card {
            transition: background-color 0.5s ease, color 0.5s ease, border-color 0.5s ease;
        }
    </style>
</head>
<body>
    <!-- Floating Particles Background -->
    <div class="particles" id="particles"></div>

    <!-- Helpline Banner -->
    <div class="helpline-container text-center">
        <h2 id="helpline" class="animate__animated animate__fadeIn"></h2>
    </div>

    <!-- Header Section -->
    <header class="header">
        <div class="container">
            <h1 class="animate__animated animate__fadeInDown" data-lang-key="features_header_title">Explore Our Features</h1>
            <p class="animate__animated animate__fadeInUp" data-lang-key="features_header_desc">Discover high-quality seeds, fertilizers, pesticides, and expert advice tailored for your farming needs.</p>
        </div>
    </header>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="home.php"><i class="fas fa-leaf"></i> <span data-lang-key="logo">AgroMeds</span></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="home.php" data-lang-key="home">Home</a></li>
                    <li class="nav-item"><a class="nav-link active" href="Features.php" data-lang-key="features">Features</a></li>
                    <li class="nav-item"><a class="nav-link" href="products.php" data-lang-key="products">Products</a></li>
                    <li class="nav-item"><a class="nav-link" href="contactUs.php" data-lang-key="contact">Contact</a></li>
                    <?php if ($isLoggedIn): ?>
                        <!-- Profile Dropdown Menu -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user-circle me-1"></i> <span data-lang-key="profile">Profile</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                                <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i>Profile</a></li>
                                <li><a class="dropdown-item" href="favorites.php"><i class="fas fa-heart me-2"></i>Favorites</a></li>
                                <li><a class="dropdown-item" href="cart.php"><i class="fas fa-shopping-cart me-2"></i>Cart</a></li>
                                <li><a class="dropdown-item" href="orders.php"><i class="fas fa-clipboard-list me-2"></i>Orders</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <!-- Login Link -->
                        <li class="nav-item"><a class="nav-link btn btn-outline-light px-3 ms-2" href="login.php"><i class="fas fa-sign-in-alt me-1"></i> Login</a></li>
                    <?php endif; ?>
                    
                    <!-- Language Selector Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="languageDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-language"></i> <span data-lang-key="language">Language</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="languageDropdown">
                            <li><a class="dropdown-item language-selector" href="#" data-lang="en"><i class="flag-icon flag-icon-us me-2"></i>English</a></li>
                            <li><a class="dropdown-item language-selector" href="#" data-lang="hi"><i class="flag-icon flag-icon-in me-2"></i>हिंदी (Hindi)</a></li>
                            <li><a class="dropdown-item language-selector" href="#" data-lang="gu"><i class="flag-icon flag-icon-in me-2"></i>ગુજરાતી (Gujarati)</a></li>
                        </ul>
                    </li>
                    
                    <!-- Theme Selector Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="themeDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-palette"></i> <span data-lang-key="theme">Theme</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="themeDropdown">
                            <li><a class="dropdown-item theme-selector" href="#" data-theme="light"><i class="fas fa-sun me-2"></i>Light</a></li>
                            <li><a class="dropdown-item theme-selector" href="#" data-theme="dark"><i class="fas fa-moon me-2"></i>Dark</a></li>
                            <li><a class="dropdown-item theme-selector" href="#" data-theme="blue"><i class="fas fa-tint me-2"></i>Blue</a></li>
                            <li><a class="dropdown-item theme-selector" href="#" data-theme="green"><i class="fas fa-leaf me-2"></i>Green</a></li>
                            <li><a class="dropdown-item theme-selector" href="#" data-theme="sunset"><i class="fas fa-sun me-2"></i>Sunset</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Features Overview Section -->
    <section id="features-overview" class="py-5">
        <div class="container">
            <div class="section-title" data-aos="fade-up">
                <h2 data-lang-key="features_overview_title">Our Key Features</h2>
                <p data-lang-key="features_overview_desc">Discover the comprehensive solutions we offer to enhance your agricultural productivity</p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-3" data-aos="fade-up" data-aos-delay="100">
                    <div class="feature-overview-card">
                        <div class="feature-overview-icon">
                            <i class="fas fa-seedling"></i>
                        </div>
                        <h4 data-lang-key="seeds_title">Premium Seeds</h4>
                        <p data-lang-key="seeds_overview_desc">High-quality, high-yield seeds for all your farming needs</p>
                        <a href="#seeds" class="btn-view-details" data-lang-key="explore">Explore</a>
                    </div>
                </div>
                
                <div class="col-md-3" data-aos="fade-up" data-aos-delay="200">
                    <div class="feature-overview-card">
                        <div class="feature-overview-icon">
                            <i class="fas fa-tint"></i>
                        </div>
                        <h4 data-lang-key="fertilizers_title">Organic Fertilizers</h4>
                        <p data-lang-key="fertilizers_overview_desc">Nutrient-rich fertilizers for healthy plant growth</p>
                        <a href="#fertilizers" class="btn-view-details" data-lang-key="explore">Explore</a>
                    </div>
                </div>
                
                <div class="col-md-3" data-aos="fade-up" data-aos-delay="300">
                    <div class="feature-overview-card">
                        <div class="feature-overview-icon">
                            <i class="fas fa-bug"></i>
                        </div>
                        <h4 data-lang-key="pesticides_title">Eco Pesticides</h4>
                        <p data-lang-key="pesticides_overview_desc">Effective pest control solutions that are environmentally friendly</p>
                        <a href="#pesticides" class="btn-view-details" data-lang-key="explore">Explore</a>
                    </div>
                </div>
                
                <div class="col-md-3" data-aos="fade-up" data-aos-delay="400">
                    <div class="feature-overview-card">
                        <div class="feature-overview-icon">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <h4 data-lang-key="experts_title">Expert Advice</h4>
                        <p data-lang-key="experts_overview_desc">Connect with agricultural experts for personalized guidance</p>
                        <a href="#experts" class="btn-view-details" data-lang-key="explore">Explore</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Seeds Section -->
    <section id="seeds" class="py-5">
        <div class="container">
            <div class="section-title" data-aos="fade-up">
                <h2 data-lang-key="seeds_section_title">Premium Seeds Collection</h2>
                <p data-lang-key="seeds_section_desc">Browse our selection of high-quality seeds for various crops</p>
            </div>
            
            <div class="owl-carousel seeds-carousel">
                <?php if (!empty($seeds)) : ?>
                    <?php foreach ($seeds as $seed) : ?>
                        <div class="item">
                            <div class="feature-card">
                                <img src="<?php echo $seed['image_url']; ?>" class="card-img-top" alt="Seed Image">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($seed['product_name']); ?></h5>
                                    <p class="card-text"><?php echo limitWords(htmlspecialchars($seed['description'])); ?></p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="price text-primary fw-bold">₹<?php echo number_format($seed['price'], 2); ?></span>
                                        <a href="product_details.php?id=<?php echo $seed['product_id']; ?>" class="btn-view-details" data-lang-key="view_details">
                                            <i class="fas fa-eye me-1"></i> View
                                        </a>
                                    </div>
                                </div>
                                <div class="action-buttons">
                                    <button class="btn-favorite" data-product-id="<?php echo $seed['product_id']; ?>">
                                        <i class="far fa-heart"></i>
                                    </button>
                                    <a href="product_details.php?id=<?php echo $seed['product_id']; ?>" class="btn-details">
                                        <i class="fas fa-info-circle"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <div class="col-12 text-center py-5">
                        <div class="alert alert-info" data-lang-key="no_seeds">
                            No seeds available at the moment. Please check back later.
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="text-center mt-4" data-aos="fade-up">
                <a href="products.php?category=seeds" class="btn btn-primary btn-lg" data-lang-key="view_all_seeds">
                    <i class="fas fa-seedling me-2"></i> View All Seeds
                </a>
            </div>
        </div>
    </section>

    <!-- Fertilizers Section -->
    <section id="fertilizers" class="py-5 bg-light">
        <div class="container">
            <div class="section-title" data-aos="fade-up">
                <h2 data-lang-key="fertilizers_section_title">Quality Fertilizers</h2>
                <p data-lang-key="fertilizers_section_desc">Enhance your soil fertility with our premium fertilizers</p>
            </div>
            
            <div class="owl-carousel fertilizers-carousel">
                <?php if (!empty($fertilizers)) : ?>
                    <?php foreach ($fertilizers as $fertilizer) : ?>
                        <div class="item">
                            <div class="feature-card">
                                <img src="<?php echo $fertilizer['image_url']; ?>" class="card-img-top" alt="Fertilizer Image">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($fertilizer['product_name']); ?></h5>
                                    <p class="card-text"><?php echo limitWords(htmlspecialchars($fertilizer['description'])); ?></p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="price text-primary fw-bold">₹<?php echo number_format($fertilizer['price'], 2); ?></span>
                                        <a href="product_details.php?id=<?php echo $fertilizer['product_id']; ?>" class="btn-view-details" data-lang-key="view_details">
                                            <i class="fas fa-eye me-1"></i> View
                                        </a>
                                    </div>
                                </div>
                                <div class="action-buttons">
                                    <button class="btn-favorite" data-product-id="<?php echo $fertilizer['product_id']; ?>">
                                        <i class="far fa-heart"></i>
                                    </button>
                                    <a href="product_details.php?id=<?php echo $fertilizer['product_id']; ?>" class="btn-details">
                                        <i class="fas fa-info-circle"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <div class="col-12 text-center py-5">
                        <div class="alert alert-info" data-lang-key="no_fertilizers">
                            No fertilizers available at the moment. Please check back later.
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="text-center mt-4" data-aos="fade-up">
                <a href="products.php?category=fertilizers" class="btn btn-primary btn-lg" data-lang-key="view_all_fertilizers">
                    <i class="fas fa-tint me-2"></i> View All Fertilizers
                </a>
            </div>
        </div>
    </section>

    <!-- Pesticides Section -->
    <section id="pesticides" class="py-5">
        <div class="container">
            <div class="section-title" data-aos="fade-up">
                <h2 data-lang-key="pesticides_section_title">Effective Pesticides</h2>
                <p data-lang-key="pesticides_section_desc">Protect your crops with our range of effective pesticides</p>
            </div>
            
            <div class="owl-carousel pesticides-carousel">
                <?php if (!empty($pesticides)) : ?>
                    <?php foreach ($pesticides as $pesticide) : ?>
                        <div class="item">
                            <div class="feature-card">
                                <img src="<?php echo $pesticide['image_url']; ?>" class="card-img-top" alt="Pesticide Image">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($pesticide['product_name']); ?></h5>
                                    <p class="card-text"><?php echo limitWords(htmlspecialchars($pesticide['description'])); ?></p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="price text-primary fw-bold">₹<?php echo number_format($pesticide['price'], 2); ?></span>
                                        <a href="product_details.php?id=<?php echo $pesticide['product_id']; ?>" class="btn-view-details" data-lang-key="view_details">
                                            <i class="fas fa-eye me-1"></i> View
                                        </a>
                                    </div>
                                </div>
                                <div class="action-buttons">
                                    <button class="btn-favorite" data-product-id="<?php echo $pesticide['product_id']; ?>">
                                        <i class="far fa-heart"></i>
                                    </button>
                                    <a href="product_details.php?id=<?php echo $pesticide['product_id']; ?>" class="btn-details">
                                        <i class="fas fa-info-circle"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <div class="col-12 text-center py-5">
                        <div class="alert alert-info" data-lang-key="no_pesticides">
                            No pesticides available at the moment. Please check back later.
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="text-center mt-4" data-aos="fade-up">
                <a href="products.php?category=pesticides" class="btn btn-primary btn-lg" data-lang-key="view_all_pesticides">
                    <i class="fas fa-bug me-2"></i> View All Pesticides
                </a>
            </div>
        </div>
    </section>

    <!-- Experts Section -->
    <section id="experts" class="py-5 bg-light">
        <div class="container">
            <div class="section-title" data-aos="fade-up">
                <h2 data-lang-key="experts_section_title">Agricultural Experts</h2>
                <p data-lang-key="experts_section_desc">Connect with our team of experienced agricultural specialists</p>
            </div>
            
            <div class="owl-carousel experts-carousel">
                <?php if (!empty($experts)) : ?>
                    <?php foreach ($experts as $expert) : ?>
                        <div class="item">
                            <div class="feature-card">
                                <img src="<?php echo $expert['image_url']; ?>" class="card-img-top" alt="Expert Image">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($expert['name']); ?></h5>
                                    <p class="card-text text-muted mb-2">
                                        <i class="fas fa-graduation-cap me-2"></i><?php echo htmlspecialchars($expert['qualification']); ?>
                                    </p>
                                    <p class="card-text"><?php echo limitWords(htmlspecialchars($expert['description'])); ?></p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge bg-primary">
                                            <i class="fas fa-star me-1"></i> <?php echo $expert['rating']; ?>/5
                                        </span>
                                        <a href="expert_details.php?id=<?php echo $expert['expert_id']; ?>" class="btn-view-details" data-lang-key="view_details">
                                            <i class="fas fa-eye me-1"></i> View
                                        </a>
                                    </div>
                                </div>
                                <div class="action-buttons">
                                    <button class="btn-favorite" data-expert-id="<?php echo $expert['expert_id']; ?>">
                                        <i class="far fa-heart"></i>
                                    </button>
                                    <a href="expert_details.php?id=<?php echo $expert['expert_id']; ?>" class="btn-details">
                                        <i class="fas fa-info-circle"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <div class="col-12 text-center py-5">
                        <div class="alert alert-info" data-lang-key="no_experts">
                            No experts available at the moment. Please check back later.
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="text-center mt-4" data-aos="fade-up">
                <a href="experts.php" class="btn btn-primary btn-lg" data-lang-key="view_all_experts">
                    <i class="fas fa-user-tie me-2"></i> View All Experts
                </a>
            </div>
        </div>
    </section>

    <!-- Call to Action Section -->
    <section class="py-5 bg-primary text-white">
        <div class="container text-center" data-aos="zoom-in">
            <h2 class="mb-4" data-lang-key="cta_title">Ready to boost your agricultural productivity?</h2>
            <p class="lead mb-5" data-lang-key="cta_desc">Join thousands of farmers who trust AgroMeds for their farming needs</p>
            <div class="d-flex justify-content-center gap-3">
                <a href="products.php" class="btn btn-light btn-lg px-4" data-lang-key="browse_products">
                    <i class="fas fa-shopping-basket me-2"></i> Browse Products
                </a>
                <a href="contactUs.php" class="btn btn-outline-light btn-lg px-4" data-lang-key="contact_us">
                    <i class="fas fa-envelope me-2"></i> Contact Us
                </a>
            </div>
        </div>
    </section>

    <!-- Footer Section -->
    <footer class="bg-dark text-white pt-5 pb-4">
        <div class="container">
            <div class="row">
                <!-- About Us Section -->
                <div class="col-lg-4 mb-4">
                    <h5 class="text-uppercase mb-4" data-lang-key="aboutUs">About Us</h5>
                    <p data-lang-key="aboutDescription">AgroMeds is your trusted partner in agriculture, providing high-quality products and expert advice.</p>
                    <p data-lang-key="missionStatement">Our mission is to empower farmers with innovative solutions for sustainable agriculture.</p>
                </div>
                
                <!-- Contact Info Section -->
                <div class="col-lg-4 mb-4">
                    <h5 class="text-uppercase mb-4" data-lang-key="contactInfo">Contact Info</h5>
                    <ul class="list-unstyled">
                        <li class="mb-3">
                            <i class="fas fa-map-marker-alt me-3"></i> 
                            <span data-lang-key="addressText">123 Farm Road, Agro City</span>
                        </li>
                        <li class="mb-3">
                            <i class="fas fa-phone me-3"></i> 
                            <span data-lang-key="phoneNumber">+91 99999 99999</span>
                        </li>
                        <li class="mb-3">
                            <i class="fas fa-envelope me-3"></i> 
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
        <a href="https://github.com/Vatsalladani"
           target="_blank"
           rel="noopener noreferrer">
            <i class="fab fa-github"></i>
        </a>
    </li>

    <!-- Portfolio (Coming Soon) -->
    <li class="list-inline-item">
        <a href="javascript:void(0);"
           title="Portfolio Coming Soon">
            <i class="fas fa-globe"></i>
        </a>
    </li>

</ul>
</div>

                <!-- Quick Links Section -->
                <div class="col-lg-4 mb-4">
                    <h5 class="text-uppercase mb-4" data-lang-key="quickLinks">Quick Links</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="home.php" class="text-white" data-lang-key="homeLink">Home</a></li>
                        <li class="mb-2"><a href="products.php" class="text-white" data-lang-key="productsLink">Products</a></li>
                        <li class="mb-2"><a href="features.php" class="text-white" data-lang-key="featuresLink">Features</a></li>
                        <li class="mb-2"><a href="contactUs.php" class="text-white" data-lang-key="contactUsLink">Contact Us</a></li>
                        <li class="mb-2"><a href="privacy.php" class="text-white" data-lang-key="privacyLink">Privacy Policy</a></li>
                        <li class="mb-2"><a href="terms.php" class="text-white" data-lang-key="termsLink">Terms of Service</a></li>
                    </ul>
                </div>
            </div>

            <!-- Copyright Section -->
            <hr class="mb-4" style="border-color: rgba(255,255,255,0.1);">
            <div class="row align-items-center">
                <div class="col-md-6 mb-3 mb-md-0">
                  <p class="mb-0" data-lang-key="copyright">
    © 2025 AgroMeds. All rights reserved.
</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0" data-lang-key="developedBy">Developed with <i class="fas fa-heart text-danger"></i> by AgroMeds Team</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <a href="#" class="btn btn-primary btn-lg back-to-top position-fixed" style="bottom: 20px; right: 20px; display: none; z-index: 1000;">
        <i class="fas fa-arrow-up"></i>
    </a>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
    
    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });

        // Initialize Owl Carousels
        $(document).ready(function() {
            // Seeds Carousel
            $('.seeds-carousel').owlCarousel({
                loop: true,
                margin: 30,
                nav: true,
                center: true,
                dots: false,
                autoplay: true,
                autoplayTimeout: 3000,
                autoplayHoverPause: true,
                responsive: {
                    0: { items: 1 },
                    768: { items: 2 },
                    992: { items: 3 }
                }
            });

            // Fertilizers Carousel
            $('.fertilizers-carousel').owlCarousel({
                loop: true,
                margin: 30,
                nav: true,
                center: true,
                dots: false,
                autoplay: true,
                autoplayTimeout: 3500,
                autoplayHoverPause: true,
                responsive: {
                    0: { items: 1 },
                    768: { items: 2 },
                    992: { items: 3 }
                }
            });

            // Pesticides Carousel
            $('.pesticides-carousel').owlCarousel({
                loop: true,
                margin: 30,
                nav: true,
                center: true,
                dots: false,
                autoplay: true,
                autoplayTimeout: 4000,
                autoplayHoverPause: true,
                responsive: {
                    0: { items: 1 },
                    768: { items: 2 },
                    992: { items: 3 }
                }
            });

            // Experts Carousel
            $('.experts-carousel').owlCarousel({
                loop: true,
                margin: 30,
                nav: true,
                center: true,
                dots: false,
                autoplay: true,
                autoplayTimeout: 4500,
                autoplayHoverPause: true,
                responsive: {
                    0: { items: 1 },
                    768: { items: 2 },
                    992: { items: 3 }
                }
            });
        });

        // Back to Top Button
        $(window).scroll(function() {
            if ($(this).scrollTop() > 300) {
                $('.back-to-top').fadeIn('slow');
            } else {
                $('.back-to-top').fadeOut('slow');
            }
        });

        $('.back-to-top').click(function(e) {
            e.preventDefault();
            $('html, body').animate({scrollTop: 0}, 800, 'easeInOutExpo');
            return false;
        });

        // Floating Particles Background
        function createParticles() {
            const particlesContainer = document.getElementById('particles');
            const particleCount = 30;
            
            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.classList.add('particle');
                
                // Random size between 5px and 15px
                const size = Math.random() * 10 + 5;
                particle.style.width = `${size}px`;
                particle.style.height = `${size}px`;
                
                // Random position
                particle.style.left = `${Math.random() * 100}%`;
                particle.style.top = `${Math.random() * 100 + 100}%`;
                
                // Random animation duration between 10s and 20s
                const duration = Math.random() * 10 + 10;
                particle.style.animationDuration = `${duration}s`;
                
                // Random delay
                particle.style.animationDelay = `${Math.random() * 5}s`;
                
                particlesContainer.appendChild(particle);
            }
        }

        // Helpline Rotator
        function initHelplineRotator() {
            const helplineElement = document.getElementById('helpline');
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
        }

        // Theme Selector
        function initThemeSelector() {
            const themeSelectors = document.querySelectorAll('.theme-selector');
            
            themeSelectors.forEach(selector => {
                selector.addEventListener('click', function(e) {
                    e.preventDefault();
                    const theme = this.getAttribute('data-theme');
                    
                    // Set theme in localStorage
                    localStorage.setItem('theme', theme);
                    
                    // Apply theme to document
                    document.documentElement.setAttribute('data-theme', theme);
                    
                    // Dispatch event for other components to listen to
                    window.dispatchEvent(new Event('themeChanged'));
                });
            });
            
            // Listen for theme changes
            window.addEventListener('themeChanged', function() {
                // You can add additional theme change logic here
            });
        }

        // Language Selector
        function initLanguageSelector() {
            const languageSelectors = document.querySelectorAll('.language-selector');
            
            languageSelectors.forEach(selector => {
                selector.addEventListener('click', function(e) {
                    e.preventDefault();
                    const lang = this.getAttribute('data-lang');
                    
                    // Set language in localStorage
                    localStorage.setItem('language', lang);
                    
                    // Reload translations
                    loadTranslations();
                    
                    // Dispatch event for other components to listen to
                    window.dispatchEvent(new Event('languageChanged'));
                });
            });
        }

        // Load Translations
        function loadTranslations() {
            // Get the selected language from localStorage (default to 'en' if not set)
            const language = localStorage.getItem('language') || 'en';
            
            // Fetch the translations from the JSON file
            fetch('translation_f.json')
                .then(response => response.json())
                .then(translations => {
                    // Get the translation for the selected language (fallback to 'en' if not found)
                    const translation = translations[language] || translations['en'];
                    
                    // Update all elements with the `data-lang-key` attribute
                    document.querySelectorAll('[data-lang-key]').forEach(element => {
                        const key = element.getAttribute('data-lang-key');
                        if (translation[key]) {
                            // Update the text content of the element
                            element.textContent = translation[key];
                        } else {
                            // Log a warning if the translation key is not found
                            console.warn(`Translation not found for key: ${key}`);
                        }
                    });
                })
                .catch(error => console.error("Error loading translations:", error));
        }

        // Favorite Button Handler
        function initFavoriteButtons() {
            document.querySelectorAll('.btn-favorite').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Toggle active state
                    this.classList.toggle('active');
                    
                    // Change icon
                    const icon = this.querySelector('i');
                    if (this.classList.contains('active')) {
                        icon.classList.remove('far', 'fa-heart');
                        icon.classList.add('fas', 'fa-heart', 'text-danger');
                        
                        // Add animation
                        this.classList.add('animate__animated', 'animate__pulse');
                        setTimeout(() => {
                            this.classList.remove('animate__animated', 'animate__pulse');
                        }, 1000);
                    } else {
                        icon.classList.remove('fas', 'fa-heart', 'text-danger');
                        icon.classList.add('far', 'fa-heart');
                    }
                    
                    // Get the item ID (either product or expert)
                    const productId = this.getAttribute('data-product-id');
                    const expertId = this.getAttribute('data-expert-id');
                    const action = this.classList.contains('active') ? 'add' : 'remove';
                    
                    // Prepare data for AJAX request
                    let data = `action=${action}`;
                    if (productId) {
                        data += `&product_id=${productId}`;
                    } else if (expertId) {
                        data += `&expert_id=${expertId}`;
                    }
                    
                    // Send AJAX request to update favorites
                    fetch('update_favorites.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: data
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (!data.success) {
                            // Revert changes if the operation failed
                            this.classList.toggle('active');
                            const icon = this.querySelector('i');
                            if (this.classList.contains('active')) {
                                icon.classList.remove('far', 'fa-heart');
                                icon.classList.add('fas', 'fa-heart', 'text-danger');
                            } else {
                                icon.classList.remove('fas', 'fa-heart', 'text-danger');
                                icon.classList.add('far', 'fa-heart');
                            }
                            
                            // Show error message
                            showToast(data.message || 'Error updating favorites', 'error');
                        } else {
                            // Show success message
                            showToast(
                                action === 'add' ? 'Added to favorites!' : 'Removed from favorites', 
                                'success'
                            );
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showToast('Network error', 'error');
                    });
                });
            });
        }

        // Show Toast Notification
        function showToast(message, type) {
            // Remove existing toasts
            const existingToasts = document.querySelectorAll('.custom-toast');
            existingToasts.forEach(toast => toast.remove());
            
            // Create toast element
            const toast = document.createElement('div');
            toast.className = `custom-toast alert alert-${type} fixed-top`;
            toast.style.top = '20px';
            toast.style.right = '20px';
            toast.style.zIndex = '2000';
            toast.style.maxWidth = '350px';
            toast.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
            toast.style.animation = 'fadeIn 0.3s, fadeOut 0.3s 2.7s';
            
            toast.innerHTML = `
                <div class="d-flex justify-content-between align-items-center">
                    <span>${message}</span>
                    <button type="button" class="btn-close" onclick="this.parentElement.parentElement.remove()"></button>
                </div>
            `;
            
            document.body.appendChild(toast);
            
            // Auto-remove after 3 seconds
            setTimeout(() => {
                toast.remove();
            }, 3000);
        }

        // Initialize everything when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            createParticles();
            initHelplineRotator();
            initThemeSelector();
            initLanguageSelector();
            loadTranslations();
            initFavoriteButtons();
            
            // Apply saved theme on page load
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
        });

        // Google Translate Element Initialization
        function googleTranslateElementInit() {
            new google.translate.TranslateElement({
                pageLanguage: 'en',
                includedLanguages: 'en,hi,gu',
                layout: google.translate.TranslateElement.InlineLayout.SIMPLE,
                autoDisplay: false
            }, 'google_translate_element');
        }
    </script>
</body>
</html>