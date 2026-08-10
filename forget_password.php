<?php
require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();

// Database connection
$db = new mysqli('localhost', 'root', '', 'agriculture');
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Function to generate OTP
function generateOTP() {
    return rand(100000, 999999);
}

// Function to send email
function sendOTP($email, $otp) {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
 $mail->Username   = 'agromeds.official.1@gmail.com'; // Your Gmail address
        $mail->Password   = 'YOUR_GMAIL_APP_PASSWORD'; // Your Gmail App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        
        // Recipients
        $mail->setFrom('agromeds.official.1@gmail.com', 'AgroMeds');
        $mail->addAddress($email);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset OTP';
        $mail->Body    = '
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; background-color: #f5f5f5; padding: 20px; }
                .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
                .header { color: #6c5ce7; text-align: center; }
                .otp-box { background: linear-gradient(135deg, #6c5ce7, #a29bfe); color: white; padding: 15px; border-radius: 5px; font-size: 24px; text-align: center; margin: 20px 0; letter-spacing: 5px; }
                .footer { margin-top: 30px; text-align: center; color: #777; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class="container">
                <h1 class="header">Password Reset Request</h1>
                <p>Hello,</p>
                <p>We received a request to reset your password. Please use the following OTP to proceed:</p>
                <div class="otp-box">'.$otp.'</div>
                <p>This OTP is valid for 10 minutes. If you didn\'t request this, please ignore this email.</p>
                <div class="footer">
                    <p>© '.date('Y').' AgroMeds. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>';
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address";
    } else {
        // Check if email exists
        $stmt = $db->prepare("SELECT user_id, full_name FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $otp = generateOTP();
            
            // Store OTP in session
            $_SESSION['reset_email'] = $email;
            $_SESSION['reset_otp'] = $otp;
            $_SESSION['otp_expiry'] = time() + 600; // 10 minutes expiry
            
            // Send OTP
            if (sendOTP($email, $otp)) {
                $success = "An OTP has been sent to your email address";
                header("Location: verify_otp.php");
                exit();
            } else {
                $error = "Failed to send OTP. Please try again.";
            }
        } else {
            $error = "No account found with this email address";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | AgroMeds</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary-color: #4a6fa5;
            --secondary-color: #7db1b1;
            --accent-color: #ff7e5f;
            --dark-color: #2d3436;
            --light-color: #f8f9fa;
            --leaf-green: #5cb85c;
            --earth-brown: #8d6e63;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #e0f7fa, #b2ebf2);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow-x: hidden;
            position: relative;
        }
        
        .floating-icon {
            position: absolute;
            opacity: 0.2;
            z-index: 0;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .floating-icon:nth-child(1) {
            top: 10%;
            left: 5%;
            font-size: 5rem;
            color: var(--primary-color);
        }
        
        .floating-icon:nth-child(2) {
            top: 70%;
            left: 10%;
            font-size: 4rem;
            color: var(--accent-color);
        }
        
        .floating-icon:nth-child(3) {
            top: 30%;
            right: 10%;
            font-size: 6rem;
            color: var(--secondary-color);
        }
        
        .floating-icon:nth-child(4) {
            bottom: 20%;
            right: 5%;
            font-size: 3rem;
            color: var(--leaf-green);
        }
        
        .container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 500px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            padding: 40px;
            margin: 20px;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .container:hover {
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15),
                        0 0 30px rgba(74, 111, 165, 0.1),
                        0 0 30px rgba(125, 177, 177, 0.1);
            transform: translateY(-5px);
        }
        
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo i {
            font-size: 3rem;
            color: var(--leaf-green);
            background: linear-gradient(135deg, var(--leaf-green), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            transition: all 0.5s ease;
        }
        
        .logo:hover i {
            transform: rotate(15deg) scale(1.1);
        }
        
        h1 {
            text-align: center;
            color: var(--dark-color);
            margin-bottom: 10px;
            font-weight: 600;
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        p.subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
            font-size: 0.9rem;
        }
        
        .form-group {
            margin-bottom: 25px;
            position: relative;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--dark-color);
            font-weight: 500;
        }
        
        .input-with-icon {
            position: relative;
        }
        
        .input-with-icon i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary-color);
            z-index: 1;
            transition: all 0.3s ease;
        }
        
        .input-with-icon:hover i {
            color: var(--accent-color);
            transform: translateY(-50%) scale(1.2);
        }
        
        .input-with-icon input {
            width: 100%;
            padding: 15px 15px 15px 45px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background-color: #f9f9f9;
        }
        
        .input-with-icon input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(74, 111, 165, 0.2);
            outline: none;
            background-color: white;
        }
        
        button {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(74, 111, 165, 0.3);
            position: relative;
            overflow: hidden;
        }
        
        button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
            transition: all 0.4s ease;
            z-index: -1;
        }
        
        button:hover::before {
            left: 0;
        }
        
        button:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(74, 111, 165, 0.4);
        }
        
        button:active {
            transform: translateY(0);
        }
        
        .back-to-login {
            text-align: center;
            margin-top: 20px;
            color: #666;
        }
        
        .back-to-login a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .back-to-login a::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--accent-color);
            transition: width 0.3s ease;
        }
        
        .back-to-login a:hover {
            color: var(--accent-color);
        }
        
        .back-to-login a:hover::after {
            width: 100%;
        }
        
        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            text-align: center;
            animation: fadeIn 0.5s ease;
        }
        
        .alert-error {
            background-color: #ffebee;
            color: #c62828;
            border: 1px solid #ef9a9a;
        }
        
        .alert-success {
            background-color: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #a5d6a7;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .nature-elements {
            position: absolute;
            width: 100%;
            height: 100%;
            z-index: 0;
            overflow: hidden;
        }
        
        .nature-element {
            position: absolute;
            opacity: 0.1;
            animation: float-element 15s infinite linear;
        }
        
        @keyframes float-element {
            0% { transform: translateY(0) translateX(0) rotate(0deg); }
            50% { transform: translateY(-50px) translateX(20px) rotate(10deg); }
            100% { transform: translateY(0) translateX(0) rotate(0deg); }
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 30px 20px;
            }
            
            .floating-icon {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Floating background icons -->
    <i class="floating-icon fas fa-seedling"></i>
    <i class="floating-icon fas fa-tractor"></i>
    <i class="floating-icon fas fa-leaf"></i>
    <i class="floating-icon fas fa-apple-alt"></i>
    
    <!-- Nature elements background -->
    <div class="nature-elements" id="nature-elements"></div>
    
    <!-- Main container -->
    <div class="container">
        <div class="logo">
            <i class="fas fa-leaf"></i>
        </div>
        <h1>Forgot Password?</h1>
        <p class="subtitle">Enter your email to receive a password reset OTP</p>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <form action="forget_password.php" method="POST">
            <div class="form-group">
                <label for="email">Email Address</label>
                <div class="input-with-icon">
                    <i class="fas fa-envelope"></i>
                    <input type="email" id="email" name="email" placeholder="Enter your registered email" required>
                </div>
            </div>
            
            <button type="submit">
                <i class="fas fa-paper-plane"></i> Send OTP
            </button>
        </form>
        
        <div class="back-to-login">
            Remember your password? <a href="login.php">Login here</a>
        </div>
    </div>
    
    <script>
        // Create nature elements
        document.addEventListener('DOMContentLoaded', function() {
            const natureContainer = document.getElementById('nature-elements');
            const elementCount = 10;
            const elements = ['🌱', '🌾', '🍃', '🌻', '🌷', '🍂', '🌿', '☘️', '🌵', '🌴'];
            
            for (let i = 0; i < elementCount; i++) {
                const element = document.createElement('div');
                element.classList.add('nature-element');
                element.textContent = elements[Math.floor(Math.random() * elements.length)];
                
                // Random size between 20 and 50px
                const size = Math.random() * 30 + 20;
                element.style.fontSize = `${size}px`;
                
                // Random position
                element.style.left = `${Math.random() * 100}%`;
                element.style.top = `${Math.random() * 100}%`;
                
                // Random animation duration and delay
                const duration = Math.random() * 10 + 10;
                const delay = Math.random() * 5;
                element.style.animationDuration = `${duration}s`;
                element.style.animationDelay = `${delay}s`;
                
                natureContainer.appendChild(element);
            }
            
            // Interactive floating icons
            const floatingIcons = document.querySelectorAll('.floating-icon');
            const repelDistance = 50; // Distance icons move away from cursor
            
            document.addEventListener('mousemove', function(e) {
                floatingIcons.forEach(icon => {
                    const iconRect = icon.getBoundingClientRect();
                    const iconCenter = {
                        x: iconRect.left + iconRect.width / 2,
                        y: iconRect.top + iconRect.height / 2
                    };
                    
                    const distance = Math.sqrt(
                        Math.pow(e.clientX - iconCenter.x, 2) + 
                        Math.pow(e.clientY - iconCenter.y, 2)
                    );
                    
                    if (distance < 150) { // If cursor is within 150px of icon
                        const angle = Math.atan2(
                            e.clientY - iconCenter.y,
                            e.clientX - iconCenter.x
                        );
                        
                        // Calculate new position (opposite direction from cursor)
                        const newX = -Math.cos(angle) * repelDistance * (1 - distance/150);
                        const newY = -Math.sin(angle) * repelDistance * (1 - distance/150);
                        
                        icon.style.transform = `translate(${newX}px, ${newY}px)`;
                        icon.style.opacity = '0.5';
                    } else {
                        // Return to original position
                        icon.style.transform = 'translate(0, 0)';
                        icon.style.opacity = '0.2';
                    }
                });
            });
        });
    </script>
</body>
</html>