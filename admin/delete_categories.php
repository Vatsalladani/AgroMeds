<?php
// delete_categories.php

include 'db_connection.php';

// Check if category_id is provided
if (!isset($_POST['category_id']) || empty($_POST['category_id'])) {
    echo "error|Category ID is required";
    exit;
}

$categoryId = $_POST['category_id'];

try {
    $stmt = $conn->prepare("DELETE FROM category WHERE category_id = :category_id");
    $stmt->execute([':category_id' => $categoryId]);

    if ($stmt->rowCount() > 0) {
        echo "success|Category deleted successfully!";
    } else {
        echo "error|Category not found or already deleted";
    }
} catch (PDOException $e) {
    echo "error|Failed to delete category: " . $e->getMessage();
}
?>
