<?php
include 'config.php';

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get seller_id and role_id from query parameters
$seller_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
$role_id = isset($_GET['role_id']) ? intval($_GET['role_id']) : null;
$buyer_id = isset($_GET['buyer_id']) ? intval($_GET['buyer_id']) : null;

// Get additional filters from query parameters
$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : null;
$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : null;
$min_price = isset($_GET['min_price']) ? floatval($_GET['min_price']) : null;
$max_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : null;
$price_per_quantity = isset($_GET['price_per_quantity']) ? floatval($_GET['price_per_quantity']) : null;
$quantity = isset($_GET['quantity']) ? intval($_GET['quantity']) : null;
$quantity_type_id = isset($_GET['quantity_type_id']) ? intval($_GET['quantity_type_id']) : null;

$params = [];
$types = "";

// Base query - removed JSON_ARRAYAGG function which is not available in MariaDB 10.4
$query = "SELECT pl.id, p.id AS product_id, p.name AS product_name, p.description AS product_description, pl.quantity, qt.unit_name,
                 pl.price_per_quantity, pl.product_image_url, p.is_derived,
                 u.id AS seller_id, u.name AS user_name, u.email AS user_email, u.phone AS user_phone, pl.status_id,
                 s.name AS status_name, pl.created_at, pl.updated_at, p.category_id, c.name AS category_name
          FROM product_listings pl
          JOIN products p ON pl.product_id = p.id
          JOIN categories c ON p.category_id = c.id
          JOIN quantity_types qt ON pl.quantity_type_id = qt.id
          JOIN statuses s ON pl.status_id = s.id
          JOIN users u ON pl.seller_id = u.id
          WHERE pl.quantity > 0";  // Only fetch listings with quantity > 0

// Apply filters based on role_id and seller_id
if ($role_id === 2 && $seller_id) {
    $query .= " AND pl.seller_id = ?";
    $params[] = $seller_id;
    $types .= "i";
} elseif ($role_id !== null) {
    $query .= " AND u.role_id = ?";
    $params[] = $role_id;
    $types .= "i";
}

// Apply additional filters
if ($product_id !== null) {
    $query .= " AND pl.product_id = ?";
    $params[] = $product_id;
    $types .= "i";
}

if ($category_id !== null) {
    $query .= " AND p.category_id = ?";
    $params[] = $category_id;
    $types .= "i";
}

if ($min_price !== null) {
    $query .= " AND pl.price_per_quantity >= ?";
    $params[] = $min_price;
    $types .= "d";
}

if ($max_price !== null) {
    $query .= " AND pl.price_per_quantity <= ?";
    $params[] = $max_price;
    $types .= "d";
}

if ($price_per_quantity !== null) {
    $query .= " AND pl.price_per_quantity = ?";
    $params[] = $price_per_quantity;
    $types .= "d";
}

if ($quantity !== null) {
    $query .= " AND pl.quantity = ?";
    $params[] = $quantity;
    $types .= "i";
}

if ($quantity_type_id !== null) {
    $query .= " AND pl.quantity_type_id = ?";
    $params[] = $quantity_type_id;
    $types .= "i";
}

// Prevent users from seeing their own products when they are buying
if ($buyer_id !== null) {
    $query .= " AND pl.seller_id != ?";
    $params[] = $buyer_id;
    $types .= "i";
}

$product_name = isset($_GET['filterName']) ? trim($_GET['filterName']) : null;

if ($product_name !== null) {
    $query .= " AND p.name LIKE ?";
    $params[] = "%" . $product_name . "%";
    $types .= "s";
}

// Prepare statement
$stmt = $conn->prepare($query);
if ($types) {
    $stmt->bind_param($types, ...$params);
}

// Validate query execution
if (!$stmt) {
    echo json_encode(["status" => 500, "message" => "Database query error: " . $conn->error, "data" => []]);
    exit();
}

$stmt->execute();
$result = $stmt->get_result();

$product_listings = [];
while ($row = $result->fetch_assoc()) {
    // Initialize source_materials as null
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

// Ensure valid JSON response
echo json_encode([
    "status" => !empty($product_listings) ? 200 : 404,
    "message" => !empty($product_listings) ? "Product listings retrieved successfully." : "No product listings found.",
    "data" => $product_listings
]);

$stmt->close();
$conn->close();
?>