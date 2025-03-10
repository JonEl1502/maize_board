<?php
include 'config.php';

$status = isset($_GET['status']) ? $_GET['status'] : '';

$query = $conn->prepare("SELECT * FROM maize_listings WHERE status = ?");
$query->bind_param("s", $status);
$query->execute();
$result = $query->get_result();

$maizeListings = [];

while ($row = $result->fetch_assoc()) {
    $maizeListings[] = $row;
}

echo json_encode($maizeListings);
?>