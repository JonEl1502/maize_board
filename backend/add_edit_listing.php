<?php
include 'config.php'; // Ensure database connection is correct
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["status" => 405, "message" => "Invalid request method."]);
    exit();
}

// Capture and sanitize input
$listing_id = isset($_POST['listing_id']) ? intval($_POST['listing_id']) : 0;
$product_id = intval($_POST['product_id']);
$quantity = intval($_POST['quantity']);
$quantity_unit_id = intval($_POST['quantity_unit_id']);
$price_per_unit = isset($_POST['price_per_quantity']) ? floatval($_POST['price_per_quantity']) : 0.00;
$user_id = isset($_POST['user_id']) && !empty($_POST['user_id']) ? intval($_POST['user_id']) : null;
$product_image_url = isset($_POST['product_image_url']) ? $_POST['product_image_url'] : '';

// Calculate total price
$price = $quantity * $price_per_unit;

// Handle file upload
if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
    $target_dir = "../uploads/";
    $image_name = time() . "_" . basename($_FILES["product_image"]["name"]);
    $target_file = $target_dir . $image_name;
    if (move_uploaded_file($_FILES["product_image"]["tmp_name"], $target_file)) {
        $product_image_url = "uploads/" . $image_name; // Store relative path
    }
}

// Validate inputs
if (empty($product_id) || empty($quantity) || empty($quantity_unit_id) || empty($price_per_unit)) {
    echo json_encode(["status" => 400, "message" => "All fields are required."]);
    exit();
}

if ($listing_id > 0) {
    // Update existing listing
    $sql = "UPDATE product_listings SET product_id = ?, quantity = ?, quantity_type_id = ?, price_per_quantity = ?, price = ?";
    $params = [$product_id, $quantity, $quantity_unit_id, $price_per_unit, $price];

    if ($user_id !== null) {
        $sql .= ", user_id = ?";
        $params[] = $user_id;
    }

    if (!empty($product_image_url)) {
        $sql .= ", product_image_url = ?";
        $params[] = $product_image_url;
    }

    $sql .= " WHERE id = ?";
    $params[] = $listing_id;

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(["status" => 500, "message" => "SQL prepare error: " . $conn->error]);
        exit();
    }
    $stmt->bind_param(str_repeat("i", count($params) - 1) . "s", ...$params);
} else {
    // Insert new listing
    $sql = "INSERT INTO product_listings (product_id, quantity, quantity_type_id, price_per_quantity, price, user_id, product_image_url) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(["status" => 500, "message" => "SQL prepare error: " . $conn->error]);
        exit();
    }

    // Adjust binding parameters
    if ($user_id === null) {
        $stmt->bind_param("iiidss", $product_id, $quantity, $quantity_unit_id, $price_per_unit, $price, $product_image_url);
    } else {
        $stmt->bind_param("iiidsss", $product_id, $quantity, $quantity_unit_id, $price_per_unit, $price, $user_id, $product_image_url);
    }
}

if ($stmt->execute()) {
    echo json_encode(["status" => 200, "message" => $listing_id > 0 ? "Listing updated successfully!" : "Listing added successfully!"]);
} else {
    echo json_encode(["status" => 500, "message" => "Database error: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>