<?php
include 'config.php';
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $farmer_id = $_POST['farmer_id'];
    $quantity = $_POST['quantity'];
    $quality = $_POST['quality'];
    $price_per_unit = $_POST['price_per_unit'];

    $stmt = $conn->prepare("INSERT INTO maize_listings (farmer_id, quantity, quality, price_per_unit) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iisd", $farmer_id, $quantity, $quality, $price_per_unit);

    if ($stmt->execute()) {
        echo json_encode(["status" => 200, "message" => "Listing added successfully!"]);
    } else {
        echo json_encode(["status" => 500, "message" => "Error adding listing."]);
    }
}
?>