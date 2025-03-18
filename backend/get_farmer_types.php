<?php
include 'config.php';
header('Content-Type: application/json');

$sql = "SELECT id, type_name FROM farmer_types";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $farmerTypes = [];
    while ($row = $result->fetch_assoc()) {
        $farmerTypes[] = $row;
    }
    echo json_encode(["status" => 200, "farmer_types" => $farmerTypes]);
} else {
    echo json_encode(["status" => 400, "message" => "No farmer types found"]);
}
?>