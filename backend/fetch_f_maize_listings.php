<?php
include 'config.php';
header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get farmer ID from request
$farmerId = isset($_GET['farmer_id']) ? intval($_GET['farmer_id']) : 0;

if ($farmerId <= 0) {
    echo json_encode(["status" => 400, "message" => "Invalid farmer ID"]);
    exit();
}

// Prepare SQL query
$query = $conn->prepare("SELECT * FROM maize_listings m join quantity_units q on m.quantity_unit_id = q.id  WHERE farmer_id = ?");
if (!$query) {
    echo json_encode(["status" => 500, "message" => "SQL Prepare Error: " . $conn->error]);
    exit();
}

// Bind parameter and execute
$query->bind_param("i", $farmerId);
if (!$query->execute()) {
    echo json_encode(["status" => 500, "message" => "Query Execution Error: " . $query->error]);
    exit();
}

// Get results
$result = $query->get_result();
$maizeListings = [];

while ($row = $result->fetch_assoc()) {
    $maizeListings[] = $row;
}

// Return JSON response
echo json_encode(["status" => 200, "data" => $maizeListings]);
exit();
?>