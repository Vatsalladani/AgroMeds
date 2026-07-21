<?php
// add_categories.php

include 'db_connection.php';

if (!isset($_POST['category_name']) || empty(trim($_POST['category_name']))) {
    echo "error|Category name is required";
    exit;
}

$categoryName = trim($_POST['category_name']);

try {
    $stmt = $conn->prepare("INSERT INTO category (category_name) VALUES (:category_name)");
    $stmt->execute([':category_name' => $categoryName]);

    if ($stmt->rowCount() > 0) {
        echo "success|Category added successfully!";
    } else {
        echo "error|Failed to add category";
    }
} catch (PDOException $e) {
    echo "error|Database error: " . $e->getMessage();
}
?>
