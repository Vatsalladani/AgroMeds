<?php
session_start();
// Database connection
$conn = new mysqli('localhost', 'root', '', 'agriculture');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create testimonials table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS testimonials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    expert_id INT NOT NULL,
    client_name VARCHAR(255) NOT NULL,
    profession VARCHAR(255) NOT NULL,
    city VARCHAR(255) NOT NULL,
    testimonial TEXT NOT NULL,
    rating INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (expert_id) REFERENCES experts(expert_id)
)");

// Create consultations table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS consultations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    expert_id INT NOT NULL,
    user_name VARCHAR(255) NOT NULL,
    user_email VARCHAR(255) NOT NULL,
    user_phone VARCHAR(20) NOT NULL,
    problem_description TEXT NOT NULL,
    preferred_date DATE NOT NULL,
    preferred_time TIME NOT NULL,
    communication_method ENUM('whatsapp', 'phone', 'video_call') NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (expert_id) REFERENCES experts(expert_id)
)");
// Get expert ID from URL
$expert_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch expert details
$expert = [];
if ($expert_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM experts WHERE expert_id = ?");
    $stmt->bind_param("i", $expert_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $expert = $result->fetch_assoc();
    $stmt->close();
}

// Process testimonial form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_testimonial'])) {
    $client_name = $conn->real_escape_string($_POST['client_name']);
    $profession = $conn->real_escape_string($_POST['profession']);
    $city = $conn->real_escape_string($_POST['city']);
    $testimonial = $conn->real_escape_string($_POST['testimonial']);
    $rating = intval($_POST['rating']);
    
    $stmt = $conn->prepare("INSERT INTO testimonials (expert_id, client_name, profession, city, testimonial, rating) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssi", $expert_id, $client_name, $profession, $city, $testimonial, $rating);
    $stmt->execute();
    $stmt->close();
    
    $_SESSION['testimonial_submitted'] = true;
    header("Location: ".$_SERVER['REQUEST_URI']);
    exit();
}

// Process consultation form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_consultation'])) {
    $user_name = $conn->real_escape_string($_POST['user_name']);
    $user_email = $conn->real_escape_string($_POST['user_email']);
    $user_phone = $conn->real_escape_string($_POST['user_phone']);
    $problem_description = $conn->real_escape_string($_POST['problem_description']);
    $preferred_date = $conn->real_escape_string($_POST['preferred_date']);
    $preferred_time = $conn->real_escape_string($_POST['preferred_time']);
    $communication_method = $conn->real_escape_string($_POST['communication_method']);
    
    $stmt = $conn->prepare("INSERT INTO consultations (expert_id, user_name, user_email, user_phone, problem_description, preferred_date, preferred_time, communication_method) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssssss", $expert_id, $user_name, $user_email, $user_phone, $problem_description, $preferred_date, $preferred_time, $communication_method);
    $stmt->execute();
    $stmt->close();
    
    $_SESSION['consultation_submitted'] = true;
    header("Location: ".$_SERVER['REQUEST_URI']);
    exit();
}

// Get theme from session
$theme = isset($_SESSION['theme']) ? $_SESSION['theme'] : 'light';

// Expert approaches - random for each expert
$approaches = [
    [
        'icon' => 'ri-lightbulb-flash-fill',
        'title' => 'Innovative Solutions',
        'desc' => 'Cutting-edge agricultural techniques tailored to your needs.'
    ],
    [
        'icon' => 'ri-leaf-fill',
        'title' => 'Sustainable Practices',
        'desc' => 'Environmentally friendly methods for long-term success.'
    ],
    [
        'icon' => 'ri-line-chart-fill',
        'title' => 'Proven Results',
        'desc' => 'Track record of improving yields and efficiency.'
    ],
    [
        'icon' => 'ri-team-fill',
        'title' => 'Community Focused',
        'desc' => 'Solutions that benefit the entire farming community.'
    ],
    [
        'icon' => 'ri-seedling-fill',
        'title' => 'Organic Methods',
        'desc' => 'Natural approaches for healthier crops and soil.'
    ],
    [
        'icon' => 'ri-water-flash-fill',
        'title' => 'Water Management',
        'desc' => 'Optimizing irrigation for maximum efficiency.'
    ],
    [
        'icon' => 'ri-bug-fill',
        'title' => 'Pest Control',
        'desc' => 'Effective strategies to protect your crops.'
    ],
    [
        'icon' => 'ri-money-dollar-circle-fill',
        'title' => 'Cost Reduction',
        'desc' => 'Methods to decrease expenses while maintaining quality.'
    ]
];

// Shuffle and pick 4 random approaches
shuffle($approaches);
$selected_approaches = array_slice($approaches, 0, 4);
?>

<!DOCTYPE html>
<html lang="en" data-theme="<?php echo $theme; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($expert['name'] ?? 'Expert Details'); ?> - AgroMeds</title>
    
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.4.0/fonts/remixicon.css" rel="stylesheet">
    
    <!-- Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    
    <style>
    :root {
        --primary-color: #28a745;
        --secondary-color: #218838;
        --background-color: #f8f9fa;
        --text-color: #212529;
        --card-bg: #ffffff;
        --shadow-color: rgba(0,0,0,0.1);
    }

    /* Theme overrides */
    .theme-dark {
        --primary-color: #343a40;
        --secondary-color: #121212;
        --background-color: #1e1e1e;
        --text-color: #f8f9fa;
        --card-bg: #2d2d2d;
        --shadow-color: rgba(0,0,0,0.3);
    }

    .theme-blue {
        --primary-color: #007bff;
        --secondary-color: #0056b3;
    }

    .theme-green {
        --primary-color: #28a745;
        --secondary-color: #218838;
    }

    body {
        font-family: 'Poppins', sans-serif;
        background-color: var(--background-color);
        color: var(--text-color);
        transition: all 0.3s ease;
    }

    /* Expert Header */
    .expert-header {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        color: white;
        padding: 100px 0 60px;
        position: relative;
        overflow: hidden;
    }

    .expert-header::before {
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

    .expert-image {
        width: 200px;
        height: 200px;
        border-radius: 50%;
        border: 5px solid white;
        object-fit: cover;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        transition: all 0.5s ease;
    }

    .expert-image:hover {
        transform: scale(1.05) rotate(5deg);
    }

    /* Expert Details Card */
    .expert-card {
        background-color: var(--card-bg);
        border-radius: 15px;
        box-shadow: 0 10px 30px var(--shadow-color);
        overflow: hidden;
        transition: all 0.3s ease;
        border: none;
    }

    .expert-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 40px var(--shadow-color);
    }

    .card-body {
        padding: 30px;
    }

    /* Contact Info */
    .contact-info {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
        padding: 15px;
        background-color: rgba(var(--primary-color-rgb), 0.1);
        border-radius: 10px;
        transition: all 0.3s ease;
    }

    .contact-info:hover {
        background-color: rgba(var(--primary-color-rgb), 0.2);
        transform: translateX(5px);
    }

    .contact-icon {
        width: 50px;
        height: 50px;
        background-color: var(--primary-color);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        margin-right: 15px;
        flex-shrink: 0;
    }

    /* Specialization Tags */
    .specialization-tag {
        display: inline-block;
        background-color: rgba(var(--primary-color-rgb), 0.2);
        color: var(--primary-color);
        padding: 5px 15px;
        border-radius: 20px;
        margin-right: 10px;
        margin-bottom: 10px;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .specialization-tag:hover {
        background-color: var(--primary-color);
        color: white;
        transform: translateY(-3px);
    }

    /* Action Buttons */
    .action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 12px 25px;
        border-radius: 50px;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.3s ease;
        margin-right: 10px;
        margin-bottom: 10px;
    }

    .primary-btn {
        background-color: var(--primary-color);
        color: white;
        box-shadow: 0 4px 15px rgba(var(--primary-color-rgb), 0.3);
    }

    .primary-btn:hover {
        background-color: var(--secondary-color);
        color: white;
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(var(--primary-color-rgb), 0.4);
    }

    .outline-btn {
        border: 2px solid var(--primary-color);
        color: var(--primary-color);
        background-color: transparent;
    }

    .outline-btn:hover {
        background-color: var(--primary-color);
        color: white;
        transform: translateY(-3px);
    }

    /* Floating Particles */
    .particles-container {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: -1;
        pointer-events: none;
    }

    .particle {
        position: absolute;
        background-color: rgba(var(--primary-color-rgb), 0.1);
        border-radius: 50%;
    }

    /* Testimonials */
    .testimonial-card {
        background-color: var(--card-bg);
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 5px 15px var(--shadow-color);
        position: relative;
        transition: all 0.3s ease;
    }

    .testimonial-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px var(--shadow-color);
    }

    .testimonial-card::before {
        content: '"';
        position: absolute;
        top: 20px;
        left: 20px;
        font-size: 60px;
        font-family: serif;
        color: rgba(var(--primary-color-rgb), 0.1);
        line-height: 1;
    }

     /* Testimonial Form Styles */
     .testimonial-form {
        background-color: var(--card-bg);
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 5px 15px var(--shadow-color);
    }
    
    .rating-stars {
        display: flex;
        gap: 5px;
        margin-bottom: 15px;
    }
    
    .rating-stars input {
        display: none;
    }
    
    .rating-stars label {
        font-size: 24px;
        color: #ccc;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .rating-stars input:checked ~ label {
        color: #ffc107;
    }
    
    .rating-stars label:hover,
    .rating-stars label:hover ~ label {
        color: #ffc107;
    }
    
    /* Consultation Modal Styles */
    .consultation-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
        z-index: 9999;
        align-items: center;
        justify-content: center;
    }
    
    .consultation-modal-content {
        background-color: var(--card-bg);
        border-radius: 15px;
        width: 90%;
        max-width: 600px;
        padding: 30px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        position: relative;
        animation: modalFadeIn 0.3s ease-out;
    }
    
    @keyframes modalFadeIn {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .close-modal {
        position: absolute;
        top: 15px;
        right: 15px;
        font-size: 24px;
        cursor: pointer;
        color: var(--text-color);
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
    }
    
    .form-control {
        width: 100%;
        padding: 12px 15px;
        border-radius: 8px;
        border: 1px solid #ddd;
        background-color: var(--card-bg);
        color: var(--text-color);
        transition: all 0.3s ease;
    }
    
    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(var(--primary-color-rgb), 0.2);
        outline: none;
    }
    
    .btn-submit {
        background-color: var(--primary-color);
        color: white;
        border: none;
        padding: 12px 25px;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 500;
        transition: all 0.3s ease;
        width: 100%;
    }
    
    .btn-submit:hover {
        background-color: var(--secondary-color);
        transform: translateY(-2px);
    }
    
    /* Alert Styles */
    .alert {
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
    }
    
    .alert-success {
        background-color: rgba(40, 167, 69, 0.2);
        color: #28a745;
        border-left: 4px solid #28a745;
    }
    
    .alert i {
        margin-right: 10px;
        font-size: 20px;
    }

    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .expert-image {
            width: 150px;
            height: 150px;
        }
        
        .expert-header {
            padding: 80px 0 40px;
        }
    }

    /* Animation Classes */
    .float-animation {
        animation: float 6s ease-in-out infinite;
    }

    @keyframes float {
        0% { transform: translateY(0px); }
        50% { transform: translateY(-15px); }
        100% { transform: translateY(0px); }
    }

    .pulse-animation {
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }

    /* Gradient Text */
    .gradient-text {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
    }
    </style>
</head>
<body>
    <!-- Floating Particles Background -->
    <div class="particles-container" id="particles-js"></div>

    <!-- Expert Header Section -->
    <section class="expert-header text-center">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8" data-aos="fade-up">
                    <?php if (!empty($expert['image_url'])): ?>
                        <img src="<?php echo htmlspecialchars($expert['image_url']); ?>" 
                             alt="<?php echo htmlspecialchars($expert['name']); ?>" 
                             class="expert-image mb-4 float-animation">
                    <?php endif; ?>
                    <h1 class="display-4 fw-bold mb-3"><?php echo htmlspecialchars($expert['name'] ?? 'Expert Name'); ?></h1>
                    <div class="d-flex justify-content-center flex-wrap">
                        <?php if (!empty($expert['specialization'])): 
                            $specializations = explode(',', $expert['specialization']);
                            foreach ($specializations as $spec): ?>
                                <span class="specialization-tag animate__animated animate__fadeInUp"><?php echo trim($spec); ?></span>
                            <?php endforeach; 
                        endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Expert Details Section -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <!-- Main Content -->
                <div class="col-lg-8 mb-4" data-aos="fade-right">
                    <div class="expert-card h-100">
                        <div class="card-body">
                            <h3 class="gradient-text mb-4">About Me</h3>
                            <p class="lead" style="line-height: 1.8;">
                                <?php echo nl2br(htmlspecialchars($expert['description'] ?? 'Expert description goes here...')); ?>
                            </p>
                            
                            <div class="mt-5">
                                <h4 class="gradient-text mb-4">My Approach</h4>
                                <div class="row">
                                    <div class="col-md-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                                        <div class="d-flex">
                                            <div class="me-3 text-primary">
                                                <i class="ri-lightbulb-flash-fill fs-2"></i>
                                            </div>
                                            <div>
                                                <h5>Innovative Solutions</h5>
                                                <p class="text-muted">Cutting-edge agricultural techniques tailored to your needs.</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                                        <div class="d-flex">
                                            <div class="me-3 text-primary">
                                                <i class="ri-leaf-fill fs-2"></i>
                                            </div>
                                            <div>
                                                <h5>Sustainable Practices</h5>
                                                <p class="text-muted">Environmentally friendly methods for long-term success.</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-4" data-aos="fade-up" data-aos-delay="300">
                                        <div class="d-flex">
                                            <div class="me-3 text-primary">
                                                <i class="ri-line-chart-fill fs-2"></i>
                                            </div>
                                            <div>
                                                <h5>Proven Results</h5>
                                                <p class="text-muted">Track record of improving yields and efficiency.</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-4" data-aos="fade-up" data-aos-delay="400">
                                        <div class="d-flex">
                                            <div class="me-3 text-primary">
                                                <i class="ri-team-fill fs-2"></i>
                                            </div>
                                            <div>
                                                <h5>Community Focused</h5>
                                                <p class="text-muted">Solutions that benefit the entire farming community.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Sidebar -->
                <div class="col-lg-4" data-aos="fade-left">
                    <!-- Contact Card -->
                    <div class="expert-card mb-4">
                        <div class="card-body">
                            <h3 class="gradient-text mb-4">Contact Information</h3>
                            
                            <?php if (!empty($expert['Experts_email'])): ?>
                            <div class="contact-info animate__animated animate__fadeInRight">
                                <div class="contact-icon">
                                    <i class="ri-mail-fill"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Email</h6>
                                    <a href="mailto:<?php echo htmlspecialchars($expert['Experts_email']); ?>" class="text-decoration-none">
                                        <?php echo htmlspecialchars($expert['Experts_email']); ?>
                                    </a>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($expert['Contact_no'])): ?>
                            <div class="contact-info animate__animated animate__fadeInRight" data-aos-delay="100">
                                <div class="contact-icon">
                                    <i class="ri-phone-fill"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Phone</h6>
                                    <a href="tel:<?php echo htmlspecialchars($expert['Contact_no']); ?>" class="text-decoration-none">
                                        <?php echo htmlspecialchars($expert['Contact_no']); ?>
                                    </a>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <div class="contact-info animate__animated animate__fadeInRight" data-aos-delay="200">
                                <div class="contact-icon">
                                    <i class="ri-calendar-check-fill"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Availability</h6>
                                    <p class="mb-0">Mon-Fri: 9AM - 5PM</p>
                                </div>
                            </div>
                            
                            <div class="mt-4 pt-3">
                                <a href="#" class="action-btn primary-btn pulse-animation me-2">
                                    <i class="ri-calendar-line me-2"></i> Book Consultation
                                </a>
                                <a href="#" class="action-btn outline-btn">
                                    <i class="ri-message-2-line me-2"></i> Send Message
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Contact Form -->
                    <div class="expert-card" data-aos="fade-up" data-aos-delay="300">
                        <div class="card-body">
                            <h3 class="gradient-text mb-4">Quick Contact</h3>
                            <form id="contactExpertForm">
                                <div class="mb-3">
                                    <input type="text" class="form-control" placeholder="Your Name" required>
                                </div>
                                <div class="mb-3">
                                    <input type="email" class="form-control" placeholder="Your Email" required>
                                </div>
                                <div class="mb-3">
                                    <textarea class="form-control" rows="3" placeholder="Your Message" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="ri-send-plane-line me-2"></i> Send Message
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 class="gradient-text">Client Testimonials</h2>
                <p class="lead">What farmers say about this expert</p>
            </div>
            
            <div class="row">
                <?php
                // Fetch testimonials from database
                $testimonials_query = $conn->prepare("SELECT * FROM testimonials WHERE expert_id = ? ORDER BY created_at DESC LIMIT 3");
                $testimonials_query->bind_param("i", $expert_id);
                $testimonials_query->execute();
                $testimonials_result = $testimonials_query->get_result();
                
                if ($testimonials_result->num_rows > 0) {
                    $delay = 100;
                    while($testimonial = $testimonials_result->fetch_assoc()) {
                        ?>
                        <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="<?php echo $delay; ?>">
                            <div class="testimonial-card h-100">
                                <div class="d-flex align-items-center mb-3">
                                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($testimonial['client_name']); ?>&background=random" 
                                         class="rounded-circle me-3" width="50" alt="<?php echo htmlspecialchars($testimonial['client_name']); ?>">
                                    <div>
                                        <h5 class="mb-0"><?php echo htmlspecialchars($testimonial['client_name']); ?></h5>
                                        <small class="text-muted"><?php echo htmlspecialchars($testimonial['profession']); ?>, <?php echo htmlspecialchars($testimonial['city']); ?></small>
                                    </div>
                                </div>
                                <div class="rating-stars mb-2">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="ri-star-fill <?php echo $i <= $testimonial['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <p class="mb-0">"<?php echo htmlspecialchars($testimonial['testimonial']); ?>"</p>
                            </div>
                        </div>
                        <?php
                        $delay += 100;
                    }
                } else {
                    echo '<div class="col-12 text-center"><p>No testimonials yet. Be the first to review!</p></div>';
                }
                $testimonials_query->close();
                ?>
            </div>
            
            <!-- Testimonial Form -->
            <div class="row justify-content-center mt-5">
                <div class="col-lg-8" data-aos="fade-up">
                    <div class="testimonial-form">
                        <h3 class="gradient-text mb-4 text-center">Share Your Experience</h3>
                        
                        <?php if (isset($_SESSION['testimonial_submitted'])): ?>
                            <div class="alert alert-success">
                                <i class="ri-checkbox-circle-fill"></i> Thank you for your testimonial! It will be displayed after approval.
                            </div>
                            <?php unset($_SESSION['testimonial_submitted']); ?>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="client_name" class="form-label">Your Name</label>
                                        <input type="text" id="client_name" name="client_name" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="profession" class="form-label">Your Profession</label>
                                        <input type="text" id="profession" name="profession" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="city" class="form-label">City</label>
                                        <input type="text" id="city" name="city" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Rating</label>
                                        <div class="rating-stars">
                                            <input type="radio" id="star5" name="rating" value="5" required>
                                            <label for="star5"><i class="ri-star-fill"></i></label>
                                            <input type="radio" id="star4" name="rating" value="4">
                                            <label for="star4"><i class="ri-star-fill"></i></label>
                                            <input type="radio" id="star3" name="rating" value="3">
                                            <label for="star3"><i class="ri-star-fill"></i></label>
                                            <input type="radio" id="star2" name="rating" value="2">
                                            <label for="star2"><i class="ri-star-fill"></i></label>
                                            <input type="radio" id="star1" name="rating" value="1">
                                            <label for="star1"><i class="ri-star-fill"></i></label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="testimonial" class="form-label">Your Testimonial</label>
                                <textarea id="testimonial" name="testimonial" class="form-control" rows="4" required></textarea>
                            </div>
                            
                            <button type="submit" name="submit_testimonial" class="btn-submit">
                                <i class="ri-send-plane-line me-2"></i> Submit Testimonial
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Consultation Modal -->
    <div class="consultation-modal" id="consultationModal">
        <div class="consultation-modal-content">
            <span class="close-modal" id="closeModal">&times;</span>
            <h3 class="gradient-text mb-4">Book Consultation</h3>
            
            <?php if (isset($_SESSION['consultation_submitted'])): ?>
                <div class="alert alert-success">
                    <i class="ri-checkbox-circle-fill"></i> Your consultation request has been submitted! The expert will contact you soon.
                </div>
                <?php unset($_SESSION['consultation_submitted']); ?>
            <?php else: ?>
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="user_name" class="form-label">Your Name</label>
                                <input type="text" id="user_name" name="user_name" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="user_email" class="form-label">Your Email</label>
                                <input type="email" id="user_email" name="user_email" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="user_phone" class="form-label">Phone Number</label>
                                <input type="tel" id="user_phone" name="user_phone" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="communication_method" class="form-label">Preferred Contact Method</label>
                                <select id="communication_method" name="communication_method" class="form-control" required>
                                    <option value="whatsapp">WhatsApp</option>
                                    <option value="phone">Phone Call</option>
                                    <option value="video_call">Video Call</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="preferred_date" class="form-label">Preferred Date</label>
                                <input type="date" id="preferred_date" name="preferred_date" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="preferred_time" class="form-label">Preferred Time</label>
                                <input type="time" id="preferred_time" name="preferred_time" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="problem_description" class="form-label">Describe Your Problem/Need</label>
                        <textarea id="problem_description" name="problem_description" class="form-control" rows="4" required></textarea>
                    </div>
                    
                    <input type="hidden" name="expert_id" value="<?php echo $expert_id; ?>">
                    <button type="submit" name="submit_consultation" class="btn-submit">
                        <i class="ri-calendar-check-line me-2"></i> Request Consultation
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
    <!-- Similar Experts Section -->
    <!-- Similar Experts Section -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="gradient-text">Other Experts You May Like</h2>
            <p class="lead">Connect with more agricultural specialists</p>
        </div>
        
        <div class="row" id="similarExpertsCarousel">
            <!-- This will be populated dynamically from database -->
            <?php
            // Use the existing connection instead of creating a new one
            // Exclude the current expert from the results
            $query = "SELECT * FROM experts WHERE expert_id != ? ORDER BY RAND() LIMIT 3";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $expert_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $delay = 100;
                while($similar_expert = $result->fetch_assoc()) {
                    // Split specializations if stored as comma-separated string
                    $specializations = explode(',', $similar_expert['specialization']);
                    ?>
                    <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="<?php echo $delay; ?>">
                        <div class="expert-card h-100">
                            <img src="<?php echo htmlspecialchars(!empty($similar_expert['image_url']) ? $similar_expert['image_url'] : 'https://randomuser.me/api/portraits/lego/'.rand(1,8).'.jpg'); ?>" 
                                 class="card-img-top" 
                                 alt="<?php echo htmlspecialchars($similar_expert['name']); ?>" 
                                 style="height: 200px; object-fit: cover;">
                            <div class="card-body">
                                <h4><?php echo htmlspecialchars($similar_expert['name']); ?></h4>
                                <p class="text-muted"><?php echo htmlspecialchars($similar_expert['title'] ?? 'Agricultural Expert'); ?></p>
                                <div class="mb-3">
                                    <?php foreach ($specializations as $spec) { 
                                        if(trim($spec)) { ?>
                                            <span class="specialization-tag"><?php echo htmlspecialchars(trim($spec)); ?></span>
                                        <?php }
                                    } ?>
                                </div>
                                <a href="expert_details.php?id=<?php echo $similar_expert['expert_id']; ?>" class="action-btn outline-btn w-100 text-center">
                                    <i class="ri-user-line me-2"></i> View Profile
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php
                    $delay += 100;
                }
            } else {
                echo '<div class="col-12 text-center"><p>No other experts found at this time.</p></div>';
            }
            $stmt->close();
            ?>
        </div>
    </div>
</section>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.11.4/gsap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
    <!-- Add this after GSAP script but before your custom JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.11.4/ScrollTrigger.min.js"></script>
    
    <script>
    // Initialize AOS animations
    AOS.init({
        duration: 800,
        easing: 'ease-in-out',
        once: true
    });
    
    // Initialize particles.js
    document.addEventListener('DOMContentLoaded', function() {
        if (document.getElementById('particles-js')) {
            particlesJS('particles-js', {
                "particles": {
                    "number": {
                        "value": 60,
                        "density": {
                            "enable": true,
                            "value_area": 800
                        }
                    },
                    "color": {
                        "value": getComputedStyle(document.documentElement).getPropertyValue('--primary-color')
                    },
                    "shape": {
                        "type": "circle",
                        "stroke": {
                            "width": 0,
                            "color": "#000000"
                        },
                        "polygon": {
                            "nb_sides": 5
                        }
                    },
                    "opacity": {
                        "value": 0.3,
                        "random": true,
                        "anim": {
                            "enable": true,
                            "speed": 1,
                            "opacity_min": 0.1,
                            "sync": false
                        }
                    },
                    "size": {
                        "value": 5,
                        "random": true,
                        "anim": {
                            "enable": true,
                            "speed": 2,
                            "size_min": 1,
                            "sync": false
                        }
                    },
                    "line_linked": {
                        "enable": true,
                        "distance": 150,
                        "color": getComputedStyle(document.documentElement).getPropertyValue('--primary-color'),
                        "opacity": 0.2,
                        "width": 1
                    },
                    "move": {
                        "enable": true,
                        "speed": 1,
                        "direction": "none",
                        "random": true,
                        "straight": false,
                        "out_mode": "out",
                        "bounce": false,
                        "attract": {
                            "enable": true,
                            "rotateX": 600,
                            "rotateY": 1200
                        }
                    }
                },
                "interactivity": {
                    "detect_on": "canvas",
                    "events": {
                        "onhover": {
                            "enable": true,
                            "mode": "grab"
                        },
                        "onclick": {
                            "enable": true,
                            "mode": "push"
                        },
                        "resize": true
                    },
                    "modes": {
                        "grab": {
                            "distance": 140,
                            "line_linked": {
                                "opacity": 0.5
                            }
                        },
                        "bubble": {
                            "distance": 400,
                            "size": 40,
                            "duration": 2,
                            "opacity": 8,
                            "speed": 3
                        },
                        "repulse": {
                            "distance": 200,
                            "duration": 0.4
                        },
                        "push": {
                            "particles_nb": 4
                        },
                        "remove": {
                            "particles_nb": 2
                        }
                    }
                },
                "retina_detect": true
            });
        }
        
        // Get primary color RGB values
        const primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--primary-color');
        const rgb = primaryColor.match(/\d+/g);
        document.documentElement.style.setProperty('--primary-color-rgb', rgb.join(','));
        
        // Animate contact info on scroll
        gsap.utils.toArray(".contact-info").forEach((item, i) => {
            gsap.from(item, {
                scrollTrigger: {
                    trigger: item,
                    start: "top 80%",
                    toggleActions: "play none none none"
                },
                x: 50,
                opacity: 0,
                duration: 0.8,
                delay: i * 0.1
            });
        });
        
        // Form submission
        $("#contactExpertForm").submit(function(e) {
            e.preventDefault();
            // Here you would typically make an AJAX call
            const submitBtn = $(this).find("button[type=submit]");
            const originalText = submitBtn.html();
            
            submitBtn.html('<i class="ri-loader-4-line animate-spin me-2"></i> Sending...');
            submitBtn.prop('disabled', true);
            
            // Simulate API call
            setTimeout(() => {
                submitBtn.html('<i class="ri-check-line me-2"></i> Sent!');
                
                // Create and show success notification
                const notification = $(`
                    <div class="alert alert-success alert-dismissible fade show position-fixed" 
                         style="bottom: 20px; right: 20px; z-index: 9999;">
                        <i class="ri-checkbox-circle-fill me-2"></i>
                        Your message has been sent successfully!
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `);
                
                $('body').append(notification);
                
                // Auto-remove after 5 seconds
                setTimeout(() => {
                    notification.alert('close');
                }, 5000);
                
                // Reset form after 2 seconds
                setTimeout(() => {
                    submitBtn.html(originalText);
                    submitBtn.prop('disabled', false);
                    this.reset();
                }, 2000);
            }, 1500);
        });
        
        // Theme change observer
        const themeObserver = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.attributeName === 'data-theme') {
                    // Update particles color when theme changes
                    if (window.pJSDom && window.pJSDom.length > 0) {
                        const primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--primary-color');
                        pJSDom[0].pJS.particles.color.value = primaryColor;
                        pJSDom[0].pJS.particles.line_linked.color = primaryColor;
                        pJSDom[0].pJS.fn.particlesRefresh();
                    }
                }
            });
        });
        
        themeObserver.observe(document.documentElement, {
            attributes: true
        });
    });
    
    // Animate spin class for loading icon
    $.fn.extend({
        animateSpinner: function() {
            this.each(function() {
                $(this).css({
                    'animation': 'spin 1s linear infinite',
                    'display': 'inline-block'
                });
            });
            return this;
        }
    });
    
    // Add spin keyframes
    const style = document.createElement('style');
    style.textContent = `
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    `;
    document.head.appendChild(style);

    // Consultation Modal
    const consultationModal = document.getElementById('consultationModal');
    const bookConsultationBtn = document.querySelector('.primary-btn.pulse-animation');
    const closeModal = document.getElementById('closeModal');
    
    if (bookConsultationBtn) {
        bookConsultationBtn.addEventListener('click', function(e) {
            e.preventDefault();
            consultationModal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        });
    }
    
    if (closeModal) {
        closeModal.addEventListener('click', function() {
            consultationModal.style.display = 'none';
            document.body.style.overflow = 'auto';
        });
    }
    
    // Close modal when clicking outside
    consultationModal.addEventListener('click', function(e) {
        if (e.target === consultationModal) {
            consultationModal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    });
    
    // WhatsApp redirect
    const sendMessageBtn = document.querySelector('.action-btn.outline-btn');
    if (sendMessageBtn && <?php echo !empty($expert['Contact_no']) ? 'true' : 'false'; ?>) {
        sendMessageBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const phoneNumber = "<?php echo !empty($expert['Contact_no']) ? $expert['Contact_no'] : ''; ?>";
            window.open(`https://wa.me/${phoneNumber}?text=Hello%20<?php echo urlencode($expert['name']); ?>%2C%20I%20would%20like%20to%20discuss%20about...`, '_blank');
        });
    }
    
    // Form validation for consultation
    const consultationForm = document.querySelector('form[method="POST"]');
    if (consultationForm) {
        consultationForm.addEventListener('submit', function(e) {
            const dateInput = document.getElementById('preferred_date');
            const selectedDate = new Date(dateInput.value);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            if (selectedDate < today) {
                e.preventDefault();
                alert('Please select a future date for the consultation.');
                dateInput.focus();
            }
        });
    }
    </script>
</body>
</html>