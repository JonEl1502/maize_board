<?php
include 'config.php';
header('Content-Type: application/json');

$rawInput = file_get_contents("php://input");
$data = json_decode($rawInput, true);

// Add JSON decode error checking
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode([
        "status" => 400,
        "message" => "Invalid JSON: " . json_last_error_msg()
    ]);
    exit();
}

if (!isset($data['listing_id']) || !isset($data['payment_reference']) || !isset($data['buyer_id']) || !isset($data['quantity']) || empty($data['payment_reference'])) {
    echo json_encode(["status" => 400, "message" => "Invalid request. Listing ID, Buyer ID, Quantity, and Payment Reference are required."]);
    exit();
}

$listing_id = intval($data['listing_id']);
$buyer_id = intval($data['buyer_id']);
$quantity = intval($data['quantity']);
$payment_reference = trim($data['payment_reference']);

// Validate buyer_id exists in the users table
$checkBuyerStmt = $conn->prepare("SELECT id FROM users WHERE id = ?");
if (!$checkBuyerStmt) {
    echo json_encode(["status" => 500, "message" => "Database error: " . $conn->error]);
    exit();
}
$checkBuyerStmt->bind_param("i", $buyer_id);
$checkBuyerStmt->execute();
$checkBuyerStmt->store_result();

if ($checkBuyerStmt->num_rows === 0) {
    echo json_encode(["status" => 400, "message" => "Invalid buyer ID. User does not exist."]);
    exit();
}
$checkBuyerStmt->close();

$conn->begin_transaction();

try {
    // Check if the product exists and is available for purchase
    $query = "SELECT id, status_id, quantity, price_per_quantity FROM product_listings WHERE id = ? AND status_id = 1";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Database error: " . $conn->error);
    }
    $stmt->bind_param("i", $listing_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $listing = $result->fetch_assoc();

    if (!$listing) {
        throw new Exception("Product is no longer available for purchase.");
    }

    if ($quantity > $listing['quantity']) {
        throw new Exception("Requested quantity exceeds available stock.");
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

    // Prevent users from buying their own products
    if ($seller_id == $buyer_id) {
        throw new Exception("You cannot purchase your own product.");
    }

    // Calculate total price
    $total_price = $quantity * $listing['price_per_quantity'];

    // Update product status and quantity (single update)
    // Set status to 3 (Paid) since payment reference is provided
    $updateQuery = "UPDATE product_listings SET status_id = 3, buyer_id = ?, quantity = quantity - ? WHERE id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    if (!$updateStmt) {
        throw new Exception("Error updating status: " . $conn->error);
    }
    $updateStmt->bind_param("iii", $buyer_id, $quantity, $listing_id);
    $updateStmt->execute();

    // Insert purchase transaction
    $insertQuery = "INSERT INTO purchases (listing_id, buyer_id, seller_id, payment_reference, quantity, total_price, status, created_at) VALUES (?, ?, ?, ?, ?, ?, 'paid', NOW())";
    $insertStmt = $conn->prepare($insertQuery);
    if (!$insertStmt) {
        throw new Exception("Error inserting purchase: " . $conn->error);
    }
    $insertStmt->bind_param("iiisid", $listing_id, $buyer_id, $seller_id, $payment_reference, $quantity, $total_price);
    $insertStmt->execute();

    $conn->commit();

    // Ensure clean JSON output
    $response = [
        "status" => 200,
        "message" => "Purchase successful"
    ];
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Exception $e) {
    $conn->rollback();
    $response = [
        "status" => 500,
        "message" => $e->getMessage()
    ];
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

$stmt->close();
$sellerStmt->close();
$updateStmt->close();
$insertStmt->close();
$conn->close();
?>
