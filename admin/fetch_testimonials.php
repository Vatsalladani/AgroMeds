<?php
$host = 'localhost';
$db = 'agriculture';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

try {
    // Get parameters
    $search = $_GET['search'] ?? '';
    $page = intval($_GET['page'] ?? 1);
    $limit = intval($_GET['limit'] ?? 8);
    $offset = ($page - 1) * $limit;

    // Build query
    $query = "SELECT t.*, e.name as expert_name 
              FROM testimonials t
              LEFT JOIN experts e ON t.expert_id = e.expert_id
              WHERE t.client_name LIKE ? OR t.profession LIKE ? OR t.city LIKE ? OR t.testimonial LIKE ?
              ORDER BY t.created_at DESC
              LIMIT ? OFFSET ?";
    
    $searchParam = "%$search%";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssii", $searchParam, $searchParam, $searchParam, $searchParam, $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Get total count for pagination
    $countQuery = "SELECT COUNT(*) as total FROM testimonials 
                   WHERE client_name LIKE ? OR profession LIKE ? OR city LIKE ? OR testimonial LIKE ?";
    $countStmt = $conn->prepare($countQuery);
    $countStmt->bind_param("ssss", $searchParam, $searchParam, $searchParam, $searchParam);
    $countStmt->execute();
    $total = $countStmt->get_result()->fetch_assoc()['total'];

    // Format response
    $testimonials = [];
    while ($row = $result->fetch_assoc()) {
        $testimonials[] = $row;
    }

    echo json_encode([
        'status' => 'success',
        'data' => $testimonials,
        'total' => $total
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch testimonials: ' . $e->getMessage()
    ]);
}
?>