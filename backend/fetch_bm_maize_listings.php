<?php
include 'config.php';
header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get status from request
$status = isset($_GET['status']) ? $_GET['status'] : '';

if (empty($status)) {
    echo json_encode(["status" => 400, "message" => "Invalid status"]);
    exit();
}

// Prepare SQL query
$query = $conn->prepare("SELECT m.id, m.quantity, m.price_per_unit, ifnull(m.moisture_percentage, 'N/A') as moisture_percentage,
                                IFNULL(m.aflatoxin_level, 'N/A') AS aflatoxin_level, c.name as county, m.location, m.need_transport, 
                                m.quantity_unit_id, q.unit_name, m.status, m.listing_date, u.id as farmer_id, 
                                u.name as farmer_name, u.email as farmer_email, u.phone as farmer_phone 
                                FROM maize_listings m 
                                JOIN quantity_units q ON m.quantity_unit_id = q.id 
                                JOIN users u ON m.farmer_id = u.id 
                                JOIN counties c ON m.county_id = c.id 
                                WHERE m.status = ?");
if (!$query) {
    echo json_encode(["status" => 500, "message" => "SQL Prepare Error: " . $conn->error]);
    exit();
}

// Bind parameter and execute
$query->bind_param("s", $status);
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