<?php
session_start();

// Redirect if the admin is not logged in
if (!isset($_SESSION['admin_logged_in'])) {
    echo json_encode(['status' => 'error', 'message' => 'Admin not logged in.']);
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "agriculture";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
    exit();
}

// Get form data
$product_id = $_POST['product_id'];
$product_name = $_POST['product_name'];
$description = $_POST['description'];
$price = $_POST['price'];
$quantity = $_POST['quantity'];
$category_id = $_POST['category_id'];
$image_url = $_POST['image_url'];
$image1 = $_POST['image1'] ?? null;
$image2 = $_POST['image2'] ?? null;
$image3 = $_POST['image3'] ?? null;
$weighting_ml = $_POST['weighting_ml'] ?? 0;
$weighting_kg = $_POST['weighting_kg'] ?? 0;
$weighting_packs = $_POST['weighting_packs'] ?? 0;

// Update product in the database
$sql = "UPDATE products SET 
            product_name = ?, 
            description = ?, 
            price = ?, 
            quantity = ?, 
            category_id = ?, 
            image_url = ?, 
            image1 = ?, 
            image2 = ?, 
            image3 = ?, 
            weighting_ml = ?, 
            weighting_kg = ?, 
            weighting_packs = ? 
        WHERE product_id = ?";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to prepare the SQL statement.']);
    exit();
}

$stmt->bind_param(
    "ssdiisssssssi",
    $product_name,
    $description,
    $price,
    $quantity,
    $category_id,
    $image_url,
    $image1,
    $image2,
    $image3,
    $weighting_ml,
    $weighting_kg,
    $weighting_packs,
    $product_id
);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Product updated successfully.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to update product.']);
}

$stmt->close();
$conn->close();
?>