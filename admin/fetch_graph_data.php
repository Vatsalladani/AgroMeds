<?php
// Database connection
$host = '127.0.0.1';
$username = 'root'; // Update with your database username
$password = '';     // Update with your database password
$database = 'agriculture';

$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]));
}

// Retrieve graph type from the request
$graphType = $_GET['graphType'] ?? null;

// Initialize response
$response = [
    'labels' => [],
    'values' => [],
    'additionalData' => [] // For extra data needed by certain chart types
];

// Generate data based on graph type
switch ($graphType) {
    case 'bar_chart':
        // Bar chart: Products count by category
        $query = "SELECT category.category_name, COUNT(products.product_id) AS product_count 
                  FROM products 
                  LEFT JOIN category ON products.category_id = category.category_id 
                  GROUP BY category.category_name";
        $result = $conn->query($query);
        while ($row = $result->fetch_assoc()) {
            $response['labels'][] = $row['category_name'] ?? 'Uncategorized';
            $response['values'][] = (int) $row['product_count'];
        }
        break;

    case 'line_chart':
        // Line chart: Users count over time
        $query = "SELECT DATE(created_at) AS date, COUNT(user_id) AS user_count 
                  FROM users 
                  GROUP BY DATE(created_at) 
                  ORDER BY date ASC";
        $result = $conn->query($query);
        while ($row = $result->fetch_assoc()) {
            $response['labels'][] = $row['date'];
            $response['values'][] = (int) $row['user_count'];
        }
        break;

    case 'pie_chart':
        // Pie chart: Admin roles distribution
        $query = "SELECT role, COUNT(admin_id) AS count FROM admin GROUP BY role";
        $result = $conn->query($query);
        while ($row = $result->fetch_assoc()) {
            $response['labels'][] = ucfirst($row['role']);
            $response['values'][] = (int) $row['count'];
        }
        break;

    case 'comparison_chart':
        // Comparison chart: Compare users and products count
        $response['labels'] = ['Users', 'Products'];
        $response['values'] = [
            (int) $conn->query("SELECT COUNT(user_id) AS total FROM users")->fetch_assoc()['total'],
            (int) $conn->query("SELECT COUNT(product_id) AS total FROM products")->fetch_assoc()['total']
        ];
        break;

    case 'heatmap':
        // Heatmap: Product availability per category
        $query = "SELECT category.category_name, SUM(products.quantity) AS total_quantity 
                  FROM products 
                  LEFT JOIN category ON products.category_id = category.category_id 
                  GROUP BY category.category_name";
        $result = $conn->query($query);
        while ($row = $result->fetch_assoc()) {
            $response['labels'][] = $row['category_name'] ?? 'Uncategorized';
            $response['values'][] = (int) $row['total_quantity'];
        }
        break;

    case 'scatter_chart':
        // Scatter chart: Product price vs quantity
        $query = "SELECT price, quantity FROM products";
        $result = $conn->query($query);
        while ($row = $result->fetch_assoc()) {
            $response['labels'][] = (float) $row['price']; // X-axis
            $response['values'][] = (int) $row['quantity']; // Y-axis
        }
        break;

    case 'radar_chart':
        // Radar chart: Category-wise product count
        $query = "SELECT category.category_name, COUNT(products.product_id) AS product_count 
                  FROM products 
                  LEFT JOIN category ON products.category_id = category.category_id 
                  GROUP BY category.category_name";
        $result = $conn->query($query);
        while ($row = $result->fetch_assoc()) {
            $response['labels'][] = $row['category_name'] ?? 'Uncategorized';
            $response['values'][] = (int) $row['product_count'];
        }
        break;

    default:
        $response['error'] = 'Invalid graph type specified.';
        echo json_encode($response);
        exit;
}

// Close connection
$conn->close();

// Return JSON response
echo json_encode($response);
?>
