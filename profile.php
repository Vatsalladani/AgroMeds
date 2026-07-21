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
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user details
$user_sql = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($user_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $pincode = $_POST['pincode'];
    
    // Handle file upload
    $profile_photo = $user['profile_photo'];
    if ($_FILES['profile_photo']['error'] == UPLOAD_ERR_OK) {
    $upload_dir = 'Uploads/Users/';
        $file_name = time() . '_' . basename($_FILES['profile_photo']['name']);
        $target_path = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $target_path)) {
            // Delete old profile photo if it exists and isn't the default
            if ($profile_photo && !str_contains($profile_photo, 'default')) {
                @unlink($profile_photo);
            }
            $profile_photo = $target_path;
        }
    }
    
    $update_sql = "UPDATE users SET full_name=?, email=?, phone=?, address=?, city=?, pincode=?, profile_photo=? WHERE user_id=?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("sssssssi", $full_name, $email, $phone, $address, $city, $pincode, $profile_photo, $user_id);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Profile updated successfully!";
        header("Location: profile.php");
        exit;
    } else {
        $_SESSION['error_message'] = "Error updating profile: " . $conn->error;
    }
}

// Handle profile deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_profile'])) {
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Get profile photo path
        $profile_photo = $user['profile_photo'];
        
        // Delete all user data from related tables
        $tables = ['orders', 'order_items', 'payments', 'cart', 'reviews']; // Add all tables with user data
        
        foreach ($tables as $table) {
            $delete_sql = "DELETE FROM $table WHERE user_id = ?";
            $stmt = $conn->prepare($delete_sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
        }
        
        // Finally delete the user
        $delete_user_sql = "DELETE FROM users WHERE user_id = ?";
        $stmt = $conn->prepare($delete_user_sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        // Delete profile photo if it exists
        if ($profile_photo && file_exists($profile_photo)) {
            @unlink($profile_photo);
        }
        
        // Commit transaction
        $conn->commit();
        
        // Logout and redirect
        session_destroy();
        header("Location: register.php?message=account_deleted");
        exit;
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        $_SESSION['error_message'] = "Error deleting account: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | AgroMeds</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
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

        .profile-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px;
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .profile-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            transition: all 0.3s ease;
            margin-bottom: 30px;
        }

        .profile-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.12);
        }

        .profile-header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 30px;
            text-align: center;
            position: relative;
        }

        .profile-picture {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid white;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .profile-picture:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .profile-name {
            font-size: 1.8rem;
            font-weight: 600;
            margin-top: 15px;
            margin-bottom: 5px;
        }

        .profile-email {
            font-size: 1rem;
            opacity: 0.9;
            margin-bottom: 15px;
        }

        .profile-body {
            padding: 30px;
        }

        .profile-section {
            margin-bottom: 25px;
            animation: slideIn 0.5s ease-out;
            animation-fill-mode: both;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateX(-20px); }
            to { opacity: 1; transform: translateX(0); }
        }

        .profile-section:nth-child(1) { animation-delay: 0.2s; }
        .profile-section:nth-child(2) { animation-delay: 0.4s; }
        .profile-section:nth-child(3) { animation-delay: 0.6s; }
        .profile-section:nth-child(4) { animation-delay: 0.8s; }

        .section-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 15px;
            position: relative;
            padding-bottom: 8px;
        }

        .section-title::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 50px;
            height: 3px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 3px;
        }

        .info-item {
            display: flex;
            margin-bottom: 12px;
            align-items: center;
        }

        .info-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            flex-shrink: 0;
            transition: all 0.3s ease;
        }

        .info-icon:hover {
            transform: rotate(15deg) scale(1.1);
        }

        .info-label {
            font-weight: 500;
            color: var(--dark);
            min-width: 100px;
        }

        .info-value {
            color: #555;
        }

        .btn-edit {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border: none;
            border-radius: 30px;
            padding: 10px 25px;
            color: white;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(106, 17, 203, 0.3);
        }

        .btn-edit:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(106, 17, 203, 0.4);
            color: white;
        }

        .btn-delete {
            background: linear-gradient(135deg, var(--danger), #ff6b6b);
            border: none;
            border-radius: 30px;
            padding: 10px 25px;
            color: white;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);
        }

        .btn-delete:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(220, 53, 69, 0.4);
            color: white;
        }

        .edit-form {
            display: none;
            animation: fadeIn 0.5s ease-out;
        }

        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            border: 1px solid #ddd;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.25rem rgba(106, 17, 203, 0.25);
        }

        .file-upload {
            position: relative;
            overflow: hidden;
            display: inline-block;
            width: 100%;
        }

        .file-upload-btn {
            border: 2px dashed #ddd;
            border-radius: 10px;
            padding: 30px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
        }

        .file-upload-btn:hover {
            border-color: var(--primary);
            background-color: rgba(106, 17, 203, 0.05);
        }

        .file-upload-input {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .confirmation-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1050;
            justify-content: center;
            align-items: center;
            animation: fadeIn 0.3s ease-out;
        }

        .confirmation-content {
            background: white;
            border-radius: 15px;
            padding: 30px;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            transform: translateY(20px);
            animation: slideUp 0.3s ease-out forwards;
        }

        @keyframes slideUp {
            to { transform: translateY(0); }
        }

        .confirmation-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 25px;
        }

        .floating-action-btn {
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
            box-shadow: 0 6px 20px rgba(106, 17, 203, 0.3);
            cursor: pointer;
            z-index: 100;
            transition: all 0.3s;
        }

        .floating-action-btn:hover {
            transform: translateY(-5px) scale(1.1);
            box-shadow: 0 10px 25px rgba(106, 17, 203, 0.4);
        }

        .alert-message {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1100;
            animation: slideInRight 0.5s forwards;
        }

        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        .pulse-animation {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <!-- Success/Error Messages -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert-message">
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['success_message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert-message">
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['error_message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <div class="profile-card">
            <div class="profile-header">
             <img src="<?php
if (!empty($user['profile_photo'])) {
    echo '/Farming_meds/' . htmlspecialchars($user['profile_photo']);
} else {
    echo 'https://via.placeholder.com/150';
}
?>"
alt="Profile Picture"
class="profile-picture pulse-animation">
                <h1 class="profile-name"><?php echo htmlspecialchars($user['full_name']); ?></h1>
                <p class="profile-email"><i class="bi bi-envelope-fill"></i> <?php echo htmlspecialchars($user['email']); ?></p>
            </div>
            
            <div class="profile-body">
                <!-- View Mode -->
                <div id="view-mode">
                    <div class="profile-section">
                        <h3 class="section-title"><i class="bi bi-person-circle"></i> Personal Information</h3>
                        <div class="info-item">
                            <div class="info-icon"><i class="bi bi-person"></i></div>
                            <span class="info-label">Full Name:</span>
                            <span class="info-value"><?php echo htmlspecialchars($user['full_name']); ?></span>
                        </div>
                        <div class="info-item">
                            <div class="info-icon"><i class="bi bi-envelope"></i></div>
                            <span class="info-label">Email:</span>
                            <span class="info-value"><?php echo htmlspecialchars($user['email']); ?></span>
                        </div>
                        <div class="info-item">
                            <div class="info-icon"><i class="bi bi-telephone"></i></div>
                            <span class="info-label">Phone:</span>
                            <span class="info-value"><?php echo htmlspecialchars($user['phone']); ?></span>
                        </div>
                    </div>
                    
                    <div class="profile-section">
                        <h3 class="section-title"><i class="bi bi-house-door"></i> Address Information</h3>
                        <div class="info-item">
                            <div class="info-icon"><i class="bi bi-geo-alt"></i></div>
                            <span class="info-label">Address:</span>
                            <span class="info-value"><?php echo htmlspecialchars($user['address']); ?></span>
                        </div>
                        <div class="info-item">
                            <div class="info-icon"><i class="bi bi-building"></i></div>
                            <span class="info-label">City:</span>
                            <span class="info-value"><?php echo htmlspecialchars($user['city']); ?></span>
                        </div>
                        <div class="info-item">
                            <div class="info-icon"><i class="bi bi-postcard"></i></div>
                            <span class="info-label">Pincode:</span>
                            <span class="info-value"><?php echo htmlspecialchars($user['pincode']); ?></span>
                        </div>
                    </div>
                    
                    <div class="profile-section">
                        <h3 class="section-title"><i class="bi bi-shield-lock"></i> Account Information</h3>
                        <div class="info-item">
                            <div class="info-icon"><i class="bi bi-person-badge"></i></div>
                            <span class="info-label">Role:</span>
                            <span class="info-value"><?php echo ucfirst(htmlspecialchars($user['role'])); ?></span>
                        </div>
                        <div class="info-item">
                            <div class="info-icon"><i class="bi bi-calendar"></i></div>
                            <span class="info-label">Member Since:</span>
                            <span class="info-value"><?php echo date('F j, Y', strtotime($user['created_at'])); ?></span>
                        </div>
                    </div>
                    
                    <div class="text-center mt-4">
                        <button id="edit-btn" class="btn btn-edit me-3"><i class="bi bi-pencil-square"></i> Edit Profile</button>
                        <button id="delete-btn" class="btn btn-delete"><i class="bi bi-trash"></i> Delete Account</button>
                    </div>
                </div>
                
                <!-- Edit Mode -->
                <div id="edit-mode" class="edit-form">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="profile-section">
                            <h3 class="section-title"><i class="bi bi-person-circle"></i> Edit Personal Information</h3>
                            
                            <div class="mb-3">
                                <label for="full_name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="full_name" name="full_name" 
                                       value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="profile-section">
                            <h3 class="section-title"><i class="bi bi-house-door"></i> Edit Address Information</h3>
                            
                            <div class="mb-3">
                                <label for="address" class="form-label">Address</label>
                                <textarea class="form-control" id="address" name="address" rows="3" required><?php echo htmlspecialchars($user['address']); ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="city" class="form-label">City</label>
                                <input type="text" class="form-control" id="city" name="city" 
                                       value="<?php echo htmlspecialchars($user['city']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="pincode" class="form-label">Pincode</label>
                                <input type="text" class="form-control" id="pincode" name="pincode" 
                                       value="<?php echo htmlspecialchars($user['pincode']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="profile-section">
                            <h3 class="section-title"><i class="bi bi-image"></i> Profile Picture</h3>
                            
                            <div class="file-upload mb-3">
                                <div class="file-upload-btn">
                                    <i class="bi bi-cloud-arrow-up" style="font-size: 2rem;"></i>
                                    <p>Click to upload new profile picture</p>
                                    <small class="text-muted">(Max 2MB, JPG/PNG)</small>
                                </div>
                                <input type="file" class="file-upload-input" id="profile_photo" name="profile_photo" accept="image/*">
                            </div>
                            
                            <div class="text-center">
                             <img id="image-preview"
     src="<?php
if (!empty($user['profile_photo'])) {
    echo '/Farming_meds/' . htmlspecialchars($user['profile_photo']);
} else {
    echo 'https://via.placeholder.com/150';
}
?>"
     alt="Current Profile Picture"
     class="img-thumbnail"
     style="max-width: 200px;">
                            </div>
                        </div>
                        
                        <div class="text-center mt-4">
                            <button type="submit" name="update_profile" class="btn btn-edit me-3"><i class="bi bi-check-circle"></i> Save Changes</button>
                            <button type="button" id="cancel-edit" class="btn btn-secondary"><i class="bi bi-x-circle"></i> Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div id="delete-confirmation" class="confirmation-modal">
        <div class="confirmation-content">
            <h3><i class="bi bi-exclamation-triangle-fill text-danger"></i> Confirm Account Deletion</h3>
            <p>Are you sure you want to delete your account? This action cannot be undone. All your data including orders, payments, and reviews will be permanently deleted.</p>
            
            <form method="POST">
                <div class="mb-3">
                    <label for="confirm-email" class="form-label">Enter your email to confirm</label>
                    <input type="email" class="form-control" id="confirm-email" required>
                </div>
                
                <div class="confirmation-buttons">
                    <button type="button" id="cancel-delete" class="btn btn-secondary"><i class="bi bi-x-lg"></i> Cancel</button>
                    <button type="submit" name="delete_profile" class="btn btn-danger" id="confirm-delete" disabled>
                        <i class="bi bi-trash-fill"></i> Delete My Account
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Floating Action Button -->
    <a href="home.php" class="floating-action-btn" data-bs-toggle="tooltip" data-bs-placement="left" title="Back to Dashboard">
        <i class="bi bi-house"></i>
    </a>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Toggle between view and edit modes
        const editBtn = document.getElementById('edit-btn');
        const cancelEditBtn = document.getElementById('cancel-edit');
        const viewMode = document.getElementById('view-mode');
        const editMode = document.getElementById('edit-mode');
        
        editBtn.addEventListener('click', () => {
            viewMode.style.display = 'none';
            editMode.style.display = 'block';
        });
        
        cancelEditBtn.addEventListener('click', () => {
            editMode.style.display = 'none';
            viewMode.style.display = 'block';
        });
        
        // Delete account confirmation
        const deleteBtn = document.getElementById('delete-btn');
        const deleteModal = document.getElementById('delete-confirmation');
        const cancelDeleteBtn = document.getElementById('cancel-delete');
        const confirmEmail = document.getElementById('confirm-email');
        const confirmDeleteBtn = document.getElementById('confirm-delete');
        const userEmail = "<?php echo $user['email']; ?>";
        
        deleteBtn.addEventListener('click', () => {
            deleteModal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        });
        
        cancelDeleteBtn.addEventListener('click', () => {
            deleteModal.style.display = 'none';
            document.body.style.overflow = 'auto';
            confirmEmail.value = '';
            confirmDeleteBtn.disabled = true;
        });
        
        confirmEmail.addEventListener('input', () => {
            confirmDeleteBtn.disabled = confirmEmail.value !== userEmail;
        });
        
        // Image preview for file upload
        const profilePhotoInput = document.getElementById('profile_photo');
        const imagePreview = document.getElementById('image-preview');
        
        profilePhotoInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    imagePreview.classList.add('pulse-animation');
                    setTimeout(() => {
                        imagePreview.classList.remove('pulse-animation');
                    }, 1000);
                }
                
                reader.readAsDataURL(this.files[0]);
            }
        });
        
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            var alerts = document.querySelectorAll('.alert-message');
            alerts.forEach(function(alert) {
                var bsAlert = new bootstrap.Alert(alert.querySelector('.alert'));
                bsAlert.close();
            });
        }, 5000);
        
        // Add animation to elements when they come into view
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate__animated', 'animate__fadeInUp');
                }
            });
        }, { threshold: 0.1 });
        
        document.querySelectorAll('.profile-section').forEach(section => {
            observer.observe(section);
        });
    </script>
</body>
</html>