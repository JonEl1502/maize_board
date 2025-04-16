<?php
include 'config.php';
header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if required fields are set
if (!isset($_POST['id'], $_POST['quantity'], $_POST['quantity_unit_id'], $_POST['price_per_unit'], $_POST['location'], $_POST['need_transport'])) {
    echo json_encode(["status" => 400, "message" => "Missing required fields"]);
    exit();
}

// Prepare the SQL query for updating a listing
$stmt = $conn->prepare("UPDATE maize_listings SET quantity = ?, quantity_unit_id = ?, price_per_unit = ?, location = ?, need_transport = ? WHERE id = ?");

// Check if the statement was prepared successfully
if (!$stmt) {
    echo json_encode(["status" => 500, "message" => "SQL Error: " . $conn->error]);
    exit();
}

// Bind parameters
$stmt->bind_param("iidssi", $_POST['quantity'], $_POST['quantity_unit_id'], $_POST['price_per_unit'], $_POST['location'], $_POST['need_transport'], $_POST['id']);

// Execute query and return JSON response
if ($stmt->execute()) {
    echo json_encode(["status" => 200, "message" => "Listing updated successfully!"]);
} else {
    echo json_encode(["status" => 500, "message" => "Database error: " . $stmt->error]);
}

exit();
?>
