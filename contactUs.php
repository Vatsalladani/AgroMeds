<?php
session_start(); // Start session to check login status

// Check if the user is logged in
$isLoggedIn = isset($_SESSION['user_id']); // Replace 'user_id' with your session variable for logged-in users
$conn = new mysqli("localhost", "root", "", "agriculture");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch theme and language settings
$theme = isset($_SESSION['theme']) ? $_SESSION['theme'] : 'light';
$language = isset($_SESSION['language']) ? $_SESSION['language'] : 'en';

// Check if the user is logged in
$isUserLoggedIn = isset($_SESSION['user_id']);

// Initialize success message variable
$successMessage = '';

// Fetch user details if logged in
if ($isUserLoggedIn) {
    $user_id = $_SESSION['user_id'];
    $userSql = "SELECT email, phone FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($userSql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $userResult = $stmt->get_result();
    $user = $userResult->fetch_assoc();
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ($isUserLoggedIn) {
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $subject = $_POST['subject'];
        $query = $_POST['query'];
        $email = $user['email']; // Use email from database if logged in
        $phone = $user['phone']; // Use phone from database if logged in

        $insertSql = "INSERT INTO contactus (user_id, first_name, last_name, email, phone, subject, query) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insertSql);
        $stmt->bind_param("issssss", $user_id, $first_name, $last_name, $email, $phone, $subject, $query);
        if ($stmt->execute()) {
            // Set success message
            $successMessage = "<div class='alert alert-success' role='alert'>Your query has been submitted successfully.</div>";
        } else {
            echo "<script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops!',
                        text: 'Something went wrong, please try again.',
                    });
                  </script>";
        }
        $stmt->close();
    } else {
        echo "<script>
                Swal.fire({
                    icon: 'warning',
                    title: 'Login Required!',
                    text: 'Please login or register to submit your query.',
                });
              </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="<?php echo $language; ?>" data-theme="<?php echo $theme; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title data-lang-key="contactTitle">Contact Us</title>

    <!-- CSS Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/owl-carousel/1.3.3/owl.carousel.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.10.4/sweetalert2.min.css" rel="stylesheet">

    <style>:root {
            --primary-color: #28a745;
            --secondary-color: #218838;
            --background-color: #f8f9fa;
            --text-color: #000000;
        }


        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f8f5;
            margin: 0;
            padding: 0;
        }

        .header {
            background: url('Images/bg8.jpg') no-repeat center center;
            background-size: cover;
            background-position: center;
            color: white;
            padding: 180px 0;
            text-align: center;
        }

        .header h1 {
            font-size: 3rem;
            font-weight: bold;
        }

        .navbar .settings-icon {
            font-size: 1.5rem;
            color: #555;
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
.navbar {
    background-color: var(--primary-color) !important;
}

/* Override navbar background color for light theme */
[data-theme="light"] .navbar {
    background-color: white !important;
}
        /* Contact Form Section Styling */
        .contact-container {
            max-width: 1200px; /* Increased the container size */
            margin: 2rem auto;
            background: url('Images/bg8.jpg') no-repeat center/cover; /* Image as background */
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            margin-top: 110px;
            margin-bottom: 190px;
            overflow: hidden;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
        }.contact-info {
            flex: 1;
            padding: 40px;
            color: white; /* Adjust text color for better visibility */
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5); /* Add text shadow */
        }

        .contact-info h2 {
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .contact-info p {
            font-size: 1.1rem;
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        .contact-info .platform-links a {
            display: inline-block;
            margin-right: 15px;
            font-size: 1.5rem;
            color: white;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .contact-info .platform-links a:hover {
            color: #ddd;
        }
        /* Contact Container */
.contact-container {
    background-color: rgba(255, 255, 255, 0.8); /* Default background color */
    transition: box-shadow 0.3s ease; /* Smooth transition for shadow */
    border-radius: 10px; /* Optional rounded corners */
}

/* Hover Effect for Contact Container */
.contact-container:hover {
    box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.2); /* Shadow effect on hover */
}
.contact-container .form-control:hover,
.contact-container .form-control:focus {
    border-color: rgba(0, 123, 255, 1); /* Change border color to a theme color (blue) on hover/focus */
    background-color: rgba(230, 230, 250, 0.5); /* Light tint on hover/focus for better visibility */
}


        /* Form Container */
.form-container {
    flex: 2;
    padding: 40px;
    background-color: rgba(var(--section-background-color-rgb, 255, 255, 255), 0.8); /* Transparent theme color */
    border-radius: 0;
    box-shadow: none;
    animation: fadeIn 1s ease-in-out;
    transition: background-color 0.3s ease, box-shadow 0.3s ease;
}

/* Hover Effect for Form Container */
.form-container:hover {
    background-color: rgba(var(--section-background-color-rgb, 255, 255, 255), 1); /* Fully opaque theme color */
    box-shadow: 0px 4px 10px rgba(var(--section-background-color-rgb, 0, 0, 0), 0.4); /* Slight shadow effect */
}

/* Form Fields */
.form-container .form-control {
    border: 1px solid rgba(var(--section-background-color-rgb, 0, 0, 0), 0.5); /* Border based on theme color */
    transition: border-color 0.3s ease, background-color 0.3s ease;
}

/* Hover Effect for Form Fields */
.form-container .form-control:hover,
.form-container .form-control:focus {
    border-color: rgba(var(--section-background-color-rgb, 0, 0, 0), 1); /* Darker border on hover/focus */
    background-color: rgba(var(--section-background-color-rgb, 255, 255, 255), 0.2); /* Slightly tinted background */
}

/* Theme Definitions with RGB Values */
.theme-light {
    --section-background-color-rgb: 248, 249, 250; /* Light gray RGB */
}

.theme-dark {
    --section-background-color-rgb: 52, 58, 64; /* Dark gray RGB */
}

.theme-blue {
    --section-background-color-rgb: 233, 245, 255; /* Light blue RGB */
}

.theme-green {
    --section-background-color-rgb: 232, 245, 233; /* Light green RGB */
}

.theme-pink {
    --section-background-color-rgb: 255, 240, 246; /* Light pink RGB */
}

.theme-ocean {
    --section-background-color-rgb: 227, 242, 253; /* Ocean blue RGB */
}

.theme-sunset {
    --section-background-color-rgb: 255, 243, 224; /* Sunset orange RGB */
}

.theme-forest {
    --section-background-color-rgb: 232, 245, 233; /* Forest green RGB */
}

.theme-violet {
    --section-background-color-rgb: 243, 229, 245; /* Light violet RGB */
}


        .form-group {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between; /* Distribute space evenly */
            margin-bottom: 1.5rem;
        }

        .form-group label {
            flex-basis: 100%;
            margin-bottom: 0.5rem;
            color: #555;
        }

        .form-control {
            width: calc(50% - 0.5rem); /* Adjust width for two fields in a row */
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-control:focus {
            border-color: #5cb85c;
            outline: none;
            box-shadow: 0 0 5px rgba(92, 184, 92, 0.5);
        }

        .form-control:disabled {
            background-color: #e9ecef;
            opacity: 1;
        }

        .form-group textarea.form-control {
            width: 100%; /* Full width for the textarea */
        }

        .btn-success {
            background-color: #5cb85c;
            border-color: #5cb85c;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn-success:hover {
            background-color: #449d44;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .contact-container {
                flex-direction: column;
            }

            .contact-info,
            .form-container {
                width: 100%;
                padding: 20px;
            }

            .form-control {
                width: 100%; /* Full width for form controls on smaller screens */
            }
        }

        /* Keyframes for animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        /* Style for the login/register message box */
        .login-required {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            text-align: center;
        }

        .login-required a {
            color: #721c24;
            text-decoration: underline;
        }
        /* Styles for the success message */
        .alert-success {
            color: #3c763d;
            background-color: #dff0d8;
            border-color: #d6e9c6;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
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

    </style>
    <style>
        body[data-theme="dark"] {
            background-color: #121212;
            color: #e0e0e0;
        }

        body[data-theme="blue-gradient"] {
            background: linear-gradient(45deg, #1e3c72, #2a5298);
            color: #ffffff;
        }

        body[data-theme="pink-gradient"] {
            background: linear-gradient(45deg, #ff9a9e, #fad0c4);
            color: #000000;
        }body[data-theme="green-gradient"] {
            background: linear-gradient(45deg, #56ab2f, #a8e063);
            color: #000000;
        }

        body[data-theme="light"] {
            background-color: #ffffff;
            color: #000000;
        }
        #contacts {
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
<script src="settings.js"></script>

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
<header class="header">
    <h1 data-lang-key="contactHeader">Contact Us</h1>
    <p data-lang-key="contactDesc">We are here to help you!</p>
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

<section id="contacts" class="py-5 bg-red">
<div class="container mt-5">
    <div class="contact-container">
        <div class="contact-info">
            <h2 data-lang-key="contactInformation">Contact Information</h2>
            <p data-lang-key="contactAssist">We're here to assist you with any questions or concerns. Feel free to reach out through the following
                channels:</p>
            <p><strong data-lang-key="address">Address:</strong> 123 Agri Street, Farmville</p>
            <p><strong>Phone:</strong> +91 99999 99999</p>
            <p><strong>Email:</strong> info@agromeds.com</p>... 
       <div class="platform-links">

    <!-- LinkedIn -->
    <a href="https://www.linkedin.com/in/vatsalladani"
       target="_blank"
       rel="noopener noreferrer"
       title="LinkedIn">
        <i class="fab fa-linkedin"></i>
    </a>

    <!-- GitHub -->
    <a href="https://github.com/Vatsalladani"
       target="_blank"
       rel="noopener noreferrer"
       title="GitHub">
        <i class="fab fa-github"></i>
    </a>

    <!-- Portfolio (Coming Soon) -->
    <a href="javascript:void(0);"
       title="Portfolio Coming Soon">
        <i class="fas fa-globe"></i>
    </a>

</div>
        </div>

        <div class="form-container">
            <h2 class="text-center mb-4" data-lang-key="getInTouch">Get in Touch</h2>

            <?php if (!$isUserLoggedIn): ?>
                <div class="login-required">
                    <span data-lang-key="loginMessage">Please <a href="login.php" data-lang-key="login">login</a> or <a
                                href="register.php" data-lang-key="register">register</a> to use the contact form.</span>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <?php if ($successMessage): ?>
                    <div class="row justify-content-center">
                        <div class="col-md-8">
                            <?php echo $successMessage; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label class="form-label" data-lang-key="firstName">First Name</label>
                    <input type="text" class="form-control" name="first_name" required
                           <?php echo (!$isUserLoggedIn) ? 'disabled' : ''; ?>>
                </div>
                <div class="form-group">
                    <label class="form-label" data-lang-key="lastName">Last Name</label>
                    <input type="text" class="form-control" name="last_name" required
                           <?php echo (!$isUserLoggedIn) ? 'disabled' : ''; ?>>
                </div>
                <div class="form-group">
                    <label class="form-label" data-lang-key="email">Email</label>
                    <input type="email" class="form-control"
                           value="<?php echo ($isUserLoggedIn) ? htmlspecialchars($user['email']) : ''; ?>"
                           readonly <?php echo (!$isUserLoggedIn) ? 'disabled' : ''; ?>>
                </div>
                 <div class="form-group">
                    <label class="form-label" data-lang-key="phone">Phone</label>
                    <input type="text" class="form-control"
                           value="<?php echo ($isUserLoggedIn) ? htmlspecialchars($user['phone']) : ''; ?>"
                           readonly <?php echo (!$isUserLoggedIn) ? 'disabled' : ''; ?>>
                </div>

                <div class="form-group">
                    <label class="form-label" data-lang-key="subject">Subject</label>
                    <input type="text" class="form-control" name="subject" required
                           <?php echo (!$isUserLoggedIn) ? 'disabled' : ''; ?>>
                </div>

                <div class="form-group">
                    <label class="form-label" data-lang-key="query">Query</label>
                    <textarea class="form-control" name="query" rows="4" required
                              <?php echo (!$isUserLoggedIn) ? 'disabled' : ''; ?>></textarea>
                </div>

                <button type="submit" class="btn btn-success" data-lang-key="submit"
                        <?php echo (!$isUserLoggedIn) ? 'disabled' : ''; ?>>Submit
                </button>
            </form>
        </div>
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/i18next/21.9.1/i18next.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/owl-carousel/1.3.3/owl.carousel.min.js"></script>
<script src="script.js"></script>

<!-- JavaScript to fade out the success message -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const successMessage = document.querySelector('.alert-success');
        if (successMessage) {
            setTimeout(function() {
                successMessage.style.transition = 'opacity 1s ease-in-out';
                successMessage.style.opacity = '0';
                setTimeout(function() {
                    successMessage.style.display = 'none';
                }, 1000); // Wait for the fade-out transition to complete
            }, 3000); // Display the message for 3 seconds
        }
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
            fetch('translation_c.json')
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
</script>
<script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
</body>
</html>
<?php
$conn->close();
?>
