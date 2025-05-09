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
    // Get total users count
    $userQuery = "SELECT COUNT(*) as total_users FROM users";
    $userResult = $conn->query($userQuery);
    $totalUsers = $userResult->fetch_assoc()['total_users'];

    // Get total products count
    $productQuery = "SELECT COUNT(*) as total_products FROM products";
    $productResult = $conn->query($productQuery);
    $totalProducts = $productResult->fetch_assoc()['total_products'];

    // Get total sales count
    $salesQuery = "SELECT COUNT(*) as total_sales FROM purchases";
    $salesResult = $conn->query($salesQuery);
    $totalSales = $salesResult->fetch_assoc()['total_sales'];

    // Get total revenue
    $revenueQuery = "SELECT SUM(total_price) as total_revenue FROM purchases";
    $revenueResult = $conn->query($revenueQuery);
    $totalRevenue = $revenueResult->fetch_assoc()['total_revenue'] ?? 0;

    // Return the stats
    echo json_encode([
        'status' => 200,
        'message' => 'Dashboard statistics retrieved successfully',
        'stats' => [
            'total_users' => $totalUsers,
            'total_products' => $totalProducts,
            'total_sales' => $totalSales,
            'total_revenue' => formatCurrency($totalRevenue)
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 500,
        'message' => 'Error retrieving dashboard statistics: ' . $e->getMessage()
    ]);
}
