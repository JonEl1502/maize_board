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

if (!isset($data['cart_items']) || !isset($data['mpesa_code']) || !isset($data['buyer_id']) || empty($data['mpesa_code'])) {
    echo json_encode(["status" => 400, "message" => "Invalid request. Cart items, Buyer ID, and Mpesa code are required."]);
    exit();
}

$cart_items = $data['cart_items'];
$buyer_id = intval($data['buyer_id']);
$mpesa_code = trim($data['mpesa_code']);

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
    $successful_purchases = [];
    $failed_purchases = [];

    foreach ($cart_items as $item) {
        $listing_id = intval($item['productId']);
        $quantity = intval($item['quantity']);

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
            $failed_purchases[] = ["productId" => $listing_id, "reason" => "Product is no longer available for purchase."];
            continue;
        }

        if ($quantity > $listing['quantity']) {
            $failed_purchases[] = ["productId" => $listing_id, "reason" => "Requested quantity exceeds available stock."];
            continue;
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
            $failed_purchases[] = ["productId" => $listing_id, "reason" => "Seller not found for this listing."];
            continue;
        }

        $seller_id = $sellerRow['seller_id'];

        // Prevent users from buying their own products
        if ($seller_id == $buyer_id) {
            $failed_purchases[] = ["productId" => $listing_id, "reason" => "You cannot purchase your own product."];
            continue;
        }

        // Calculate total price
        $total_price = $quantity * $listing['price_per_quantity'];

        // Update product status and quantity (single update)
        $updateQuery = "UPDATE product_listings SET status_id = IF(quantity - ? = 0, 3, 1), buyer_id = IF(quantity - ? = 0, ?, NULL), quantity = quantity - ? WHERE id = ?";
        $updateStmt = $conn->prepare($updateQuery);
        if (!$updateStmt) {
            throw new Exception("Error updating status: " . $conn->error);
        }
        $updateStmt->bind_param("iiiii", $quantity, $quantity, $buyer_id, $quantity, $listing_id);
        $updateStmt->execute();

        // Insert purchase transaction
        $insertQuery = "INSERT INTO purchases (listing_id, buyer_id, seller_id, mpesa_code, quantity, total_price, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $insertStmt = $conn->prepare($insertQuery);
        if (!$insertStmt) {
            throw new Exception("Error inserting purchase: " . $conn->error);
        }
        $insertStmt->bind_param("iiisid", $listing_id, $buyer_id, $seller_id, $mpesa_code, $quantity, $total_price);
        $insertStmt->execute();

        $successful_purchases[] = ["productId" => $listing_id, "quantity" => $quantity, "total" => $total_price];
    }

    $conn->commit();

    // Ensure clean JSON output
    $response = [
        "status" => 200,
        "message" => "Purchase successful",
        "successful" => $successful_purchases,
        "failed" => $failed_purchases
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
?>