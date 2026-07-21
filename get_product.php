<?php
header('Content-Type: application/json');

$host = 'localhost';
$db = 'agriculture';
$user = 'root';
$pass = ' ';
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$productId = $_GET['id'];
$sql = "SELECT * FROM products WHERE product_id = $productId";
$result = $conn->query($sql);
$product = $result->fetch_assoc();

echo json_encode($product);
$conn->close();
?>