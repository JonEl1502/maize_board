<?php
include 'config.php';
header('Content-Type: application/json');

$sql = "SELECT id, name FROM categories ";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $farmerTypes = [];
    while ($row = $result->fetch_assoc()) {
        $farmerTypes[] = $row;
    }
    echo json_encode(["status" => 200, "categories" => $farmerTypes]);
} else {
    echo json_encode(["status" => 400, "message" => "No categories found"]);
}
?>