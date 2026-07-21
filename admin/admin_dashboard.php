<?php
session_set_cookie_params(3600, "/");
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.html");
    exit();
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'agriculture');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch total counts
$totalAdmins = $conn->query("SELECT COUNT(*) AS count FROM admin")->fetch_assoc()['count'];
$totalCategories = $conn->query("SELECT COUNT(*) AS count FROM category")->fetch_assoc()['count'];
$totalProducts = $conn->query("SELECT COUNT(*) AS count FROM products")->fetch_assoc()['count'];
$totalUsers = $conn->query("SELECT COUNT(*) AS count FROM users")->fetch_assoc()['count'];
$totalOrders = $conn->query("SELECT COUNT(*) AS count FROM orders")->fetch_assoc()['count'];
$totalExperts = $conn->query("SELECT COUNT(*) AS count FROM experts")->fetch_assoc()['count'];
$totalFeedbacks = $conn->query("SELECT COUNT(*) AS count FROM feedback")->fetch_assoc()['count'];
$totalQueries = $conn->query("SELECT COUNT(*) AS count FROM contactus")->fetch_assoc()['count'];

// Fetch recent orders
$recentOrders = $conn->query("SELECT o.order_id, u.full_name, o.total_amount, o.order_date, o.status 
                             FROM orders o JOIN users u ON o.user_id = u.user_id 
                             ORDER BY o.order_date DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);

// Fetch sales data for charts
$salesData = $conn->query("SELECT DATE_FORMAT(order_date, '%Y-%m') AS month, 
                          SUM(total_amount) AS total_sales, COUNT(*) AS order_count 
                          FROM orders 
                          WHERE order_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH) 
                          GROUP BY month 
                          ORDER BY month")->fetch_all(MYSQLI_ASSOC);

// Fetch daily sales for last 7 days
$dailySales = $conn->query("SELECT DATE_FORMAT(order_date, '%a') AS day, 
                           SUM(total_amount) AS total_sales, COUNT(*) AS order_count 
                           FROM orders 
                           WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) 
                           GROUP BY DAYOFWEEK(order_date), day 
                           ORDER BY DAYOFWEEK(order_date)")->fetch_all(MYSQLI_ASSOC);

// Fetch weekly sales for last 8 weeks
$weeklySales = $conn->query("SELECT CONCAT('Week ', WEEK(order_date)) AS week, 
                            SUM(total_amount) AS total_sales, COUNT(*) AS order_count 
                            FROM orders 
                            WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL 8 WEEK) 
                            GROUP BY WEEK(order_date) 
                            ORDER BY WEEK(order_date)")->fetch_all(MYSQLI_ASSOC);

// Fetch yearly sales for last 5 years
$yearlySales = $conn->query("SELECT YEAR(order_date) AS year, 
                            SUM(total_amount) AS total_sales, COUNT(*) AS order_count 
                            FROM orders 
                            WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL 5 YEAR) 
                            GROUP BY YEAR(order_date) 
                            ORDER BY YEAR(order_date)")->fetch_all(MYSQLI_ASSOC);

// Fetch category-wise sales
$categorySales = $conn->query("SELECT c.category_name, SUM(oi.total) AS total_sales, COUNT(oi.order_item_id) AS order_count 
                              FROM order_items oi 
                              JOIN products p ON oi.product_id = p.product_id 
                              JOIN category c ON p.category_id = c.category_id 
                              GROUP BY c.category_name 
                              ORDER BY total_sales DESC")->fetch_all(MYSQLI_ASSOC);

// Fetch top selling products
$topProducts = $conn->query("SELECT p.product_name, SUM(oi.total) AS total_sales, COUNT(oi.order_item_id) AS order_count 
                            FROM order_items oi 
                            JOIN products p ON oi.product_id = p.product_id 
                            GROUP BY p.product_name 
                            ORDER BY total_sales DESC 
                            LIMIT 5")->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <!-- Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --accent-color: #4895ef;
            --success-color: #4cc9f0;
            --warning-color: #f8961e;
            --danger-color: #f72585;
            --light-color: #f8f9fa;
            --dark-color: #212529;
        }
        
        body {
            display: flex;
            min-height: 100vh;
            flex-direction: column;
            background: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .top-bar {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 100;
        }
        
        .top-bar a {
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .top-bar a:hover {
            transform: translateY(-2px);
        }
        
        .sidebar {
            width: 280px;
            height: 100vh;
            background: linear-gradient(180deg, var(--dark-color), #2a2e33);
            color: white;
            flex-shrink: 0;
            position: sticky;
            top: 0;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            overflow-y: auto;
        }
        
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 12px 25px;
            margin: 2px 10px;
            border-radius: 8px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
        }
        
        .sidebar .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .sidebar .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            transform: translateX(5px);
        }
        
        .sidebar .nav-link.active {
            background: var(--primary-color);
            color: white;
            box-shadow: 0 4px 8px rgba(67, 97, 238, 0.3);
        }
        
        .sidebar h4 {
            text-align: center;
            padding: 25px 0;
            margin-bottom: 10px;
            background: rgba(0, 0, 0, 0.2);
            font-weight: 600;
        }
        
        .content {
            flex-grow: 1;
            padding: 25px;
            background-color: #f5f7fa;
        }
        
        /* Dashboard Cards */
        .dashboard-card {
            border: none;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.1);
            cursor: pointer;
            position: relative;
            color: white;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        
        .dashboard-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 20px rgba(0, 0, 0, 0.1);
        }
        
        .dashboard-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(255,255,255,0.2), rgba(255,255,255,0.05));
            z-index: 1;
        }
        
        .dashboard-card .card-body {
            position: relative;
            z-index: 2;
            padding: 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        
        .dashboard-card .card-title {
            font-size: 1rem;
            font-weight: 500;
            margin-bottom: 10px;
            opacity: 0.9;
        }
        
        .dashboard-card .card-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 15px;
        }
        
        .dashboard-card .card-icon {
            font-size: 2.5rem;
            opacity: 0.3;
            position: absolute;
            right: 20px;
            top: 20px;
            transition: all 0.3s ease;
        }
        
        .dashboard-card:hover .card-icon {
            transform: scale(1.2);
            opacity: 0.5;
        }
        
        .dashboard-card .card-footer {
            background: rgba(0, 0, 0, 0.1);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding: 10px 20px;
            font-size: 0.85rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        /* Card Colors */
        .card-admin {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
        }
        
        .card-category {
            background: linear-gradient(135deg, #7209b7, #b5179e);
        }
        
        .card-product {
            background: linear-gradient(135deg, var(--warning-color), #f3722c);
        }
        
        .card-user {
            background: linear-gradient(135deg, var(--danger-color), #b5179e);
        }
        
        .card-order {
            background: linear-gradient(135deg, #2ec4b6, #1b9aaa);
        }
        
        .card-expert {
            background: linear-gradient(135deg, #ff9e00, #ff7b00);
        }
        
        .card-feedback {
            background: linear-gradient(135deg, #4cc9f0, #4895ef);
        }
        
        .card-query {
            background: linear-gradient(135deg, #8338ec, #5e60ce);
        }
        
        /* Chart Section */
        .chart-section {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-top: 30px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.05);
        }
        
        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .chart-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--dark-color);
        }
        
        .chart-controls {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 15px;
        }
        
        .chart-controls .btn-group {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        
        .chart-controls .btn {
            border-radius: 8px !important;
        }
        
        /* Recent Orders */
        .recent-orders {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-top: 30px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.05);
        }
        
        .orders-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .orders-table th {
            text-align: left;
            padding: 12px 15px;
            background: #f8f9fa;
            font-weight: 600;
            color: var(--dark-color);
        }
        
        .orders-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }
        
        .orders-table tr:last-child td {
            border-bottom: none;
        }
        
        .orders-table tr:hover td {
            background: #f8f9fa;
        }
        
        .order-status {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .status-processing {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-shipped {
            background: #cce5ff;
            color: #004085;
        }
        
        .status-delivered {
            background: #d4edda;
            color: #155724;
        }
        
        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }
        
        /* Animations */
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
        
        .floating {
            animation: float 3s ease-in-out infinite;
        }
        
        .delay-1 { animation-delay: 0.2s; }
        .delay-2 { animation-delay: 0.4s; }
        .delay-3 { animation-delay: 0.6s; }
        .delay-4 { animation-delay: 0.8s; }
        
        /* Responsive */
        @media (max-width: 992px) {
            .sidebar {
                width: 220px;
            }
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            
            .content {
                padding: 15px;
            }
            
            .dashboard-card .card-value {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <!-- Top Bar -->
    <div class="top-bar">
        <h4 class="mb-0">Admin Dashboard</h4>
        <a href="logout.php" class="btn btn-danger btn-sm">
            <i class="fas fa-sign-out-alt me-1"></i> Logout
        </a>
    </div>

    <!-- Main Content -->
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar">
            <h4><i class="fas fa-user-shield me-2"></i> Admin Panel</h4>
            <nav class="nav flex-column">
                <a href="admin_dashboard.php" class="nav-link active">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="manage_admins.php" class="nav-link">
                    <i class="fas fa-user-shield"></i> Admins
                </a>
                <a href="manage_categories.php" class="nav-link">
                    <i class="fas fa-tags"></i> Categories
                </a>
                <a href="manage_products.php" class="nav-link">
                    <i class="fas fa-box"></i> Products
                </a>
                <a href="manage_orders.php" class="nav-link">
                    <i class="fas fa-shopping-cart"></i> Orders
                </a>
                <a href="manage_payment.php" class="nav-link">
                    <i class="fas fa-credit-card"></i> Payments
                </a>
                <a href="manage_cancel_orders.php" class="nav-link">
                    <i class="fas fa-ban"></i> Cancel Orders
                </a>
                <a href="manage_experts.php" class="nav-link">
                    <i class="fas fa-user-tie"></i> Experts
                </a>
                <a href="manage_users.php" class="nav-link">
                    <i class="fas fa-users"></i> Users
                </a>
                <a href="manage_consultations.php" class="nav-link ">
                    <i class="fas fa-calendar-check"></i> Consultations
                </a>
                <a href="manage_contactUs.php" class="nav-link">
                    <i class="fas fa-envelope"></i> Contact Queries
                </a>
                <a href="manage_feedbacks.php" class="nav-link">
                    <i class="fas fa-comments"></i> Feedbacks
                </a>
                <a href="manage_testimonials.php" class="nav-link ">
                    <i class="fas fa-quote-left"></i> Testimonials
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="content">
            <h2 class="mb-4 animate__animated animate__fadeIn">Dashboard Overview</h2>
            
            <!-- Summary Cards -->
            <div class="row g-4">
                <!-- Admins Card -->
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="dashboard-card card-admin animate__animated animate__fadeInUp" onclick="window.location.href='manage_admins.php'">
                        <div class="card-body">
                            <h5 class="card-title">Total Admins</h5>
                            <div class="card-value"><?php echo $totalAdmins; ?></div>
                            <i class="fas fa-user-shield card-icon floating"></i>
                        </div>
                        <div class="card-footer">
                            <span>Manage Admins</span>
                            <i class="fas fa-arrow-right"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Categories Card -->
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="dashboard-card card-category animate__animated animate__fadeInUp delay-1" onclick="window.location.href='manage_categories.php'">
                        <div class="card-body">
                            <h5 class="card-title">Total Categories</h5>
                            <div class="card-value"><?php echo $totalCategories; ?></div>
                            <i class="fas fa-tags card-icon floating delay-1"></i>
                        </div>
                        <div class="card-footer">
                            <span>Manage Categories</span>
                            <i class="fas fa-arrow-right"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Products Card -->
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="dashboard-card card-product animate__animated animate__fadeInUp delay-2" onclick="window.location.href='manage_products.php'">
                        <div class="card-body">
                            <h5 class="card-title">Total Products</h5>
                            <div class="card-value"><?php echo $totalProducts; ?></div>
                            <i class="fas fa-box card-icon floating delay-2"></i>
                        </div>
                        <div class="card-footer">
                            <span>Manage Products</span>
                            <i class="fas fa-arrow-right"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Users Card -->
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="dashboard-card card-user animate__animated animate__fadeInUp delay-3" onclick="window.location.href='manage_users.php'">
                        <div class="card-body">
                            <h5 class="card-title">Total Users</h5>
                            <div class="card-value"><?php echo $totalUsers; ?></div>
                            <i class="fas fa-users card-icon floating delay-3"></i>
                        </div>
                        <div class="card-footer">
                            <span>Manage Users</span>
                            <i class="fas fa-arrow-right"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Orders Card -->
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="dashboard-card card-order" onclick="window.location.href='manage_orders.php'">
                        <div class="card-body">
                            <h5 class="card-title">Total Orders</h5>
                            <div class="card-value"><?php echo $totalOrders; ?></div>
                            <i class="fas fa-shopping-cart card-icon"></i>
                        </div>
                        <div class="card-footer">
                            <span>View Orders</span>
                            <i class="fas fa-arrow-right"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Experts Card -->
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="dashboard-card card-expert" onclick="window.location.href='manage_experts.php'">
                        <div class="card-body">
                            <h5 class="card-title">Total Experts</h5>
                            <div class="card-value"><?php echo $totalExperts; ?></div>
                            <i class="fas fa-user-tie card-icon"></i>
                        </div>
                        <div class="card-footer">
                            <span>Manage Experts</span>
                            <i class="fas fa-arrow-right"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Feedbacks Card -->
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="dashboard-card card-feedback" onclick="window.location.href='manage_feedbacks.php'">
                        <div class="card-body">
                            <h5 class="card-title">Total Feedbacks</h5>
                            <div class="card-value"><?php echo $totalFeedbacks; ?></div>
                            <i class="fas fa-comments card-icon"></i>
                        </div>
                        <div class="card-footer">
                            <span>View Feedbacks</span>
                            <i class="fas fa-arrow-right"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Queries Card -->
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="dashboard-card card-query" onclick="window.location.href='manage_contactUs.php'">
                        <div class="card-body">
                            <h5 class="card-title">Total Queries</h5>
                            <div class="card-value"><?php echo $totalQueries; ?></div>
                            <i class="fas fa-envelope card-icon"></i>
                        </div>
                        <div class="card-footer">
                            <span>View Queries</span>
                            <i class="fas fa-arrow-right"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Charts Section -->
            <div class="chart-section animate__animated animate__fadeIn">
                <div class="chart-header">
                    <h3 class="chart-title">Sales Analytics</h3>
                    <div class="chart-controls">
                        <div class="btn-group">
                            <button class="chart-type-btn btn btn-sm btn-primary active" data-chart-type="bar">
                                <i class="fas fa-chart-bar"></i> Bar
                            </button>
                            <button class="chart-type-btn btn btn-sm btn-primary" data-chart-type="line">
                                <i class="fas fa-chart-line"></i> Line
                            </button>
                            <button class="chart-type-btn btn btn-sm btn-primary" data-chart-type="pie">
                                <i class="fas fa-chart-pie"></i> Pie
                            </button>
                            <button class="chart-type-btn btn btn-sm btn-primary" data-chart-type="doughnut">
                                <i class="fas fa-chart-doughnut"></i> Doughnut
                            </button>
                        </div>
                        
                        <div class="btn-group">
                            <button class="data-type-btn btn btn-sm btn-success active" data-data-type="monthly">
                                <i class="fas fa-calendar-alt"></i> Monthly
                            </button>
                            <button class="data-type-btn btn btn-sm btn-success" data-data-type="weekly">
                                <i class="fas fa-calendar-week"></i> Weekly
                            </button>
                            <button class="data-type-btn btn btn-sm btn-success" data-data-type="daily">
                                <i class="fas fa-calendar-day"></i> Daily
                            </button>
                            <button class="data-type-btn btn btn-sm btn-success" data-data-type="yearly">
                                <i class="fas fa-calendar"></i> Yearly
                            </button>
                            <button class="data-type-btn btn btn-sm btn-success" data-data-type="categories">
                                <i class="fas fa-tags"></i> Categories
                            </button>
                            <button class="data-type-btn btn btn-sm btn-success" data-data-type="products">
                                <i class="fas fa-boxes"></i> Products
                            </button>
                        </div>
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
            
            <!-- Recent Orders Section -->
            <div class="recent-orders animate__animated animate__fadeIn">
                <div class="chart-header">
                    <h3 class="chart-title">Recent Orders</h3>
                    <a href="manage_orders.php" class="btn btn-sm btn-primary">
                        <i class="fas fa-eye me-1"></i> View All
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($recentOrders)): ?>
                                <?php foreach ($recentOrders as $order): ?>
                                    <tr>
                                        <td>#<?php echo $order['order_id']; ?></td>
                                        <td><?php echo $order['full_name']; ?></td>
                                        <td>₹<?php echo number_format($order['total_amount'], 2); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                                        <td>
                                            <?php 
                                            $statusClass = '';
                                            switch($order['status']) {
                                                case 'Processing': $statusClass = 'status-processing'; break;
                                                case 'Shipped': $statusClass = 'status-shipped'; break;
                                                case 'Delivered': $statusClass = 'status-delivered'; break;
                                                case 'Cancelled': $statusClass = 'status-cancelled'; break;
                                            }
                                            ?>
                                            <span class="order-status <?php echo $statusClass; ?>"><?php echo $order['status']; ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4">No recent orders found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.4/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/animejs@3.2.1/lib/anime.min.js"></script>
    
    <script>
        // Initialize AOS animations
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });
        
        // Prepare sales data from PHP
        const monthlyData = {
            labels: <?php echo json_encode(array_map(function($item) {
                $date = new DateTime($item['month'] . '-01');
                return $date->format('M Y');
            }, $salesData)); ?>,
            amounts: <?php echo json_encode(array_column($salesData, 'total_sales')); ?>,
            counts: <?php echo json_encode(array_column($salesData, 'order_count')); ?>
        };
        
        const dailyData = {
            labels: <?php echo json_encode(array_column($dailySales, 'day')); ?>,
            amounts: <?php echo json_encode(array_column($dailySales, 'total_sales')); ?>,
            counts: <?php echo json_encode(array_column($dailySales, 'order_count')); ?>
        };
        
        const weeklyData = {
            labels: <?php echo json_encode(array_column($weeklySales, 'week')); ?>,
            amounts: <?php echo json_encode(array_column($weeklySales, 'total_sales')); ?>,
            counts: <?php echo json_encode(array_column($weeklySales, 'order_count')); ?>
        };
        
        const yearlyData = {
            labels: <?php echo json_encode(array_column($yearlySales, 'year')); ?>,
            amounts: <?php echo json_encode(array_column($yearlySales, 'total_sales')); ?>,
            counts: <?php echo json_encode(array_column($yearlySales, 'order_count')); ?>
        };
        
        const categoryData = {
            labels: <?php echo json_encode(array_column($categorySales, 'category_name')); ?>,
            amounts: <?php echo json_encode(array_column($categorySales, 'total_sales')); ?>,
            counts: <?php echo json_encode(array_column($categorySales, 'order_count')); ?>
        };
        
        const productData = {
            labels: <?php echo json_encode(array_column($topProducts, 'product_name')); ?>,
            amounts: <?php echo json_encode(array_column($topProducts, 'total_sales')); ?>,
            counts: <?php echo json_encode(array_column($topProducts, 'order_count')); ?>
        };
        
        // Colors for charts
        const chartColors = {
            primary: '#4361ee',
            secondary: '#3f37c9',
            accent: '#4895ef',
            success: '#4cc9f0',
            warning: '#f8961e',
            danger: '#f72585',
            light: '#f8f9fa',
            dark: '#212529'
        };
        
        // Chart configuration
        let salesChart;
        let currentDataType = 'monthly'; // Default data type
        
        function getCurrentData() {
            switch(currentDataType) {
                case 'daily':
                    return {
                        labels: dailyData.labels,
                        amounts: dailyData.amounts,
                        counts: dailyData.counts,
                        title: 'Daily Sales Analytics'
                    };
                case 'weekly':
                    return {
                        labels: weeklyData.labels,
                        amounts: weeklyData.amounts,
                        counts: weeklyData.counts,
                        title: 'Weekly Sales Trends'
                    };
                case 'yearly':
                    return {
                        labels: yearlyData.labels,
                        amounts: yearlyData.amounts,
                        counts: yearlyData.counts,
                        title: 'Yearly Sales Comparison'
                    };
                case 'categories':
                    return {
                        labels: categoryData.labels,
                        amounts: categoryData.amounts,
                        counts: categoryData.counts,
                        title: 'Category-wise Sales'
                    };
                case 'products':
                    return {
                        labels: productData.labels,
                        amounts: productData.amounts,
                        counts: productData.counts,
                        title: 'Top Selling Products'
                    };
                default: // monthly
                    return {
                        labels: monthlyData.labels,
                        amounts: monthlyData.amounts,
                        counts: monthlyData.counts,
                        title: 'Monthly Sales Analytics'
                    };
            }
        }
        
        function initChart(type = 'bar') {
    const ctx = document.getElementById('salesChart').getContext('2d');
    const currentData = getCurrentData();
    
    // Update chart title
    document.querySelector('.chart-title').textContent = currentData.title;
    
    // Destroy previous chart if exists
    if (salesChart) {
        salesChart.destroy();
    }
    
    // Common chart options
    const commonOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
            },
            tooltip: {
                mode: type === 'pie' || type === 'doughnut' ? 'nearest' : 'index',
                intersect: false,
                callbacks: {
                    label: function(context) {
                        let label = context.dataset.label || '';
                        if (label) {
                            label += ': ';
                        }
                        
                        // Handle pie/doughnut charts differently
                        if (type === 'pie' || type === 'doughnut') {
                            const value = context.raw || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = Math.round((value / total) * 100);
                            return `${label}₹${value.toLocaleString()} (${percentage}%)`;
                        }
                        
                        // Handle bar/line charts
                        const value = context.parsed.y;
                        if (value !== null && value !== undefined) {
                            if (label.includes('Amount')) {
                                return label + '₹' + value.toLocaleString();
                            }
                            return label + value.toLocaleString();
                        }
                        return label + '0';
                    }
                }
            },
            datalabels: {
                display: type === 'pie' || type === 'doughnut',
                formatter: (value, ctx) => {
                    if (type === 'pie' || type === 'doughnut') {
                        const sum = ctx.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                        const percentage = Math.round((value * 100 / sum)) + '%';
                        return percentage;
                    }
                    return '';
                },
                color: '#fff',
                font: {
                    weight: 'bold',
                    size: 12
                }
            }
        },
        animation: {
            duration: 1000,
            easing: 'easeOutQuart'
        }
    };

    // Configure scales only for bar/line charts
    if (type === 'bar' || type === 'line') {
        commonOptions.scales = {
            x: {
                grid: {
                    display: false
                }
            },
            y: {
                type: 'linear',
                display: true,
                position: 'left',
                title: {
                    display: true,
                    text: currentDataType === 'categories' || currentDataType === 'products' ? 'Sales Amount (₹)' : 'Sales Amount (₹)'
                },
                ticks: {
                    callback: function(value) {
                        return currentDataType === 'categories' || currentDataType === 'products' ? 
                               value.toLocaleString() : '₹' + value.toLocaleString();
                    }
                },
                grid: {
                    drawBorder: false,
                    color: 'rgba(0, 0, 0, 0.05)'
                }
            }
        };

        // Add second y-axis for order count when needed
        if (currentDataType !== 'categories' && currentDataType !== 'products') {
            commonOptions.scales.y1 = {
                type: 'linear',
                display: true,
                position: 'right',
                title: {
                    display: true,
                    text: 'Order Count'
                },
                grid: {
                    drawOnChartArea: false,
                },
                ticks: {
                    callback: function(value) {
                        return value.toLocaleString();
                    }
                }
            };
        }
    }

    // Prepare datasets differently for pie/doughnut vs bar/line
    let datasets;
    if (type === 'pie' || type === 'doughnut') {
        // For pie/doughnut, we only show amounts with different colors
        const backgroundColors = currentData.labels.map((_, i) => {
            const colors = Object.values(chartColors);
            return colors[i % colors.length];
        });
        
        datasets = [{
            label: 'Sales Amount (₹)',
            data: currentData.amounts,
            backgroundColor: backgroundColors,
            borderColor: '#fff',
            borderWidth: 2
        }];
    } else {
        // For bar/line charts, use the original dataset logic
        datasets = [{
            label: currentDataType === 'categories' || currentDataType === 'products' ? 'Sales Amount (₹)' : 'Sales Amount (₹)',
            data: currentData.amounts,
            backgroundColor: chartColors.primary,
            borderColor: chartColors.primary,
            borderWidth: 2,
            tension: 0.4,
            fill: type === 'line' ? {
                target: 'origin',
                above: 'rgba(67, 97, 238, 0.1)'
            } : false,
            yAxisID: 'y'
        }];

        if (currentDataType !== 'categories' && currentDataType !== 'products') {
            datasets.push({
                label: 'Order Count',
                data: currentData.counts,
                backgroundColor: chartColors.warning,
                borderColor: chartColors.warning,
                borderWidth: 2,
                tension: 0.4,
                fill: false,
                yAxisID: 'y1'
            });
        }
    }

    // Create the chart with error handling
    try {
        salesChart = new Chart(ctx, {
            type: type,
            data: {
                labels: currentData.labels,
                datasets: datasets
            },
            options: commonOptions,
            plugins: [ChartDataLabels]
        });
    } catch (error) {
        console.error('Chart initialization error:', error);
        document.querySelector('.chart-container').innerHTML = `
            <div class="alert alert-danger">
                Error loading chart data. Please try again later.
            </div>
        `;
    }
}
        
        // Initialize with bar chart
        $(document).ready(function() {
            initChart('bar');
            
            // Chart type switcher
            $('.chart-type-btn').click(function() {
                const type = $(this).data('chart-type');
                $('.chart-type-btn').removeClass('active');
                $(this).addClass('active');
                initChart(type);
                
                // Animate the button
                anime({
                    targets: this,
                    scale: [1, 1.1, 1],
                    duration: 300,
                    easing: 'easeInOutQuad'
                });
            });
            
            // Data type switcher
            $('.data-type-btn').click(function() {
                currentDataType = $(this).data('data-type');
                $('.data-type-btn').removeClass('active');
                $(this).addClass('active');
                
                // Get current chart type
                const currentChartType = $('.chart-type-btn.active').data('chart-type') || 'bar';
                initChart(currentChartType);
                
                // Animate the button
                anime({
                    targets: this,
                    scale: [1, 1.1, 1],
                    duration: 300,
                    easing: 'easeInOutQuad'
                });
            });
            
            // Animate cards on hover
            $('.dashboard-card').hover(
                function() {
                    anime({
                        targets: this,
                        scale: 1.02,
                        duration: 200,
                        easing: 'easeInOutQuad'
                    });
                },
                function() {
                    anime({
                        targets: this,
                        scale: 1,
                        duration: 200,
                        easing: 'easeInOutQuad'
                    });
                }
            );
            
            // Add ripple effect to cards
            $('.dashboard-card').click(function(e) {
                // Create ripple element
                const ripple = document.createElement('span');
                ripple.className = 'ripple-effect';
                ripple.style.width = ripple.style.height = Math.max(this.offsetWidth, this.offsetHeight) + 'px';
                ripple.style.left = (e.pageX - this.getBoundingClientRect().left - ripple.offsetWidth / 2) + 'px';
                ripple.style.top = (e.pageY - this.getBoundingClientRect().top - ripple.offsetHeight / 2) + 'px';
                this.appendChild(ripple);
                
                // Remove ripple after animation
                setTimeout(() => {
                    ripple.remove();
                }, 1000);
            });
        });
        
        // Add CSS for ripple effect
        const style = document.createElement('style');
        style.innerHTML = `
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
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>