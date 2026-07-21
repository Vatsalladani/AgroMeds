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


// Check if email is in session (user came from forget password)
if (!isset($_SESSION['reset_email'])) {
    header("Location: forget_password.php");
    exit();
}

// Handle OTP verification
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Combine the OTP digits into a single string
    $user_otp = implode('', $_POST['otp']);
    
    if (empty($user_otp)) {
        $error = "Please enter the OTP";
    } elseif (!is_numeric($user_otp) || strlen($user_otp) != 6) {
        $error = "Invalid OTP format";
    } elseif ($user_otp != $_SESSION['reset_otp']) {
        $error = "Incorrect OTP";
    } elseif (time() > $_SESSION['otp_expiry']) {
        $error = "OTP has expired. Please request a new one.";
        unset($_SESSION['reset_otp']);
        unset($_SESSION['otp_expiry']);
    } else {
        // OTP is correct and valid
        $_SESSION['otp_verified'] = true;
        header("Location: reset_password.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP | Stellar Security</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --space-dark: #0f0c29;
            --space-purple: #302b63;
            --space-blue: #24243e;
            --neon-blue: #00d2ff;
            --neon-pink: #ff00ff;
            --star-color: rgba(255, 255, 255, 0.8);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Montserrat', sans-serif;
            background: linear-gradient(to right, var(--space-dark), var(--space-purple), var(--space-blue));
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            overflow: hidden;
            position: relative;
        }
        
        .stars {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
        }
        
        .star {
            position: absolute;
            background-color: var(--star-color);
            border-radius: 50%;
            animation: twinkle var(--duration) infinite ease-in-out;
            opacity: 0;
        }
        
        @keyframes twinkle {
            0%, 100% { opacity: 0; }
            50% { opacity: 1; }
        }
        
        .container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 500px;
            background: rgba(15, 12, 41, 0.8);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3),
                        0 0 20px rgba(0, 210, 255, 0.1),
                        0 0 30px rgba(255, 0, 255, 0.1);
            padding: 40px;
            margin: 20px;
            transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        
        .container:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4),
                        0 0 30px rgba(0, 210, 255, 0.2),
                        0 0 40px rgba(255, 0, 255, 0.2);
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            position: relative;
        }
        
        .header i {
            font-size: 3.5rem;
            margin-bottom: 15px;
            background: linear-gradient(to right, var(--neon-blue), var(--neon-pink));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: pulse 2s infinite alternate;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            100% { transform: scale(1.1); }
        }
        
        h1 {
            font-weight: 600;
            margin-bottom: 10px;
            background: linear-gradient(to right, var(--neon-blue), var(--neon-pink));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .subtitle {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .otp-inputs {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        
        .otp-inputs input {
            width: 15%;
            height: 60px;
            text-align: center;
            font-size: 1.5rem;
            font-weight: 600;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            color: white;
            transition: all 0.3s ease;
        }
        
        .otp-inputs input:focus {
            outline: none;
            border-color: var(--neon-blue);
            box-shadow: 0 0 10px rgba(0, 210, 255, 0.5);
            transform: translateY(-3px);
        }
        
        button {
            width: 100%;
            padding: 15px;
            background: linear-gradient(to right, var(--neon-blue), var(--neon-pink));
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }
        
        button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(to right, var(--neon-pink), var(--neon-blue));
            transition: all 0.4s ease;
            z-index: -1;
        }
        
        button:hover::before {
            left: 0;
        }
        
        button:hover {
            box-shadow: 0 5px 20px rgba(0, 210, 255, 0.5),
                        0 5px 20px rgba(255, 0, 255, 0.5);
        }
        
        .resend-otp {
            text-align: center;
            margin-top: 20px;
            color: rgba(255, 255, 255, 0.6);
        }
        
        .resend-otp a {
            color: var(--neon-blue);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .resend-otp a:hover {
            color: var(--neon-pink);
            text-decoration: underline;
        }
        
        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            text-align: center;
            animation: slideIn 0.5s ease;
            background: rgba(255, 0, 0, 0.1);
            border: 1px solid rgba(255, 0, 0, 0.3);
            color: #ff6b6b;
        }
        
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .satellite {
            position: absolute;
            width: 100px;
            height: 40px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50px;
            top: 10%;
            right: 5%;
            animation: orbit 20s linear infinite;
            transform-origin: center center;
        }
        
        .satellite::before {
            content: '';
            position: absolute;
            width: 10px;
            height: 10px;
            background: var(--neon-blue);
            border-radius: 50%;
            top: 15px;
            left: 15px;
            box-shadow: 0 0 10px var(--neon-blue),
                        0 0 20px var(--neon-blue);
        }
        
        @keyframes orbit {
            0% { transform: rotate(0deg) translateX(150px) rotate(0deg); }
            100% { transform: rotate(360deg) translateX(150px) rotate(-360deg); }
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 30px 20px;
            }
            
            .otp-inputs input {
                height: 50px;
                font-size: 1.2rem;
            }
            
            .satellite {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Animated stars background -->
    <div class="stars" id="stars"></div>
    
    <!-- Animated satellite -->
    <div class="satellite"></div>
    
    <!-- Main container -->
    <div class="container">
        <div class="header">
            <i class="fas fa-satellite-dish"></i>
            <h1>Verify OTP</h1>
            <p class="subtitle">Enter the 6-digit code sent to <?php echo substr($_SESSION['reset_email'], 0, 3) . '****' . strstr($_SESSION['reset_email'], '@'); ?></p>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="alert">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form action="verify_otp.php" method="POST">
            <div class="otp-inputs">
                <input type="text" name="otp[]" maxlength="1" pattern="[0-9]" required>
                <input type="text" name="otp[]" maxlength="1" pattern="[0-9]" required>
                <input type="text" name="otp[]" maxlength="1" pattern="[0-9]" required>
                <input type="text" name="otp[]" maxlength="1" pattern="[0-9]" required>
                <input type="text" name="otp[]" maxlength="1" pattern="[0-9]" required>
                <input type="text" name="otp[]" maxlength="1" pattern="[0-9]" required>
            </div>
            
            <button type="submit">
                <i class="fas fa-rocket"></i> Verify & Continue
            </button>
        </form>
        
        <div class="resend-otp">
            Didn't receive code? <a href="forget_password.php">Resend OTP</a>
        </div>
    </div>
    
    <script>
        // Create stars
        document.addEventListener('DOMContentLoaded', function() {
            const starsContainer = document.getElementById('stars');
            const starCount = 100;
            
            for (let i = 0; i < starCount; i++) {
                const star = document.createElement('div');
                star.classList.add('star');
                
                // Random size between 1 and 3px
                const size = Math.random() * 2 + 1;
                star.style.width = `${size}px`;
                star.style.height = `${size}px`;
                
                // Random position
                star.style.left = `${Math.random() * 100}%`;
                star.style.top = `${Math.random() * 100}%`;
                
                // Random animation duration and delay
                const duration = Math.random() * 5 + 3;
                const delay = Math.random() * 5;
                star.style.setProperty('--duration', `${duration}s`);
                star.style.animationDelay = `${delay}s`;
                
                starsContainer.appendChild(star);
            }
            
            // Auto-focus first OTP input
            document.querySelector('.otp-inputs input').focus();
            
            // Handle OTP input movement
            const inputs = document.querySelectorAll('.otp-inputs input');
            inputs.forEach((input, index) => {
                input.addEventListener('input', function() {
                    if (this.value.length === 1) {
                        if (index < inputs.length - 1) {
                            inputs[index + 1].focus();
                        } else {
                            this.blur();
                        }
                    }
                });
                
                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Backspace' && this.value.length === 0 && index > 0) {
                        inputs[index - 1].focus();
                    }
                });
            });
        });
    </script>
</body>
</html>