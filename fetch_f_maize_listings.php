<?php
include 'config.php';

// 🔹 Get the farmer ID from the request
$farmerId = isset($_GET['farmer_id']) ? intval($_GET['farmer_id']) : 0;

$query = $conn->prepare("SELECT * FROM maize_listings WHERE farmer_id = ?");
$query->bind_param("i", $farmerId);
$query->execute();
$result = $query->get_result();

$maizeListings = [];

while ($row = $result->fetch_assoc()) {
    $maizeListings[] = $row;
}

echo json_encode($maizeListings);
?>