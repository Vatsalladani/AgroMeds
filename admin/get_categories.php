<?php
// get_categories.php

include 'db_connection.php';

try {
    // Check if category_id is provided in POST request
    if (!isset($_POST['category_id']) || empty($_POST['category_id'])) {
        echo "error|Missing category ID";
        exit;
    }

    $categoryId = $_POST['category_id'];

    // Prepare and execute the query
    $stmt = $conn->prepare("SELECT category_id, category_name FROM category WHERE category_id = :category_id");
    $stmt->execute(['category_id' => $categoryId]);

    // Fetch the result
    $category = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($category) {
        // Return data in plain text format: "ID|Category Name"
        echo $category['category_id'] . "|" . $category['category_name'];
    } else {
        echo "error|Category not found";
    }
} catch (PDOException $e) {
    echo "error|Database error: " . $e->getMessage();
}
?>
