<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "agriculture";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables
$message = "";
$selectedLanguage = $_GET['lang'] ?? 'en';

// Language translations
$translations = [
    'en' => [
        'register_heading' => "Register",
        'full_name' => "Full Name",
        'email' => "Email",
        'phone' => "Phone",
        'address' => "Address",
        'city' => "City",
        'pincode' => "Pincode",
        'password' => "Password",
        'confirm_password' => "Confirm Password",
        'profile_photo' => "Upload Profile Photo",
        'register_button' => "Register",
        'already_registered' => "Already Registered? Login",
        'registration_successful' => "Registration successful! Redirecting to login page...",
        'change_language' => "Change Language:",
    ],
    'hi' => [
        'register_heading' => "पंजीकरण करें",
        'full_name' => "पूरा नाम",
        'email' => "ईमेल",
        'phone' => "फोन",
        'address' => "पता",
        'city' => "शहर",
        'pincode' => "पिनकोड",
        'password' => "पासवर्ड",
        'confirm_password' => "पासवर्ड की पुष्टि करें",
        'profile_photo' => "प्रोफाइल फोटो अपलोड करें",
        'register_button' => "पंजीकरण करें",
        'already_registered' => "पहले से पंजीकृत? लॉगिन",
        'registration_successful' => "पंजीकरण सफल! लॉगिन पेज पर पुनः निर्देशित किया जा रहा है...",
        'change_language' => "भाषा बदलें:",
    ],
    'gu' => [
        'register_heading' => "નોંધણી કરો",
        'full_name' => "પૂર્ણ નામ",
        'email' => "ઇમેઇલ",
        'phone' => "ફોન",
        'address' => "સરનામું",
        'city' => "શહેર",
        'pincode' => "પિનકોડ",
        'password' => "પાસવર્ડ",
        'confirm_password' => "પાસવર્ડની પુષ્ટિ કરો",
        'profile_photo' => "પ્રોફાઇલ ફોટો અપલોડ કરો",
        'register_button' => "નોંધણી કરો",
        'already_registered' => "પહેલેથી જ નોંધણી કરેલ છે? લૉગિન",
        'registration_successful' => "નોંધણી સફળ! લૉગિન પેજ પર રીડિરેક્ટ કરવામાં આવી રહ્યું છે...",
        'change_language' => "ભાષા બદલો:",
    ],
];

// Handle registration
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    $fullName = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $pincode = $_POST['pincode'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $profilePhoto = $_FILES['profile_photo'];

    // Validate inputs
    $errors = [];

    if (empty($fullName) || empty($email) || empty($phone) || empty($address) || empty($city) || empty($pincode) || empty($password) || empty($confirmPassword)) {
        $errors[] = "All fields are required.";
    }

    if ($password !== $confirmPassword) {
        $errors[] = "Passwords do not match.";
    }

    if (!empty($profilePhoto['name'])) {
        $allowedFileTypes = ['jpg', 'png', 'jpeg', 'webp', 'avif'];
        $fileType = pathinfo($profilePhoto["name"], PATHINFO_EXTENSION);
        if (!in_array($fileType, $allowedFileTypes) || $profilePhoto["size"] > 5000000) {
            $errors[] = "Invalid file type or size (max 5MB).";
        } else {
            $targetDir = "uploads/";
            $fileName = time() . "_" . basename($profilePhoto["name"]);
            $targetFilePath = $targetDir . $fileName;
            move_uploaded_file($profilePhoto["tmp_name"], $targetFilePath);
        }
    } else {
        $errors[] = "Profile photo is required.";
    }

    if (empty($errors)) {
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $conn->prepare("INSERT INTO users (full_name, email, phone, address, city, pincode, password, profile_photo) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $fullName, $email, $phone, $address, $city, $pincode, $passwordHash, $targetFilePath);

        if ($stmt->execute()) {
            echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: '" . $translations[$selectedLanguage]['registration_successful'] . "',
                            timer: 4000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = 'login.php';
                        });
                    });
                  </script>";
        } else {
            $message = "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        foreach ($errors as $error) {
            $message .= $error . "<br>";
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="<?php echo $selectedLanguage; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $translations[$selectedLanguage]['register_heading']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(135deg, #56ab2f, #a8e063, #3b8d99);
            background-size: 400% 400%;
            animation: gradientAnimation 8s ease infinite;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .container {
            width: 60%; /* Smaller width */
            max-width: 700px; /* Maximum width */
            background: url('images/bg10.jpg') no-repeat center center; /* Image Background only */
            background-size: cover;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            color: #fff; /* Text color on the image */
            animation: fadeIn 1s ease-out; /* Fade-in animation */
        }

        @keyframes gradientAnimation {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Flexbox form styling */
        .form-flex-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }

        .form-group {
            width: calc(50% - 10px);
            margin-bottom: 15px;
            box-sizing: border-box;
            position: relative; /* For the password toggle */
        }

        .form-control {
            width: 100%;
            background-color: transparent; /* No background */
            border: none;
            border-bottom: 2px solid rgba(255, 255, 255, 0.8); /* White bottom border */
            border-radius: 0; /* No rounded corners */
            padding: 10px 15px;
            color: #fff; /* White text */
            font-size: 16px;
            box-sizing: border-box;
            outline: none; /* Remove focus outline */
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.6); /* Semi-transparent placeholder */
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #fff; /* White label text */
            font-size: 14px;
        }

        .btn {
            background: #56ab2f;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s, transform 0.2s; /* Add transform */
            width: 100%;
            box-sizing: border-box;
            margin-top: 15px;
        }

        .btn:hover {
            background: #3a8e1d;
            transform: scale(1.05); /* Scale up on hover */
        }

        .language-select {
            text-align: center;
            margin-top: 20px;
        }

        .language-select a {
            text-decoration: none;
            color: #fff; /* White language links */
            margin: 0 10px;
        }

        .language-select a:hover {
            text-decoration: underline;
        }

        h1 {
            color: #fff; /* White heading */
            text-align: center;
            margin-bottom: 30px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5); /* Text shadow for readability */
        }

        .text-center a {
            color: #fff; /* White link color */
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .container {
                width: 90%;
                padding: 20px;
            }

            .form-group {
                width: 100%;
            }
        }

        /* Password toggle styling */
        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><?php echo $translations[$selectedLanguage]['register_heading']; ?></h1>
        <?php if (!empty($message)) : ?>
            <div class="alert alert-danger"><?php echo $message; ?></div>
        <?php endif; ?>
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="form-flex-container">
                <div class="form-group">
                    <label for="full_name"><?php echo $translations[$selectedLanguage]['full_name']; ?></label>
                    <input type="text" name="full_name" class="form-control" id="full_name" required>
                </div>
                <div class="form-group">
                    <label for="email"><?php echo $translations[$selectedLanguage]['email']; ?></label>
                    <input type="email" name="email" class="form-control" id="email" required>
                </div>
                <div class="form-group">
                    <label for="phone"><?php echo $translations[$selectedLanguage]['phone']; ?></label>
                    <input type="text" name="phone" class="form-control" id="phone" required>
                </div>
                <div class="form-group">
                    <label for="address"><?php echo $translations[$selectedLanguage]['address']; ?></label>
                    <input type="text" name="address" class="form-control" id="address" required>
                </div>
                <div class="form-group">
                    <label for="city"><?php echo $translations[$selectedLanguage]['city']; ?></label>
                    <input type="text" name="city" class="form-control" id="city" required>
                </div>
                <div class="form-group">
                    <label for="pincode"><?php echo $translations[$selectedLanguage]['pincode']; ?></label>
                    <input type="text" name="pincode" class="form-control" id="pincode" required>
                </div>
                <div class="form-group">
                    <label for="password"><?php echo $translations[$selectedLanguage]['password']; ?></label>
                    <input type="password" name="password" class="form-control" id="password" required>
                    <span class="password-toggle" onclick="togglePasswordVisibility('password')">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>
                <div class="form-group">
                    <label for="confirm_password"><?php echo $translations[$selectedLanguage]['confirm_password']; ?></label>
                    <input type="password" name="confirm_password" class="form-control" id="confirm_password" required>
                    <span class="password-toggle" onclick="togglePasswordVisibility('confirm_password')">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>
            </div>
            <div class="form-group">
                <label for="profile_photo"><?php echo $translations[$selectedLanguage]['profile_photo']; ?></label>
                <input type="file" name="profile_photo" class="form-control" id="profile_photo" required>
            </div>
            <button type="submit" name="register" class="btn"><?php echo $translations[$selectedLanguage]['register_button']; ?></button>
        </form>
        <div class="text-center mt-3">
            <a href="login.php"><?php echo $translations[$selectedLanguage]['already_registered']; ?></a>
        </div>
        <div class="language-select">
            <label><?php echo $translations[$selectedLanguage]['change_language']; ?></label>
            <a href="?lang=en">English</a> | <a href="?lang=hi">Hindi</a> | <a href="?lang=gu">Gujarati</a>
        </div>
    </div>
    <script>
        function togglePasswordVisibility(inputId) {
            var input = document.getElementById(inputId);
            var icon = document.querySelector(`[onclick="togglePasswordVisibility('${inputId}')"] i`);
            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            } else {
                input.type = "password";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            }
        }
    </script>
</body>
</html>
