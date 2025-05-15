<?php
include 'config.php';
header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get data from request
$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (!isset($data['wholesaler_id'], $data['product_name'], $data['description'], $data['processing_method'],
           $data['category_id'], $data['materials'], $data['quantity'], $data['quantity_type_id'], $data['price_per_unit'])) {
    echo json_encode([
        'status' => 400,
        'message' => 'Missing required fields: wholesaler_id, product_name, description, processing_method, category_id, materials, quantity, quantity_type_id, and price_per_unit are required'
    ]);
    exit;
}

// Validate materials array
if (!is_array($data['materials']) || empty($data['materials'])) {
    echo json_encode([
        'status' => 400,
        'message' => 'Materials must be a non-empty array'
    ]);
    exit;
}

// Extract data
$wholesaler_id = intval($data['wholesaler_id']);
$product_name = trim($data['product_name']);
$description = trim($data['description']);
$processing_method = trim($data['processing_method']);
$category_id = intval($data['category_id']);
$materials = $data['materials'];
$image_url = isset($data['image_url']) ? trim($data['image_url']) : '';
$quantity = floatval($data['quantity']);
$quantity_type_id = intval($data['quantity_type_id']);
$price_per_unit = floatval($data['price_per_unit']);
$price = isset($data['price']) ? floatval($data['price']) : ($price_per_unit * $quantity);

try {
    // Start transaction
    $conn->begin_transaction();

    // Check if the wholesaler exists and has the correct role
    $checkUserQuery = "SELECT id, role_id FROM users WHERE id = ?";
    $checkUserStmt = $conn->prepare($checkUserQuery);
    $checkUserStmt->bind_param("i", $wholesaler_id);
    $checkUserStmt->execute();
    $userResult = $checkUserStmt->get_result();

    if ($userResult->num_rows === 0) {
        throw new Exception("Wholesaler not found");
    }

    $user = $userResult->fetch_assoc();
    if ($user['role_id'] !== 3) { // Role ID 3 is for wholesalers
        throw new Exception("Only wholesalers can create derived products");
    }

    // Check if the product name already exists
    $checkProductQuery = "SELECT id FROM products WHERE name = ?";
    $checkProductStmt = $conn->prepare($checkProductQuery);
    $checkProductStmt->bind_param("s", $product_name);
    $checkProductStmt->execute();

    if ($checkProductStmt->get_result()->num_rows > 0) {
        throw new Exception("A product with this name already exists");
    }

    // Generate a unique tracking ID for the derived product
    $tracking_id = 'DP-' . date('Ymd') . '-' . substr(uniqid(), -6);

    // Insert the new product with tracking information
    $insertProductQuery = "INSERT INTO products (name, description, image_url, is_derived, created_by, category_id, updated_at)
                          VALUES (?, ?, ?, 1, ?, ?, NOW())";
    $insertProductStmt = $conn->prepare($insertProductQuery);
    $insertProductStmt->bind_param("sssii", $product_name, $description, $image_url, $wholesaler_id, $category_id);

    if (!$insertProductStmt->execute()) {
        throw new Exception("Failed to create product: " . $insertProductStmt->error);
    }

    $product_id = $conn->insert_id;

    // Log the product creation for tracking
    error_log("Created derived product ID: {$product_id}, Tracking ID: {$tracking_id}, Created by: {$wholesaler_id}");

    // Insert the derived product record
    $insertDerivedQuery = "INSERT INTO derived_products (product_id, wholesaler_id, description, processing_method) VALUES (?, ?, ?, ?)";
    $insertDerivedStmt = $conn->prepare($insertDerivedQuery);
    $insertDerivedStmt->bind_param("iiss", $product_id, $wholesaler_id, $description, $processing_method);

    if (!$insertDerivedStmt->execute()) {
        throw new Exception("Failed to create derived product: " . $insertDerivedStmt->error);
    }

    $derived_product_id = $conn->insert_id;

    // Process each material
    foreach ($materials as $material) {
        // Validate material data
        if (!isset($material['source_product_id'], $material['quantity_used'], $material['quantity_type_id'])) {
            throw new Exception("Invalid material data: source_product_id, quantity_used, and quantity_type_id are required");
        }

        $source_product_id = intval($material['source_product_id']);
        $quantity_used = floatval($material['quantity_used']);
        $quantity_type_id = intval($material['quantity_type_id']);

        // Check if the wholesaler has enough of this material in their purchases
        $checkInventoryQuery = "SELECT SUM(p.quantity) as total_purchased, pl.quantity_type_id
                               FROM purchases p
                               JOIN product_listings pl ON p.listing_id = pl.id
                               WHERE p.buyer_id = ? AND pl.product_id = ? AND pl.quantity_type_id = ?
                               GROUP BY pl.quantity_type_id";

        $checkInventoryStmt = $conn->prepare($checkInventoryQuery);
        $checkInventoryStmt->bind_param("iii", $wholesaler_id, $source_product_id, $quantity_type_id);
        $checkInventoryStmt->execute();
        $inventoryResult = $checkInventoryStmt->get_result();

        if ($inventoryResult->num_rows === 0) {
            throw new Exception("You don't have any of this material in your inventory");
        }

        $inventory = $inventoryResult->fetch_assoc();
        $total_purchased = floatval($inventory['total_purchased']);

        // Check if the wholesaler has enough of this material
        if ($total_purchased < $quantity_used) {
            throw new Exception("Not enough material available. You have $total_purchased but need $quantity_used");
        }

        // Insert the material record
        $insertMaterialQuery = "INSERT INTO product_materials (derived_product_id, source_product_id, quantity_used, quantity_type_id) VALUES (?, ?, ?, ?)";
        $insertMaterialStmt = $conn->prepare($insertMaterialQuery);
        $insertMaterialStmt->bind_param("iidd", $derived_product_id, $source_product_id, $quantity_used, $quantity_type_id);

        if (!$insertMaterialStmt->execute()) {
            throw new Exception("Failed to add material: " . $insertMaterialStmt->error);
        }

        // Subtract the used material from the wholesaler's inventory
        // This is a logical subtraction - we're not actually removing records from purchases
        // Instead, we're tracking usage in the product_materials table
    }

    // Create a product listing for the derived product so the wholesaler can see it in their dashboard
    // Use the provided quantity, quantity type, and price
    $statusId = 1; // Available

    // Insert the product listing
    $insertListingQuery = "INSERT INTO product_listings (seller_id, product_id, quantity, quantity_type_id, price_per_quantity, price, status_id)
                          VALUES (?, ?, ?, ?, ?, ?, ?)";
    $insertListingStmt = $conn->prepare($insertListingQuery);
    $insertListingStmt->bind_param("iidiidi", $wholesaler_id, $product_id, $quantity, $quantity_type_id, $price_per_unit, $price, $statusId);

    if (!$insertListingStmt->execute()) {
        throw new Exception("Failed to create product listing: " . $insertListingStmt->error);
    }

    $listing_id = $conn->insert_id;

    // Commit the transaction
    $conn->commit();

    echo json_encode([
        'status' => 200,
        'message' => 'Derived product created successfully',
        'product_id' => $product_id,
        'derived_product_id' => $derived_product_id,
        'listing_id' => $listing_id,
        'tracking_id' => $tracking_id
    ]);

} catch (Exception $e) {
    // Rollback the transaction in case of error
    $conn->rollback();

    echo json_encode([
        'status' => 500,
        'message' => 'An error occurred: ' . $e->getMessage()
    ]);
}

$conn->close();
?>
