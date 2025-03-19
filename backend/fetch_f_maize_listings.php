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
$query = $conn->prepare("SELECT m.id, m.quantity, m.price_per_unit, m.moisture_percentage, m.aflatoxin_level, m.location, 
                                c.name as county, m.need_transport, m.quantity_unit_id, q.unit_name, m.status, m.listing_date 
                                FROM maize_listings m 
                                JOIN quantity_units q ON m.quantity_unit_id = q.id 
                                JOIN counties c ON m.county_id = c.id 
                                WHERE m.farmer_id = ?");
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