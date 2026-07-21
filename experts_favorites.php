<?php
session_start();
$host = 'localhost';
$db = 'agriculture';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the user is logged in
$isLoggedIn = isset($_SESSION['user_id']); // Replace 'user_id' with your session variable for logged-in users

// Fetch theme and language from session
$theme = isset($_SESSION['theme']) ? $_SESSION['theme'] : 'light';
$language = isset($_SESSION['language']) ? $_SESSION['language'] : 'en';


// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];

// Fetch favorite experts
$sql = "SELECT e.* FROM experts e 
        JOIN user_favorites uf ON e.expert_id = uf.expert_id 
        WHERE uf.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$favorites = $result->fetch_all(MYSQLI_ASSOC);

// Fetch theme and language from session
$theme = isset($_SESSION['theme']) ? $_SESSION['theme'] : 'light';
$language = isset($_SESSION['language']) ? $_SESSION['language'] : 'en';
?>

<!DOCTYPE html>
<html lang="en" data-theme="<?php echo $theme; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title data-lang-key="favorites_title">My Favorite Experts - AgroMeds</title>
    
    <!-- Core CSS -->
    <link rel="stylesheet" href="home.css">
    <link rel="stylesheet" href="theme_home.css">
    
    <!-- Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    
    <!-- Animation Libraries -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/hover.css/2.3.1/css/hover-min.css"/>
    
    <!-- Custom CSS with advanced animations -->
    <style>
        :root {
            --primary-color: #4CAF50;
            --secondary-color: #2E7D32;
            --accent-color: #8BC34A;
            --danger-color: #F44336;
            --text-color: #333;
            --light-bg: #f9f9f9;
            --card-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            --card-shadow-hover: 0 12px 20px rgba(0, 0, 0, 0.15);
        }
        
        [data-theme="dark"] {
            --primary-color: #689F38;
            --secondary-color: #33691E;
            --accent-color: #7CB342;
            --text-color: #f0f0f0;
            --light-bg: #2a2a2a;
            --card-shadow: 0 6px 15px rgba(0, 0, 0, 0.3);
            --card-shadow-hover: 0 12px 20px rgba(0, 0, 0, 0.4);
        }
        
        body {
            background-color: var(--light-bg);
            color: var(--text-color);
            transition: all 0.5s ease;
        }

        .navbar .settings-icon {
            font-size: 1.5rem;
            color: #555;
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
        
        .favorites-container {
            padding: 3rem 0;
            min-height: 70vh;
        }
        
        .section-title {
            position: relative;
            margin-bottom: 3rem;
            text-align: center;
            font-weight: 700;
        }
        
        .section-title:after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: var(--primary-color);
            border-radius: 2px;
        }
        
        .favorite-card {
            transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
            margin-bottom: 2rem;
            border: none;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--card-shadow);
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            transform: translateY(0);
            opacity: 1;
        }
        
        .favorite-card.removing {
            transform: translateX(100%) rotate(10deg);
            opacity: 0;
        }
        
        .favorite-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--card-shadow-hover);
        }
        
        .card-img-top {
            height: 220px;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .favorite-card:hover .card-img-top {
            transform: scale(1.05);
        }
        
        .card-body {
            padding: 1.5rem;
            position: relative;
        }
        
        .expert-name {
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--primary-color);
        }
        
        .expert-specialty {
            color: var(--text-color);
            opacity: 0.8;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }
        
        .card-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .btn-details {
            background: var(--primary-color);
            border: none;
            padding: 0.5rem 1.2rem;
            border-radius: 30px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-details:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .btn-remove {
            background: transparent;
            border: 1px solid var(--danger-color);
            color: var(--danger-color);
            padding: 0.5rem 1.2rem;
            border-radius: 30px;
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-remove:hover {
            background: var(--danger-color);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(244, 67, 54, 0.2);
        }
        
        .empty-favorites {
            text-align: center;
            padding: 5rem 1rem;
            animation: fadeIn 1s ease;
        }
        
        .empty-icon {
            font-size: 5rem;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            opacity: 0.7;
            animation: pulse 2s infinite;
        }
        
        .empty-title {
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--text-color);
        }
        
        .empty-text {
            color: var(--text-color);
            opacity: 0.7;
            margin-bottom: 2rem;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .btn-explore {
            background: var(--primary-color);
            border: none;
            padding: 0.8rem 2rem;
            border-radius: 30px;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .btn-explore:hover {
            background: var(--secondary-color);
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }
        
        /* Confirmation modal */
        .confirmation-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        
        .confirmation-modal.active {
            opacity: 1;
            visibility: visible;
        }
        
        .modal-content {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            max-width: 400px;
            width: 90%;
            text-align: center;
            transform: translateY(-20px);
            transition: all 0.3s ease;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }
        
        .confirmation-modal.active .modal-content {
            transform: translateY(0);
        }
        
        .modal-icon {
            font-size: 3rem;
            color: var(--danger-color);
            margin-bottom: 1rem;
        }
        
        .modal-title {
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        .modal-text {
            margin-bottom: 2rem;
            color: #666;
        }
        
        .modal-actions {
            display: flex;
            justify-content: center;
            gap: 1rem;
        }
        
        .modal-btn {
            padding: 0.6rem 1.5rem;
            border-radius: 30px;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        
        .modal-btn-cancel {
            background: #f0f0f0;
            color: #333;
        }
        
        .modal-btn-confirm {
            background: var(--danger-color);
            color: white;
        }
        
        .modal-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        /* Floating action button */
        .fab {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            background: var(--primary-color);
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            font-size: 1.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            cursor: pointer;
            z-index: 100;
            transition: all 0.3s ease;
            animation: bounce 2s infinite;
        }
        
        .fab:hover {
            transform: scale(1.1) translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
        }
        
        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-15px); }
            60% { transform: translateY(-7px); }
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .favorites-container {
                padding: 2rem 0;
            }
            
            .card-actions {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .btn-details, .btn-remove {
                width: 100%;
                text-align: center;
            }
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
    </style>
</head>
<body>
    <!-- Include your navbar here -->
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
    
    <div class="favorites-container container">
        <h2 class="section-title animate__animated animate__fadeIn" data-lang-key="my_favorites">My Favorite Experts</h2>
        
        <?php if (count($favorites) > 0): ?>
            <div class="row animate__animated animate__fadeInUp">
                <?php foreach ($favorites as $expert): ?>
                    <div class="col-lg-4 col-md-6" id="expert-<?php echo $expert['expert_id']; ?>">
                        <div class="card favorite-card hvr-float">
                            <img src="<?php echo htmlspecialchars($expert['image_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($expert['name']); ?>">
                            <div class="card-body">
                                <h5 class="expert-name"><?php echo htmlspecialchars($expert['name']); ?></h5>
                                <p class="expert-specialty"><?php echo htmlspecialchars($expert['specialization']); ?></p>
                                <div class="card-actions">
                                    <a href="expert_details.php?id=<?php echo $expert['expert_id']; ?>" class="btn btn-details" data-lang-key="view_details">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <button class="btn btn-remove remove-favorite" data-expert-id="<?php echo $expert['expert_id']; ?>" data-expert-name="<?php echo htmlspecialchars($expert['name']); ?>">
                                        <i class="fas fa-heart"></i> Remove
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-favorites animate__animated animate__fadeIn">
                <div class="empty-icon">
                    <i class="far fa-heart"></i>
                </div>
                <h3 class="empty-title" data-lang-key="no_favorites">Your favorites list is empty</h3>
                <p class="empty-text" data-lang-key="explore_experts">You haven't added any experts to your favorites yet. Discover our agriculture specialists and save your favorites for easy access!</p>
                <a href="Features.php#experts" class="btn btn-explore" data-lang-key="browse_experts">
                    <i class="fas fa-search"></i> Browse Experts
                </a>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Floating action button -->
    <div class="fab hvr-bob" onclick="window.location.href='Features.php#experts'">
        <i class="fas fa-plus"></i>
    </div>
    
    <!-- Confirmation Modal -->
    <div class="confirmation-modal" id="confirmationModal">
        <div class="modal-content">
            <div class="modal-icon">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <h4 class="modal-title">Remove from Favorites</h4>
            <p class="modal-text" id="modalText">Are you sure you want to remove this expert from your favorites?</p>
            <div class="modal-actions">
                <button class="modal-btn modal-btn-cancel" id="cancelRemove">Cancel</button>
                <button class="modal-btn modal-btn-confirm" id="confirmRemove">Remove</button>
            </div>
        </div>
    </div>
    
    <!-- Include your footer here -->
    
    <!-- JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.11.4/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    
    <script>
    $(document).ready(function() {
        let currentExpertId = null;
        let currentExpertName = null;
        
        // Initialize tooltips
        $('[data-toggle="tooltip"]').tooltip();
        
        // Remove from favorites with confirmation
        $('.remove-favorite').click(function(e) {
            e.preventDefault();
            currentExpertId = $(this).data('expert-id');
            currentExpertName = $(this).data('expert-name');
            
            // Update modal text
            $('#modalText').text(`Are you sure you want to remove ${currentExpertName} from your favorites?`);
            
            // Show modal
            $('#confirmationModal').addClass('active');
        });
        
        // Cancel removal
        $('#cancelRemove').click(function() {
            $('#confirmationModal').removeClass('active');
            currentExpertId = null;
            currentExpertName = null;
        });
        
        // Confirm removal
        $('#confirmRemove').click(function() {
            if (!currentExpertId) return;
            
            // Close modal
            $('#confirmationModal').removeClass('active');
            
            // Animate card removal
            const card = $(`#expert-${currentExpertId} .favorite-card`);
            
            // Add removal animation
            card.addClass('removing');
            
            // Send AJAX request
            $.ajax({
                url: 'add_to_favoritess.php',
                method: 'POST',
                data: {
                    expert_id: currentExpertId,
                    action: 'remove'
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // After animation completes, remove card
                        setTimeout(() => {
                            card.parent().remove();
                            
                            // Show success notification
                            showNotification(`${currentExpertName} has been removed from favorites`, 'success');
                            
                            // Check if no favorites left
                            if ($('.favorite-card').length === 0) {
                                // Reload to show empty state with animation
                                setTimeout(() => {
                                    location.reload();
                                }, 500);
                            }
                        }, 500);
                    } else {
                        // Show error notification
                        showNotification('Failed to remove from favorites', 'error');
                        // Reset card if error occurs
                        card.removeClass('removing');
                    }
                },
                error: function() {
                    // Show error notification
                    showNotification('Error connecting to server', 'error');
                    // Reset card if error occurs
                    card.removeClass('removing');
                }
            });
            
            currentExpertId = null;
            currentExpertName = null;
        });
        
        // Show notification function
        function showNotification(message, type) {
            // Create notification element
            const notification = $(`
                <div class="notification ${type} animate__animated animate__fadeInUp">
                    <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
                    ${message}
                </div>
            `);
            
            // Add to body
            $('body').append(notification);
            
            // Auto remove after 3 seconds
            setTimeout(() => {
                notification.addClass('animate__fadeOutDown');
                setTimeout(() => {
                    notification.remove();
                }, 500);
            }, 3000);
        }
        
        // Add CSS for notifications
        $('<style>')
            .prop('type', 'text/css')
            .html(`
                .notification {
                    position: fixed;
                    bottom: 30px;
                    left: 50%;
                    transform: translateX(-50%);
                    padding: 15px 25px;
                    border-radius: 8px;
                    color: white;
                    font-weight: 500;
                    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
                    z-index: 1000;
                    display: flex;
                    align-items: center;
                    gap: 10px;
                    max-width: 90%;
                }
                
                .notification i {
                    font-size: 1.2rem;
                }
                
                .notification.success {
                    background: #4CAF50;
                }
                
                .notification.error {
                    background: #F44336;
                }
            `)
            .appendTo('head');
    });
    </script>
</body>
</html>