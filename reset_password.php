<?php
session_start();

// Check if OTP is verified
if (!isset($_SESSION['otp_verified']) || !$_SESSION['otp_verified']) {
    header("Location: verify_otp.php");
    exit();
}

// Database connection
$db = new mysqli('localhost', 'root', '', 'agriculture');
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Handle password reset
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $email = $_SESSION['reset_email'];
    
    if (empty($password) || empty($confirm_password)) {
        $error = "Please fill in all fields";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters";
    } elseif ($password != $confirm_password) {
        $error = "Passwords do not match";
    } else {
        // Check password strength
        $strength = 0;
        $strengthMessages = [];
        
        // Length check
        if (strlen($password) >= 8) $strength += 1;
        if (strlen($password) >= 12) $strength += 1;
        
        // Character variety checks
        if (preg_match('/[A-Z]/', $password)) $strength += 1;
        if (preg_match('/[0-9]/', $password)) $strength += 1;
        if (preg_match('/[^A-Za-z0-9]/', $password)) $strength += 1;
        
        if ($strength < 4) {
            $error = "Password is not strong enough. Please include uppercase letters, numbers, and special characters.";
        } else {
            // Check if password is same as previous password
            $stmt = $db->prepare("SELECT password FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                if (password_verify($password, $user['password'])) {
                    $error = "You cannot use your previous password. Please choose a new one.";
                } else {
                    // Hash the new password
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Update password in database
                    $update_stmt = $db->prepare("UPDATE users SET password = ? WHERE email = ?");
                    $update_stmt->bind_param("ss", $hashed_password, $email);
                    
                    if ($update_stmt->execute()) {
                        // Password updated successfully
                        $success = "Password updated successfully!";
                        
                        // Clear reset session
                        unset($_SESSION['reset_email']);
                        unset($_SESSION['reset_otp']);
                        unset($_SESSION['otp_expiry']);
                        unset($_SESSION['otp_verified']);
                        
                        // Redirect after 3 seconds
                        header("refresh:3;url=login.php");
                    } else {
                        $error = "Failed to update password. Please try again.";
                    }
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | Verdant Security</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --forest-dark: #0a2e38;
            --forest-green: #1e6f5c;
            --leaf-green: #29bb89;
            --lime-green: #a7ff83;
            --mist-color: rgba(255, 255, 255, 0.8);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Nunito', sans-serif;
            background: linear-gradient(135deg, var(--forest-dark), var(--forest-green));
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            overflow: hidden;
            position: relative;
        }
        
        .leaf {
            position: absolute;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="%23a7ff83"><path d="M17 8C8 10 5.9 16.8 3 22c5-3 11-5 19-2-1-5-3-11-5-12z"/></svg>');
            background-size: contain;
            background-repeat: no-repeat;
            opacity: 0.3;
            animation: falling linear infinite;
            z-index: 0;
        }
        
        @keyframes falling {
            0% { transform: translateY(-10%) rotate(0deg) translateX(0); opacity: 0; }
            10% { opacity: 0.3; }
            90% { opacity: 0.3; }
            100% { transform: translateY(110vh) rotate(360deg) translateX(50px); opacity: 0; }
        }
        
        .container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 500px;
            background: rgba(10, 46, 56, 0.7);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            border: 1px solid rgba(167, 255, 131, 0.2);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3),
                        0 0 20px rgba(167, 255, 131, 0.1);
            padding: 40px;
            margin: 20px;
            transition: all 0.3s ease;
        }
        
        .container:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4),
                        0 0 30px rgba(167, 255, 131, 0.2);
        }
        
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo i {
            font-size: 3.5rem;
            color: var(--lime-green);
            margin-bottom: 15px;
            display: inline-block;
            animation: sway 3s ease-in-out infinite alternate;
        }
        
        @keyframes sway {
            0% { transform: rotate(-5deg); }
            100% { transform: rotate(5deg); }
        }
        
        h1 {
            text-align: center;
            font-weight: 700;
            margin-bottom: 10px;
            color: var(--lime-green);
        }
        
        .subtitle {
            text-align: center;
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 25px;
            position: relative;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--lime-green);
            font-weight: 600;
        }
        
        .input-with-icon {
            position: relative;
        }
        
        .input-with-icon i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--leaf-green);
            z-index: 1;
        }
        
        .input-with-icon input {
            width: 100%;
            padding: 15px 15px 15px 45px;
            border: 2px solid rgba(167, 255, 131, 0.3);
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background-color: rgba(10, 46, 56, 0.5);
            color: white;
        }
        
        .input-with-icon input:focus {
            border-color: var(--lime-green);
            box-shadow: 0 0 0 3px rgba(167, 255, 131, 0.2);
            outline: none;
            background-color: rgba(10, 46, 56, 0.8);
        }
        
        .password-strength {
            height: 5px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 5px;
            margin-top: 10px;
            overflow: hidden;
            position: relative;
        }
        
        .strength-meter {
            height: 100%;
            width: 0;
            background: linear-gradient(to right, #ff4d4d, #f9cb28, #29bb89);
            border-radius: 5px;
            transition: width 0.3s ease;
        }
        
        .strength-feedback {
            font-size: 0.8rem;
            margin-top: 5px;
            color: #ff6b6b;
            min-height: 20px;
        }
        
        .strength-requirements {
            margin-top: 10px;
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.6);
        }
        
        .strength-requirements ul {
            padding-left: 20px;
            margin-top: 5px;
        }
        
        .strength-requirements li {
            margin-bottom: 3px;
        }
        
        .strength-requirements .valid {
            color: var(--lime-green);
        }
        
        button {
            width: 100%;
            padding: 15px;
            background: linear-gradient(to right, var(--leaf-green), var(--lime-green));
            color: var(--forest-dark);
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            z-index: 1;
            margin-top: 10px;
        }
        
        button:disabled {
            background: #cccccc;
            cursor: not-allowed;
            transform: none !important;
            box-shadow: none !important;
        }
        
        button::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to right, var(--lime-green), var(--leaf-green));
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: -1;
        }
        
        button:hover:not(:disabled)::after {
            opacity: 1;
        }
        
        button:hover:not(:disabled) {
            box-shadow: 0 5px 20px rgba(167, 255, 131, 0.5);
            transform: translateY(-3px);
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
            background: rgba(255, 107, 107, 0.1);
            border: 1px solid rgba(255, 107, 107, 0.3);
            color: #ff6b6b;
        }
        
        .alert-success {
            background: rgba(41, 187, 137, 0.2);
            border: 1px solid rgba(41, 187, 137, 0.4);
            color: var(--lime-green);
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .deer {
            position: absolute;
            bottom: 5%;
            left: 5%;
            font-size: 3rem;
            opacity: 0.2;
            animation: deerMove 20s linear infinite;
        }
        
        @keyframes deerMove {
            0% { transform: translateX(-100px); }
            100% { transform: translateX(calc(100vw + 100px)); }
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 30px 20px;
            }
            
            .deer {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Animated leaves -->
    <div id="leaves"></div>
    
    <!-- Animated deer -->
    <div class="deer">
        <i class="fas fa-deer"></i>
    </div>
    
    <!-- Main container -->
    <div class="container">
        <div class="logo">
            <i class="fas fa-leaf"></i>
            <h1>New Password</h1>
            <p class="subtitle">Create a strong, secure password</p>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                <p>Redirecting to login page...</p>
            </div>
        <?php else: ?>
        
        <form action="reset_password.php" method="POST" id="passwordForm">
            <div class="form-group">
                <label for="password">New Password</label>
                <div class="input-with-icon">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="password" name="password" placeholder="Enter new password" required>
                </div>
                <div class="password-strength">
                    <div class="strength-meter" id="strength-meter"></div>
                </div>
                <div class="strength-feedback" id="strength-feedback"></div>
                <div class="strength-requirements">
                    <p>Password must contain:</p>
                    <ul>
                        <li id="length-req">At least 8 characters</li>
                        <li id="uppercase-req">At least one uppercase letter</li>
                        <li id="number-req">At least one number</li>
                        <li id="special-req">At least one special character</li>
                    </ul>
                </div>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <div class="input-with-icon">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password" required>
                </div>
            </div>
            
            <button type="submit" id="submitBtn" disabled>
                <i class="fas fa-key"></i> Reset Password
            </button>
        </form>
        
        <?php endif; ?>
    </div>
    
    <script>
        // Create falling leaves
        document.addEventListener('DOMContentLoaded', function() {
            const leavesContainer = document.getElementById('leaves');
            const leafCount = 15;
            
            for (let i = 0; i < leafCount; i++) {
                const leaf = document.createElement('div');
                leaf.classList.add('leaf');
                
                // Random size between 20 and 40px
                const size = Math.random() * 20 + 20;
                leaf.style.width = `${size}px`;
                leaf.style.height = `${size}px`;
                
                // Random position
                leaf.style.left = `${Math.random() * 100}%`;
                
                // Random animation duration and delay
                const duration = Math.random() * 10 + 10;
                const delay = Math.random() * 5;
                leaf.style.animationDuration = `${duration}s`;
                leaf.style.animationDelay = `${delay}s`;
                
                leavesContainer.appendChild(leaf);
            }
            
            // Password strength validation
            const passwordInput = document.getElementById('password');
            const confirmInput = document.getElementById('confirm_password');
            const strengthMeter = document.getElementById('strength-meter');
            const strengthFeedback = document.getElementById('strength-feedback');
            const submitBtn = document.getElementById('submitBtn');
            
            // Requirement elements
            const lengthReq = document.getElementById('length-req');
            const uppercaseReq = document.getElementById('uppercase-req');
            const numberReq = document.getElementById('number-req');
            const specialReq = document.getElementById('special-req');
            
            function checkPasswordStrength(password) {
                let strength = 0;
                let messages = [];
                
                // Length check
                if (password.length >= 8) {
                    strength += 1;
                    lengthReq.classList.add('valid');
                } else {
                    lengthReq.classList.remove('valid');
                }
                
                if (password.length >= 12) strength += 1;
                
                // Uppercase check
                if (/[A-Z]/.test(password)) {
                    strength += 1;
                    uppercaseReq.classList.add('valid');
                } else {
                    uppercaseReq.classList.remove('valid');
                }
                
                // Number check
                if (/[0-9]/.test(password)) {
                    strength += 1;
                    numberReq.classList.add('valid');
                } else {
                    numberReq.classList.remove('valid');
                }
                
                // Special character check
                if (/[^A-Za-z0-9]/.test(password)) {
                    strength += 1;
                    specialReq.classList.add('valid');
                } else {
                    specialReq.classList.remove('valid');
                }
                
                // Update meter width and color
                const width = strength * 25;
                strengthMeter.style.width = `${width}%`;
                
                // Update feedback message
                if (password.length === 0) {
                    strengthFeedback.textContent = '';
                    strengthFeedback.style.color = '#ff6b6b';
                } else if (strength <= 2) {
                    strengthFeedback.textContent = 'Weak password';
                    strengthFeedback.style.color = '#ff4d4d';
                } else if (strength <= 3) {
                    strengthFeedback.textContent = 'Moderate password';
                    strengthFeedback.style.color = '#f9cb28';
                } else {
                    strengthFeedback.textContent = 'Strong password';
                    strengthFeedback.style.color = '#29bb89';
                }
                
                // Enable/disable submit button based on strength
                submitBtn.disabled = strength < 4 || password !== confirmInput.value || password.length < 8;
                
                return strength;
            }
            
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                checkPasswordStrength(password);
                
                // Also check if passwords match
                if (confirmInput.value.length > 0) {
                    if (password !== confirmInput.value) {
                        confirmInput.setCustomValidity("Passwords do not match");
                    } else {
                        confirmInput.setCustomValidity("");
                    }
                }
            });
            
            confirmInput.addEventListener('input', function() {
                const password = passwordInput.value;
                const confirmPassword = this.value;
                
                if (password !== confirmPassword) {
                    this.setCustomValidity("Passwords do not match");
                    submitBtn.disabled = true;
                } else {
                    this.setCustomValidity("");
                    submitBtn.disabled = checkPasswordStrength(password) < 4 || password.length < 8;
                }
            });
            
            // Form submission validation
            document.getElementById('passwordForm').addEventListener('submit', function(e) {
                const password = passwordInput.value;
                const strength = checkPasswordStrength(password);
                
                if (strength < 4 || password.length < 8) {
                    e.preventDefault();
                    strengthFeedback.textContent = 'Password is not strong enough';
                    strengthFeedback.style.color = '#ff4d4d';
                }
                
                if (password !== confirmInput.value) {
                    e.preventDefault();
                    confirmInput.setCustomValidity("Passwords do not match");
                }
            });
        });
    </script>
</body>
</html>