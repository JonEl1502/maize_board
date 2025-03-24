<?php
include 'config.php';

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get seller_id and role_id from query parameters
$seller_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
$role_id = isset($_GET['role_id']) ? intval($_GET['role_id']) : null;

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

// Base query
$query = "SELECT pl.id, p.name AS product_name, pl.quantity, qt.unit_name, pl.price_per_quantity, pl.product_image_url,
                 u.id AS seller_id, u.name AS user_name, u.email AS user_email, u.phone AS user_phone, pl.status_id,
                 s.name AS status_name, pl.created_at, pl.updated_at, p.category_id, c.name AS category_name
          FROM product_listings pl
          JOIN products p ON pl.product_id = p.id
          JOIN categories c ON p.category_id = c.id
          JOIN quantity_types qt ON pl.quantity_type_id = qt.id
          JOIN statuses s ON pl.status_id = s.id
          JOIN users u ON pl.seller_id = u.id
          WHERE 1=1";

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