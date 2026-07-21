<?php
// Database connection parameters (UPDATE THESE WITH YOUR ACTUAL CREDENTIALS)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "agriculture";

// Create a new MySQLi connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check for connection errors
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]));
}

$search = isset($_GET['search']) ? $_GET['search'] : '';
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) && is_numeric($_GET['limit']) ? (int)$_GET['limit'] : 10;

$start = ($page - 1) * $limit;

// Base query
$query = "SELECT contact_id, first_name, last_name, email, subject, query FROM contactus";

// Add search condition if search term is provided
if (!empty($search)) {
    $search = $conn->real_escape_string($search);
    $query .= " WHERE first_name LIKE '%$search%' OR last_name LIKE '%$search%' OR email LIKE '%$search%' OR subject LIKE '%$search%'";
}

// Count total contacts (before limit)
$count_query = "SELECT COUNT(*) AS total FROM contactus";
if (!empty($search)) {
    $count_query .= " WHERE first_name LIKE '%$search%' OR last_name LIKE '%$search%' OR email LIKE '%$search%' OR subject LIKE '%$search%'";
}
$count_result = $conn->query($count_query);
$total_contacts = $count_result->fetch_assoc()['total'];

// Add limit
$query .= " ORDER BY created_at DESC LIMIT $start, $limit";
$result = $conn->query($query);

$data = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

$conn->close();

echo json_encode(["status" => "success", "data" => $data, "total" => $total_contacts]);
?>
