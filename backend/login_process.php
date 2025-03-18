<?php
session_start();
include 'config.php';
header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('display_errors', 1);

// ✅ Check database connection
if (!$conn) {
    echo json_encode(["status" => 500, "message" => "Database connection error."]);
    exit();
}

// ✅ Ensure request is POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["status" => 405, "message" => "Invalid request method."]);
    exit();
}

// ✅ Capture and sanitize input
$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

if (empty($email) || empty($password)) {
    echo json_encode(["status" => 400, "message" => "email and password are required."]);
    exit();
}

// ✅ Fix: Check if using `email` instead of `email`
$stmt = $conn->prepare("SELECT id, name, email, password, profile_id FROM users WHERE email = ?");
if (!$stmt) {
    echo json_encode(["status" => 500, "message" => "SQL error: " . $conn->error]);
    exit();
}

// ✅ Bind and execute query
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

// ✅ Verify user exists
if ($stmt->num_rows > 0) {
    $stmt->bind_result($id, $name, $dbemail, $hashedPassword, $role);
    $stmt->fetch();

    // ✅ Verify password
    if (password_verify($password, $hashedPassword)) {
        $_SESSION['id'] = $id;
        $_SESSION['name'] = $name;
        $_SESSION['email'] = $dbemail;
        $_SESSION['role'] = $role;

        // ✅ Success Response
        echo json_encode([
            "status" => 200,
            "message" => "Login successful!",
            "user" => [
                "id" => $id,
                "name" => $name,
                "email" => $dbemail,
                "role" => $role
            ]
        ]);
        exit();
    } else {
        echo json_encode(["status" => 401, "message" => "Incorrect password."]);
        exit();
    }
} else {
    echo json_encode(["status" => 404, "message" => "User not found."]);
    exit();
}
?>