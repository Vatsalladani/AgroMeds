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
            background: url('Images/bg3.jpeg') center/cover no-repeat;
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

        .navbar {
            font-size: 1.01rem;
        }

        .feature-icon {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 15px;
        }

        .feature-card {
            background: white;
            border: 1px solid #ddd;
            border-radius: 10px;
            height: 250px;
            padding: 20px;
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .feature-card img {
            border-radius: 10px;
            margin-bottom: 15px;
        }

        .owl-carousel .item {
            transform: scale(0.8);
            opacity: 0.6;
            transition: all 0.4s ease;
        }

        .owl-carousel .center .item {
            transform: scale(1);
            opacity: 1;
        }

        .owl-carousel {
            width: 150%;
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
            height: 630px;
        }

        .owl-stage-outer {
            overflow: hidden !important;
        }

        .owl-item {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .owl-nav {
            position: absolute;
            top: 50%;
            right: 0.1px;
            width: 105%;
            display: flex;
            justify-content: space-between;
            transform: translateY(-50%);
        }

        .owl-prev, .owl-next {
            background-color: rgba(0, 0, 0, 0.5) !important;
            width: 37px;
            color: white !important;
            font-size: 24px !important;
            padding: 10px 15px !important;
            border-radius: 50% !important;
        }

        .owl-prev:hover, .owl-next:hover {
            background-color: rgba(0, 0, 0, 0.8) !important;
        }

        .owl-stage {
            transition: 0.25s;
            width: 4267px;
            transform: translate3d(-1706px, 0px, 0px);
            height: 600px;
            display: flex;
            align-items: center;
        }

        .owl-item {
            width: 406.667px;
            margin-right: 20px;
            height: 500px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .item {
            height: 100%;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-view-details {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            margin-top: 10px;
            transition: background-color 0.3s ease;
        }

        .btn-view-details:hover {
            background-color: var(--secondary-color);
        }
        /* General Styles */
:root {
    --primary-color: #28a745;
    --secondary-color: #218838;
    --background-color: #ffffff;
    --text-color: #000000;
    --font-family: 'Arial', sans-serif;
}

body {
    background-color: var(--background-color);
    color: var(--text-color);
    font-family: var(--font-family);
}

/* Header */
header {
    background-color: var(--primary-color);
    color: var(--text-color);
    padding: 20px 0;
    text-align: center;
}

header h1 {
    font-size: 2.5rem;
    font-weight: bold;
}

/* Navbar */
.navbar {
    background-color: var(--secondary-color);
    color: var(--text-color);
}

.navbar .nav-link {
    color: var(--text-color);
}

.navbar .nav-link:hover {
    color: var(grey);
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
.navbar {
    background-color: var(--primary-color) !important;
}

/* Override navbar background color for light theme */
[data-theme="light"] .navbar {
    background-color: white !important;
}                       
/* Cards */
.card {
    background-color: var(--background-color);
    color: var(--text-color);
    border: 1px solid var(--primary-color);
    border-radius: 10px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card:hover {
    transform: translateY(-10px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
}

.card-title {
    color: var(--primary-color);
}

.card-text {
    color: var(--text-color);
}

/* Buttons */
.btn {
    background-color: var(--primary-color);
    color: var(--text-color);
    border: 1px solid var(--secondary-color);
    padding: 8px 15px;
    border-radius: 5px;
    text-decoration: none;
    transition: background-color 0.3s ease;
}

.btn:hover {
    background-color: var(--secondary-color);
}

/* Section Backgrounds */
#seeds,
#fertilizers,
#pesticides,
#experts {
    background-color: var(--section-background-color);
    padding: 20px;
    border-radius: 10px;
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

#experts .card{
    height: 100px;
    width: 450px;
}

#experts .card img{
     height: 110%;
     width: 100%;
}

.card {
    position: relative;
    overflow: hidden; /* Ensures buttons are contained within card */
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card:hover {
    transform: scale(1.05); /* Slightly enlarge card on hover */
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2); /* Add shadow on hover */
}

.action-buttons {
    position: absolute;
    top: 10px;
    right: 10px;
    display: none; /* Hide buttons by default */
    flex-direction: column;
}

.card:hover .action-buttons {
    display: flex; /* Show buttons when card is hovered */
}

.btn-favorite,
.btn-details {
    background-color: rgba(255, 255, 255, 0.8); /* Semi-transparent white */
    border-radius: 50%;
    padding: 10px;
    margin-bottom: 5px;
    transition: background-color 0.3s ease;
}

.btn-favorite:hover,
.btn-details:hover {
    background-color: rgba(255, 255, 255, 1); /* Fully opaque on hover */
}

.btn-favorite i,
.btn-details i {
    color: #ff4081; /* Change icon color as desired */
}

.card-img-top {
    transition: transform 0.3s ease; /* Smooth image transition */
}

.card:hover .card-img-top {
    transform: scale(1.1); /* Slightly zoom image on card hover */
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



/* Advanced Card Styles */
.feature-card-3d {
    perspective: 1000px;
    transform-style: preserve-3d;
    transition: all 0.5s cubic-bezier(0.25, 0.8, 0.25, 1);
    position: relative;
    z-index: 1;
}

.feature-card-3d:hover {
    transform: translateY(-10px) scale(1.02);
    z-index: 2;
}

.feature-card-3d .card {
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
    transform-style: preserve-3d;
}

.feature-card-3d:hover .card {
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
    transform: translateZ(20px);
}

/* Card Image with Parallax Effect */
.card-img-parallax {
    height: 200px;
    overflow: hidden;
    position: relative;
    transform-style: preserve-3d;
}

.card-img-parallax img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.8s cubic-bezier(0.25, 0.8, 0.25, 1);
}

.feature-card-3d:hover .card-img-parallax img {
    transform: scale(1.1) translateZ(20px);
}

/* Floating Animation */
@keyframes float {
    0% { transform: translateY(0px) rotateY(0deg); }
    50% { transform: translateY(-10px) rotateY(5deg); }
    100% { transform: translateY(0px) rotateY(0deg); }
}

.feature-card-3d {
    animation: float 6s ease-in-out infinite;
}

/* Unique Carousel Styles */
/* Seeds Carousel - Plant Growth Effect */
.seeds-carousel .owl-item {
    transition: all 0.5s ease;
    transform: scale(0.9);
    opacity: 0.8;
}

.seeds-carousel .owl-item.center {
    transform: scale(1.1);
    opacity: 1;
    position: relative;
}

.seeds-carousel .owl-item.center::before {
    content: '';
    position: absolute;
    bottom: -15px;
    left: 50%;
    transform: translateX(-50%);
    width: 80%;
    height: 20px;
    background: radial-gradient(ellipse at center, rgba(40,167,69,0.5) 0%, rgba(0,0,0,0) 70%);
    z-index: -1;
}

/* Fertilizers Carousel - Bubble Effect */
.fertilizers-carousel .owl-item {
    transition: all 0.5s ease;
    transform: scale(0.85);
    opacity: 0.7;
    filter: blur(1px);
}

.fertilizers-carousel .owl-item.center {
    transform: scale(1);
    opacity: 1;
    filter: blur(0);
    animation: bubble 2s ease-in-out infinite;
}

@keyframes bubble {
    0%, 100% { transform: scale(1) translateY(0); }
    50% { transform: scale(1.05) translateY(-10px); }
}

/* Pesticides Carousel - Toxic Wave Effect */
.pesticides-carousel .owl-item {
    transition: all 0.5s ease;
    transform: scale(0.9) rotate(-5deg);
    opacity: 0.8;
}

.pesticides-carousel .owl-item.center {
    transform: scale(1.1) rotate(0deg);
    opacity: 1;
    position: relative;
}

.pesticides-carousel .owl-item.center::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, transparent, #ff5722, transparent);
    animation: toxic-wave 2s linear infinite;
}

@keyframes toxic-wave {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}

/* Experts Carousel - Floating Card Effect */
.experts-carousel .owl-item {
    transition: all 0.5s ease;
    transform: scale(0.9) translateY(20px);
    opacity: 0.8;
}

.experts-carousel .owl-item.center {
    transform: scale(1) translateY(0);
    opacity: 1;
    animation: float-card 3s ease-in-out infinite;
}

@keyframes float-card {
    0%, 100% { transform: scale(1) translateY(0) rotate(0deg); }
    50% { transform: scale(1.02) translateY(-15px) rotate(2deg); }
}

/* Action Buttons with Micro-interactions */
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
}

.card:hover .action-buttons {
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
    color: var(--primary-color);
    border: none;
}

.btn-favorite:hover, .btn-details:hover {
    transform: scale(1.1);
    background: var(--primary-color);
    color: white;
}

/* Ripple Effect for Buttons */
.btn-view-details {
    position: relative;
    overflow: hidden;
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

/* Glow Effect for Featured Items */
.featured-glow {
    position: relative;
}

.featured-glow::before {
    content: '';
    position: absolute;
    top: -5px;
    left: -5px;
    right: -5px;
    bottom: -5px;
    background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
    z-index: -1;
    border-radius: 20px;
    filter: blur(10px);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.featured-glow:hover::before {
    opacity: 0.6;
}

/* Theme-Specific Card Colors */
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
            <h1 class="animate__animated animate__fadeInDown" data-lang-key="features_header_title">Explore Our Features</h1>
            <p class="animate__animated animate__fadeInUp" data-lang-key="features_header_desc">Discover high-quality seeds, fertilizers, pesticides, and expert advice tailored for your farming needs.</p>
        </div>
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

    <!-- Features Overview Section -->
    <section id="features-overview" class="py-5 bg-light">
    <section id="features">
        <div class="container">
            <div class="row text-center">
                <div class="col-md-3" data-aos="fade-up">
                    <div class="feature-card">
                        <i class="fas fa-seedling feature-icon"></i>
                        <h4 data-lang-key="seeds_title">Seeds</h4>
                        <p data-lang-key="seeds_desc">High-quality seeds for better yield.</p>
                        <a href="#seeds" class="btn-view-details" data-lang-key="learn_more">Learn More</a>
                    </div>
                </div>
                <div class="col-md-3" data-aos="fade-up" data-aos-delay="200">
                    <div class="feature-card">
                        <i class="fas fa-tint feature-icon"></i>
                        <h4 data-lang-key="fertilizers_title">Fertilizers</h4>
                        <p data-lang-key="fertilizers_desc">Nutrient-rich fertilizers for healthy crops.</p>
                        <a href="#fertilizers" class="btn-view-details" data-lang-key="learn_more">Learn More</a>
                    </div>
                </div>
                <div class="col-md-3" data-aos="fade-up" data-aos-delay="400">
                    <div class="feature-card">
                        <i class="fas fa-bug feature-icon"></i>
                        <h4 data-lang-key="pesticides_title">Pesticides</h4>
                        <p data-lang-key="pesticides_desc">Effective pesticides for pest control.</p>
                        <a href="#pesticides" class="btn-view-details" data-lang-key="learn_more">Learn More</a>
                    </div>
                </div>
                <div class="col-md-3" data-aos="fade-up" data-aos-delay="600">
                    <div class="feature-card">
                        <i class="fas fa-user-tie feature-icon"></i>
                        <h4 data-lang-key="experts_title">Experts</h4>
                        <p data-lang-key="experts_desc">Consult with our agricultural experts.</p>
                        <a href="#experts" class="btn-view-details" data-lang-key="learn_more">Learn More</a>
                    </div>
                </div>
            </div>
        </div>
      </section>
    </section>

    <!-- Seeds Section -->
<section id="seeds" class="py-5 bg-red">
    <div class="container">
        <h2 class="text-center mb-5" data-aos="fade-down" data-lang-key="seeds_section_title">Seeds</h2>
        <div class="owl-carousel seeds-carousel">
            <?php if (!empty($seeds)) : ?>
                <?php foreach ($seeds as $seed) : ?>
                    <div class="item">
                        <div class="feature-card-3d featured-glow">
                            <div class="card">
                                <div class="card-img-parallax">
                                    <img src="<?php echo $seed['image_url']; ?>" class="card-img-top" alt="Seed Image">
                                </div>
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($seed['product_name']); ?></h5>
                                    <p class="card-text"><?php echo limitWords(htmlspecialchars($seed['description'])); ?></p>
                                    <a href="product_details.php?id=<?php echo $seed['product_id']; ?>" class="btn-view-details" data-lang-key="view_details">
                                        <span>View Details</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <div class="col-12 text-center">
                    <p data-lang-key="no_seeds">No seeds available at the moment.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Fertilizers Section -->
<section id="fertilizers" class="py-5 bg-red">
    <div class="container">
        <h2 class="text-center mb-5" data-aos="fade-down" data-lang-key="fertilizers_section_title">Fertilizers</h2>
        <div class="owl-carousel fertilizers-carousel">
            <?php if (!empty($fertilizers)) : ?>
                <?php foreach ($fertilizers as $fertilizer) : ?>
                    <div class="item">
                        <div class="feature-card-3d">
                            <div class="card">
                                <div class="card-img-parallax">
                                    <img src="<?php echo $fertilizer['image_url']; ?>" class="card-img-top" alt="Fertilizer Image">
                                </div>
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($fertilizer['product_name']); ?></h5>
                                    <p class="card-text"><?php echo limitWords(htmlspecialchars($fertilizer['description'])); ?></p>
                                    <a href="product_details.php?id=<?php echo $fertilizer['product_id']; ?>" class="btn-view-details" data-lang-key="view_details">
                                        <span>View Details</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <div class="col-12 text-center">
                    <p data-lang-key="no_fertilizers">No fertilizers available at the moment.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Pesticides Section -->
<section id="pesticides" class="py-5 bg-red">
    <div class="container">
        <h2 class="text-center mb-5" data-aos="fade-down" data-lang-key="pesticides_section_title">Pesticides</h2>
        <div class="owl-carousel pesticides-carousel">
            <?php if (!empty($pesticides)) : ?>
                <?php foreach ($pesticides as $pesticide) : ?>
                    <div class="item">
                        <div class="feature-card-3d">
                            <div class="card">
                                <div class="card-img-parallax">
                                    <img src="<?php echo $pesticide['image_url']; ?>" class="card-img-top" alt="Pesticide Image">
                                </div>
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($pesticide['product_name']); ?></h5>
                                    <p class="card-text"><?php echo limitWords(htmlspecialchars($pesticide['description'])); ?></p>
                                    <a href="product_details.php?id=<?php echo $pesticide['product_id']; ?>" class="btn-view-details" data-lang-key="view_details">
                                        <span>View Details</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <div class="col-12 text-center">
                    <p data-lang-key="no_pesticides">No pesticides available at the moment.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Experts Section -->
<section id="experts" class="py-5 bg-red">
    <div class="container">
        <h2 class="text-center mb-5" data-aos="fade-down" data-lang-key="experts_section_title">Our Experts</h2>
        <div class="owl-carousel experts-carousel">
            <?php if (!empty($experts)) : ?>
                <?php foreach ($experts as $expert) : ?>
                    <div class="item">
                        <div class="feature-card-3d">
                            <div class="card position-relative">
                                <div class="card-img-parallax">
                                    <img src="<?php echo $expert['image_url']; ?>" class="card-img-top" alt="Expert Image">
                                </div>
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($expert['name']); ?></h5>
                                    <p class="card-text"><?php echo limitWords(htmlspecialchars($expert['description'])); ?></p>
                                    <a href="expert_details.php?id=<?php echo $expert['expert_id']; ?>" class="btn-view-details" data-lang-key="view_details">
                                        <span>View Details</span>
                                    </a>
                                </div>
                                <!-- Action Buttons -->
                                <div class="action-buttons">
                                    <button class="btn-favorite">
                                        <i class="far fa-heart"></i>
                                    </button>
                                    <a href="expert_details.php?id=<?php echo $expert['expert_id']; ?>" class="btn-details">
                                        <i class="fas fa-info-circle"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <div class="col-12 text-center">
                    <p data-lang-key="no_experts">No experts available at the moment.</p>
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
                        <span data-lang-key="phoneNumber">+91 99999 99999
</span>
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
        // Initialize Owl Carousel with Barrel Style
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
    </script>
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        // Get the selected language from localStorage (default to 'en' if not set)
        const language = localStorage.getItem('language') || 'en';

        // Get the selected theme from localStorage (default to 'light' if not set)
        const theme = localStorage.getItem('theme') || 'light';

        // Apply the theme to the document
        document.documentElement.setAttribute('data-theme', theme);

        // Function to load translations and update the page
        const loadTranslations = () => {
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
        };

        // Load translations when the page loads
        loadTranslations();

        // Listen for language changes (e.g., from the settings page)
        window.addEventListener('languageChanged', () => {
            // Reload translations when the language is changed
            loadTranslations();
        });
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

// Initialize unique carousels
function initCarousels() {
    // Seeds Carousel - Plant Growth Effect
    $('.seeds-carousel').owlCarousel({
        loop: true,
        center: true,
        margin: 30,
        nav: true,
        dots: false,
        autoplay: true,
        autoplayTimeout: 3000,
        autoplayHoverPause: true,
        responsive: {
            0: { items: 1 },
            768: { items: 2 },
            992: { items: 3 }
        },
        onInitialized: function(event) {
            setCarouselHeights(event);
        },
        onChanged: function(event) {
            setCarouselHeights(event);
            animateCards(event);
        }
    });

    // Fertilizers Carousel - Bubble Effect
    $('.fertilizers-carousel').owlCarousel({
        loop: true,
        center: true,
        margin: 40,
        nav: true,
        dots: false,
        autoplay: true,
        autoplayTimeout: 3500,
        autoplayHoverPause: true,
        responsive: {
            0: { items: 1 },
            768: { items: 2 },
            992: { items: 3 }
        },
        onInitialized: function(event) {
            setCarouselHeights(event);
        },
        onChanged: function(event) {
            setCarouselHeights(event);
            animateCards(event);
        }
    });

    // Pesticides Carousel - Toxic Wave Effect
    $('.pesticides-carousel').owlCarousel({
        loop: true,
        center: true,
        margin: 20,
        nav: true,
        dots: false,
        autoplay: true,
        autoplayTimeout: 4000,
        autoplayHoverPause: true,
        responsive: {
            0: { items: 1 },
            768: { items: 2 },
            992: { items: 3 }
        },
        onInitialized: function(event) {
            setCarouselHeights(event);
        },
        onChanged: function(event) {
            setCarouselHeights(event);
            animateCards(event);
        }
    });

    // Experts Carousel - Floating Card Effect
    $('.experts-carousel').owlCarousel({
        loop: true,
        center: true,
        margin: 50,
        nav: true,
        dots: false,
        autoplay: true,
        autoplayTimeout: 4500,
        autoplayHoverPause: true,
        responsive: {
            0: { items: 1 },
            768: { items: 2 },
            992: { items: 3 }
        },
        onInitialized: function(event) {
            setCarouselHeights(event);
        },
        onChanged: function(event) {
            setCarouselHeights(event);
            animateCards(event);
        }
    });
}

// Set consistent heights for carousel items
function setCarouselHeights(event) {
    const carousel = event.target;
    const items = carousel.querySelectorAll('.owl-item');
    let maxHeight = 0;
    
    items.forEach(item => {
        const card = item.querySelector('.card');
        if (card) {
            card.style.height = 'auto';
            maxHeight = Math.max(maxHeight, card.offsetHeight);
        }
    });
    
    items.forEach(item => {
        const card = item.querySelector('.card');
        if (card) {
            card.style.height = `${maxHeight}px`;
        }
    });
}

// Animate cards when carousel changes
function animateCards(event) {
    const carousel = event.target;
    const centerItem = carousel.querySelector('.center .item');
    
    if (centerItem) {
        // Add animation class to center item
        centerItem.classList.add('animate__animated', 'animate__pulse');
        
        // Remove animation class after animation completes
        setTimeout(() => {
            centerItem.classList.remove('animate__animated', 'animate__pulse');
        }, 1000);
    }
}

// 3D hover effect for cards
function init3DHoverEffects() {
    document.querySelectorAll('.feature-card-3d').forEach(card => {
        card.addEventListener('mousemove', (e) => {
            const rect = card.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            const centerX = rect.width / 2;
            const centerY = rect.height / 2;
            const angleX = (y - centerY) / 20;
            const angleY = (centerX - x) / 20;
            
            card.style.transform = `rotateX(${angleX}deg) rotateY(${angleY}deg)`;
        });
        
        card.addEventListener('mouseleave', () => {
            card.style.transform = 'rotateX(0) rotateY(0)';
        });
    });
}

// Initialize everything when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initCarousels();
    init3DHoverEffects();
});
  
    // Add ripple effect to all buttons
    document.querySelectorAll('.btn-view-details').forEach(button => {
        button.addEventListener('click', function(e) {
            // Create ripple element
            const ripple = document.createElement('span');
            ripple.classList.add('ripple-effect');
            
            // Position ripple at click location
            const rect = this.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            ripple.style.left = `${x}px`;
            ripple.style.top = `${y}px`;
            
            this.appendChild(ripple);
            
            // Remove ripple after animation
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    });
    
    // Update the favorites button click handler in your existing JavaScript
document.querySelectorAll('.btn-favorite').forEach(button => {
    button.addEventListener('click', function(e) {
        e.preventDefault();
        const expertId = this.closest('.card').querySelector('a[href*="expert_details.php"]').href.split('id=')[1];
        
        // Toggle visual state
        this.classList.toggle('active');
        
        if (this.classList.contains('active')) {
            this.innerHTML = '<i class="fas fa-heart"></i>';
            this.classList.add('animate__animated', 'animate__heartBeat');
            
            // Send AJAX request to add to favorites
            fetch('add_to_favoritess.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `expert_id=${expertId}&action=add`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    showFavoriteMessage('Expert added to favorites!', 'success');
                } else {
                    showFavoriteMessage(data.message || 'Error adding to favorites', 'error');
                }
            })
            .catch(error => {
                showFavoriteMessage('Network error', 'error');
            });
            
            setTimeout(() => {
                this.classList.remove('animate__animated', 'animate__heartBeat');
            }, 1000);
        } else {
            this.innerHTML = '<i class="far fa-heart"></i>';
            // Send AJAX request to remove from favorites
            fetch('add_to_favorites.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `expert_id=${expertId}&action=remove`
            });
        }
    });
});

// Function to show favorite message
function showFavoriteMessage(message, type) {
    // Remove any existing messages
    const existingMessage = document.querySelector('.favorite-message');
    if (existingMessage) existingMessage.remove();
    
    // Create message element
    const messageDiv = document.createElement('div');
    messageDiv.className = `favorite-message alert alert-${type} fixed-top text-center`;
    messageDiv.style.top = '70px';
    messageDiv.style.zIndex = '2000';
    messageDiv.style.width = '100%';
    messageDiv.style.padding = '15px';
    messageDiv.style.boxShadow = '0 2px 10px rgba(0,0,0,0.1)';
    
    messageDiv.innerHTML = `
        ${message} 
        <a href="experts_favorites.php" class="alert-link" style="margin-left: 10px;">
            View Favorites
        </a>
        <button type="button" class="btn-close" style="float: right;" onclick="this.parentElement.remove()"></button>
    `;
    
    document.body.appendChild(messageDiv);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        messageDiv.remove();
    }, 5000);
}
</script>

<script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
</body>
</html>