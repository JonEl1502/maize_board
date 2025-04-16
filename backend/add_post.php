<?php
include 'config.php';
header('Content-Type: application/json');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check required fields
if (!isset($_POST['seller_id'], $_POST['product_id'], $_POST['quantity'], $_POST['quantity_type_id'], $_POST['price_per_quantity'])) {
    echo json_encode(["status" => 400, "message" => "Missing required fields"]);
    exit();
}

// Calculate total price
$price = $_POST['quantity'] * $_POST['price_per_quantity'];

// Prepare SQL query with correct placeholders
$stmt = $conn->prepare("INSERT INTO product_listings (seller_id, product_id, quantity, quantity_type_id, price_per_quantity, price, status_id) VALUES (?, ?, ?, ?, ?, ?, 1)");

// Check if the query was prepared correctly
if (!$stmt) {
    echo json_encode(["status" => 500, "message" => "SQL Error: " . $conn->error]);
    exit();
}

// Bind parameters correctly - using 'i' for integers and 'd' for decimals
$stmt->bind_param("iiiddd", 
    $_POST['seller_id'], 
    $_POST['product_id'], 
    $_POST['quantity'], 
    $_POST['quantity_type_id'], 
    $_POST['price_per_quantity'],
    $price
);

// Execute query and return JSON response
if ($stmt->execute()) {
    echo json_encode(["status" => 200, "message" => "Listing added successfully!"]);
} else {
    echo json_encode(["status" => 500, "message" => "Database error: " . $stmt->error]);
}

exit();