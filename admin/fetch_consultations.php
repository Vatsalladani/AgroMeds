<?php
header('Content-Type: application/json');

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
// Get parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 8;
$offset = ($page - 1) * $limit;

try {
    // Build base query
    $query = "SELECT c.*, e.name as expert_name 
              FROM consultations c 
              LEFT JOIN experts e ON c.expert_id = e.expert_id";
    
    $countQuery = "SELECT COUNT(*) as total FROM consultations c";
    
    // Add search conditions if provided
    $conditions = [];
    $params = [];
    if (!empty($search)) {
        $conditions[] = "(c.user_name LIKE ? OR c.user_email LIKE ? OR c.problem_description LIKE ? OR e.name LIKE ?)";
        $searchTerm = "%$search%";
        $params = array_fill(0, 4, $searchTerm);
    }
    
    if (!empty($conditions)) {
        $query .= " WHERE " . implode(" AND ", $conditions);
        $countQuery .= " WHERE " . implode(" AND ", $conditions);
    }
    
    // Add pagination
    $query .= " ORDER BY c.preferred_date DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    
    // Prepare and execute main query
    $stmt = $conn->prepare($query);
    $types = str_repeat('s', count($params) - 2) . 'ii'; // All strings except last two are integers
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $consultations = $result->fetch_all(MYSQLI_ASSOC);
    
    // Get total count
    $countStmt = $conn->prepare($countQuery);
    if (!empty($search)) {
        $countStmt->bind_param(str_repeat('s', count($params) - 2), ...array_slice($params, 0, -2));
    }
    $countStmt->execute();
    $total = $countStmt->get_result()->fetch_assoc()['total'];
    
    // Return response
    echo json_encode([
        'status' => 'success',
        'data' => $consultations,
        'total' => $total
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}

$conn->close();
?>