<?php
// fetch_categories.php

include 'db_connection.php';

$searchQuery = isset($_GET['search']) ? $_GET['search'] : '';

try {
    if (!empty($searchQuery)) {
        $stmt = $conn->prepare("SELECT * FROM category WHERE category_name LIKE :search");
        $stmt->execute(['search' => "%$searchQuery%"]);
    } else {
        $stmt = $conn->query("SELECT * FROM category");
    }

    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'data' => $categories,
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch categories: ' . $e->getMessage(),
    ]);
}
?>