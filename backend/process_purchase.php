<?php
include 'config.php';
header('Content-Type: application/json');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['listing_id']) || !isset($data['mpesa_code']) || !isset($data['buyer_id']) || empty($data['mpesa_code'])) {
    echo json_encode(["status" => 400, "message" => "Invalid request. Listing ID, Buyer ID, and Mpesa code are required."]);
    exit();
}

$listing_id = intval($data['listing_id']);
$buyer_id = intval($data['buyer_id']);
$mpesa_code = trim($data['mpesa_code']);

$conn->begin_transaction();

try {
    // Check if the product exists and is available for purchase
    $query = "SELECT id, status_id FROM product_listings WHERE id = ? AND status_id = 1";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Database error: " . $conn->error);
    }
    $stmt->bind_param("i", $listing_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception("Product is no longer available for purchase.");
    }

    // Fetch seller_id from product_listings
    $sellerQuery = "SELECT seller_id FROM product_listings WHERE id = ?";
    $sellerStmt = $conn->prepare($sellerQuery);
    if (!$sellerStmt) {
        throw new Exception("Database error: " . $conn->error);
    }
    $sellerStmt->bind_param("i", $listing_id);
    $sellerStmt->execute();
    $sellerResult = $sellerStmt->get_result();
    $sellerRow = $sellerResult->fetch_assoc();

    if (!$sellerRow) {
        throw new Exception("Seller not found for this listing.");
    }

    $seller_id = $sellerRow['seller_id'];

    // Update product status to "Paid For" (status_id = 3) and assign buyer_id
    $updateQuery = "UPDATE product_listings SET status_id = 3, buyer_id = ? WHERE id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    if (!$updateStmt) {
        throw new Exception("Error updating status: " . $conn->error);
    }
    $updateStmt->bind_param("ii", $buyer_id, $listing_id);
    $updateStmt->execute();

    // Insert purchase transaction including seller_id
    $insertQuery = "INSERT INTO purchases (listing_id, buyer_id, seller_id, mpesa_code, created_at) VALUES (?, ?, ?, ?, NOW())";
    $insertStmt = $conn->prepare($insertQuery);
    if (!$insertStmt) {
        throw new Exception("Error inserting purchase: " . $conn->error);
    }
    $insertStmt->bind_param("iiis", $listing_id, $buyer_id, $seller_id, $mpesa_code);
    $insertStmt->execute();

    $conn->commit();

    echo json_encode(["status" => 200, "message" => "Purchase successful."]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(["status" => 500, "message" => $e->getMessage()]);
}

$stmt->close();
$sellerStmt->close();
$updateStmt->close();
$insertStmt->close();
$conn->close();
?>
