<?php
include 'config.php';
header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get user ID from query parameters
if (!isset($_GET['user_id'])) {
    echo json_encode([
        'status' => 400,
        'message' => 'User ID is required'
    ]);
    exit;
}

$user_id = intval($_GET['user_id']);

try {
    // Query to get all materials the user has purchased
    $query = "SELECT 
                p.product_id,
                pr.name AS product_name,
                pr.description AS product_description,
                SUM(p.quantity) AS total_quantity,
                qt.id AS quantity_type_id,
                qt.unit_name,
                (
                    SELECT COALESCE(SUM(pm.quantity_used), 0)
                    FROM product_materials pm
                    JOIN derived_products dp ON pm.derived_product_id = dp.id
                    WHERE pm.source_product_id = p.product_id
                    AND dp.wholesaler_id = ?
                    AND pm.quantity_type_id = pl.quantity_type_id
                ) AS used_quantity
            FROM purchases p
            JOIN product_listings pl ON p.listing_id = pl.id
            JOIN products pr ON pl.product_id = pr.id
            JOIN quantity_types qt ON pl.quantity_type_id = qt.id
            WHERE p.buyer_id = ?
            GROUP BY p.product_id, pl.quantity_type_id";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $user_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $materials = [];
    while ($row = $result->fetch_assoc()) {
        // Calculate available quantity
        $row['available_quantity'] = $row['total_quantity'] - $row['used_quantity'];
        $materials[] = $row;
    }
    
    echo json_encode([
        'status' => 200,
        'message' => 'Materials retrieved successfully',
        'materials' => $materials
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 500,
        'message' => 'An error occurred: ' . $e->getMessage()
    ]);
}

$conn->close();
?>
