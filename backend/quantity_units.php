<?php
include 'config.php';
header('Content-Type: application/json');

$sql = "SELECT id, unit_name FROM quantity_units";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $farmerTypes = [];
    while ($row = $result->fetch_assoc()) {
        $farmerTypes[] = $row;
    }
    echo json_encode(["status" => 200, "quantity_units" => $farmerTypes]);
} else {
    echo json_encode(["status" => 400, "message" => "No farmer types found"]);
}
?>