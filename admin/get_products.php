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

// Get product ID from the request
$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;

if ($product_id === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid product ID.']);
    exit();
}

// Fetch product details
$sql = "SELECT 
            product_id, 
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
        FROM products 
        WHERE product_id = ?";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to prepare the SQL statement.']);
    exit();
}

$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Product not found.']);
    exit();
}

$product = $result->fetch_assoc();

$stmt->close();
$conn->close();

// Return JSON response
echo json_encode([
    'status' => 'success',
    'data' => $product
]);
?>