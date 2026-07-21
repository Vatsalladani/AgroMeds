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
$user_id = $_POST['user_id'];
$full_name = $_POST['full_name'];
$email = $_POST['email'];
$phone = $_POST['phone'];
$address = $_POST['address'];
$city = $_POST['city'];
$pincode = $_POST['pincode'];
$role = $_POST['role'];

// Handle file upload if a new image is provided
$profile_photo = null;
if (!empty($_FILES['profile_photo']['name'])) {
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
            $profile_photo = $targetFilePath;
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to upload image']);
            exit;
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid file type']);
        exit;
    }
}

// Update user in database
if ($profile_photo) {
    $sql = "UPDATE users SET full_name = ?, email = ?, phone = ?, address = ?, city = ?, pincode = ?, profile_photo = ?, role = ? WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssi", $full_name, $email, $phone, $address, $city, $pincode, $profile_photo, $role, $user_id);
} else {
    $sql = "UPDATE users SET full_name = ?, email = ?, phone = ?, address = ?, city = ?, pincode = ?, role = ? WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssi", $full_name, $email, $phone, $address, $city, $pincode, $role, $user_id);
}

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'User updated successfully']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to update user']);
}

$stmt->close();
$conn->close();
?>