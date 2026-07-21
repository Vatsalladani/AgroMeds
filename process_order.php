<?php
session_start();

// Database connection
$host = 'localhost';
$dbname = 'agriculture';
$username = 'root';
$password = '';
$conn = new mysqli($host, $username, $password, $dbname);

// Function to send response in appropriate format
function sendResponse($success, $message, $additionalData = []) {
    // Check if request expects JSON
    $acceptHeader = $_SERVER['HTTP_ACCEPT'] ?? '';
    $isJsonRequest = strpos($acceptHeader, 'application/json') !== false || 
                    ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST));

    if ($isJsonRequest) {
        header('Content-Type: application/json');
        $response = array_merge(['success' => $success, 'message' => $message], $additionalData);
        echo json_encode($response);
    } else {
        // Plain text response
        header('Content-Type: text/plain');
        echo $success ? "SUCCESS: $message" : "ERROR: $message";
        
        // Output additional data if present
        if (!empty($additionalData)) {
            echo "\n\nAdditional Data:\n";
            foreach ($additionalData as $key => $value) {
                if (is_array($value)) {
                    $value = json_encode($value);
                }
                echo "$key: $value\n";
            }
        }
    }
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    sendResponse(false, 'User not logged in');
}

// Handle database connection error
if ($conn->connect_error) {
    sendResponse(false, 'Database connection failed');
}

// Get input data based on request type
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST)) {
    // JSON input
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        sendResponse(false, 'Invalid JSON data');
    }
} else {
    // Form data input
    $data = $_POST;
}

// Validate data
$requiredFields = ['name', 'email', 'phone', 'pincode', 'address', 'products', 'total'];
foreach ($requiredFields as $field) {
    if (empty($data[$field])) {
        sendResponse(false, "Missing required field: $field");
    }
}

// Start transaction
$conn->begin_transaction();

try {
    // Insert order into database
    $user_id = $_SESSION['user_id'];
    $name = $conn->real_escape_string($data['name']);
    $email = $conn->real_escape_string($data['email']);
    $phone = $conn->real_escape_string($data['phone']);
    $pincode = $conn->real_escape_string($data['pincode']);
    $address = $conn->real_escape_string($data['address']);
    $total = floatval($data['total']);
    $status = 'Processing';
    
    $order_sql = "INSERT INTO orders (user_id, customer_name, email, phone, pincode, address, total_amount, status, order_date) 
                  VALUES ($user_id, '$name', '$email', '$phone', '$pincode', '$address', $total, '$status', NOW())";
    
    if (!$conn->query($order_sql)) {
        throw new Exception("Failed to create order: " . $conn->error);
    }
    
    $order_id = $conn->insert_id;
    
    // Insert order items
    foreach ($data['products'] as $product) {
        $product_id = intval($product['product_id']);
        $quantity = intval($product['quantity']);
        $price = floatval($product['price']);
        $total = floatval($product['total']);
        
        $item_sql = "INSERT INTO order_items (order_id, product_id, quantity, price, total) 
                     VALUES ($order_id, $product_id, $quantity, $price, $total)";
        
        if (!$conn->query($item_sql)) {
            throw new Exception("Failed to add order items: " . $conn->error);
        }
        
        // Update product quantity in inventory
        $update_sql = "UPDATE products SET quantity = quantity - $quantity WHERE product_id = $product_id";
        if (!$conn->query($update_sql)) {
            throw new Exception("Failed to update product inventory: " . $conn->error);
        }
    }
    
    // Commit transaction
    $conn->commit();
    
    // Send notifications
    sendOrderConfirmationEmail($email, $order_id, $data);
    sendWhatsAppNotification($phone, $order_id, $data);
    
    sendResponse(true, 'Order processed successfully', ['order_id' => $order_id]);
    
} catch (Exception $e) {
    $conn->rollback();
    sendResponse(false, $e->getMessage());
}

function sendOrderConfirmationEmail($email, $order_id, $order_data) {
    $subject = "Order Confirmation #$order_id";
    $message = "Thank you for your order!\n\n";
    $message .= "Order Details:\n";
    $message .= "Order ID: #$order_id\n";
    $message .= "Total Amount: ₹" . number_format($order_data['total'], 2) . "\n";
    $message .= "Delivery Address: " . $order_data['address'] . "\n\n";
    $message .= "We'll notify you when your order ships.";
    
    // In production, use a proper email library
    @mail($email, $subject, $message);
}

function sendWhatsAppNotification($phone, $order_id, $order_data) {
    // This is just a placeholder - implement actual WhatsApp integration
    $message = "Thank you for your order #$order_id. Total: ₹" . number_format($order_data['total'], 2);
    // In production, use WhatsApp Business API
}
?>