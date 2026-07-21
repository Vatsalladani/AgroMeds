<?php
session_start();

// Database connection details
$host = 'localhost';
$dbname = 'agriculture';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage();
    exit;
}

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "User not logged in";
    exit;
}

// Get the favorite_id from the POST data
if (isset($_POST['favorite_id']) && is_numeric($_POST['favorite_id'])) {
    $favorite_id = $_POST['favorite_id'];
} else {
    echo "Invalid favorite ID";
    exit;
}

// Remove the favorite from the database
try {
    $stmt = $pdo->prepare("DELETE FROM favorites WHERE favorite_id = :favorite_id AND user_id = :user_id");
    $stmt->bindParam(':favorite_id', $favorite_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo "Item removed from favorites.";
    } else {
        echo "Item not found in favorites or you do not have permission to remove it.";
    }
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
}
?>
