<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "User not logged in"]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $productId = $_POST['product_id'];
    $quantity = $_POST['quantity'];

    // Database connection
    $host = 'localhost';
    $dbname = 'agriculture';
    $username = 'root';
    $password = '';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Check if the product already exists in the cart
        $stmt = $pdo->prepare("SELECT * FROM cart WHERE user_id = :user_id AND product_id = :product_id");
        $stmt->execute([':user_id' => $_SESSION['user_id'], ':product_id' => $productId]);
        $existingCartItem = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingCartItem) {
            // Update the quantity if the product already exists in the cart
            $newQuantity = $existingCartItem['quantity'] + $quantity;
            $stmt = $pdo->prepare("UPDATE cart SET quantity = :quantity WHERE cart_id = :cart_id");
            $stmt->execute([':quantity' => $newQuantity, ':cart_id' => $existingCartItem['cart_id']]);
        } else {
            // Insert new item into the cart
            $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (:user_id, :product_id, :quantity)");
            $stmt->execute([':user_id' => $_SESSION['user_id'], ':product_id' => $productId, ':quantity' => $quantity]);
        }

       echo json_encode([
    "success" => true,
    "message" => "Product added to cart successfully!"
]);
    } catch (PDOException $e) {
        echo json_encode(["error" => "Database error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["error" => "Invalid request method"]);
}
?>