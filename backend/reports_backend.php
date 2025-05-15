<?php
header('Content-Type: application/json');
include 'config.php';

// Get user ID and role ID from request
$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : null;
$role_id = isset($_GET['role_id']) ? $_GET['role_id'] : null;

if (!$user_id || !$role_id) {
    echo json_encode(['status' => 400, 'message' => 'Missing required parameters']);
    exit;
}

try {
    $response = [
        'status' => 200,
        'message' => 'Reports data fetched successfully',
        'summary' => [],
        'transaction_history' => [],
        'status_distribution' => [],
        'recent_transactions' => []
    ];

    // Base query for purchases
    $base_query = "FROM purchases p
                   LEFT JOIN product_listings pl ON p.listing_id = pl.id
                   LEFT JOIN products pr ON pl.product_id = pr.id
                   LEFT JOIN quantity_types qu ON pl.quantity_type_id = qu.id
                   LEFT JOIN statuses s ON pl.status_id = s.id
                   LEFT JOIN users u ON p.seller_id = u.id
                   LEFT JOIN users b ON p.buyer_id = b.id";

    // Role-specific conditions
    if ($role_id == 1) {
        $role_condition = "1=1"; // Admin can view all transactions
    } elseif ($role_id == 2) {
        $role_condition = "p.seller_id = $user_id"; // Seller - show sales
    } elseif ($role_id == 3) {
        $role_condition = "p.buyer_id = $user_id"; // Buyer - show purchases
    } else {
        echo json_encode(['status' => 403, 'message' => 'Unauthorized access']);
        exit;
    }

    // Get summary data
    $summary_query = "SELECT
                        COUNT(*) as total_transactions,
                        SUM(p.quantity * pl.price_per_quantity) as total_amount,
                        /* Calculate success rate based on completed transactions (status_id = 4) */
                        ROUND(COUNT(CASE WHEN s.id = 4 THEN 1 END) * 100.0 / COUNT(*), 1) as success_rate
                      $base_query
                      WHERE $role_condition";

    $summary_result = $conn->query($summary_query);
    if ($summary_row = $summary_result->fetch_assoc()) {
        $response['summary'] = [
            'total_transactions' => (int)$summary_row['total_transactions'],
            'total_amount' => (float)$summary_row['total_amount'],
            'success_rate' => (float)$summary_row['success_rate']
        ];
    }

    // Get transaction history (last 7 days)
    $history_query = "SELECT
                        DATE(p.created_at) as date,
                        SUM(p.quantity * pl.price_per_quantity) as amount
                      $base_query
                      WHERE $role_condition
                        AND p.created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                      GROUP BY DATE(p.created_at)
                      ORDER BY date";

    $history_result = $conn->query($history_query);
    while ($row = $history_result->fetch_assoc()) {
        $response['transaction_history'][] = [
            'date' => $row['date'],
            'amount' => (float)$row['amount']
        ];
    }

    // Get status distribution
    $status_query = "SELECT
                       s.name as status,
                       COUNT(*) as count
                     $base_query
                     WHERE $role_condition
                     GROUP BY s.name";

    $status_result = $conn->query($status_query);
    while ($row = $status_result->fetch_assoc()) {
        $response['status_distribution'][$row['status']] = (int)$row['count'];
    }

    // Get recent transactions
    $recent_query = "SELECT
                       p.created_at as date,
                       pr.name as product_name,
                       p.quantity,
                       qu.unit_name as unit_name,
                       (p.quantity * pl.price_per_quantity) as amount,
                       s.name as status
                     $base_query
                     WHERE $role_condition
                     ORDER BY p.created_at DESC
                     LIMIT 10";

    $recent_result = $conn->query($recent_query);
    while ($row = $recent_result->fetch_assoc()) {
        $response['recent_transactions'][] = [
            'date' => $row['date'],
            'product_name' => $row['product_name'],
            'quantity' => (float)$row['quantity'],
            'unit_name' => $row['unit_name'],
            'amount' => (float)$row['amount'],
            'status' => $row['status']
        ];
    }

    echo json_encode($response);

} catch (Exception $e) {
    echo json_encode([
        'status' => 500,
        'message' => 'An error occurred while fetching reports data: ' . $e->getMessage()
    ]);
}

$conn->close();