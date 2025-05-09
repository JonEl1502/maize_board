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
    // Get recent activity from activity_logs table
    $query = "SELECT al.*, u.name as user_name 
              FROM activity_logs al
              JOIN users u ON al.user_id = u.id
              ORDER BY al.created_at DESC
              LIMIT 10";
    
    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }
    
    $activities = [];
    while ($row = $result->fetch_assoc()) {
        $activities[] = $row;
    }
    
    echo json_encode([
        'status' => 200,
        'message' => 'Recent activity retrieved successfully',
        'activities' => $activities
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 500,
        'message' => 'Error retrieving recent activity: ' . $e->getMessage()
    ]);
}
