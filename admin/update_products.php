<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'agriculture');

if ($conn->connect_error) {
    die(json_encode(['status' => 'error', 'message' => 'Database connection failed']));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $field = $_POST['field'];
    $value = $_POST['value'];

    // Validate field to prevent SQL injection
    $allowedFields = ['product_name', 'category_id', 'description', 'price', 'quantity'];
    if (!in_array($field, $allowedFields)) {
        die(json_encode(['status' => 'error', 'message' => 'Invalid field']));
    }

    $stmt = $conn->prepare("UPDATE products SET $field = ? WHERE product_id = ?");
    $stmt->bind_param("si", $value, $id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Product updated']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Update failed']);
    }

    $stmt->close();
    $conn->close();
}
?>
