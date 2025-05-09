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
    // Get sales data for the last 7 days
    $query = "SELECT 
                DATE(created_at) as sale_date,
                COUNT(*) as sale_count,
                SUM(total_price) as sale_amount
              FROM purchases
              WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
              GROUP BY DATE(created_at)
              ORDER BY sale_date ASC";
    
    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }
    
    $labels = [];
    $sales = [];
    
    while ($row = $result->fetch_assoc()) {
        $labels[] = date('M d', strtotime($row['sale_date']));
        $sales[] = (float)$row['sale_amount'];
    }
    
    // If we have less than 7 days of data, fill in the missing days
    $today = new DateTime();
    $sevenDaysAgo = (new DateTime())->sub(new DateInterval('P7D'));
    
    if (count($labels) < 7) {
        $existingDates = array_map(function($label) {
            return date('Y-m-d', strtotime($label));
        }, $labels);
        
        for ($i = 0; $i < 7; $i++) {
            $date = clone $sevenDaysAgo;
            $date->add(new DateInterval("P{$i}D"));
            $dateStr = $date->format('Y-m-d');
            
            if (!in_array($dateStr, $existingDates)) {
                $index = array_search($dateStr, array_map(function($d) {
                    return (new DateTime($d))->format('Y-m-d');
                }, $existingDates));
                
                if ($index === false) {
                    $labels[] = $date->format('M d');
                    $sales[] = 0;
                }
            }
        }
        
        // Sort by date
        array_multisort($labels, $sales);
    }
    
    echo json_encode([
        'status' => 200,
        'message' => 'Sales chart data retrieved successfully',
        'labels' => $labels,
        'sales' => $sales
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 500,
        'message' => 'Error retrieving sales chart data: ' . $e->getMessage()
    ]);
}
