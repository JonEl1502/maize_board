<?php
include '../config.php';

// Check if user is admin
if (!isLoggedIn() || !isAdmin()) {
    echo json_encode([
        'status' => 403,
        'message' => 'Unauthorized access'
    ]);
    exit();
}

try {
    // Get user distribution by role
    $query = "SELECT 
                r.name as role_name,
                COUNT(u.id) as user_count
              FROM users u
              JOIN roles r ON u.role_id = r.id
              GROUP BY u.role_id
              ORDER BY r.id ASC";
    
    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }
    
    $labels = [];
    $counts = [];
    
    while ($row = $result->fetch_assoc()) {
        $labels[] = $row['role_name'];
        $counts[] = (int)$row['user_count'];
    }
    
    echo json_encode([
        'status' => 200,
        'message' => 'User chart data retrieved successfully',
        'labels' => $labels,
        'counts' => $counts
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 500,
        'message' => 'Error retrieving user chart data: ' . $e->getMessage()
    ]);
}
