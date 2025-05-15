<?php
include 'config.php';
header('Content-Type: application/json');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get user_id from query parameters
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;

if (!$user_id) {
    echo json_encode(["status" => 400, "message" => "User ID is required", "data" => []]);
    exit();
}

try {
    // Query to fetch user's products without the JSON_ARRAYAGG function
    $query = "SELECT pl.id, p.id AS product_id, p.name AS product_name, p.description AS product_description,
                     p.is_derived, pl.quantity, qt.id AS quantity_id, qt.unit_name,
                     pl.price_per_quantity, pl.product_image_url, pl.status_id,
                     s.name AS status_name, pl.created_at,
                     u.id AS user_id, u.name AS user_name, u.email AS user_email,
                     u.phone AS user_phone
              FROM product_listings pl
              JOIN products p ON pl.product_id = p.id
              JOIN quantity_types qt ON pl.quantity_type_id = qt.id
              JOIN users u ON pl.seller_id = u.id
              LEFT JOIN statuses s ON pl.status_id = s.id
              WHERE pl.seller_id = ?";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("i", $user_id);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $result = $stmt->get_result();
    $product_listings = [];

    while ($row = $result->fetch_assoc()) {
        // Initialize source_materials as an empty array
        $row['source_materials'] = null;

        // If the product is derived, fetch its source materials separately
        if ($row['is_derived']) {
            $sourceMaterialsQuery = "SELECT
                pm.source_product_id,
                sp.name AS source_product_name,
                pm.quantity_used,
                qt2.unit_name
            FROM derived_products dp
            LEFT JOIN product_materials pm ON dp.id = pm.derived_product_id
            LEFT JOIN products sp ON pm.source_product_id = sp.id
            LEFT JOIN quantity_types qt2 ON pm.quantity_type_id = qt2.id
            WHERE dp.product_id = ?";

            $sourceMaterialsStmt = $conn->prepare($sourceMaterialsQuery);
            if ($sourceMaterialsStmt) {
                $sourceMaterialsStmt->bind_param("i", $row['product_id']);
                $sourceMaterialsStmt->execute();
                $sourceMaterialsResult = $sourceMaterialsStmt->get_result();

                $sourceMaterials = [];
                while ($materialRow = $sourceMaterialsResult->fetch_assoc()) {
                    $sourceMaterials[] = [
                        'source_product_id' => $materialRow['source_product_id'],
                        'source_product_name' => $materialRow['source_product_name'],
                        'quantity_used' => $materialRow['quantity_used'],
                        'unit_name' => $materialRow['unit_name']
                    ];
                }

                // Convert the array to a JSON string
                $row['source_materials'] = !empty($sourceMaterials) ? json_encode($sourceMaterials) : null;
                $sourceMaterialsStmt->close();
            }
        }

        $product_listings[] = $row;
    }

    echo json_encode([
        "status" => 200,
        "message" => !empty($product_listings) ? "Product listings retrieved successfully." : "No product listings found.",
        "data" => $product_listings
    ]);

} catch (Exception $e) {
    error_log("Error in fetch_f_maize_listings.php: " . $e->getMessage());
    echo json_encode([
        "status" => 500,
        "message" => "Database error: " . $e->getMessage(),
        "data" => []
    ]);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    $conn->close();
}
?>