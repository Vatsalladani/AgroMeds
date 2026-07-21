<?php
session_start();
require 'vendor/autoload.php';

// Database connection
$host = 'localhost';
$dbname = 'agriculture';
$username = 'root';
$password = '';
$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$user_id = $isLoggedIn ? $_SESSION['user_id'] : null;
$user_email = '';
$user_name = '';

if ($isLoggedIn) {
    $user_sql = "SELECT full_name, email FROM users WHERE user_id = $user_id";
    $user_result = $conn->query($user_sql);
    if ($user_result->num_rows > 0) {
        $user = $user_result->fetch_assoc();
        $user_email = $user['email'];
        $user_name = $user['full_name'];
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $subject = $conn->real_escape_string($_POST['subject']);
    $message = $conn->real_escape_string($_POST['message']);
    $priority = $conn->real_escape_string($_POST['priority']);
    
    // Insert into database
    $insert_sql = "INSERT INTO support_tickets (user_id, name, email, subject, message, priority, status, created_at) 
                  VALUES (".($user_id ? $user_id : 'NULL').", '$name', '$email', '$subject', '$message', '$priority', 'Open', NOW())";
    
    if ($conn->query($insert_sql)) {
        $_SESSION['support_success'] = "Your support request has been submitted successfully! We'll get back to you soon.";
        header("Location: support.php");
        exit();
    } else {
        $_SESSION['support_error'] = "There was an error submitting your request. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Center | AgroMeds</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6a11cb;
            --secondary: #2575fc;
            --success: #28a745;
            --warning: #ffc107;
            --danger: #dc3545;
            --light: #f8f9fa;
            --dark: #343a40;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
            min-height: 100vh;
        }

        .hero-section {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 100px 0;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="none"><path fill="rgba(255,255,255,0.05)" d="M0,0 L100,0 L100,100 L0,100 Z" /></svg>');
            background-size: cover;
            opacity: 0.5;
        }

        .hero-content {
            position: relative;
            z-index: 1;
        }

        .support-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            transition: all 0.4s ease;
            overflow: hidden;
            margin-bottom: 30px;
            border: none;
            opacity: 0;
            transform: translateY(30px);
        }

        .support-card.animate {
            opacity: 1;
            transform: translateY(0);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }

        .support-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.12);
        }

        .card-icon {
            font-size: 2.5rem;
            margin-bottom: 20px;
            color: var(--primary);
            transition: all 0.4s ease;
        }

        .support-card:hover .card-icon {
            transform: scale(1.2);
            color: var(--secondary);
        }

        .support-form {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            padding: 30px;
            opacity: 0;
            transform: scale(0.95);
            transition: all 0.6s ease;
        }

        .support-form.animate {
            opacity: 1;
            transform: scale(1);
        }

        .form-control, .form-select {
            border-radius: 10px;
            padding: 12px 15px;
            border: 1px solid #e0e0e0;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.25rem rgba(106, 17, 203, 0.25);
        }

        .btn-submit {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(106, 17, 203, 0.3);
        }

        .btn-submit::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(-100%);
            transition: all 0.4s ease;
        }

        .btn-submit:hover::after {
            transform: translateX(0);
        }

        .faq-item {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 15px;
            overflow: hidden;
            transition: all 0.3s ease;
            opacity: 0;
            transform: translateY(20px);
        }

        .faq-item.animate {
            opacity: 1;
            transform: translateY(0);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }

        .faq-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .faq-header {
            padding: 15px 20px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(135deg, rgba(106, 17, 203, 0.1), rgba(37, 117, 252, 0.1));
            transition: all 0.3s ease;
        }

        .faq-header:hover {
            background: linear-gradient(135deg, rgba(106, 17, 203, 0.2), rgba(37, 117, 252, 0.2));
        }

        .faq-header h5 {
            margin: 0;
            font-weight: 600;
            color: var(--dark);
        }

        .faq-header i {
            transition: all 0.3s ease;
        }

        .faq-body {
            padding: 0 20px;
            max-height: 0;
            overflow: hidden;
            transition: all 0.4s ease;
        }

        .faq-item.active .faq-body {
            padding: 20px;
            max-height: 500px;
        }

        .faq-item.active .faq-header i {
            transform: rotate(180deg);
        }

        .contact-method {
            text-align: center;
            padding: 30px;
            border-radius: 15px;
            background: white;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.4s ease;
            opacity: 0;
            transform: translateY(30px);
        }

        .contact-method.animate {
            opacity: 1;
            transform: translateY(0);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }

        .contact-method:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
            background: linear-gradient(135deg, rgba(106, 17, 203, 0.05), rgba(37, 117, 252, 0.05));
        }

        .contact-icon {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 15px;
            transition: all 0.4s ease;
        }

        .contact-method:hover .contact-icon {
            transform: scale(1.2);
            color: var(--secondary);
        }

        /* Animations */
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
            100% { transform: translateY(0px); }
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        @keyframes ripple {
            0% { transform: scale(0); opacity: 1; }
            100% { transform: scale(2); opacity: 0; }
        }

        .animate-float {
            animation: float 3s ease-in-out infinite;
        }

        .animate-pulse {
            animation: pulse 2s infinite;
        }

        .ripple-effect {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.4);
            transform: scale(0);
            animation: ripple 0.6s linear;
            pointer-events: none;
        }

        /* Floating chat button */
        .chat-button {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            box-shadow: 0 10px 25px rgba(106, 17, 203, 0.3);
            cursor: pointer;
            z-index: 100;
            transition: all 0.3s;
        }

        .chat-button:hover {
            transform: scale(1.1) translateY(-5px);
            box-shadow: 0 15px 30px rgba(106, 17, 203, 0.4);
        }

        .chat-button .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            width: 20px;
            height: 20px;
            background: var(--danger);
            color: white;
            border-radius: 50%;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: pulse 1.5s infinite;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .hero-section {
                padding: 60px 0;
            }
            
            .support-card, .contact-method {
                margin-bottom: 20px;
            }
            
            .chat-button {
                width: 50px;
                height: 50px;
                font-size: 20px;
                bottom: 20px;
                right: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="hero-content text-center">
                <h1 class="display-4 fw-bold mb-4 animate-float">How Can We Help You?</h1>
                <p class="lead mb-5">Our support team is here to assist you with any questions or issues you may have.</p>
                <div class="d-flex justify-content-center gap-3">
                    <a href="#contact-form" class="btn btn-light btn-lg px-4 py-2 ripple">Contact Us</a>
                    <a href="#faq" class="btn btn-outline-light btn-lg px-4 py-2 ripple">Browse FAQs</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Support Options -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold gradient-text">Support Options</h2>
                <p class="text-muted">Choose the best way to get the help you need</p>
            </div>
            
                
                <div class="col-md-4">
                    <div class="support-card animate delay-2">
                        <div class="card-body text-center p-4">
                            <div class="card-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <h4>Email Support</h4>
                            <p class="text-muted">Send us an email and we'll get back to you within 24 hours.</p>
                            <a href="mailto:support@agromeds.com" class="btn btn-outline-primary">Email Us</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="support-card animate delay-3">
                        <div class="card-body text-center p-4">
                            <div class="card-icon">
                                <i class="fas fa-phone-alt"></i>
                            </div>
                            <h4>Phone Support</h4>
                            <p class="text-muted">Call our support line for direct assistance from our team.</p>
                            <a href="tel:+18005551234" class="btn btn-outline-primary">Call Now</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Form -->
    <section id="contact-form" class="py-5 bg-light">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="support-form animate">
                        <h2 class="text-center mb-4 fw-bold">Contact Our Support Team</h2>
                        <p class="text-center text-muted mb-5">Fill out the form below and we'll get back to you as soon as possible</p>
                        
                        <?php if (isset($_SESSION['support_success'])): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <?php echo $_SESSION['support_success']; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                            <?php unset($_SESSION['support_success']); ?>
                        <?php endif; ?>
                        
                        <?php if (isset($_SESSION['support_error'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php echo $_SESSION['support_error']; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                            <?php unset($_SESSION['support_error']); ?>
                        <?php endif; ?>
                        
                        <form method="POST" action="support.php">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Full Name</label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?php echo $isLoggedIn ? htmlspecialchars($user_name) : ''; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo $isLoggedIn ? htmlspecialchars($user_email) : ''; ?>" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="subject" class="form-label">Subject</label>
                                <input type="text" class="form-control" id="subject" name="subject" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="priority" class="form-label">Priority</label>
                                <select class="form-select" id="priority" name="priority" required>
                                    <option value="Low">Low</option>
                                    <option value="Medium" selected>Medium</option>
                                    <option value="High">High</option>
                                    <option value="Urgent">Urgent</option>
                                </select>
                            </div>
                            
                            <div class="mb-4">
                                <label for="message" class="form-label">Your Message</label>
                                <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                            </div>
                            
                            <div class="text-center">
                                <button type="submit" class="btn btn-submit px-5 py-3">Submit Request</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section id="faq" class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold gradient-text">Frequently Asked Questions</h2>
                <p class="text-muted">Find answers to common questions about our products and services</p>
            </div>
            
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="faq-item animate delay-1">
                        <div class="faq-header">
                            <h5>How do I track my order?</h5>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-body">
                            <p>You can track your order by logging into your account and visiting the "My Orders" section. Once your order has shipped, you'll receive a tracking number via email that you can use to monitor your package's progress.</p>
                            <p>If you're having trouble tracking your order, please contact our support team for assistance.</p>
                        </div>
                    </div>
                    
                    <div class="faq-item animate delay-2">
                        <div class="faq-header">
                            <h5>What is your return policy?</h5>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-body">
                            <p>We offer a 30-day return policy for most items. To be eligible for a return, your item must be unused and in the same condition that you received it, with original packaging.</p>
                            <p>To initiate a return, please visit the "My Orders" section in your account and follow the return process. You'll receive a return label and instructions via email.</p>
                        </div>
                    </div>
                    
                    <div class="faq-item animate delay-3">
                        <div class="faq-header">
                            <h5>How can I change my shipping address?</h5>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-body">
                            <p>If your order hasn't shipped yet, you can change your shipping address by contacting our support team immediately. Once an order has been processed and shipped, we cannot change the delivery address.</p>
                            <p>To update your default shipping address for future orders, go to "Account Settings" in your profile.</p>
                        </div>
                    </div>
                    
                    <div class="faq-item animate delay-4">
                        <div class="faq-header">
                            <h5>What payment methods do you accept?</h5>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-body">
                            <p>We accept all major credit and debit cards (Visa, MasterCard, American Express), PayPal, and bank transfers. We also offer cash on delivery for select locations.</p>
                            <p>All transactions are secured with SSL encryption to ensure your payment information is protected.</p>
                        </div>
                    </div>
                    
                    <div class="faq-item animate delay-5">
                        <div class="faq-header">
                            <h5>How do I contact customer support?</h5>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-body">
                            <p>You can contact our customer support team through multiple channels:</p>
                            <ul>
                                <li>Live Chat: Available 24/7 on our website</li>
                                <li>Email: support@agromeds.com (response within 24 hours)</li>
                                <li>Phone: 1-800-555-1234 (Monday-Friday, 9am-5pm EST)</li>
                                <li>Contact Form: Fill out the form above</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Methods -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold gradient-text">Other Ways to Reach Us</h2>
                <p class="text-muted">Connect with us through these additional channels</p>
            </div>
            
            <div class="row">
                <div class="col-md-4">
                    <div class="contact-method animate delay-1">
                        <div class="contact-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <h4>Visit Us</h4>
                        <p class="text-muted">123 Agriculture Street<br>Farmville, PV 12345<br>United States</p>
                        <a href="#" class="btn btn-outline-primary">Get Directions</a>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="contact-method animate delay-2">
                        <div class="contact-icon">
                            <i class="fas fa-comments"></i>
                        </div>
                        <h4>Social Media</h4>
                        <p class="text-muted">Connect with us on social media for updates and support</p>
                        <div class="d-flex justify-content-center gap-3">
                            <a href="#" class="text-primary"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" class="text-primary"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="text-primary"><i class="fab fa-instagram"></i></a>
                            <a href="#" class="text-primary"><i class="fab fa-linkedin-in"></i></a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="contact-method animate delay-3">
                        <div class="contact-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h4>Support Hours</h4>
                        <p class="text-muted">
                            Monday-Friday: 9am - 8pm EST<br>
                            Saturday: 10am - 6pm EST<br>
                            Sunday: 12pm - 5pm EST
                        </p>
                        <a href="#" class="btn btn-outline-primary">Holiday Schedule</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Floating Chat Button -->
    <div class="chat-button ripple">
        <i class="fas fa-comment-dots"></i>
        <span class="notification-badge">3</span>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Animate elements when they come into view
            function animateOnScroll() {
                $('.support-card, .support-form, .faq-item, .contact-method').each(function() {
                    var element = $(this);
                    var position = element.offset().top;
                    var scroll = $(window).scrollTop();
                    var windowHeight = $(window).height();
                    
                    if (scroll + windowHeight > position + 100) {
                        element.addClass('animate');
                    }
                });
            }
            
            // Initial check
            animateOnScroll();
            
            // Check on scroll
            $(window).scroll(function() {
                animateOnScroll();
            });
            
            // FAQ accordion functionality
            $('.faq-header').click(function() {
                $(this).parent().toggleClass('active');
                $(this).parent().siblings().removeClass('active');
            });
            
            // Ripple effect for buttons
            $('.ripple').click(function(e) {
                var $this = $(this);
                var $offset = $this.offset();
                var $circle = $this.find('.ripple-effect');
                
                // Remove old ripple circle
                $circle && $circle.remove();
                
                // Create new ripple circle
                $circle = $('<span class="ripple-effect"></span>');
                
                // Get click coordinates
                var x = e.pageX - $offset.left;
                var y = e.pageY - $offset.top;
                
                // Add ripple circle to button
                $this.append($circle);
                
                // Position and animate ripple
                $circle.css({
                    'top': y + 'px',
                    'left': x + 'px'
                }).addClass('ripple-animate');
            });
            
            // Add ripple effect to all buttons with ripple class
            $('.ripple').each(function() {
                $(this).append('<span class="ripple-effect"></span>');
            });
            
            // Start chat button functionality
            $('.start-chat-btn').click(function() {
                alert('Our live chat service will open in a new window. Please make sure pop-ups are enabled for this site.');
                // In a real implementation, this would open your chat widget
            });
            
            // Chat button functionality
            $('.chat-button').click(function() {
                alert('Live chat is opening...');
                // In a real implementation, this would open your chat widget
            });
        });
    </script>
</body>
</html>