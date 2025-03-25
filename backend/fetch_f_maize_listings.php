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
    // Query to fetch user's products
    $query = "SELECT pl.id, p.name AS product_name, pl.quantity, qt.unit_name, 
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