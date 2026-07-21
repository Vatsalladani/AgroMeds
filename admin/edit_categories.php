<?php
// edit_categories.php

include 'db_connection.php';

if (!isset($_POST['category_id']) || !isset($_POST['category_name'])) {
    echo "error|Missing required fields";
    exit;
}

$categoryId = $_POST['category_id'];
$categoryName = $_POST['category_name'];

try {
    $stmt = $conn->prepare("UPDATE category SET category_name = :category_name WHERE category_id = :category_id");
    $stmt->execute([
        ':category_name' => $categoryName,
        ':category_id' => $categoryId
    ]);

    if ($stmt->rowCount() > 0) {
        echo "success|Category updated successfully!";
    } else {
        echo "error|No changes made or category not found";
    }
} catch (PDOException $e) {
    echo "error|Failed to update category: " . $e->getMessage();
}
?>
