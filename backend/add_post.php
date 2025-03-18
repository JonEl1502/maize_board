<?php
include 'config.php';
header('Content-Type: application/json');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check required fields
if (!isset($_POST['farmer_id'], $_POST['quantity'], $_POST['quantity_unit_id'], $_POST['price_per_unit'], $_POST['location'], $_POST['need_transport'])) {
    echo json_encode(["status" => 400, "message" => "Missing required fields"]);
    exit();
}

// Prepare SQL query with correct placeholders
$stmt = $conn->prepare("INSERT INTO maize_listings (farmer_id, quantity, quantity_unit_id, price_per_unit, location, need_transport) VALUES (?, ?, ?, ?, ?, ?)");

// Check if the query was prepared correctly
if (!$stmt) {
    echo json_encode(["status" => 500, "message" => "SQL Error: " . $conn->error]);
    exit();
}

// Bind parameters correctly
$stmt->bind_param("iiidss", $_POST['farmer_id'], $_POST['quantity'], $_POST['quantity_unit_id'], $_POST['price_per_unit'], $_POST['location'], $_POST['need_transport']);

// Execute query and return JSON response
if ($stmt->execute()) {
    echo json_encode(["status" => 200, "message" => "Listing added successfully!"]);
} else {
    echo json_encode(["status" => 500, "message" => "Database error: " . $stmt->error]);
}

exit();