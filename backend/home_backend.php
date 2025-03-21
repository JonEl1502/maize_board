<?php
include 'config.php';

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get user_id and role_id from query parameters
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
$role_id = isset($_GET['role_id']) ? intval($_GET['role_id']) : null;

if ($role_id === 2 && $user_id) {
    // If the user is a farmer, show only their product listings
    $query = "SELECT pl.id, p.name AS product_name, pl.quantity, qt.unit_name, pl.price_per_quantity, pl.product_image_url 
              FROM product_listings pl
              JOIN products p ON pl.product_id = p.id
              JOIN quantity_types qt ON pl.quantity_type_id = qt.id
              WHERE pl.user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
} else {
    // Show all product listings for other roles
    $query = "SELECT pl.id, p.name AS product_name, pl.quantity, qt.unit_name, pl.price_per_quantity, pl.product_image_url 
              FROM product_listings pl
              JOIN products p ON pl.product_id = p.id
              JOIN quantity_types qt ON pl.quantity_type_id = qt.id";
    $stmt = $conn->prepare($query);
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