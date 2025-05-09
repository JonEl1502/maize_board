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

// Get report type from query parameter
$reportType = isset($_GET['type']) ? $_GET['type'] : 'all';

try {
    $reports = [];
    
    switch ($reportType) {
        case 'sales':
            // Sales report
            $query = "SELECT 
                        DATE(p.created_at) as sale_date,
                        COUNT(*) as sale_count,
                        SUM(p.total_price) as sale_amount,
                        pr.name as product_name,
                        SUM(p.quantity) as quantity_sold
                      FROM purchases p
                      JOIN product_listings pl ON p.listing_id = pl.id
                      JOIN products pr ON pl.product_id = pr.id
                      GROUP BY DATE(p.created_at), pr.id
                      ORDER BY sale_date DESC, sale_amount DESC
                      LIMIT 100";
            break;
            
        case 'inventory':
            // Inventory report
            $query = "SELECT 
                        p.name as product_name,
                        SUM(i.quantity) as total_quantity,
                        qt.unit_name,
                        AVG(i.unit_cost) as avg_cost,
                        MAX(i.last_restock_date) as last_restock
                      FROM inventory i
                      JOIN products p ON i.product_id = p.id
                      JOIN quantity_types qt ON i.quantity_type_id = qt.id
                      GROUP BY p.id, qt.id
                      ORDER BY total_quantity DESC";
            break;
            
        case 'users':
            // User activity report
            $query = "SELECT 
                        u.name as user_name,
                        r.name as role_name,
                        COUNT(DISTINCT p.id) as purchase_count,
                        SUM(p.total_price) as total_spent,
                        MAX(p.created_at) as last_purchase
                      FROM users u
                      LEFT JOIN purchases p ON u.id = p.buyer_id
                      JOIN roles r ON u.role_id = r.id
                      GROUP BY u.id
                      ORDER BY purchase_count DESC";
            break;
            
        case 'financial':
            // Financial report
            $query = "SELECT 
                        YEAR(p.created_at) as year,
                        MONTH(p.created_at) as month,
                        SUM(p.total_price) as revenue,
                        COUNT(*) as transaction_count,
                        SUM(p.quantity) as units_sold
                      FROM purchases p
                      GROUP BY YEAR(p.created_at), MONTH(p.created_at)
                      ORDER BY year DESC, month DESC";
            break;
            
        default:
            // Get all reports
            $query = "SELECT * FROM reports ORDER BY created_at DESC";
            break;
    }
    
    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }
    
    while ($row = $result->fetch_assoc()) {
        $reports[] = $row;
    }
    
    // Log report access
    logActivity(getUserId(), "Report Access", "Accessed {$reportType} report");
    
    echo json_encode([
        'status' => 200,
        'message' => 'Reports retrieved successfully',
        'report_type' => $reportType,
        'data' => $reports
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 500,
        'message' => 'Error retrieving reports: ' . $e->getMessage()
    ]);
}
