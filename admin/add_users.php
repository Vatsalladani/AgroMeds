<?php
// Database connection
$host = 'localhost';
$db = 'agriculture';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get POST data
$full_name = $_POST['full_name'];
$email = $_POST['email'];
$phone = $_POST['phone'];
$address = $_POST['address'];
$city = $_POST['city'];
$pincode = $_POST['pincode'];
$role = $_POST['role'];

// Handle file upload
$targetDir = "../Uploads/Users/";
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0777, true); // Create directory if it doesn't exist
}

$fileName = basename($_FILES['profile_photo']['name']);
$targetFilePath = $targetDir . $fileName;
$fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

// Allow certain file formats
$allowTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
if (in_array($fileType, $allowTypes)) {
    if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $targetFilePath)) {
        // Insert user into database
        $profile_photo = $targetFilePath;
        $sql = "INSERT INTO users (full_name, email, phone, address, city, pincode, profile_photo, role) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssss", $full_name, $email, $phone, $address, $city, $pincode, $profile_photo, $role);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'User added successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to add user']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to upload image']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid file type']);
}

$stmt->close();
$conn->close();
?>