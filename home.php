<?php
session_start();

// Database connection
$conn = new mysqli('localhost', 'root', '', 'agriculture');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user is logged in AND logged_in value is 0
$isLoggedIn = false; // Default to not logged in
if (isset($_SESSION['user_id'])) {
    $sql = "SELECT logged_in FROM users WHERE user_id = " . $_SESSION['user_id'];
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if ($row['logged_in'] == 1) {
            $isLoggedIn = true; // User is logged in and logged_in is 0
        }
    }
}

// Fetch theme and language from session
$theme = isset($_SESSION['theme']) ? $_SESSION['theme'] : 'light';
$language = isset($_SESSION['language']) ? $_SESSION['language'] : 'en';

// Function to limit words in product descriptions
function limitWords($text, $limit = 13) {
    $words = explode(' ', $text);
    return count($words) > $limit ? implode(' ', array_slice($words, 0, $limit)) . '...' : $text;
}

// Pagination Logic
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 6; // Number of products per page
$offset = ($page - 1) * $limit;

// Fetch total number of products
$sql = "SELECT COUNT(*) AS total FROM products";
$result = $conn->query($sql);
$totalProducts = $result->fetch_assoc()['total'];
$totalPages = ceil($totalProducts / $limit);

// Fetch products for the current page
$sql = "SELECT product_id, product_name, description, price, quantity, category_id, image_url FROM products LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);
$products = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

$sql = "SELECT * FROM experts";
$result = $conn->query($sql);
$experts = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $experts[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en" data-theme="<?php echo $theme; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title data-lang-key="welcome">Agricultural Medicines</title>
    <link rel="stylesheet" href="home.css">
    <link rel="stylesheet" href="theme_home.css">

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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet">

    <style>
        :root {
    --primary-color:rgb(222, 228, 223);
    --secondary-color: #218838;
    --background-color: #ffffff;
    --text-color: #000000;
    --font-family: 'Arial', sans-serif;
}

.theme-light {
    --primary-color:rgb(222, 228, 226);
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

body {
    background-color: var(--background-color);
    color: var(--text-color);
    font-family: var(--font-family);
}

header {
    background-color: var(--primary-color);
    color: var(--text-color);
}

.navbar {
    background-color: var(--primary-color) !important;
}

/* Override navbar background color for light theme */
[data-theme="light"] .navbar {
    background-color: white !important;
}


.navbar {
    background-color: var(--secondary-color);
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

/* Ensure settings icon adapts to theme */
.settings-icon {
    color: var(--text-color);
    transition: color 0.3s ease;
}

.settings-icon:hover {
    color: var(--secondary-color);
}   

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
/* Light Theme Specific Footer */
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

.card {
    background-color: var(--background-color);
    color: var(--text-color);
    border: 1px solid var(--primary-color);
}

.btn {
    background-color: var(--primary-color);
    color: var(--text-color);
    border: 1px solid var(--secondary-color);
}
        /* Card Styles */
.card {
    position: relative;
    overflow: hidden;
    border: 1px solid var(--primary-color);
    transition: transform 0.3s ease;
}

.card:hover {
    transform: scale(1.05);
}

.card-img-top {
    width: 100%;
    height: 200px;
    object-fit: cover;
}

.card-body {
    padding: 15px;
}

/* Hover Buttons */
.card-hover-buttons {
    position: absolute;
    top: 10px;
    right: 10px;
    display: none;
    flex-direction: column;
    gap: 5px;
}

.card:hover .card-hover-buttons {
    display: flex;
}

.card-hover-buttons button {
    background-color: rgba(255, 255, 255, 0.8);
    border: none;
    border-radius: 50%;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.card-hover-buttons button:hover {
    background-color: rgba(255, 255, 255, 1);
}

        /* Apply theme variables to the Features section */
        #features {
            background-color: var(--primary-color);
            color: var(--text-color);
            padding: 20px;
            /* Adjust padding as needed */
        }

        #features h4 {
            color: var(--text-color);
        }

        #features p {
            color: var(--text-color);
        }

        #features i {
            color: var(--text-color);
            /* Icon color */
        }

        /* Light theme-specific styles for the Features section */
        .theme-light #features {
            background-color: var(--primary-color);
            /* Use primary color for background */
        }

        .theme-light #features h4,
        .theme-light #features p,
        .theme-light #features i {
            color: green;
            /* Set text and icon color to green in light theme */
        }

        /* Ensure the Features container covers the entire width */
        #features .container {
            width: 100%;
            max-width: 100%;
            /* Ensure full width */
            padding: 0;
            /* Remove default container padding */
        }

        #features .col-md-4 {
            padding: 20px;
            /* Add padding to columns */
        }

        .btn {
            background-color: var(--primary-color);
            color: var(--text-color);
            border: 1px solid var(--secondary-color);
        }
 

        #about-us .about-text {
            flex: 1;
            padding-right: 20px;
        }

        #about-us .about-images {
            flex: 1;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
        }

        #about-us .about-image {
            width: 45%; /* Adjust as needed */
            margin-bottom: 20px;
            text-align: center;
        }

        #about-us img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
        }

        #about-us h2 {
            color: var(--primary-color);
            margin-bottom: 30px;

        }

        #about-us p {
            font-size: 16px;
            line-height: 1.6;
        }

        /* Barrel-Style Carousel */
.barrel-carousel .owl-stage-outer {
    padding: 20px 0; /* Add padding to avoid clipping */
}

.barrel-carousel .owl-item {
    transition: transform 0.3s ease, opacity 0.3s ease;
    opacity: 0.6; /* Make non-center items slightly transparent */
    transform: scale(0.8); /* Make non-center items smaller */
}

.barrel-carousel .owl-item.center {
    opacity: 1; /* Make the center item fully visible */
    transform: scale(1); /* Make the center item larger */
}

/* Ensure all cards have the same height and width */
.barrel-carousel .card {
    height: 110%;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.barrel-carousel .card-img-top {
    height: 250px;
    object-fit: cover;
}

.barrel-carousel .card-body {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

/* Limit product name to 7 words */
.barrel-carousel .card-title {
    display: -webkit-box;
    -webkit-line-clamp: 2; /* Show 2 lines max */
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
    font-size: 1.1rem;
    margin-bottom: 10px;
}

#products,
#experts {
    background-color: var(--background-color); /* Theme-based background */
    padding: 50px 0; /* Add padding for better spacing */
}

/* Custom navigation buttons for Owl Carousel */
.owl-carousel .owl-nav button {
    background-color: var(--primary-color) !important;
    color: var(--text-color) !important;
    border: 2px solid var(--secondary-color) !important;
    border-radius: 50% !important;
    width: 40px;
    height: 40px;
    font-size: 20px !important;
    line-height: 40px !important;
    transition: all 0.3s ease;
}

.owl-carousel .owl-nav button:hover {
    background-color: var(--secondary-color) !important;
    color: var(--primary-color) !important;
    transform: scale(1.1);
}

.owl-carousel .owl-nav {
    position: absolute;
    top: 50%;
    width: 100%;
    display: flex;
    justify-content: space-between;
    transform: translateY(-50%);
    padding: 0 20px;
}

.owl-carousel .owl-nav button.owl-prev,
.owl-carousel .owl-nav button.owl-next {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
}

.owl-carousel .owl-nav button.owl-prev {
    left: -50px; /* Adjust position */
}

.owl-carousel .owl-nav button.owl-next {
    right: -50px; /* Adjust position */
}

#experts .card {
    height: 100%; /* Ensure all cards have the same height */
    width: 100%;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

#experts .card-img-top {
    height: 200px; /* Fixed height for images */
    object-fit: cover; /* Ensure images cover the area */
}

#experts .card-body {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.notification {
    position: fixed;
    bottom: 20px;
    right: 20px;
    padding: 15px 20px;
    border-radius: 5px;
    color: white;
    font-size: 14px;
    z-index: 1000;
    animation: slideIn 0.5s ease-out;
}

.notification.success {
    background-color: #28a745;
}

.notification.error {
    background-color: #dc3545;
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
    }
    to {
        transform: translateX(0);
    }
}
    </style>
    <style>
    /* Hover Effect for Dropdown Items */
    .hover-effect:hover {
        background-color: #f8f9fa;
        color: #007bff !important;
    }

    /* Dropdown Menu Styling */
    .profile-dropdown {
        display: none; /* Initially hidden */
    }

    .profile-dropdown.show {
        display: block !important; /* Show on hover */
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

<!-- Include Settings Script -->
<script src="settings.js"></script>

<!-- Add this inside the <body> section where you want the headline -->
<div class="helpline-container text-center py-3">
    <h2 id="helpline" class="animate__animated animate__fadeIn"></h2>
</div>

<!-- Add this CSS inside the <head> section or your external CSS file -->
<style>
    .helpline-container {
        background-color: rgb(101, 107, 102);
        color: white;
        font-size: 1.5rem;
        font-weight: bold;
    }
</style>

<!-- Add this script before the closing </body> tag -->
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

<div id="theme-wrapper" class="theme-<?php echo $theme; ?>">

     <!-- Header Section -->
     <header class="header">
    <h1 class="animate__animated animate__fadeInDown" data-lang-key="welcome">Welcome to AgroMeds</h1>
    <p class="animate__animated animate__fadeInUp" data-lang-key="about_desc">Your Trusted Source for Agricultural Solutions</p>
    <a href="#products" class="btn btn-light btn-lg animate__animated animate__bounceIn" data-lang-key="explore">Explore Products</a>
</header>

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

   <!-- Features Section -->
<section id="features" class="features py-5">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-4" data-aos="fade-up">
                <i class="fas fa-handshake"></i>
                <h4 class="mt-3" data-lang-key="feature_expert_consultation">Expert Consultation</h4>
                <p data-lang-key="feature_expert_consultation_desc">We provide professional consultancy for crops and address all your farming-related doubts.</p>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                <i class="fas fa-seedling"></i>
                <h4 class="mt-3" data-lang-key="feature_quality_seeds">High-Quality Seeds</h4>
                <p data-lang-key="feature_quality_seeds_desc">Premium seeds for a better yield and healthy crops.</p>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="400">
                <i class="fas fa-prescription-bottle-alt"></i>
                <h4 class="mt-3" data-lang-key="feature_effective_medicines">Effective Medicines</h4>
                <p data-lang-key="feature_effective_medicines_desc">Wide range of pesticides, herbicides, and fertilizers.</p>
            </div>
        </div>
    </div>
</section>

<!-- About Us Section -->
<section id="about-us" class="py-5">
    <div class="container">
        <h2 class="text-center mb-5" data-aos="fade-down" data-lang-key="a_us">About AgroMeds</h2>
        <div class="row align-items-center">
            <div class="col-md-6">
                <p class="lead" data-aos="fade-right" data-lang-key="desc" style="color: var(--text-color);">
                    AgroMeds is your trusted partner in agriculture...
                </p>
            </div>
            <div class="col-md-6">
                <div class="row">
                    <div class="col-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                        <div class="card h-100 shadow-sm">
                            <img src="images/Feature2.jpg" class="card-img-top" alt="Products">
                            <div class="card-body">
                                <p class="card-text text-center" style="color: var(--text-color);" data-lang-key="quality_seeds">High-Quality Products</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                        <div class="card h-100 shadow-sm">
                            <img src="images/Feature3.jpg" class="card-img-top" alt="Medicines">
                            <div class="card-body">
                                <p class="card-text text-center" style="color: var(--text-color);" data-lang-key="effective_medicines">Effective Medicines</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 mb-4" data-aos="fade-up" data-aos-delay="300">
                        <div class="card h-100 shadow-sm">
                            <img src="images/Feature1.jpg" class="card-img-top" alt="Consultancy">
                            <div class="card-body">
                                <p class="card-text text-center" style="color: var(--text-color);" data-lang-key="expert_consultation">Expert Consultancy</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 mb-4" data-aos="fade-up" data-aos-delay="400">
                        <div class="card h-100 shadow-sm">
                            <img src="images/Feature1.jpg" class="card-img-top" alt="Communication">
                            <div class="card-body">
                                <p class="card-text text-center" style="color: var(--text-color);" data-lang-key="seamless_communication">Seamless Communication</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

    <!-- Products Section with Barrel-Style Carousel -->
<!-- Products Section -->
<section id="products" class="py-5">
    <div class="container">
      <h2 class="text-center mb-5" data-aos="fade-down" data-lang-key="our_products">
    Our Products
</h2>
        <div class="owl-carousel owl-theme barrel-carousel">
            <?php if (!empty($products)) : ?>
                <?php foreach ($products as $product) : ?>
                    <div class="item">
                        <div class="card">
                            <img src="<?php echo $product['image_url']; ?>" class="card-img-top" alt="Product Image">
                            <div class="card-hover-buttons">
                            <div class="card-hover-buttons">
    <button title="Add to Favorites" data-id="<?php echo $product['product_id']; ?>" data-type="product" class="btn-favorite">
        <i class="fas fa-heart"></i>
    </button>
    <button title="Add to Cart" data-id="<?php echo $product['product_id']; ?>" data-type="product" class="btn-cart">
        <i class="fas fa-shopping-cart"></i>
    </button>
    <button title="View Details" data-id="<?php echo $product['product_id']; ?>" data-type="product" class="btn-details">
        <i class="fas fa-info-circle"></i>
    </button>
</div>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title">
                                    <?php
                                    $name = htmlspecialchars($product['product_name']);
                                    $words = explode(' ', $name);
                                    echo implode(' ', array_slice($words, 0, 7)); // Limit to 7 words
                                    ?>
                                </h5>
                                <p class="card-text"><?php echo limitWords(htmlspecialchars($product['description']), 13); ?></p>
                                <p class="fw-bold">
    <span data-lang-key="price">Price</span>:
    ₹<?php echo number_format($product['price'], 2); ?>
</p>
                               <p>
    <span data-lang-key="quantity_available">
        Quantity Available
    </span>:
    <?php echo $product['quantity']; ?>
</p>
                               <a href="product_details.php?id=<?php echo $product['product_id']; ?>"
   class="btn btn-success"
   data-lang-key="more_details">
    More Details
</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <div class="col-12 text-center">
                    <p>No products available at the moment.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Experts Section -->
<section id="experts" class="py-5">
    <div class="container">
        <h2 class="text-center mb-5" data-aos="fade-down" data-lang-key="our_experts">
    Our Experts
</h2>
        <div class="owl-carousel owl-theme">
            <?php if (!empty($experts)) : ?>
                <?php foreach ($experts as $expert) : ?>
                    <div class="item" data-aos="fade-up">
                        <div class="card">
                            <div class="card-img-container">
                                <?php
                                $image_url = htmlspecialchars($expert['image_url']);
                                ?>
                                <img src="<?php echo $image_url; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($expert['name']); ?>">
                                <div class="card-hover-buttons">
                                <div class="card-hover-buttons">
    <button title="Add to Favorites" data-id="<?php echo $expert['expert_id']; ?>" data-type="expert" class="btn-favorite">
        <i class="fas fa-heart"></i>
    </button>
    <button title="View Details" data-id="<?php echo $expert['expert_id']; ?>" data-type="expert" class="btn-details">
        <i class="fas fa-info-circle"></i>
    </button>
</div>
                                </div>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($expert['name']); ?></h5>
                               <p class="card-text">
    <strong data-lang-key="specialization">
        Specialization
    </strong>:
    <?php echo htmlspecialchars($expert['specialization']); ?>
</p>
                                <p class="card-text"><?php echo htmlspecialchars($expert['description']); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <div class="col-12 text-center">
                    <p>No experts available at the moment.</p>
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



    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>

    <script>
        // Initialize AOS
        AOS.init();
        // Initialize Owl Carousel
        $(document).ready(function () {
        $('.owl-carousel').owlCarousel({
            loop: true,
            margin: 20,
            nav: true,
            center: true, // Center the active item
            responsive: {
                0: {
                    items: 1
                },
                600: {
                    items: 2
                },
                1000: {
                    items: 3
                }
            },
            autoplay: true,
            autoplayTimeout: 3000,
            autoplayHoverPause: true,
            onChanged: function (event) {
                // Add custom animation on change
                $('.owl-item').removeClass('active');
                $('.owl-item.center').addClass('active');
            }
        });
    });

    // Initialize AOS
    AOS.init({
        duration: 1000, // Animation duration
        once: true, // Animate only once
    });

    </script>
     <script>
        document.addEventListener('DOMContentLoaded', () => {
    const language = localStorage.getItem('language') || 'en'; // Get language from localStorage
    const theme = localStorage.getItem('theme') || 'light'; // Get theme from localStorage

    // Apply theme
    document.documentElement.setAttribute('data-theme', theme);

// Fetch translations
fetch('translation.json')
    .then(response => response.json())
    .then(translations => {
        const translation = translations[language] || translations['en'];

        // Update text for elements with data-lang-key
        document.querySelectorAll('[data-lang-key]').forEach(element => {
            const key = element.getAttribute('data-lang-key');

            if (translation[key]) {
                element.textContent = translation[key];
            } else {
                console.warn(`Translation not found for key: ${key}`);
            }
        });

        // Force phone number from JSON
        const phoneElement = document.querySelector('[data-lang-key="phoneNumber"]');

        if (phoneElement && translation.phoneNumber) {
            phoneElement.textContent = translation.phoneNumber;
        }

    })
    .catch(error => console.error("Error loading translations:", error));
});
    </script>

   <script>
    document.addEventListener('DOMContentLoaded', () => {
    const themeWrapper = document.getElementById('theme-wrapper');
    const theme = localStorage.getItem('theme') || 'light';

    // Apply the theme on page load
    themeWrapper.className = `theme-${theme}`;

    // Listen for theme changes (if theme dropdown exists)
    const themeDropdown = document.getElementById('theme');
    if (themeDropdown) {
        themeDropdown.value = theme;
        themeDropdown.addEventListener('change', function () {
            const selectedTheme = this.value;
            themeWrapper.className = `theme-${selectedTheme}`;
            localStorage.setItem('theme', selectedTheme);
        });
    }
});

</script>
<script>
   document.addEventListener("DOMContentLoaded", function () {
    // Add to Favorites
    document.querySelectorAll(".btn-favorite").forEach(button => {
        button.addEventListener("click", function () {
            const id = this.getAttribute("data-id");
            const type = this.getAttribute("data-type");

            // Send AJAX request to add to favorites
            fetch("add_to_favorites.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({ id, type }),
            })
                .then(response => {
                    // Check the Content-Type header
                    const contentType = response.headers.get("content-type");
                    if (contentType && contentType.includes("application/json")) {
                        return response.json();
                    } else {
                        return response.text();
                    }
                })
                .then(data => {
                    if (typeof data === "object") {
                        // Handle JSON response
                        if (data.success) {
                            showNotification("Added to favorites!", "success");
                        } else {
                            showNotification("Failed to add to favorites.", "error");
                        }
                    } else {
                        // Handle plain text response
                        showNotification(data, "success");
                    }
                })
                .catch(error => {
                    console.error("Error:", error);
                    showNotification("An error occurred.", "error");
                });
        });
    });

    // View Details
    document.querySelectorAll(".btn-details").forEach(button => {
        button.addEventListener("click", function () {
            const id = this.getAttribute("data-id");
            const type = this.getAttribute("data-type");

            // Redirect to details page
            if (type === "product") {
                window.location.href = `product_details.php?id=${id}`;
            } else if (type === "expert") {
                window.location.href = `expert_details.php?id=${id}`;
            }
        });
    });

  

        // Function to show notifications
        function showNotification(message, type) {
            const notification = document.createElement("div");
            notification.className = `notification ${type}`;
            notification.textContent = message;
            document.body.appendChild(notification);

            // Remove notification after 3 seconds
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }
    });
</script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Add to Cart (for products only)
        document.querySelectorAll(".btn-cart").forEach(button => {
            button.addEventListener("click", function () {
                const productId = this.getAttribute("data-id");

                // Check if the user is logged in
                if (!<?php echo $isLoggedIn ? 'true' : 'false'; ?>) {
                    showNotification("Please log in to add items to your cart.", "error");
                    return;
                }

                // Send AJAX request to add to cart
                fetch("add_to_cart.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded",
                    },
                    body: `product_id=${productId}&quantity=1`, // Default quantity is 1
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showNotification("Product added to cart!", "success");
                        } else if (data.error) {
                            showNotification(data.error, "error");
                        } else {
                            showNotification("An unknown error occurred.", "error");
                        }
                    })
                    .catch(error => {
                        console.error("Error:", error);
                        showNotification("An error occurred while adding to cart.", "error");
                    });
            });
        });

        // Function to show notifications
        function showNotification(message, type) {
            const notification = document.createElement("div");
            notification.className = `notification ${type}`;
            notification.textContent = message;
            document.body.appendChild(notification);

            // Remove notification after 3 seconds
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }
    });
</script>
<script>

function googleTranslateElementInit(){

    new google.translate.TranslateElement({

        pageLanguage:"en",

        includedLanguages:"en,hi,gu",

        autoDisplay:false

    },"google_translate_element");

}

function applyGoogleLanguage(lang){

    localStorage.setItem("language",lang);

    localStorage.setItem("googtrans","/en/"+lang);

    document.cookie="googtrans=/en/"+lang+";path=/";

    const timer=setInterval(function(){

        const combo=document.querySelector(".goog-te-combo");

        if(combo){

            combo.value=lang;

            combo.dispatchEvent(new Event("change"));

            clearInterval(timer);

        }

    },300);

}

function changeLanguage(lang){

    fetch("save_settings.php",{

        method:"POST",

        headers:{
            "Content-Type":"application/json"
        },

        body:JSON.stringify({

            language:lang,

            theme:localStorage.getItem("theme")||"light"

        })

    }).then(function(){

        applyGoogleLanguage(lang);

    });

}

document.addEventListener("DOMContentLoaded",function(){

    document.querySelectorAll(".language-selector").forEach(function(item){

        item.addEventListener("click",function(e){

            e.preventDefault();

            changeLanguage(this.dataset.lang);

        });

    });

});

window.addEventListener("load",function(){

    const params=new URLSearchParams(location.search);

    if(params.get("applyLanguage")==="1"){

        history.replaceState({}, "", "home.php");

        applyGoogleLanguage(

            localStorage.getItem("language")||"en"

        );

    }

});

</script>

<div id="google_translate_element" style="display:none"></div>

<script src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>

</body>

</html>