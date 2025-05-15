<?php
include 'config.php';
header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user_id is provided
if (!isset($_GET['user_id']) || empty($_GET['user_id'])) {
    echo json_encode([
        "status" => 400,
        "message" => "User ID is required"
    ]);
    exit();
}

$user_id = intval($_GET['user_id']);

// Prepare SQL query to check if user exists
$stmt = $conn->prepare("SELECT id FROM users WHERE id = ?");
if (!$stmt) {
    echo json_encode([
        "status" => 500,
        "message" => "SQL Error: " . $conn->error
    ]);
    exit();
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->store_result();

// Return response based on whether user exists
if ($stmt->num_rows > 0) {
    echo json_encode([
        "status" => 200,
        "message" => "User exists",
        "user_id" => $user_id
    ]);
} else {
    echo json_encode([
        "status" => 404,
        "message" => "User not found",
        "user_id" => $user_id
    ]);
}

$stmt->close();
$conn->close();
