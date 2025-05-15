<?php
header('Content-Type: application/json');
include 'config.php';

// Get user ID, role ID and timeframe from request
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
$role_id = isset($_GET['role_id']) ? intval($_GET['role_id']) : null;
$timeframe = isset($_GET['timeframe']) ? $_GET['timeframe'] : 'weekly';

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
        'top_products' => [],
        'transactions' => [],
        'statistics' => []
    ];

    // Base query for purchases
    $base_query = "FROM purchases p
                   LEFT JOIN product_listings pl ON p.listing_id = pl.id
                   LEFT JOIN products pr ON pl.product_id = pr.id
                   LEFT JOIN quantity_types qu ON pl.quantity_type_id = qu.id
                   LEFT JOIN statuses s ON pl.status_id = s.id
                   LEFT JOIN users seller ON p.seller_id = seller.id
                   LEFT JOIN users buyer ON p.buyer_id = buyer.id";

    // Role-specific conditions
    switch ($role_id) {
        case 1:
            $role_condition = "1=1"; // Admin can view all transactions
            break;
        case 2:
            $role_condition = "p.seller_id = $user_id"; // Farmer - show sales
            break;
        case 3:
            $role_condition = "p.buyer_id = $user_id OR p.seller_id = $user_id"; // Wholesaler - show both sales and purchases
            break;
        case 4:
            $role_condition = "p.buyer_id = $user_id"; // Customer - show purchases
            break;
        case 5:
            $role_condition = "p.buyer_id = $user_id OR p.seller_id = $user_id"; // Custom role - show both
            break;
        default:
            echo json_encode(['status' => 403, 'message' => 'Unauthorized access']);
            exit;
    }

    // Get summary data
    $summary_query = "SELECT
                        COUNT(*) as total_transactions,
                        COUNT(CASE WHEN p.seller_id = $user_id THEN 1 END) as sales_count,
                        COUNT(CASE WHEN p.buyer_id = $user_id THEN 1 END) as purchases_count,
                        SUM(CASE WHEN p.seller_id = $user_id THEN p.quantity * pl.price_per_quantity ELSE 0 END) as total_sales,
                        SUM(CASE WHEN p.buyer_id = $user_id THEN p.quantity * pl.price_per_quantity ELSE 0 END) as total_purchases,
                        AVG(p.quantity * pl.price_per_quantity) as average_transaction,
                        /* Calculate success rate based on completed transactions (status_id = 4) */
                        ROUND(COUNT(CASE WHEN s.id = 4 THEN 1 END) * 100.0 / NULLIF(COUNT(*), 0), 1) as success_rate
                      $base_query
                      WHERE $role_condition";

    $summary_result = $conn->query($summary_query);
    if ($summary_row = $summary_result->fetch_assoc()) {
        // Calculate transaction trend (% change from last month)
        $trend_query = "SELECT
                          SUM(CASE WHEN p.created_at >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH) THEN p.quantity * pl.price_per_quantity ELSE 0 END) as current_month,
                          SUM(CASE WHEN p.created_at >= DATE_SUB(CURDATE(), INTERVAL 2 MONTH) AND p.created_at < DATE_SUB(CURDATE(), INTERVAL 1 MONTH) THEN p.quantity * pl.price_per_quantity ELSE 0 END) as previous_month
                        $base_query
                        WHERE $role_condition";

        $trend_result = $conn->query($trend_query);
        $trend_row = $trend_result->fetch_assoc();

        $current_month = (float)$trend_row['current_month'];
        $previous_month = (float)$trend_row['previous_month'];

        $transaction_trend = 0;
        if ($previous_month > 0) {
            $transaction_trend = round((($current_month - $previous_month) / $previous_month) * 100, 1);
        }

        $response['summary'] = [
            'total_transactions' => (int)$summary_row['total_transactions'],
            'sales_count' => (int)$summary_row['sales_count'],
            'purchases_count' => (int)$summary_row['purchases_count'],
            'total_sales' => (float)$summary_row['total_sales'] ?: 0,
            'total_purchases' => (float)$summary_row['total_purchases'] ?: 0,
            'average_transaction' => (float)$summary_row['average_transaction'] ?: 0,
            'success_rate' => (float)$summary_row['success_rate'] ?: 0,
            'transaction_trend' => $transaction_trend
        ];
    }

    // Get transaction history based on timeframe
    $date_format = '';
    $interval = '';
    $group_by = '';
    $label_format = '';

    switch ($timeframe) {
        case 'weekly':
            $date_format = '%Y-%m-%d';
            $interval = '7 DAY';
            $group_by = 'DATE(p.created_at)';
            $label_format = '%b %d';
            break;
        case 'monthly':
            $date_format = '%Y-%U';
            $interval = '4 WEEK';
            $group_by = 'YEARWEEK(p.created_at)';
            $label_format = 'Week %U';
            break;
        case 'yearly':
            $date_format = '%Y-%m';
            $interval = '12 MONTH';
            $group_by = 'DATE_FORMAT(p.created_at, "%Y-%m")';
            $label_format = '%b %Y';
            break;
        default:
            $date_format = '%Y-%m-%d';
            $interval = '7 DAY';
            $group_by = 'DATE(p.created_at)';
            $label_format = '%b %d';
    }

    $response['transaction_history'] = [
        'sales' => [],
        'purchases' => []
    ];

    // Sales history
    $sales_history_query = "SELECT
                        $group_by as date_group,
                        DATE_FORMAT(p.created_at, '$label_format') as label,
                        SUM(p.quantity * pl.price_per_quantity) as amount
                      $base_query
                      WHERE p.seller_id = $user_id
                        AND p.created_at >= DATE_SUB(CURDATE(), INTERVAL $interval)
                      GROUP BY date_group
                      ORDER BY date_group";

    $sales_history_result = $conn->query($sales_history_query);
    while ($row = $sales_history_result->fetch_assoc()) {
        $response['transaction_history']['sales'][] = [
            'date_group' => $row['date_group'],
            'label' => $row['label'],
            'amount' => (float)$row['amount']
        ];
    }

    // Purchases history
    $purchases_history_query = "SELECT
                        $group_by as date_group,
                        DATE_FORMAT(p.created_at, '$label_format') as label,
                        SUM(p.quantity * pl.price_per_quantity) as amount
                      $base_query
                      WHERE p.buyer_id = $user_id
                        AND p.created_at >= DATE_SUB(CURDATE(), INTERVAL $interval)
                      GROUP BY date_group
                      ORDER BY date_group";

    $purchases_history_result = $conn->query($purchases_history_query);
    while ($row = $purchases_history_result->fetch_assoc()) {
        $response['transaction_history']['purchases'][] = [
            'date_group' => $row['date_group'],
            'label' => $row['label'],
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

    // Get top products sold
    $response['top_products'] = [
        'sold' => [],
        'purchased' => []
    ];

    $top_sold_query = "SELECT
                       pr.name as product_name,
                       SUM(p.quantity) as quantity,
                       qu.unit_name as unit_name
                     $base_query
                     WHERE p.seller_id = $user_id
                     GROUP BY pr.name, qu.unit_name
                     ORDER BY quantity DESC
                     LIMIT 5";

    $top_sold_result = $conn->query($top_sold_query);
    while ($row = $top_sold_result->fetch_assoc()) {
        $response['top_products']['sold'][] = [
            'product_name' => $row['product_name'],
            'quantity' => (float)$row['quantity'],
            'unit_name' => $row['unit_name']
        ];
    }

    // Get top products purchased
    $top_purchased_query = "SELECT
                       pr.name as product_name,
                       SUM(p.quantity) as quantity,
                       qu.unit_name as unit_name
                     $base_query
                     WHERE p.buyer_id = $user_id
                     GROUP BY pr.name, qu.unit_name
                     ORDER BY quantity DESC
                     LIMIT 5";

    $top_purchased_result = $conn->query($top_purchased_query);
    while ($row = $top_purchased_result->fetch_assoc()) {
        $response['top_products']['purchased'][] = [
            'product_name' => $row['product_name'],
            'quantity' => (float)$row['quantity'],
            'unit_name' => $row['unit_name']
        ];
    }

    // Get transactions for tables
    $response['transactions'] = [
        'all' => [],
        'sales' => [],
        'purchases' => []
    ];

    // All transactions
    $all_transactions_query = "SELECT
                       p.id,
                       p.created_at as date,
                       CASE WHEN p.seller_id = $user_id THEN 'sale' ELSE 'purchase' END as type,
                       pr.name as product_name,
                       p.quantity,
                       qu.unit_name as unit_name,
                       (p.quantity * pl.price_per_quantity) as amount,
                       s.name as status,
                       s.id as status_id,
                       buyer.name as buyer_name,
                       seller.name as seller_name
                     $base_query
                     WHERE $role_condition
                     ORDER BY p.created_at DESC
                     LIMIT 20";

    $all_transactions_result = $conn->query($all_transactions_query);
    while ($row = $all_transactions_result->fetch_assoc()) {
        $response['transactions']['all'][] = [
            'id' => $row['id'],
            'date' => $row['date'],
            'type' => $row['type'],
            'product_name' => $row['product_name'],
            'quantity' => (float)$row['quantity'],
            'unit_name' => $row['unit_name'],
            'amount' => (float)$row['amount'],
            'status' => $row['status'],
            'status_id' => (int)$row['status_id'],
            'buyer_name' => $row['buyer_name'],
            'seller_name' => $row['seller_name']
        ];
    }

    // Sales transactions
    $sales_transactions_query = "SELECT
                       p.id,
                       p.created_at as date,
                       pr.name as product_name,
                       p.quantity,
                       qu.unit_name as unit_name,
                       (p.quantity * pl.price_per_quantity) as amount,
                       s.name as status,
                       s.id as status_id,
                       buyer.name as buyer_name
                     $base_query
                     WHERE p.seller_id = $user_id
                     ORDER BY p.created_at DESC
                     LIMIT 10";

    $sales_transactions_result = $conn->query($sales_transactions_query);
    while ($row = $sales_transactions_result->fetch_assoc()) {
        $response['transactions']['sales'][] = [
            'id' => $row['id'],
            'date' => $row['date'],
            'product_name' => $row['product_name'],
            'quantity' => (float)$row['quantity'],
            'unit_name' => $row['unit_name'],
            'amount' => (float)$row['amount'],
            'status' => $row['status'],
            'status_id' => (int)$row['status_id'],
            'buyer_name' => $row['buyer_name']
        ];
    }

    // Purchases transactions
    $purchases_transactions_query = "SELECT
                       p.id,
                       p.created_at as date,
                       pr.name as product_name,
                       p.quantity,
                       qu.unit_name as unit_name,
                       (p.quantity * pl.price_per_quantity) as amount,
                       s.name as status,
                       s.id as status_id,
                       seller.name as seller_name
                     $base_query
                     WHERE p.buyer_id = $user_id
                     ORDER BY p.created_at DESC
                     LIMIT 10";

    $purchases_transactions_result = $conn->query($purchases_transactions_query);
    while ($row = $purchases_transactions_result->fetch_assoc()) {
        $response['transactions']['purchases'][] = [
            'id' => $row['id'],
            'date' => $row['date'],
            'product_name' => $row['product_name'],
            'quantity' => (float)$row['quantity'],
            'unit_name' => $row['unit_name'],
            'amount' => (float)$row['amount'],
            'status' => $row['status'],
            'status_id' => (int)$row['status_id'],
            'seller_name' => $row['seller_name']
        ];
    }

    // Get statistics
    $response['statistics'] = [
        'sales' => [],
        'purchases' => []
    ];

    // Sales statistics
    $sales_stats_query = "SELECT
                       SUM(p.quantity * pl.price_per_quantity) as total,
                       AVG(p.quantity * pl.price_per_quantity) as average,
                       MAX(p.quantity * pl.price_per_quantity) as highest
                     $base_query
                     WHERE p.seller_id = $user_id";

    $sales_stats_result = $conn->query($sales_stats_query);
    if ($sales_stats_row = $sales_stats_result->fetch_assoc()) {
        // Get top sold product
        $top_sold_product_query = "SELECT
                                  pr.name as product_name,
                                  SUM(p.quantity) as total_quantity
                                $base_query
                                WHERE p.seller_id = $user_id
                                GROUP BY pr.name
                                ORDER BY total_quantity DESC
                                LIMIT 1";

        $top_sold_product_result = $conn->query($top_sold_product_query);
        $top_sold_product = $top_sold_product_result->fetch_assoc();

        $response['statistics']['sales'] = [
            'total' => (float)$sales_stats_row['total'] ?: 0,
            'average' => (float)$sales_stats_row['average'] ?: 0,
            'highest' => (float)$sales_stats_row['highest'] ?: 0,
            'top_product' => $top_sold_product ? $top_sold_product['product_name'] : null
        ];
    }

    // Purchases statistics
    $purchases_stats_query = "SELECT
                       SUM(p.quantity * pl.price_per_quantity) as total,
                       AVG(p.quantity * pl.price_per_quantity) as average,
                       MAX(p.quantity * pl.price_per_quantity) as highest
                     $base_query
                     WHERE p.buyer_id = $user_id";

    $purchases_stats_result = $conn->query($purchases_stats_query);
    if ($purchases_stats_row = $purchases_stats_result->fetch_assoc()) {
        // Get top purchased product
        $top_purchased_product_query = "SELECT
                                      pr.name as product_name,
                                      SUM(p.quantity) as total_quantity
                                    $base_query
                                    WHERE p.buyer_id = $user_id
                                    GROUP BY pr.name
                                    ORDER BY total_quantity DESC
                                    LIMIT 1";

        $top_purchased_product_result = $conn->query($top_purchased_product_query);
        $top_purchased_product = $top_purchased_product_result->fetch_assoc();

        $response['statistics']['purchases'] = [
            'total' => (float)$purchases_stats_row['total'] ?: 0,
            'average' => (float)$purchases_stats_row['average'] ?: 0,
            'highest' => (float)$purchases_stats_row['highest'] ?: 0,
            'top_product' => $top_purchased_product ? $top_purchased_product['product_name'] : null
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
