<?php
include 'config.php';
header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get seller_id or buyer_id
$seller_id = isset($_GET['seller_id']) ? intval($_GET['seller_id']) : 0;
$buyer_id = isset($_GET['buyer_id']) ? intval($_GET['buyer_id']) : 0;

// Build query based on provided ID
if ($seller_id > 0) {
    $query = "SELECT 
        p.*, 
        pl.product_id,
        pl.quantity_type_id,
        pl.price_per_quantity,
        pr.name as product_name,
        qu.unit_name,
        s.name as status_name,
        b.name as buyer_name,
        p.created_at
    FROM purchases p
    LEFT JOIN product_listings pl ON p.listing_id = pl.id
    LEFT JOIN products pr ON pl.product_id = pr.id
    LEFT JOIN quantity_types qu ON pl.quantity_type_id = qu.id
    LEFT JOIN statuses s ON pl.status_id = s.id
    LEFT JOIN users b ON p.buyer_id = b.id
    WHERE p.seller_id = ?
    ORDER BY p.created_at DESC";
    $param_id = $seller_id;
} elseif ($buyer_id > 0) {
    $query = "SELECT 
        p.*, 
        pl.product_id,
        pl.quantity_type_id,
        pl.price_per_quantity,
        pr.name as product_name,
        qu.unit_name,
        s.name as status_name,
        u.name as seller_name,
        p.created_at
    FROM purchases p
    LEFT JOIN product_listings pl ON p.listing_id = pl.id
    LEFT JOIN products pr ON pl.product_id = pr.id
    LEFT JOIN quantity_types qu ON pl.quantity_type_id = qu.id
    LEFT JOIN statuses s ON pl.status_id = s.id
    LEFT JOIN users u ON p.seller_id = u.id
    WHERE p.buyer_id = ?
    ORDER BY p.created_at DESC";
    $param_id = $buyer_id;
} else {
    echo json_encode(['status' => 400, 'message' => 'Either seller_id or buyer_id is required']);
    exit();
}

try {
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $param_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $row['created_at'] = date('Y-m-d H:i:s', strtotime($row['created_at']));
        $row['total_price'] = $row['quantity'] * $row['price_per_quantity'];
        $data[] = $row;
    }
    
    echo json_encode([
        'status' => 200,
        'message' => 'Data retrieved successfully',
        'data' => $data
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 500,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}

$stmt->close();
$conn->close();
?>