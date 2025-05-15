<?php
include 'config.php';
header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Query to get all quantity types
    $query = "SELECT id, unit_name FROM quantity_types ORDER BY unit_name";
    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception("Database query failed: " . $conn->error);
    }
    
    $quantity_types = [];
    while ($row = $result->fetch_assoc()) {
        $quantity_types[] = $row;
    }
    
    echo json_encode([
        'status' => 200,
        'message' => 'Quantity types retrieved successfully',
        'quantity_types' => $quantity_types
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 500,
        'message' => 'An error occurred: ' . $e->getMessage()
    ]);
}

$conn->close();
?>
