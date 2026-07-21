<?php
session_start();

// Check if the user is logged in
$isLoggedIn = isset($_SESSION['user_id']); // Replace 'user_id' with your session variable for logged-in users
// Fetch theme and language from session
$theme = isset($_SESSION['theme']) ? $_SESSION['theme'] : 'light';
$language = isset($_SESSION['language']) ? $_SESSION['language'] : 'en';

// Database connection details (replace with your actual credentials)
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

// Fetch Product Details
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $productId = $_GET['id']; // Define $productId here

    try {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = :product_id");
        $stmt->execute([':product_id' => $productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            die("Product not found.");
        }
    } catch (PDOException $e) {
        die("Error fetching product: " . $e->getMessage());
    }
} else {
    die("Invalid product ID.");
}

// Fetch Feedback for the Product
try {
    $stmt = $pdo->prepare("SELECT feedback.*, users.full_name, users.profile_photo FROM feedback JOIN users ON feedback.user_id = users.user_id WHERE product_id = :product_id ORDER BY feedback.created_at DESC");
    $stmt->execute([':product_id' => $productId]);
    $feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching feedback: " . $e->getMessage());
}

// Process Feedback Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if the user is logged in
    if (!isset($_SESSION['user_id'])) {
        header("Content-Type: application/json");
        echo json_encode(["error" => "User not logged in"]);
        exit;
    }

    // Get form data
    $productId = $_POST['product_id'];
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];

    // Validate input
    if (empty($rating) || empty($comment)) {
        header("Content-Type: application/json");
        echo json_encode(["error" => "Rating and comment are required"]);
        exit;
    }

    // Insert feedback into the database
    try {
        $stmt = $pdo->prepare("INSERT INTO feedback (user_id, product_id, rating, comment) VALUES (:user_id, :product_id, :rating, :comment)");
        $stmt->execute([
            ":user_id" => $_SESSION['user_id'],
            ":product_id" => $productId,
            ":rating" => $rating,
            ":comment" => $comment,
        ]);

        header("Content-Type: application/json");
        echo json_encode(["success" => "Feedback submitted successfully"]);
        exit;  // Important: Exit after successful submission
    } catch (PDOException $e) {
        header("Content-Type: application/json");
        echo json_encode(["error" => "Database error: " . $e->getMessage()]);
        exit;  // Important: Exit after an error
    }
}

// Define theme (if not already defined)
$theme = isset($_SESSION['theme']) ? $_SESSION['theme'] : 'light'; // Default to 'light' theme if not set
?>

<!DOCTYPE html>
<html lang="en" data-theme="<?php echo $theme; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title data-lang-key="product_details">Product Details - <?php echo htmlspecialchars($product['product_name']); ?></title>
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
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Custom CSS for themes */
        :root {
            --primary-color: #007bff;
            --secondary-color: #6c757d;
            --background-color: #ffffff;
            --text-color: #000000;
            --card-bg: #ffffff;
            --card-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            --border-radius: 12px;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --info-color: #17a2b8;
            --font-primary: 'Poppins', sans-serif;
            --font-secondary: 'Montserrat', sans-serif;
        }

        [data-theme="dark"] {
            --primary-color: #343a40;
            --secondary-color: #6c757d;
            --background-color: #212529;
            --text-color: #f8f9fa;
            --card-bg: #343a40;
            --card-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }

        [data-theme="blue"] {
            --primary-color: #007bff;
            --secondary-color: #0056b3;
            --background-color: #e9f5ff;
            --text-color: #000000;
            --card-bg: #ffffff;
        }

        [data-theme="green"] {
            --primary-color: #28a745;
            --secondary-color: #1e7e34;
            --background-color: #e2f0e5;
            --text-color: #000000;
            --card-bg: #ffffff;
        }

        [data-theme="pink"] {
            --primary-color: #e83e8c;
            --secondary-color: #d01a72;
            --background-color: #f8e9f0;
            --text-color: #000000;
            --card-bg: #ffffff;
        }

        [data-theme="ocean"] {
            --primary-color: #17a2b8;
            --secondary-color: #117a8b;
            --background-color: #e2f3f5;
            --text-color: #000000;
            --card-bg: #ffffff;
        }

        [data-theme="sunset"] {
            --primary-color: #ff7f50;
            --secondary-color: #e67347;
            --background-color: #fff3e6;
            --text-color: #000000;
            --card-bg: #ffffff;
        }

        [data-theme="forest"] {
            --primary-color: #228b22;
            --secondary-color: #1c7a1c;
            --background-color: #e6f4e6;
            --text-color: #000000;
            --card-bg: #ffffff;
        }

        [data-theme="violet"] {
            --primary-color: #8a2be2;
            --secondary-color: #7b1fa2;
            --background-color: #f0e6ff;
            --text-color: #000000;
            --card-bg: #ffffff;
        }

        /* Base Styles */
        body {
            font-family: var(--font-primary);
            background-color: var(--background-color);
            color: var(--text-color);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: var(--transition);
            line-height: 1.6;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: var(--font-secondary);
            font-weight: 600;
        }

        a {
            text-decoration: none;
            transition: var(--transition);
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

        /* Product Details Section */
        #product-details {
            padding: 3rem 0;
        }

        .product-info {
            background-color: var(--card-bg);
            padding: 2rem;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            transition: var(--transition);
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .product-info:hover {
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
            transform: translateY(-5px);
        }

        .product-info h1 {
            font-size: 2.2rem;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }

        .product-info .lead {
            font-size: 1.1rem;
            margin-bottom: 1.5rem;
        }

        .product-info .fw-bold {
            font-size: 1.4rem;
            color: var(--primary-color);
        }

        /* Carousel Styles */
        .carousel {
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--card-shadow);
            transition: var(--transition);
        }

        .carousel:hover {
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
        }

        .carousel-item img {
            width: 100%;
            height: 450px;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .carousel-item:hover img {
            transform: scale(1.02);
        }

        .carousel-control-prev,
        .carousel-control-next {
            width: 50px;
            height: 50px;
            background-color: rgba(0, 0, 0, 0.5);
            border-radius: 50%;
            top: 50%;
            transform: translateY(-50%);
            opacity: 0;
            transition: var(--transition);
        }

        .carousel:hover .carousel-control-prev,
        .carousel:hover .carousel-control-next {
            opacity: 0.7;
        }

        .carousel-control-prev:hover,
        .carousel-control-next:hover {
            background-color: rgba(0, 0, 0, 0.8);
            opacity: 1;
        }

        /* Button Styles */
        .btn {
            padding: 0.6rem 1.2rem;
            border-radius: 50px;
            font-weight: 500;
            transition: var(--transition);
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .btn-success {
            background-color: var(--success-color);
            border-color: var(--success-color);
        }

        .btn-success:hover {
            background-color: #218838;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .btn-container {
            display: flex;
            gap: 15px;
            margin-top: 2rem;
        }

        /* Star Rating */
        .star-rating {
            display: flex;
            gap: 5px;
            margin-bottom: 1rem;
        }

        .star-rating .star {
            cursor: pointer;
            font-size: 1.8rem;
            color: #ddd;
            transition: var(--transition);
        }

        .star-rating .star.selected,
        .star-rating .star:hover,
        .star-rating .star:hover ~ .star {
            color: var(--warning-color);
            transform: scale(1.1);
        }

        /* Feedback Section */
        #feedback {
            padding: 3rem 0;
        }

        .feedback-container {
            display: flex;
            gap: 2rem;
            flex-wrap: wrap;
            justify-content: space-between;
        }

        .feedback-form,
        .feedback-carousel {
            flex: 1;
            min-width: 300px;
        }

        .feedback-form {
            background-color: var(--card-bg);
            padding: 2rem;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            transition: var(--transition);
        }

        .feedback-form:hover {
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
            transform: translateY(-5px);
        }

        .feedback-form label {
            font-weight: 500;
            margin-bottom: 0.5rem;
            display: block;
        }

        .feedback-form textarea {
            min-height: 120px;
            resize: vertical;
        }

        /* Feedback Carousel */
        .feedback-carousel {
            background-color: var(--card-bg);
            padding: 2rem;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            position: relative;
            transition: var(--transition);
        }

        .feedback-carousel:hover {
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
            transform: translateY(-5px);
        }

        .feedback-item {
            text-align: center;
            padding: 1rem;
            display: none;
            animation: fadeIn 0.5s ease-out;
        }

        .feedback-item.active {
            display: block;
        }

        .user-image {
            width: 100px;
            height: 100px;
            margin: 0 auto 1.5rem;
            border-radius: 50%;
            overflow: hidden;
            border: 3px solid var(--primary-color);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            transition: var(--transition);
        }

        .user-image:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }

        .user-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .username {
            font-size: 1.2rem;
            font-weight: 600;
            margin: 1rem 0;
            color: var(--primary-color);
        }

        .rating {
            margin: 1rem 0;
        }

        .rating .fa-star {
            font-size: 1.5rem;
            color: var(--warning-color);
        }

        .rating .fa-regular {
            color: #ddd;
        }

        .comment {
            font-size: 1rem;
            color: var(--text-color);
            font-style: italic;
            line-height: 1.6;
            margin: 1rem 0;
            padding: 0 1rem;
        }

        .carousel-controls {
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            display: flex;
            justify-content: space-between;
            transform: translateY(-50%);
            z-index: 10;
            padding: 0 1rem;
        }

        .carousel-controls button {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            color: white;
            padding: 12px;
            border-radius: 50%;
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            transition: var(--transition);
        }

        .carousel-controls button:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
            animation: pulse 1.5s infinite;
        }

        /* Popup Styles */
        .popup {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 1000;
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(5px);
        }

        .popup-content {
            background: var(--card-bg);
            padding: 2rem;
            border-radius: var(--border-radius);
            text-align: center;
            position: relative;
            max-width: 90%;
            max-height: 90%;
            overflow: auto;
            animation: fadeIn 0.3s ease-out;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            border: 2px solid var(--primary-color);
        }

        .popup img {
            max-width: 100%;
            max-height: 300px;
            border-radius: var(--border-radius);
            margin-bottom: 1.5rem;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
        }

        .popup .close {
            position: absolute;
            top: 15px;
            right: 15px;
            color: var(--text-color);
            font-size: 1.8rem;
            cursor: pointer;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
        }

        .popup .close:hover {
            background: var(--danger-color);
            color: white;
            transform: rotate(90deg);
        }

        .popup-username {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }

        .popup-comment {
            font-size: 1.1rem;
            color: var(--text-color);
            font-style: italic;
            line-height: 1.6;
        }

        /* Add to Cart Popup */
        #addToCartPopup .popup-content,
        #purchase-popup .popup-content {
            max-width: 500px;
            width: 90%;
            padding: 2rem;
        }

        .product-details {
            margin-bottom: 1.5rem;
        }

        .product-details img {
            max-width: 100%;
            max-height: 200px;
            border-radius: var(--border-radius);
            margin-bottom: 1rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .product-details p {
            margin: 0.5rem 0;
            font-size: 1rem;
        }

        .quantity-control {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 1.5rem 0;
            gap: 15px;
        }

        .quantity-control button {
            background: var(--primary-color);
            color: white;
            border: none;
            width: 40px;
            height: 40px;
            font-size: 1.2rem;
            cursor: pointer;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
        }

        .quantity-control button:hover {
            background: var(--secondary-color);
            transform: scale(1.1);
        }

        .quantity-control input {
            width: 60px;
            text-align: center;
            font-size: 1.1rem;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            padding: 0.5rem;
        }

        /* Success Message */
        #successMessage {
            display: none;
            position: fixed;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--success-color);
            color: white;
            padding: 1rem 2rem;
            border-radius: 50px;
            z-index: 1000;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            animation: fadeInOut 3s ease-in-out;
        }

        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInOut {
            0% {
                opacity: 0;
                transform: translateX(-50%) translateY(20px);
            }
            10% {
                opacity: 1;
                transform: translateX(-50%) translateY(0);
            }
            90% {
                opacity: 1;
                transform: translateX(-50%) translateY(0);
            }
            100% {
                opacity: 0;
                transform: translateX(-50%) translateY(20px);
            }
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            }
            50% {
                transform: scale(1.05);
                box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
            }
            100% {
                transform: scale(1);
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            }
        }

        @keyframes float {
            0% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-10px);
            }
            100% {
                transform: translateY(0);
            }
        }

        /* Floating Animation for Buttons */
        .btn-float {
            animation: float 3s ease-in-out infinite;
        }

        /* Footer */
        .footer {
            background-color: var(--primary-color);
            color: white;
            text-align: center;
            padding: 1.5rem 0;
            margin-top: auto;
            font-size: 0.9rem;
        }

        /* Responsive Adjustments */
        @media (max-width: 992px) {
            .feedback-container {
                flex-direction: column;
            }
            
            .feedback-form,
            .feedback-carousel {
                width: 100%;
            }
            
            .btn-container {
                flex-direction: column;
                gap: 10px;
            }
        }

        @media (max-width: 768px) {
            .product-info h1 {
                font-size: 1.8rem;
            }
            
            .carousel-item img {
                height: 350px;
            }
            
            .feedback-carousel {
                padding: 1.5rem;
            }
        }

        /* Special Effects */
        .glow-on-hover {
            transition: var(--transition);
        }

        .glow-on-hover:hover {
            box-shadow: 0 0 15px var(--primary-color);
        }

        .hover-zoom {
            transition: transform 0.3s ease;
        }

        .hover-zoom:hover {
            transform: scale(1.03);
        }

        /* Ribbon for Special Offers */
        .ribbon {
            position: absolute;
            top: 0;
            right: 0;
            width: 150px;
            height: 150px;
            overflow: hidden;
            z-index: 10;
        }

        .ribbon span {
            position: absolute;
            display: block;
            width: 225px;
            padding: 15px 0;
            background-color: var(--warning-color);
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
            color: #333;
            font-weight: 600;
            text-align: center;
            right: -25px;
            top: 30px;
            transform: rotate(45deg);
        }

        /* Badge for New Products */
        .badge-new {
            position: absolute;
            top: 10px;
            left: 10px;
            background-color: var(--danger-color);
            color: white;
            padding: 5px 10px;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
            z-index: 10;
            animation: pulse 2s infinite;
        }

        /* Floating Icons Background */
        .floating-icons {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: -1;
            opacity: 0.1;
        }

        .floating-icons i {
            position: absolute;
            color: var(--primary-color);
            font-size: 1.5rem;
            animation: float 6s infinite ease-in-out;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--background-color);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--secondary-color);
        }

        /* Loading Spinner */
        .spinner {
            display: none;
            width: 40px;
            height: 40px;
            margin: 0 auto;
            border: 4px solid rgba(0, 0, 0, 0.1);
            border-radius: 50%;
            border-top: 4px solid var(--primary-color);
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Tooltip */
        .tooltip-custom {
            position: relative;
            display: inline-block;
        }

        .tooltip-custom .tooltip-text {
            visibility: hidden;
            width: 200px;
            background-color: var(--primary-color);
            color: white;
            text-align: center;
            border-radius: 6px;
            padding: 5px;
            position: absolute;
            z-index: 1;
            bottom: 125%;
            left: 50%;
            transform: translateX(-50%);
            opacity: 0;
            transition: opacity 0.3s;
        }

        .tooltip-custom:hover .tooltip-text {
            visibility: visible;
            opacity: 1;
        }

        /* Highlight Box */
        .highlight-box {
            background: linear-gradient(135deg, rgba(0, 123, 255, 0.1), rgba(0, 123, 255, 0.05));
            border-left: 4px solid var(--primary-color);
            padding: 1rem;
            border-radius: 0 var(--border-radius) var(--border-radius) 0;
            margin: 1rem 0;
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

    <!-- Product Details Section -->
    <section id="product-details" class="py-5">
        <div class="container">
            <div class="row">
                <!-- Product Images Carousel -->
                <div class="col-md-6">
                    <div id="productCarousel" class="carousel slide" data-bs-ride="carousel">
                        <!-- Add ribbon for special offers -->
                        <?php if ($product['price'] < 100): ?>
                            <div class="ribbon">
                                <span>Special Offer</span>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Add badge for new products -->
                        <?php if (strtotime($product['created_at']) > strtotime('-30 days')): ?>
                            <div class="badge-new">NEW</div>
                        <?php endif; ?>
                        
                        <div class="carousel-inner">
                            <!-- Main Image -->
                            <div class="carousel-item active">
                                <?php
                                $imageName = basename($product['image_url']);
                                $imagePath = "Uploads/Products/" . $imageName;
                                ?>
                                <img src="<?php echo $imagePath; ?>" alt="Product Image" class="hover-zoom">
                            </div>
                            <!-- Additional Images -->
                            <?php if (!empty($product['image1'])) : ?>
                                <div class="carousel-item">
                                    <?php
                                    $imageName = basename($product['image1']);
                                    $imagePath = "Uploads/Products/" . $imageName;
                                    ?>
                                    <img src="<?php echo $imagePath; ?>" alt="Product Image 1" class="hover-zoom">
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($product['image2'])) : ?>
                                <div class="carousel-item">
                                    <?php
                                    $imageName = basename($product['image2']);
                                    $imagePath = "Uploads/Products/" . $imageName;
                                    ?>
                                    <img src="<?php echo $imagePath; ?>" alt="Product Image 2" class="hover-zoom">
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($product['image3'])) : ?>
                                <div class="carousel-item">
                                    <?php
                                    $imageName = basename($product['image3']);
                                    $imagePath = "Uploads/Products/" . $imageName;
                                    ?>
                                    <img src="<?php echo $imagePath; ?>" alt="Product Image 3" class="hover-zoom">
                                </div>
                            <?php endif; ?>
                        </div>
                        <!-- Carousel Controls -->
                        <button class="carousel-control-prev" type="button" data-bs-target="#productCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#productCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    </div>
                </div>

                <!-- Product Information -->
                <div class="col-md-6">
                    <div class="product-info">
                        <div>
                            <h1 class="mb-3"><?php echo htmlspecialchars($product['product_name']); ?></h1>
                            
                            <!-- Highlight Box for Description -->
                            <div class="highlight-box">
                                <p class="lead mb-0"><?php echo htmlspecialchars($product['description']); ?></p>
                            </div>
                            
                            <div class="d-flex align-items-center mt-3">
                                <i class="fas fa-tag me-2" style="color: var(--primary-color);"></i>
                                <p class="fw-bold mb-0">Price: ₹<?php echo number_format($product['price'], 2); ?></p>
                            </div>
                            
                            <div class="d-flex align-items-center mt-2">
                                <i class="fas fa-box-open me-2" style="color: var(--primary-color);"></i>
                                <p class="mb-0">Quantity Available: <?php echo $product['quantity']; ?></p>
                            </div>

                            <!-- Weighting Options -->
                            <div class="weighting-options mt-3">
                                <h5><i class="fas fa-weight-hanging me-2"></i> Weighting Options</h5>
                                <?php
                                $weightingOptions = [
                                    'ml' => $product['weighting_ml'],
                                    'kg' => $product['weighting_kg'],
                                    'packs' => $product['weighting_packs'],
                                ];

                                $selectedWeighting = array_filter($weightingOptions, function ($value) {
                                    return !empty($value) && $value != 0;
                                });

                                if (!empty($selectedWeighting)) :
                                    $selectedKey = array_key_first($selectedWeighting);
                                    $selectedValue = $selectedWeighting[$selectedKey];
                                ?>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-<?php echo $selectedKey === 'ml' ? 'flask' : ($selectedKey === 'kg' ? 'weight' : 'boxes'); ?> me-2" style="color: var(--primary-color);"></i>
                                        <p class="mb-0"><strong><?php echo strtoupper($selectedKey); ?>:</strong> <?php echo $selectedValue; ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Add to Cart and Purchase Buttons -->
                        <div class="btn-container">
                            <?php if (isset($_SESSION['user_id'])) : ?>
                                <button class="btn btn-primary btn-float glow-on-hover" onclick="openAddToCartPopup(
                                    '<?php echo $productId; ?>',
                                    '<?php echo htmlspecialchars($product['product_name']); ?>',
                                    '<?php echo htmlspecialchars($product['image_url']); ?>',
                                    '<?php echo $product['price']; ?>',
                                    '<?php echo $product['weighting_ml'] ?? $product['weighting_kg'] ?? $product['weighting_packs']; ?>',
                                    '<?php echo $product['quantity']; ?>'
                                )">
                                    <i class="fas fa-cart-plus me-2"></i> Add to Cart
                                </button>
                                <button class="btn btn-success btn-float glow-on-hover" id="purchaseBtn" 
                                    data-product-id="<?php echo $productId; ?>"
                                    data-product-name="<?php echo htmlspecialchars($product['product_name']); ?>"
                                    data-image-url="<?php echo htmlspecialchars($product['image_url']); ?>"
                                    data-price="<?php echo $product['price']; ?>"
                                    data-weight="<?php echo $product['weighting_ml'] ?? $product['weighting_kg'] ?? $product['weighting_packs']; ?>"
                                    data-quantity="<?php echo $product['quantity']; ?>"
                                    data-description="<?php echo htmlspecialchars($product['description']); ?>"
                                    data-category-id="<?php echo $product['category_id']; ?>">
                                    <i class="fas fa-bolt me-2"></i> Purchase Now
                                </button>
                            <?php else : ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i> Please log in to purchase.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Add to Cart Pop-up Box -->
    <div id="addToCartPopup" class="popup">
        <div class="popup-content">
            <span class="close" onclick="closeAddToCartPopup()">&times;</span>
            <h4><i class="fas fa-shopping-cart me-2"></i> Add to Cart</h4>
            <div class="product-details">
                <img id="popupProductImage" src="" alt="Product Image" class="mb-3">
                <p id="popupProductName" class="fw-bold"></p>
                <p id="popupProductPrice"></p>
                <p id="popupProductWeight"></p>
                <p id="popupProductQuantity"></p>
            </div>
            <div class="quantity-control">
                <button onclick="decreaseQuantity()"><i class="fas fa-minus"></i></button>
                <input type="number" id="quantityInput" value="1" min="1">
                <button onclick="increaseQuantity()"><i class="fas fa-plus"></i></button>
            </div>
            <button class="btn btn-primary w-100 mt-3" onclick="addToCart()">
                <i class="fas fa-cart-plus me-2"></i> Add to Cart
            </button>
            <div id="cartSpinner" class="spinner mt-3"></div>
        </div>
    </div>

    <!-- Purchase Popup -->
    <div id="purchase-popup" class="popup">
        <div class="popup-content">
            <span class="close" onclick="closePurchasePopup()">&times;</span>
            <h4><i class="fas fa-bolt me-2"></i> Confirm Purchase</h4>
            <div class="product-details">
                <img id="purchasePopupProductImage" src="" alt="Product Image" class="mb-3">
                <p id="purchasePopupProductName" class="fw-bold"></p>
                <p id="purchasePopupProductPrice"></p>
                <p id="purchasePopupProductWeight"></p>
                <p id="purchasePopupProductQuantity"></p>
            </div>
            <div class="quantity-control">
                <button onclick="decreasePurchaseQuantity()"><i class="fas fa-minus"></i></button>
                <input type="number" id="purchaseQuantityInput" value="1" min="1">
                <button onclick="increasePurchaseQuantity()"><i class="fas fa-plus"></i></button>
            </div>
            <p id="purchaseTotalPrice" class="fw-bold fs-5 my-3">Total: ₹<?php echo $product['price']; ?></p>
            <button class="btn btn-success w-100" onclick="confirmPurchase()">
                <i class="fas fa-check-circle me-2"></i> Confirm Purchase
            </button>
            <div id="purchaseSpinner" class="spinner mt-3"></div>
        </div>
    </div>

    <!-- Success Message -->
    <div id="successMessage" class="success-message">
        <i class="fas fa-check-circle me-2"></i> Product added to cart!
    </div>

    <!-- Feedback Section -->
    <section id="feedback" class="py-5">
        <div class="container">
            <h2 class="text-center mb-4"><i class="fas fa-comments me-2"></i> Feedback</h2>
            <div class="feedback-container">
                <!-- Feedback Form (Only for Logged-In Users) -->
                <?php if (isset($_SESSION['user_id'])) : ?>
                    <div class="feedback-form">
                        <form id="feedbackForm" method="post">
                            <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($productId); ?>">
                            <div class="mb-4">
                                <label for="ratingInput" class="form-label">Rating</label>
                                <div class="star-rating">
                                    <?php for ($i = 1; $i <= 5; $i++) : ?>
                                        <span class="star" data-rating="<?php echo $i; ?>">&#9733;</span>
                                    <?php endfor; ?>
                                </div>
                                <input type="hidden" id="ratingInput" name="rating" required>
                            </div>
                            <div class="mb-4">
                                <label for="commentInput" class="form-label">Comment</label>
                                <textarea id="commentInput" name="comment" class="form-control" rows="5" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-paper-plane me-2"></i> Submit Feedback
                            </button>
                            <div id="feedbackSpinner" class="spinner mt-3"></div>
                        </form>
                    </div>
                <?php else : ?>
                    <div class="feedback-form">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i> Please log in to give feedback.
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Feedback Carousel -->
                <div class="feedback-carousel">
                    <?php if (!empty($feedbacks)) : ?>
                        <?php foreach ($feedbacks as $index => $feedback) : ?>
                            <div class="feedback-item <?php echo $index === 0 ? 'active' : ''; ?>">
                                <!-- User Image -->
                                <div class="user-image" onclick="openPopup('<?php echo htmlspecialchars($feedback['profile_photo']); ?>', '<?php echo htmlspecialchars($feedback['full_name']); ?>', '<?php echo htmlspecialchars($feedback['comment']); ?>')">
                                    <?php
                                    $imageName = basename($feedback['profile_photo']);
                                    $baseImagePath = "\Farming_meds/Uploads/Users/";
                                    $imagePath = $baseImagePath . htmlspecialchars($imageName);
                                    ?>
                                    <img src="<?php echo $imagePath; ?>" alt="<?php echo htmlspecialchars($feedback['full_name']); ?>">
                                </div>
                                <!-- User Name -->
                                <p class="username"><?php echo htmlspecialchars($feedback['full_name']); ?></p>
                                <!-- Rating Stars -->
                                <p class="rating">
                                    <?php for ($i = 1; $i <= 5; $i++) : ?>
                                        <i class="fas fa-star<?php echo ($i <= $feedback['rating']) ? '' : ' fa-regular'; ?>"></i>
                                    <?php endfor; ?>
                                </p>
                                <!-- User Comment -->
                                <p class="comment">"<?php echo htmlspecialchars($feedback['comment']); ?>"</p>
                                <!-- Date -->
                                <p class="text-muted small">
                                    <i class="far fa-clock me-1"></i> 
                                    <?php echo date('M d, Y', strtotime($feedback['created_at'])); ?>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <div class="feedback-item active">
                            <div class="text-center py-4">
                                <i class="fas fa-comment-slash fa-3x mb-3" style="color: var(--primary-color);"></i>
                                <p>No feedback available for this product yet.</p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Carousel Controls -->
                    <div class="carousel-controls">
                        <button onclick="prevFeedback()"><i class="fas fa-chevron-left"></i></button>
                        <button onclick="nextFeedback()"><i class="fas fa-chevron-right"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Pop-up Box for Enlarged Image -->
    <div id="imagePopup" class="popup">
        <div class="popup-content">
            <span class="close" onclick="closePopup()">&times;</span>
            <img id="popupImage" src="" alt="Enlarged Image" class="mb-3">
            <p id="popupUsername" class="popup-username"></p>
            <p id="popupComment" class="popup-comment"></p>
        </div>
    </div>

    <!-- Dynamic Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-6 text-center text-md-start">
                    <p class="mb-0">&copy; 2023 AgroMeds. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <div class="social-icons">
                        <a href="#" class="text-white me-2"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-white me-2"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white me-2"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Floating Icons Background -->
    <div class="floating-icons">
        <?php for ($i = 0; $i < 20; $i++): ?>
            <i class="fas fa-leaf" style="top: <?php echo rand(0, 100); ?>%; left: <?php echo rand(0, 100); ?>%; animation-delay: <?php echo rand(0, 5); ?>s;"></i>
            <i class="fas fa-seedling" style="top: <?php echo rand(0, 100); ?>%; left: <?php echo rand(0, 100); ?>%; animation-delay: <?php echo rand(0, 5); ?>s;"></i>
        <?php endfor; ?>
    </div>

    <!-- Include JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script src="settings.js"></script>
    
    <script>
        // Initialize AOS (Animate On Scroll)
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });

        // Global variables
        let currentPurchaseProduct = {};
        let currentFeedback = 0;
        const feedbackItems = document.querySelectorAll('.feedback-item');

        // Core Functions
        function handlePurchase(button) {
            // Get all data attributes from the button
            const productId = button.getAttribute('data-product-id');
            const productName = button.getAttribute('data-product-name');
            const imageUrl = button.getAttribute('data-image-url');
            const price = button.getAttribute('data-price');
            const weight = button.getAttribute('data-weight');
            const quantity = button.getAttribute('data-quantity');
            const description = button.getAttribute('data-description');
            const categoryId = button.getAttribute('data-category-id');
            const categoryName = button.getAttribute('data-category-name');
            
            // Open the purchase popup with all the data
            openPurchasePopup(
                productId,
                productName,
                imageUrl,
                price,
                weight,
                quantity,
                description,
                categoryId,
                categoryName
            );
        }

        function parseResponse(response) {
            try {
                // Try to parse as JSON if it's a string
                if (typeof response === 'string') {
                    return JSON.parse(response);
                }
                // If it's already an object (like when jQuery auto-parses JSON)
                return response;
            } catch (e) {
                // If parsing fails, treat as plain text
                return {
                    success: false,
                    message: response
                };
            }
        }

        function showFeedback(index) {
            feedbackItems.forEach((item, i) => {
                item.classList.remove('active');
                if (i === index) {
                    item.classList.add('active');
                }
            });
        }

        function nextFeedback() {
            currentFeedback = (currentFeedback + 1) % feedbackItems.length;
            showFeedback(currentFeedback);
        }

        function prevFeedback() {
            currentFeedback = (currentFeedback - 1 + feedbackItems.length) % feedbackItems.length;
            showFeedback(currentFeedback);
        }

        function openPopup(imageSrc, username, comment) {
            const popup = document.getElementById('imagePopup');
            const popupImage = document.getElementById('popupImage');
            const popupUsername = document.getElementById('popupUsername');
            const popupComment = document.getElementById('popupComment');

            const imageName = imageSrc.split('/').pop();
            const baseImagePath = "Uploads/Users/";
            const fullImagePath = baseImagePath + imageName;

            popupImage.src = fullImagePath;
            popupUsername.textContent = username;
            popupComment.textContent = comment;
            popup.style.display = 'flex';
        }

        function closePopup() {
            document.getElementById('imagePopup').style.display = 'none';
        }

        // Cart and Purchase Functions
        function openAddToCartPopup(productId, productName, productImage, productPrice, productWeight, productQuantity) {
            const popup = document.getElementById('addToCartPopup');
            const popupImage = document.getElementById('popupProductImage');
            const popupName = document.getElementById('popupProductName');
            const popupPrice = document.getElementById('popupProductPrice');
            const popupWeight = document.getElementById('popupProductWeight');
            const popupQuantity = document.getElementById('popupProductQuantity');
            const quantityInput = document.getElementById('quantityInput');

            const imageName = productImage.split('/').pop();
            const baseImagePath = "Uploads/Products/";
            const fullImagePath = baseImagePath + imageName;

            popupImage.src = fullImagePath;
            popupName.textContent = productName;
            popupPrice.textContent = `Price: ₹${productPrice}`;
            popupWeight.textContent = `Weight: ${productWeight}`;
            popupQuantity.textContent = `Available: ${productQuantity}`;
            quantityInput.max = productQuantity;
            popup.style.display = 'flex';
        }

        function closeAddToCartPopup() {
            document.getElementById('addToCartPopup').style.display = 'none';
        }

        function increaseQuantity() {
            const quantityInput = document.getElementById('quantityInput');
            const maxQuantity = parseInt(quantityInput.max);
            if (parseInt(quantityInput.value) < maxQuantity) {
                quantityInput.value = parseInt(quantityInput.value) + 1;
            }
        }

        function decreaseQuantity() {
            const quantityInput = document.getElementById('quantityInput');
            if (parseInt(quantityInput.value) > 1) {
                quantityInput.value = parseInt(quantityInput.value) - 1;
            }
        }

        function addToCart() {
            const productId = <?php echo json_encode($productId); ?>;
            const quantity = document.getElementById('quantityInput').value;
            const spinner = document.getElementById('cartSpinner');

            // Show loading spinner
            spinner.style.display = 'block';

            $.ajax({
                type: "POST",
                url: "add_to_cart.php",
                data: {
                    product_id: productId,
                    quantity: quantity
                },
                success: function(response) {
                    const result = parseResponse(response);
                    
                    const successMessage = document.getElementById('successMessage');
                    if (result.success) {
                        successMessage.textContent = result.message || 'Product added to cart!';
                        successMessage.style.display = 'block';
                        setTimeout(() => {
                            successMessage.style.display = 'none';
                        }, 3000);
                        closeAddToCartPopup();
                    } else {
                        alert(result.message || 'Error adding to cart');
                    }
                },
                error: function(xhr, status, error) {
                    alert("Error adding to cart: " + error);
                },
                complete: function() {
                    spinner.style.display = 'none';
                }
            });
        }

        // Purchase Functions
        function openPurchasePopup(productId, productName, productImage, productPrice, productWeight, productQuantity, productDescription, categoryId, categoryName) {
            const popup = document.getElementById('purchase-popup');
            const popupImage = document.getElementById('purchasePopupProductImage');
            const popupName = document.getElementById('purchasePopupProductName');
            const popupPrice = document.getElementById('purchasePopupProductPrice');
            const popupWeight = document.getElementById('purchasePopupProductWeight');
            const popupQuantity = document.getElementById('purchasePopupProductQuantity');
            const quantityInput = document.getElementById('purchaseQuantityInput');

            currentPurchaseProduct = {
                product_id: productId,
                product_name: productName,
                image_url: productImage,
                price: parseFloat(productPrice),
                weight: productWeight,
                max_quantity: parseInt(productQuantity),
                description: productDescription,
                category_id: categoryId,
                category_name: categoryName
            };

            // Fix the image path - extract just the filename and prepend the correct path
            const imageName = productImage.split('/').pop();
            const fullImagePath = "Uploads/Products/" + imageName;

            popupImage.src = fullImagePath;
            popupName.textContent = productName;
            popupPrice.textContent = `Price: ₹${productPrice}`;
            popupWeight.textContent = `Weight: ${productWeight}`;
            popupQuantity.textContent = `Available: ${productQuantity}`;
            quantityInput.max = productQuantity;
            quantityInput.value = 1;
            updatePurchaseTotal();
            popup.style.display = 'flex';
        }

        function closePurchasePopup() {
            document.getElementById('purchase-popup').style.display = 'none';
        }

        function increasePurchaseQuantity() {
            const quantityInput = document.getElementById('purchaseQuantityInput');
            if (parseInt(quantityInput.value) < parseInt(quantityInput.max)) {
                quantityInput.value = parseInt(quantityInput.value) + 1;
                updatePurchaseTotal();
            }
        }

        function decreasePurchaseQuantity() {
            const quantityInput = document.getElementById('purchaseQuantityInput');
            if (parseInt(quantityInput.value) > 1) {
                quantityInput.value = parseInt(quantityInput.value) - 1;
                updatePurchaseTotal();
            }
        }

        function updatePurchaseTotal() {
            const quantityInput = document.getElementById('purchaseQuantityInput');
            const quantity = parseInt(quantityInput.value);
            const totalPrice = currentPurchaseProduct.price * quantity;
            document.getElementById('purchaseTotalPrice').textContent = `Total: ₹${totalPrice.toFixed(2)}`;
        }

        function confirmPurchase() {
            const quantityInput = document.getElementById('purchaseQuantityInput');
            const quantity = parseInt(quantityInput.value);
            const spinner = document.getElementById('purchaseSpinner');
            
            // Show loading spinner
            spinner.style.display = 'block';
            
            const purchaseData = [{
                product_id: currentPurchaseProduct.product_id,
                product_name: currentPurchaseProduct.product_name,
                price: currentPurchaseProduct.price,
                quantity: quantity,
                total: currentPurchaseProduct.price * quantity,
                image_url: currentPurchaseProduct.image_url,
                description: currentPurchaseProduct.description,
                category_id: currentPurchaseProduct.category_id,
                category_name: currentPurchaseProduct.category_name,
                weight: currentPurchaseProduct.weight
            }];
            
            const totalPrice = currentPurchaseProduct.price * quantity;
            const encodedData = encodeURIComponent(JSON.stringify(purchaseData));
            window.location.href = `purchase.php?data=${encodedData}&total=${totalPrice}`;
        }

        // Initialize everything when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize feedback carousel
            showFeedback(currentFeedback);

            // Set up event listeners
            document.getElementById('purchaseBtn').addEventListener('click', function() {
                handlePurchase(this);
            });
            
            // Star Rating Selection
            $('.star-rating .star').click(function() {
                const rating = $(this).data('rating');
                $('#ratingInput').val(rating);
                $('.star-rating .star').removeClass('selected');
                $(this).prevAll().addBack().addClass('selected');
            });

            // Feedback Form Submission
            $('#feedbackForm').submit(function(e) {
                e.preventDefault();
                const formData = $(this).serialize();
                const spinner = document.getElementById('feedbackSpinner');
                
                // Show loading spinner
                spinner.style.display = 'block';
                
                $.ajax({
                    type: "POST",
                    url: "product_details.php?id=<?php echo htmlspecialchars($productId); ?>",
                    data: formData,
                    success: function(response) {
                        const result = parseResponse(response);
                        
                        if (result.success) {
                            alert(result.message || 'Feedback submitted successfully');
                            $('#commentInput').val('');
                            $('.star-rating .star').removeClass('selected');
                            $('#ratingInput').val('');
                            window.location.reload();
                        } else {
                            alert(result.message || 'Error submitting feedback');
                        }
                    },
                    error: function(xhr, status, error) {
                        alert("Error submitting feedback: " + error);
                    },
                    complete: function() {
                        spinner.style.display = 'none';
                    }
                });
            });

            // Initialize tooltips
            $('[data-bs-toggle="tooltip"]').tooltip();
        });

        // Auto-rotate feedback carousel
        setInterval(nextFeedback, 5000);
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