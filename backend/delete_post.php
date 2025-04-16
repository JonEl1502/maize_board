<?php
include 'config.php';
header('Content-Type: application/json');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get the POST data
$data = json_decode(file_get_contents('php://input'), true);

// Check if ID is provided
if (!isset($data['id'])) {
    echo json_encode(["status" => 400, "message" => "Missing listing ID"]);
    exit();
}

// Prepare delete query
$stmt = $conn->prepare("DELETE FROM product_listings WHERE id = ?");

if (!$stmt) {
    echo json_encode(["status" => 500, "message" => "SQL Error: " . $conn->error]);
    exit();
}

// Bind parameter and execute
$stmt->bind_param("i", $data['id']);

if ($stmt->execute()) {
    echo json_encode(["status" => 200, "message" => "Listing deleted successfully"]);
} else {
    echo json_encode(["status" => 500, "message" => "Failed to delete listing: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>