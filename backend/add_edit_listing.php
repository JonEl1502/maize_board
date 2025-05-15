<?php
include 'config.php';
header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log the incoming request for debugging
$requestLog = [
    'POST' => $_POST,
    'FILES' => $_FILES
];
error_log('Request data: ' . json_encode($requestLog));

// Check if we're adding or editing
$isEditing = isset($_POST['listing_id']) && !empty($_POST['listing_id']);

// Validate required fields
if (!isset($_POST['user_id'], $_POST['product_id'], $_POST['quantity'], $_POST['price_per_quantity'])) {
    echo json_encode([
        "status" => 400,
        "message" => "Missing required fields",
        "received" => $_POST
    ]);
    exit();
}

// Check for quantity unit ID (could be named quantity_unit_id or quantity_type_id)
$quantityTypeField = isset($_POST['quantity_type_id']) ? 'quantity_type_id' : 'quantity_unit_id';
if (!isset($_POST[$quantityTypeField])) {
    echo json_encode([
        "status" => 400,
        "message" => "Missing quantity unit/type field",
        "received" => $_POST
    ]);
    exit();
}

// Convert form data to appropriate types
$userId = intval($_POST['user_id']);
$productId = intval($_POST['product_id']);
$quantity = floatval($_POST['quantity']);
$quantityUnitId = intval($_POST[$quantityTypeField]);
$pricePerQuantity = floatval($_POST['price_per_quantity']);

// Calculate total price
$totalPrice = $quantity * $pricePerQuantity;

try {
    // Handle file upload if provided
    $imageUrl = null;
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/products/';

        // Create directory if it doesn't exist
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = time() . '_' . basename($_FILES['product_image']['name']);
        $uploadFile = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['product_image']['tmp_name'], $uploadFile)) {
            $imageUrl = 'uploads/products/' . $fileName;
        } else {
            // File upload failed, but we'll continue with the listing
            error_log('File upload failed: ' . $_FILES['product_image']['error']);
        }
    }

    if ($isEditing) {
        // Update existing listing
        $listingId = intval($_POST['listing_id']);

        $query = "UPDATE product_listings SET
                  product_id = ?,
                  quantity = ?,
                  quantity_type_id = ?,
                  price_per_quantity = ?,
                  price = ?";

        $params = [$productId, $quantity, $quantityUnitId, $pricePerQuantity, $totalPrice];
        $types = "iiddd";

        // Add image URL to update if provided
        if ($imageUrl) {
            $query .= ", product_image_url = ?";
            $params[] = $imageUrl;
            $types .= "s";
        }

        $query .= " WHERE id = ? AND seller_id = ?";
        $params[] = $listingId;
        $params[] = $userId;
        $types .= "ii";

        $stmt = $conn->prepare($query);

        if (!$stmt) {
            throw new Exception("SQL Error: " . $conn->error);
        }

        $stmt->bind_param($types, ...$params);

        if (!$stmt->execute()) {
            throw new Exception("Database error: " . $stmt->error);
        }

        if ($stmt->affected_rows > 0) {
            echo json_encode([
                "status" => 200,
                "message" => "Listing updated successfully!",
                "listing_id" => $listingId
            ]);
        } else {
            echo json_encode([
                "status" => 404,
                "message" => "Listing not found or you don't have permission to edit it."
            ]);
        }
    } else {
        // Add new listing
        $query = "INSERT INTO product_listings
                 (seller_id, product_id, quantity, quantity_type_id, price_per_quantity, price, status_id";

        $params = [$userId, $productId, $quantity, $quantityUnitId, $pricePerQuantity, $totalPrice, 1];
        $types = "iiddddi";

        // Add image URL if provided
        if ($imageUrl) {
            $query .= ", product_image_url";
            $params[] = $imageUrl;
            $types .= "s";
        }

        $query .= ") VALUES (" . str_repeat("?,", count($params) - 1) . "?)";

        $stmt = $conn->prepare($query);

        if (!$stmt) {
            throw new Exception("SQL Error: " . $conn->error);
        }

        $stmt->bind_param($types, ...$params);

        if (!$stmt->execute()) {
            throw new Exception("Database error: " . $stmt->error);
        }

        echo json_encode([
            "status" => 200,
            "message" => "Listing added successfully!",
            "listing_id" => $conn->insert_id
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        "status" => 500,
        "message" => $e->getMessage()
    ]);
}

exit();
