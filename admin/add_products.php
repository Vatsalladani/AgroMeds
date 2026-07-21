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
$product_name = $_POST['product_name'];
$description = $_POST['description'];
$price = $_POST['price'];
$quantity = $_POST['quantity'];
$category_id = $_POST['category_id'];
$image_url = $_POST['image_url'] ?? null;
$image1 = $_POST['image1'] ?? null;
$image2 = $_POST['image2'] ?? null;
$image3 = $_POST['image3'] ?? null;
$weighting_ml = $_POST['weighting_ml'] ?? null;
$weighting_kg = $_POST['weighting_kg'] ?? null;
$weighting_packs = $_POST['weighting_packs'] ?? null;

// Insert product into the database
$sql = "INSERT INTO products (
            product_name, 
            description, 
            price, 
            quantity, 
            category_id, 
            image_url, 
            image1, 
            image2, 
            image3, 
            weighting_ml, 
            weighting_kg, 
            weighting_packs
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to prepare the SQL statement.']);
    exit();
}

$stmt->bind_param(
    "ssdiisssssss",
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
    $weighting_packs
);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Product added successfully.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to add product.']);
}

$stmt->close();
$conn->close();
?>