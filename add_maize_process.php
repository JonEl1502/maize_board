<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && $_SESSION['role'] == 'farmer') {
    $quantity = $_POST['quantity'];
    $quality = $_POST['quality'];
    $price = $_POST['price'];
    
    $stmt = $conn->prepare("INSERT INTO maize_listings 
        (farmer_id, quantity, quality, price_per_unit) 
        VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iisd", $_SESSION['user_id'], $quantity, $quality, $price);
    
    if ($stmt->execute()) {
        header("Location: dashboard.php");
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
