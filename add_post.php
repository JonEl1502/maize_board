<?php
include 'config.php';

$farmer_id = $_POST['farmer_id'];
$quantity = $_POST['quantity'];
$quality = $_POST['quality'];
$price_per_unit = $_POST['price_per_unit'];

$stmt = $conn->prepare("INSERT INTO maize_listings (farmer_id, quantity, quality, price_per_unit) VALUES (?, ?, ?, ?)");
$stmt->bind_param("iisd", $farmer_id, $quantity, $quality, $price_per_unit);
$stmt->execute();

header("Location: farmer-dashboard.php");
?>