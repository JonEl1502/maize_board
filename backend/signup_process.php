<?php
include 'config.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $farmer_type_id = trim($_POST['farmer_type_id'] ?? '');
    $created_at = date('Y-m-d H:i:s');

    if (empty($name) || empty($email) || empty($password) || empty($phone) || empty($address)) {
        echo json_encode(["status" => 400, "message" => "All fields are required."]);
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["status" => 400, "message" => "Invalid email format."]);
        exit();
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Check if email exists
    $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    if (!$checkStmt) {
        echo json_encode(["status" => 500, "message" => "Prepare failed: " . $conn->error]);
        exit();
    }

    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        echo json_encode(["status" => 400, "message" => "Email already exists!"]);
        exit();
    }

    // Insert new user
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, phone, address, created_at) VALUES (?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        echo json_encode(["status" => 500, "message" => "Prepare failed: " . $conn->error]);
        exit();
    }

    $stmt->bind_param("ssssss", $name, $email, $hashedPassword, $phone, $address, $created_at);

    if ($stmt->execute()) {
        $user_id = $stmt->insert_id; // Get the inserted user ID

        // Insert into farmers table
        $farmerStmt = $conn->prepare("INSERT INTO farmers (user_id, farmer_type_id) VALUES (?, ?)");
        if (!$farmerStmt) {
            echo json_encode(["status" => 500, "message" => "Prepare failed: " . $conn->error]);
            exit();
        }
        $farmerStmt->bind_param("ii", $user_id, $farmer_type_id);
        $farmerStmt->execute();

        echo json_encode(["status" => 200, "message" => "Registration successful! Redirecting to login..."]);
    } else {
        echo json_encode(["status" => 500, "message" => "Database error: " . $conn->error]);
    }
    exit();
}
?>