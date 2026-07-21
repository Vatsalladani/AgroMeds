<?php
session_start();
require 'vendor/autoload.php';

// Database connection
$host = 'localhost';
$dbname = 'agriculture';
$username = 'root';
$password = '';
$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$isLoggedIn = isset($_SESSION['user_id']);
$theme = isset($_SESSION['theme']) ? $_SESSION['theme'] : 'light';
$language = isset($_SESSION['language']) ? $_SESSION['language'] : 'en';
$user_id = $_SESSION['user_id'];

// Initialize filter variables
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) && $_GET['status'] != 'all' ? $_GET['status'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'order_date';
$order = isset($_GET['order']) ? $_GET['order'] : 'DESC';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Base SQL for orders
$orders_sql = "SELECT o.*, p.payment_method, p.payment_status as payment_table_status, 
               p.transaction_id, p.created_at as payment_date
               FROM orders o
               LEFT JOIN payments p ON o.order_id = p.order_id
               WHERE o.user_id = $user_id";

if (!empty($search)) {
    $search_term = $conn->real_escape_string($search);
    $orders_sql .= " AND (o.order_id LIKE '%$search_term%' OR o.address LIKE '%$search_term%' OR o.customer_name LIKE '%$search_term%')";
}

if (!empty($status_filter)) {
    $orders_sql .= " AND o.status = '" . $conn->real_escape_string($status_filter) . "'";
}

$orders_sql .= " ORDER BY $sort $order";

// For pagination
$count_sql = str_replace('o.*, p.payment_method, p.payment_status as payment_table_status, 
               p.transaction_id, p.created_at as payment_date', 'COUNT(*) as total', $orders_sql);
$count_result = $conn->query($count_sql);
$total_items = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_items / $limit);

$orders_sql .= " LIMIT $limit OFFSET $offset";
$orders_result = $conn->query($orders_sql);

// Fetch user details
$user_sql = "SELECT * FROM users WHERE user_id = $user_id";
$user_result = $conn->query($user_sql);
$user = $user_result->fetch_assoc();

// Fetch all orders for display (separated into active and delivered)
$all_orders_sql = "SELECT o.*, p.payment_method, p.payment_status as payment_table_status, 
                  p.transaction_id, p.created_at as payment_date
                  FROM orders o
                  LEFT JOIN payments p ON o.order_id = p.order_id
                  WHERE o.user_id = $user_id
                  ORDER BY o.order_date DESC";
$all_orders_result = $conn->query($all_orders_sql);

// Separate orders
$active_orders = [];
$delivered_orders = [];
$filtered_orders = [];

while ($order = $all_orders_result->fetch_assoc()) {
    if ($order['status'] == 'Delivered') {
        $delivered_orders[] = $order;
    } else {
        $active_orders[] = $order;
    }
    
    if (!empty($status_filter)) {
        if ($order['status'] == $status_filter) {
            $filtered_orders[] = $order;
        }
    }
}

// Timeline calculation function
function calculateTimeline($order_date, $status) {
    $timeline = ['Order Placed' => ['date' => $order_date, 'completed' => true]];
    $order_time = strtotime($order_date);
    
    switch ($status) {
        case 'Processing':
            $timeline['Processing'] = ['date' => date('Y-m-d H:i:s', $order_time + 3600), 'completed' => true];
            $timeline['Shipped'] = ['date' => date('Y-m-d H:i:s', $order_time + 86400 * 2), 'completed' => false];
            $timeline['Delivered'] = ['date' => date('Y-m-d H:i:s', $order_time + 86400 * 5), 'completed' => false];
            break;
        case 'Shipped':
            $timeline['Processing'] = ['date' => date('Y-m-d H:i:s', $order_time + 3600), 'completed' => true];
            $timeline['Shipped'] = ['date' => date('Y-m-d H:i:s', $order_time + 86400 * 2), 'completed' => true];
            $timeline['Delivered'] = ['date' => date('Y-m-d H:i:s', $order_time + 86400 * 5), 'completed' => false];
            break;
        case 'Delivered':
            $timeline['Processing'] = ['date' => date('Y-m-d H:i:s', $order_time + 3600), 'completed' => true];
            $timeline['Shipped'] = ['date' => date('Y-m-d H:i:s', $order_time + 86400 * 2), 'completed' => true];
            $timeline['Delivered'] = ['date' => date('Y-m-d H:i:s', $order_time + 86400 * 4), 'completed' => true];
            break;
        case 'Cancelled':
            $timeline['Processing'] = ['date' => date('Y-m-d H:i:s', $order_time + 3600), 'completed' => true];
            $timeline['Cancelled'] = ['date' => date('Y-m-d H:i:s', $order_time + 86400), 'completed' => true];
            break;
    }
    return $timeline;
}

function getStatusClass($status) {
    switch ($status) {
        case 'Processing': return 'status-processing';
        case 'Shipped': return 'status-shipped';
        case 'Delivered': return 'status-delivered';
        case 'Cancelled': return 'status-cancelled';
        default: return 'status-pending';
    }
}

function getPaymentStatusClass($status) {
    switch ($status) {
        case 'Completed': return 'status-delivered';
        case 'Failed': return 'status-cancelled';
        default: return 'status-pending';
    }
}

// Handle order cancellation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cancel_order'])) {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error_message'] = "Invalid CSRF token. Please try again.";
        header("Location: orders.php");
        exit();
    }

    $order_id = intval($_POST['order_id']);
    $reason = $conn->real_escape_string($_POST['reason']);
    $other_reason = isset($_POST['other_reason']) ? $conn->real_escape_string($_POST['other_reason']) : '';
    $refund_preference = $conn->real_escape_string($_POST['refund_preference']);
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // 1. Update order status
        $update_sql = "UPDATE orders SET status = 'Cancelled', cancelled_at = NOW() 
                       WHERE order_id = $order_id AND user_id = $user_id AND status = 'Processing'";
        
        if ($conn->query($update_sql)) {
            if ($conn->affected_rows === 0) {
                throw new Exception("Order cannot be cancelled. It may have already been processed.");
            }
            
            // 2. Insert into cancel_orders table
            $insert_sql = "INSERT INTO cancel_orders 
                          (order_id, user_id, reason, other_reason, refund_preference, cancellation_date) 
                          VALUES ($order_id, $user_id, '$reason', '$other_reason', '$refund_preference', NOW())";
            
            if (!$conn->query($insert_sql)) {
                throw new Exception("Failed to log cancellation details.");
            }
            
            // 3. If payment was completed, create refund record (example)
            $payment_sql = "SELECT payment_status FROM payments WHERE order_id = $order_id";
            $payment_result = $conn->query($payment_sql);
            if ($payment_result && $payment_result->num_rows > 0) {
                $payment = $payment_result->fetch_assoc();
                if ($payment['payment_status'] == 'Completed') {
                    $refund_sql = "INSERT INTO refunds 
                                  (order_id, user_id, amount, reason, status, requested_at) 
                                  SELECT o.order_id, o.user_id, o.total_amount, 
                                  'Order cancellation: $reason', 'Pending', NOW()
                                  FROM orders o WHERE o.order_id = $order_id";
                    $conn->query($refund_sql);
                }
            }
            
            // Commit transaction
            $conn->commit();
            
            $_SESSION['success_message'] = "Order #$order_id has been cancelled successfully. Your refund will be processed according to your selected method.";
            header("Location: orders.php");
            exit();
        } else {
            throw new Exception("Failed to update order status.");
        }
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error_message'] = $e->getMessage();
        header("Location: orders.php");
        exit();
    }
}

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders | AgroMeds</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6a11cb;
            --secondary: #2575fc;
            --success: #28a745;
            --warning: #ffc107;
            --danger: #dc3545;
            --light: #f8f9fa;
            --dark: #343a40;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
            min-height: 100vh;
        }

        .order-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            overflow: hidden;
            margin-bottom: 20px;
            border: none;
            opacity: 0;
            transform: translateY(20px);
        }

        .order-card.animate {
            opacity: 1;
            transform: translateY(0);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }

        .order-card:hover {
            transform: translateY(-5px) scale(1.01);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.12);
        }

        .order-header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 15px 20px;
            border-radius: 12px 12px 0 0 !important;
            position: relative;
            overflow: hidden;
        }

        .order-header::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(
                to bottom right,
                rgba(255, 255, 255, 0) 0%,
                rgba(255, 255, 255, 0.1) 50%,
                rgba(255, 255, 255, 0) 100%
            );
            transform: rotate(30deg);
            transition: all 0.3s ease;
        }

        .order-header:hover::after {
            animation: shine 1.5s infinite;
        }

        @keyframes shine {
            0% { transform: translateX(-100%) rotate(30deg); }
            100% { transform: translateX(100%) rotate(30deg); }
        }

        .order-body {
            padding: 20px;
        }

        .order-status {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }

        .status-pending {
            background-color: rgba(255, 193, 7, 0.2);
            color: #856404;
        }

        .status-processing {
            background-color: rgba(0, 123, 255, 0.2);
            color: #004085;
        }

        .status-shipped {
            background-color: rgba(40, 167, 69, 0.2);
            color: #155724;
        }

        .status-delivered {
            background-color: rgba(40, 167, 69, 0.3);
            color: #155724;
        }

        .status-cancelled {
            background-color: rgba(220, 53, 69, 0.2);
            color: #721c24;
        }

        .order-timeline {
            position: relative;
            padding-left: 30px;
            margin-top: 20px;
        }

        .order-timeline::before {
            content: '';
            position: absolute;
            left: 10px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e9ecef;
            transition: all 0.5s ease;
        }

        .order-card:hover .order-timeline::before {
            background: linear-gradient(to bottom, var(--primary), var(--secondary));
        }

        .timeline-step {
            position: relative;
            padding-bottom: 20px;
            opacity: 0;
            transform: translateX(-10px);
            transition: all 0.4s ease;
        }

        .order-card:hover .timeline-step {
            opacity: 1;
            transform: translateX(0);
        }

        .timeline-step:last-child {
            padding-bottom: 0;
        }

        .timeline-step::before {
            content: '';
            position: absolute;
            left: -30px;
            top: 3px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #adb5bd;
            z-index: 1;
            transition: all 0.3s ease;
        }

        .timeline-step.active::before {
            background: var(--primary);
            box-shadow: 0 0 0 3px rgba(106, 17, 203, 0.2);
        }

        .timeline-step.completed::before {
            background: var(--success);
            box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.2);
        }

        .timeline-date {
            font-size: 0.75rem;
            color: #6c757d;
            transition: all 0.3s ease;
        }

        .order-card:hover .timeline-date {
            color: var(--primary);
        }

        .btn-action {
            border-radius: 20px;
            padding: 5px 15px;
            font-size: 0.8rem;
            font-weight: 500;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }

        .btn-action::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(-100%);
            transition: all 0.4s ease;
        }

        .btn-action:hover::after {
            transform: translateX(0);
        }

        .section-title {
            position: relative;
            margin-bottom: 30px;
            padding-bottom: 10px;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.6s ease;
        }

        .section-title.animate {
            opacity: 1;
            transform: translateY(0);
        }

        .section-title::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 50px;
            height: 3px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 3px;
            transition: all 0.6s ease;
        }

        .section-title:hover::after {
            width: 100px;
        }

        .order-product-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .order-product-img:hover {
            transform: scale(1.1);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        /* Modal styling */
        .cancel-modal .modal-header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border-radius: 0;
        }

        .cancel-modal .modal-footer {
            border-top: none;
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideInFromLeft {
            from { opacity: 0; transform: translateX(-50px); }
            to { opacity: 1; transform: translateX(0); }
        }

        @keyframes slideInFromRight {
            from { opacity: 0; transform: translateX(50px); }
            to { opacity: 1; transform: translateX(0); }
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }

        .animate-fade-in {
            animation: fadeIn 0.6s ease forwards;
        }

        .animate-slide-left {
            animation: slideInFromLeft 0.6s ease forwards;
        }

        .animate-slide-right {
            animation: slideInFromRight 0.6s ease forwards;
        }

        .animate-pulse {
            animation: pulse 2s infinite;
        }

        .animate-float {
            animation: float 3s ease-in-out infinite;
        }

        .delay-1 { animation-delay: 0.2s; }
        .delay-2 { animation-delay: 0.4s; }
        .delay-3 { animation-delay: 0.6s; }
        .delay-4 { animation-delay: 0.8s; }
        .delay-5 { animation-delay: 1s; }

        /* Floating action button */
        .fab {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            box-shadow: 0 6px 20px rgba(106, 17, 203, 0.3);
            cursor: pointer;
            z-index: 100;
            transition: all 0.3s;
        }

        .fab:hover {
            transform: translateY(-5px) scale(1.1);
            box-shadow: 0 10px 25px rgba(106, 17, 203, 0.4);
            animation: none;
        }

        .pagination .page-item.active .page-link {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-color: transparent;
        }
        
        .filter-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.6s ease;
        }

        .filter-card.animate {
            opacity: 1;
            transform: translateY(0);
        }
        
        .order-items-table {
            width: 100%;
            margin-top: 15px;
            border-collapse: separate;
            border-spacing: 0 10px;
        }
        
        .order-items-table th {
            background-color: #f8f9fa;
            padding: 10px;
            text-align: left;
        }
        
        .order-items-table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
            background-color: white;
            transition: all 0.3s ease;
        }

        .order-items-table tr:hover td {
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .order-items-table img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .order-items-table img:hover {
            transform: scale(1.1);
        }
        
        .alert-message {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1100;
            animation: slideIn 0.5s forwards;
        }
        
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        /* Empty state styling */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            opacity: 0;
            transform: scale(0.9);
            transition: all 0.6s ease;
        }

        .empty-state.animate {
            opacity: 1;
            transform: scale(1);
        }

        .empty-state i {
            font-size: 3rem;
            color: var(--primary);
            margin-bottom: 20px;
            display: inline-block;
        }

        /* Progress bar animation */
        .progress-bar-animated {
            animation: progress-bar-stripes 1s linear infinite;
        }

        @keyframes progress-bar-stripes {
            from { background-position: 1rem 0; }
            to { background-position: 0 0; }
        }

        /* Ripple effect */
        .ripple {
            position: relative;
            overflow: hidden;
        }

        .ripple-effect {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.4);
            transform: scale(0);
            animation: ripple 0.6s linear;
            pointer-events: none;
        }

        @keyframes ripple {
            to {
                transform: scale(2.5);
                opacity: 0;
            }
        }

        /* Glow effect */
        .glow {
            animation: glow 2s infinite alternate;
        }

        @keyframes glow {
            from {
                box-shadow: 0 0 5px rgba(106, 17, 203, 0.5);
            }
            to {
                box-shadow: 0 0 20px rgba(106, 17, 203, 0.8);
            }
        }

        /* Gradient text */
        .gradient-text {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        /* Card hover effects */
        .card-hover-effect {
            transition: all 0.3s ease;
        }

        .card-hover-effect:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }

        /* Loading spinner */
        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 4px solid rgba(106, 17, 203, 0.1);
            border-radius: 50%;
            border-top-color: var(--primary);
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .order-card {
                margin-bottom: 15px;
            }
            
            .order-header {
                padding: 10px 15px;
            }
            
            .order-body {
                padding: 15px;
            }
            
            .fab {
                width: 50px;
                height: 50px;
                font-size: 20px;
                bottom: 20px;
                right: 20px;
            }
        }

        /* Enhanced Cancel Modal Styles */
        .cancel-modal .modal-header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-bottom: none;
        }

        .cancel-modal .modal-content {
            border: none;
            border-radius: 12px;
            overflow: hidden;
        }

        .cancel-modal .alert-warning {
            background-color: rgba(255, 193, 7, 0.15);
            border-color: rgba(255, 193, 7, 0.3);
        }

        .cancel-modal .order-summary {
            background-color: rgba(233, 236, 239, 0.5);
            border-left: 4px solid var(--primary);
        }

        .cancel-modal #confirmCancelBtn {
            transition: all 0.3s;
        }

        .cancel-modal #confirmCancelBtn:disabled {
            opacity: 0.5;
            transform: none !important;
        }

        .cancel-modal .form-control, .cancel-modal .form-select {
            border-radius: 8px;
            padding: 10px 15px;
        }

        .cancel-modal .form-select {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%236a11cb' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
        }

        /* Google Translate Widget Styling */
.goog-te-banner-frame {
    display: none !important;
}

.goog-te-gadget {
    font-family: inherit !important;
    color: transparent !important;
}

.goog-te-gadget-simple {
    background-color: transparent !important;
    border: none !important;
    padding: 0 !important;
}

.goog-te-menu-value {
    display: none !important;
}

.goog-te-gadget-icon {
    display: none !important;
}

/* Language selector dropdown styling */
.navbar .dropdown-menu {
    min-width: 150px;
}

.language-selector {
    display: flex;
    align-items: center;
    padding: 8px 16px;
}

.language-selector:hover {
    background-color: var(--secondary-color);
    color: white;
}
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light sticky-top shadow">
            <div class="container">
                <a class="navbar-brand" href="home.php"><i class="fas fa-leaf"></i> <span data-lang-key="logo">AgroMeds</span></a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item"><a class="nav-link" href="home.php" data-lang-key="home">Home</a></li>
                        <li class="nav-item"><a class="nav-link" href="Features.php" data-lang-key="features">Features</a></li>
                        <li class="nav-item"><a class="nav-link" href="products.php" data-lang-key="products">Products</a></li>
                        <li class="nav-item"><a class="nav-link" href="contactUs.php" data-lang-key="contact">Contact</a></li>
                        <?php if ($isLoggedIn): ?>
                            <!-- Profile Dropdown Menu -->
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Profile
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                                    <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                                    <li><a class="dropdown-item" href="favorites.php">Favorites</a></li>
                                    <li><a class="dropdown-item" href="cart.php">Cart</a></li>
                                    <li><a class="dropdown-item" href="orders.php">Orders</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
                                </ul>
                            </li>
                        <?php else: ?>
                            <!-- Login Link -->
                            <li class="nav-item"><a class="nav-link btn btn-outline-primary px-3 ms-2" href="login.php">Login</a></li>
                        <?php endif; ?>
                        
                        <!-- Language Selector Dropdown -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="languageDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-language"></i> <span data-lang-key="language">Language</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="languageDropdown">
                                <li><a class="dropdown-item language-selector" href="#" data-lang="en">English</a></li>
                                <li><a class="dropdown-item language-selector" href="#" data-lang="hi">हिंदी (Hindi)</a></li>
                                <li><a class="dropdown-item language-selector" href="#" data-lang="gu">ગુજરાતી (Gujarati)</a></li>
                            </ul>
                        </li>
                    </ul>
                    <a href="settings.html" class="ms-3 settings-icon"><i class="fas fa-cog"></i> <span data-lang-key="settings"></span></a>
                </div>
            </div>
        </nav>

    <div class="container py-5">
        <!-- Success/Error Messages -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert-message">
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['success_message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert-message">
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['error_message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <div class="row mb-4">
            <div class="col">
                <h1 class="display-5 fw-bold gradient-text d-inline-block animate-slide-left">My Orders</h1>
                <p class="text-muted animate-slide-left delay-1">Welcome back, <?php echo htmlspecialchars($user['full_name']); ?>!</p>
            </div>
        </div>

        <!-- Filters and Search -->
        <div class="filter-card animate-fade-in delay-2">
            <form method="get" action="orders.php" class="row g-3">
                <div class="col-md-4">
                    <label for="search" class="form-label">Search Orders</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="<?php echo htmlspecialchars($search); ?>" placeholder="Order ID, Address or Name">
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label">Filter by Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="all" <?php echo empty($status_filter) ? 'selected' : ''; ?>>All Statuses</option>
                        <option value="Processing" <?php echo $status_filter == 'Processing' ? 'selected' : ''; ?>>Processing</option>
                        <option value="Shipped" <?php echo $status_filter == 'Shipped' ? 'selected' : ''; ?>>Shipped</option>
                        <option value="Delivered" <?php echo $status_filter == 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
                        <option value="Cancelled" <?php echo $status_filter == 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="sort" class="form-label">Sort By</label>
                    <select class="form-select" id="sort" name="sort">
                        <option value="order_date" <?php echo $sort == 'order_date' ? 'selected' : ''; ?>>Order Date</option>
                        <option value="total_amount" <?php echo $sort == 'total_amount' ? 'selected' : ''; ?>>Total Amount</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100 ripple">Apply Filters</button>
                </div>
            </form>
        </div>

        <?php if (!empty($status_filter)): ?>
            <!-- Filtered Orders -->
            <div class="row mb-5">
                <div class="col">
                    <h3 class="section-title animate-slide-left delay-3">Filtered Orders (<?php echo ucfirst($status_filter); ?>)</h3>
                    
                    <?php if (empty($filtered_orders)): ?>
                        <div class="empty-state animate-fade-in delay-4">
                            <i class="bi bi-cart-x"></i>
                            <h4 class="mt-3">No orders found</h4>
                            <p class="text-muted">No orders match your selected filters</p>
                            <a href="orders.php" class="btn btn-primary mt-3 ripple">Clear Filters</a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($filtered_orders as $index => $order): 
                            $timeline = calculateTimeline($order['order_date'], $order['status']);
                            $delay_class = 'delay-' . ($index + 1);
                        ?>
                            <div class="order-card animate-fade-in <?php echo $delay_class; ?>">
                                <div class="order-header">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="mb-0">Order #<?php echo $order['order_id']; ?></h5>
                                            <small class="text-white-50">Placed on <?php echo date('M d, Y h:i A', strtotime($order['order_date'])); ?></small>
                                        </div>
                                        <span class="order-status <?php echo getStatusClass($order['status']); ?>">
                                            <?php echo $order['status']; ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="order-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>Payment:</strong> 
                                                <span class="<?php echo getPaymentStatusClass($order['payment_table_status']); ?>">
                                                    <?php echo $order['payment_table_status'] ?? 'Pending'; ?>
                                                </span>
                                            </p>
                                            <p class="mb-1"><strong>Method:</strong> <?php echo $order['payment_method'] ?? 'N/A'; ?></p>
                                            <?php if (!empty($order['transaction_id'])): ?>
                                                <p class="mb-1"><strong>Transaction ID:</strong> <?php echo $order['transaction_id']; ?></p>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>Delivery Address:</strong></p>
                                            <p class="mb-1"><?php echo htmlspecialchars($order['address']); ?></p>
                                            <p class="mb-1">Pincode: <?php echo $order['pincode']; ?></p>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-3">
                                        <table class="order-items-table">
                                            <thead>
                                                <tr>
                                                    <th>Product</th>
                                                    <th>Price</th>
                                                    <th>Qty</th>
                                                    <th>Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                // Fetch order items
                                                $order_id = $order['order_id'];
                                                $items_sql = "SELECT oi.*, p.product_name, p.image_url 
                                                             FROM order_items oi
                                                             JOIN products p ON oi.product_id = p.product_id
                                                             WHERE oi.order_id = $order_id";
                                                $items_result = $conn->query($items_sql);
                                                
                                                while ($item = $items_result->fetch_assoc()):
                                                ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>" class="order-product-img me-3">
                                                            <div><?php echo htmlspecialchars($item['product_name']); ?></div>
                                                        </div>
                                                    </td>
                                                    <td>₹<?php echo number_format($item['price'], 2); ?></td>
                                                    <td><?php echo $item['quantity']; ?></td>
                                                    <td>₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                                </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <div class="order-timeline">
                                            <?php foreach ($timeline as $step => $details): ?>
                                                <div class="timeline-step <?php echo $details['completed'] ? 'completed' : 'active'; ?>">
                                                    <h6 class="mb-0"><?php echo $step; ?></h6>
                                                    <small class="timeline-date"><?php echo date('M d, Y h:i A', strtotime($details['date'])); ?></small>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <div class="text-end">
                                            <h5 class="mb-0">Total: ₹<?php echo number_format($order['total_amount'], 2); ?></h5>
                                            <div class="mt-2">
                                                <?php if ($order['status'] == 'Processing'): ?>
                                                    <button class="btn btn-outline-danger btn-action me-2 cancel-order-btn" 
                                                            data-order-id="<?php echo $order['order_id']; ?>"
                                                            data-order-amount="<?php echo number_format($order['total_amount'], 2); ?>">
                                                        Cancel Order
                                                    </button>
                                                <?php endif; ?>
                                                <button class="btn btn-outline-primary btn-action view-details-btn" 
                                                        data-order-id="<?php echo $order['order_id']; ?>">
                                                    View Details
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <nav aria-label="Order pagination">
                                <ul class="pagination justify-content-center mt-4 animate-fade-in">
                                    <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status_filter; ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>">Previous</a>
                                    </li>
                                    
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status_filter; ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status_filter; ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>">Next</a>
                                    </li>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <!-- Active Orders -->
            <div class="row mb-5">
                <div class="col">
                    <h3 class="section-title animate-slide-left delay-3">Active Orders</h3>
                    
                    <?php if (empty($active_orders)): ?>
                        <div class="empty-state animate-fade-in delay-4">
                            <i class="bi bi-cart-x animate-float"></i>
                            <h4 class="mt-3">No active orders</h4>
                            <p class="text-muted">You don't have any active orders at the moment</p>
                            <a href="products.php" class="btn btn-primary mt-3 ripple">Shop Now</a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($active_orders as $index => $order): 
                            $timeline = calculateTimeline($order['order_date'], $order['status']);
                            $delay_class = 'delay-' . ($index + 1);
                        ?>
                            <div class="order-card animate-fade-in <?php echo $delay_class; ?>">
                                <div class="order-header">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="mb-0">Order #<?php echo $order['order_id']; ?></h5>
                                            <small class="text-white-50">Placed on <?php echo date('M d, Y h:i A', strtotime($order['order_date'])); ?></small>
                                        </div>
                                        <span class="order-status <?php echo getStatusClass($order['status']); ?>">
                                            <?php echo $order['status']; ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="order-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>Payment:</strong> 
                                                <span class="<?php echo getPaymentStatusClass($order['payment_table_status']); ?>">
                                                    <?php echo $order['payment_table_status'] ?? 'Pending'; ?>
                                                </span>
                                            </p>
                                            <p class="mb-1"><strong>Method:</strong> <?php echo $order['payment_method'] ?? 'N/A'; ?></p>
                                            <?php if (!empty($order['transaction_id'])): ?>
                                                <p class="mb-1"><strong>Transaction ID:</strong> <?php echo $order['transaction_id']; ?></p>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>Delivery Address:</strong></p>
                                            <p class="mb-1"><?php echo htmlspecialchars($order['address']); ?></p>
                                            <p class="mb-1">Pincode: <?php echo $order['pincode']; ?></p>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-3">
                                        <table class="order-items-table">
                                            <thead>
                                                <tr>
                                                    <th>Product</th>
                                                    <th>Price</th>
                                                    <th>Qty</th>
                                                    <th>Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                // Fetch order items
                                                $order_id = $order['order_id'];
                                                $items_sql = "SELECT oi.*, p.product_name, p.image_url 
                                                             FROM order_items oi
                                                             JOIN products p ON oi.product_id = p.product_id
                                                             WHERE oi.order_id = $order_id";
                                                $items_result = $conn->query($items_sql);
                                                
                                                while ($item = $items_result->fetch_assoc()):
                                                ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>" class="order-product-img me-3">
                                                            <div><?php echo htmlspecialchars($item['product_name']); ?></div>
                                                        </div>
                                                    </td>
                                                    <td>₹<?php echo number_format($item['price'], 2); ?></td>
                                                    <td><?php echo $item['quantity']; ?></td>
                                                    <td>₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                                </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <div class="order-timeline">
                                            <?php foreach ($timeline as $step => $details): ?>
                                                <div class="timeline-step <?php echo $details['completed'] ? 'completed' : 'active'; ?>">
                                                    <h6 class="mb-0"><?php echo $step; ?></h6>
                                                    <small class="timeline-date"><?php echo date('M d, Y h:i A', strtotime($details['date'])); ?></small>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <div class="text-end">
                                            <h5 class="mb-0">Total: ₹<?php echo number_format($order['total_amount'], 2); ?></h5>
                                            <div class="mt-2">
                                                <?php if ($order['status'] == 'Processing'): ?>
                                                    <button class="btn btn-outline-danger btn-action me-2 cancel-order-btn" 
                                                            data-order-id="<?php echo $order['order_id']; ?>"
                                                            data-order-amount="<?php echo number_format($order['total_amount'], 2); ?>">
                                                        Cancel Order
                                                    </button>
                                                <?php endif; ?>
                                                <button class="btn btn-outline-primary btn-action view-details-btn" 
                                                        data-order-id="<?php echo $order['order_id']; ?>">
                                                    View Details
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Delivered Orders -->
            <div class="row mb-5">
                <div class="col">
                    <h3 class="section-title animate-slide-left delay-<?php echo count($active_orders) + 3; ?>">Order History</h3>
                    
                    <?php if (empty($delivered_orders)): ?>
                        <div class="empty-state animate-fade-in delay-<?php echo count($active_orders) + 4; ?>">
                            <i class="bi bi-box-seam"></i>
                            <h4 class="mt-3">No order history</h4>
                            <p class="text-muted">Your delivered orders will appear here</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($delivered_orders as $index => $order): 
                            $timeline = calculateTimeline($order['order_date'], $order['status']);
                            $delay_class = 'delay-' . ($index + count($active_orders) + 4);
                        ?>
                            <div class="order-card animate-fade-in <?php echo $delay_class; ?>">
                                <div class="order-header">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="mb-0">Order #<?php echo $order['order_id']; ?></h5>
                                            <small class="text-white-50">Placed on <?php echo date('M d, Y h:i A', strtotime($order['order_date'])); ?></small>
                                        </div>
                                        <span class="order-status <?php echo getStatusClass($order['status']); ?>">
                                            <?php echo $order['status']; ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="order-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>Payment:</strong> 
                                                <span class="<?php echo getPaymentStatusClass($order['payment_table_status']); ?>">
                                                    <?php echo $order['payment_table_status'] ?? 'Pending'; ?>
                                                </span>
                                            </p>
                                            <p class="mb-1"><strong>Method:</strong> <?php echo $order['payment_method'] ?? 'N/A'; ?></p>
                                            <?php if (!empty($order['transaction_id'])): ?>
                                                <p class="mb-1"><strong>Transaction ID:</strong> <?php echo $order['transaction_id']; ?></p>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>Delivery Address:</strong></p>
                                            <p class="mb-1"><?php echo htmlspecialchars($order['address']); ?></p>
                                            <p class="mb-1">Pincode: <?php echo $order['pincode']; ?></p>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-3">
                                        <table class="order-items-table">
                                            <thead>
                                                <tr>
                                                    <th>Product</th>
                                                    <th>Price</th>
                                                    <th>Qty</th>
                                                    <th>Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                // Fetch order items
                                                $order_id = $order['order_id'];
                                                $items_sql = "SELECT oi.*, p.product_name, p.image_url 
                                                             FROM order_items oi
                                                             JOIN products p ON oi.product_id = p.product_id
                                                             WHERE oi.order_id = $order_id";
                                                $items_result = $conn->query($items_sql);
                                                
                                                while ($item = $items_result->fetch_assoc()):
                                                ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>" class="order-product-img me-3">
                                                            <div><?php echo htmlspecialchars($item['product_name']); ?></div>
                                                        </div>
                                                    </td>
                                                    <td>₹<?php echo number_format($item['price'], 2); ?></td>
                                                    <td><?php echo $item['quantity']; ?></td>
                                                    <td>₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                                </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <div class="order-timeline">
                                            <?php foreach ($timeline as $step => $details): ?>
                                                <div class="timeline-step <?php echo $details['completed'] ? 'completed' : 'active'; ?>">
                                                    <h6 class="mb-0"><?php echo $step; ?></h6>
                                                    <small class="timeline-date"><?php echo date('M d, Y h:i A', strtotime($details['date'])); ?></small>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <div class="text-end">
                                            <h5 class="mb-0">Total: ₹<?php echo number_format($order['total_amount'], 2); ?></h5>
                                            <div class="mt-2">
                                                <button class="btn btn-outline-primary btn-action view-details-btn" 
                                                        data-order-id="<?php echo $order['order_id']; ?>">
                                                    View Details
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <nav aria-label="Order pagination">
                                <ul class="pagination justify-content-center mt-4 animate-fade-in">
                                    <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $page-1; ?>">Previous</a>
                                    </li>
                                    
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $page+1; ?>">Next</a>
                                    </li>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Cancel Order Modal -->
    <div class="modal fade cancel-modal" id="cancelOrderModal" tabindex="-1" aria-labelledby="cancelOrderModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-gradient-primary text-white">
                    <h5 class="modal-title" id="cancelOrderModalLabel">Cancel Order #<span id="cancelOrderId"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" action="orders.php" id="cancelOrderForm">
                    <div class="modal-body">
                        <div class="alert alert-warning d-flex align-items-center">
                            <i class="fas fa-exclamation-triangle me-3 fs-4"></i>
                            <div>
                                <h6 class="alert-heading mb-1">Are you sure you want to cancel this order?</h6>
                                <p class="mb-0">Cancellation may take 3-5 business days to process.</p>
                            </div>
                        </div>
                        
                        <div class="order-summary mb-4 p-3 bg-light rounded">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Order Total:</span>
                                <strong>₹<span id="cancelOrderAmount"></span></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Order Date:</span>
                                <span id="cancelOrderDate"></span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Payment Method:</span>
                                <span id="cancelPaymentMethod"></span>
                            </div>
                        </div>
                        
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="cancel_order" value="1">
                        <input type="hidden" name="order_id" id="modalOrderId" value="">
                        
                        <div class="mb-3">
                            <label for="cancelReason" class="form-label fw-bold">Reason for cancellation</label>
                            <select class="form-select" id="cancelReason" name="reason" required>
                                <option value="" disabled selected>Select a reason</option>
                                <option value="changed_mind">Changed my mind</option>
                                <option value="wrong_item">Ordered wrong item</option>
                                <option value="found_cheaper">Found cheaper elsewhere</option>
                                <option value="delivery_time">Delivery time too long</option>
                                <option value="product_issue">Product doesn't match description</option>
                                <option value="other">Other reason</option>
                            </select>
                        </div>
                        
                        <div class="mb-3" id="otherReasonContainer" style="display: none;">
                            <label for="otherReason" class="form-label fw-bold">Please specify</label>
                            <textarea class="form-control" id="otherReason" name="other_reason" rows="3" placeholder="Please provide more details about your cancellation reason"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="refundPreference" class="form-label fw-bold">Refund preference</label>
                            <select class="form-select" id="refundPreference" name="refund_preference" required>
                                <option value="" disabled selected>Select refund method</option>
                                <option value="original_method">Original payment method</option>
                                <option value="wallet">AgroMeds wallet credit</option>
                                <option value="bank_transfer">Bank transfer</option>
                                <option value="upi">UPI payment</option>
                            </select>
                            <small class="text-muted">Refunds to original payment method may take 5-7 business days</small>
                        </div>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="confirmCancellation" required>
                            <label class="form-check-label" for="confirmCancellation">
                                I understand that cancellation cannot be undone and I may be subject to cancellation fees
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Go Back</button>
                        <button type="submit" class="btn btn-danger" id="confirmCancelBtn" disabled>
                            <i class="fas fa-ban me-2"></i> Confirm Cancellation
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Order Details Modal -->
    <div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-labelledby="orderDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="orderDetailsModalLabel">Order Details #<span id="detailsOrderId"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="orderDetailsContent">
                    <!-- Content loaded via AJAX -->
                    <div class="text-center py-4">
                        <div class="loading-spinner"></div>
                        <p class="mt-3">Loading order details...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize tooltips
            $('[data-bs-toggle="tooltip"]').tooltip();
            
            // Animate elements when they come into view
            function animateOnScroll() {
                $('.order-card, .section-title, .filter-card, .empty-state').each(function() {
                    var element = $(this);
                    var position = element.offset().top;
                    var scroll = $(window).scrollTop();
                    var windowHeight = $(window).height();
                    
                    if (scroll + windowHeight > position + 100) {
                        element.addClass('animate');
                    }
                });
            }
            
            // Initial check
            animateOnScroll();
            
            // Check on scroll
            $(window).scroll(function() {
                animateOnScroll();
            });
            
            // Cancel order button click with more data
            $('.cancel-order-btn').click(function() {
                var orderId = $(this).data('order-id');
                var orderAmount = $(this).data('order-amount');
                var orderDate = $(this).closest('.order-card').find('.order-header small').text().replace('Placed on ', '');
                var paymentMethod = $(this).closest('.order-card').find('strong:contains("Method:")').next().text().trim();
                
                $('#cancelOrderId').text(orderId);
                $('#cancelOrderAmount').text(orderAmount);
                $('#cancelOrderDate').text(orderDate);
                $('#cancelPaymentMethod').text(paymentMethod || 'Not specified');
                $('#modalOrderId').val(orderId);
                
                // Reset form
                $('#cancelOrderForm')[0].reset();
                $('#otherReasonContainer').hide();
                $('#confirmCancellation').prop('checked', false);
                $('#confirmCancelBtn').prop('disabled', true);
                
                var modal = new bootstrap.Modal(document.getElementById('cancelOrderModal'));
                modal.show();
            });

            // Toggle other reason field
            $('#cancelReason').change(function() {
                if ($(this).val() === 'other') {
                    $('#otherReasonContainer').slideDown();
                    $('#otherReason').prop('required', true);
                } else {
                    $('#otherReasonContainer').slideUp();
                    $('#otherReason').prop('required', false);
                }
            });

            // Enable/disable cancel button based on confirmation
            $('#confirmCancellation').change(function() {
                $('#confirmCancelBtn').prop('disabled', !$(this).is(':checked'));
            });
            
            // Show/hide other reason field
            $('#cancelReason').change(function() {
                if ($(this).val() === 'other') {
                    $('#otherReasonContainer').slideDown();
                } else {
                    $('#otherReasonContainer').slideUp();
                }
            });
            
            // View details button click
            $('.view-details-btn').click(function() {
                var orderId = $(this).data('order-id');
                $('#detailsOrderId').text(orderId);
                
                // Show loading state
                $('#orderDetailsContent').html(`
                    <div class="text-center py-4">
                        <div class="loading-spinner"></div>
                        <p class="mt-3">Loading order details...</p>
                    </div>
                `);
                
                // Load order details via AJAX
                $.ajax({
                    url: 'order_details.php',
                    type: 'GET',
                    data: { order_id: orderId },
                    success: function(response) {
                        $('#orderDetailsContent').html(response);
                    },
                    error: function() {
                        $('#orderDetailsContent').html(`
                            <div class="alert alert-danger">
                                Failed to load order details. Please try again.
                            </div>
                        `);
                    }
                });
                
                var modal = new bootstrap.Modal(document.getElementById('orderDetailsModal'));
                modal.show();
            });
            
            // Ripple effect for buttons
            $('.ripple').click(function(e) {
                var $this = $(this);
                var $offset = $this.parent().offset();
                var $circle = $this.find('.ripple-effect');
                
                // Remove old ripple circle
                $circle && $circle.remove();
                
                // Create new ripple circle
                $circle = $('<span class="ripple-effect"></span>');
                
                // Get click coordinates
                var x = e.pageX - $offset.left;
                var y = e.pageY - $offset.top;
                
                // Add ripple circle to button
                $this.append($circle);
                
                // Position and animate ripple
                $circle.css({
                    'top': y + 'px',
                    'left': x + 'px'
                }).addClass('ripple-animate');
            });
            
            // Add ripple effect to all buttons with ripple class
            $('.ripple').each(function() {
                $(this).append('<span class="ripple-effect"></span>');
            });
        });
    </script>
    <script type="text/javascript">
function googleTranslateElementInit() {
    new google.translate.TranslateElement({
        pageLanguage: 'en',
        includedLanguages: 'en,hi,gu', // Only show English, Hindi, Gujarati
        layout: google.translate.TranslateElement.InlineLayout.SIMPLE,
        autoDisplay: false
    }, 'google_translate_element');
    
    // Restore language selection if previously chosen
    if(localStorage.getItem('googtrans') !== null) {
        var iframe = document.querySelector('.goog-te-menu-frame');
        iframe.onload = function() {
            var select = iframe.contentWindow.document.querySelector('select');
            if(select) {
                select.value = localStorage.getItem('googtrans');
                select.dispatchEvent(new Event('change'));
            }
        };
    }
    
    // Store language selection
    document.addEventListener('click', function(e) {
        if(e.target.closest('.goog-te-menu-value')) {
            setTimeout(function() {
                var iframe = document.querySelector('.goog-te-menu-frame');
                iframe.onload = function() {
                    var select = iframe.contentWindow.document.querySelector('select');
                    if(select) {
                        select.addEventListener('change', function() {
                            localStorage.setItem('googtrans', this.value);
                        });
                    }
                };
            }, 500);
        }
    });
}
</script>
<script type="text/javascript">
// Custom Google Translate implementation
function googleTranslateElementInit() {
    // Create a hidden div for Google Translate
    const translateDiv = document.createElement('div');
    translateDiv.id = 'google_translate_element';
    translateDiv.style.display = 'none';
    document.body.appendChild(translateDiv);
    
    // Initialize Google Translate
    new google.translate.TranslateElement({
        pageLanguage: 'en',
        includedLanguages: 'en,hi,gu',
        layout: google.translate.TranslateElement.InlineLayout.HORIZONTAL,
        autoDisplay: false
    }, 'google_translate_element');
    
    // Handle our custom language selector
    document.querySelectorAll('.language-selector').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const lang = this.getAttribute('data-lang');
            
            // Set the Google Translate cookie
            document.cookie = `googtrans=/en/${lang}; path=/; domain=.${window.location.hostname}`;
            
            // For immediate effect, reload the page
            window.location.reload();
        });
    });
    
    // Remove the Google Translate branding
    const removeBranding = setInterval(() => {
        const branding = document.querySelector('.goog-te-banner-frame');
        if (branding) {
            branding.remove();
            clearInterval(removeBranding);
        }
    }, 100);
    
    // Remove the Google Translate footer
    const removeFooter = setInterval(() => {
        const footer = document.querySelector('.goog-te-footer');
        if (footer) {
            footer.remove();
            clearInterval(removeFooter);
        }
    }, 100);
}

// Load Google Translate script
function loadGoogleTranslate() {
    const script = document.createElement('script');
    script.type = 'text/javascript';
    script.src = '//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit';
    document.body.appendChild(script);
}

// Check if translation is active
function checkTranslation() {
    const googleTransCookie = document.cookie.split(';').find(c => c.trim().startsWith('googtrans='));
    if (googleTransCookie) {
        const langValue = googleTransCookie.split('=')[1];
        if (langValue && langValue !== '/en/en') {
            // Translation is active
            console.log('Translation active:', langValue);
        }
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    loadGoogleTranslate();
    checkTranslation();
});
</script>
<script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
</body>
</html>