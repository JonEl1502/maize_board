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
    echo json_encode(["status" => 400, "message" => "Email and password are required."]);
    exit();
}

// ✅ SQL Query to get user details
$sql = "SELECT u.id, u.name, u.email, u.password, u.role_id, r.name AS role_name 
        FROM users u 
        JOIN roles r ON u.role_id = r.id 
        WHERE u.email = ?";

$stmt = $conn->prepare($sql);
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
    $stmt->bind_result($id, $name, $dbemail, $hashedPassword, $role_id, $role_name);
    $stmt->fetch();

    // ✅ Verify password
    if (password_verify($password, $hashedPassword)) {
        $_SESSION['id'] = $id;
        $_SESSION['name'] = $name;
        $_SESSION['email'] = $dbemail;
        $_SESSION['role_id'] = $role_id;
        $_SESSION['role'] = $role_name;

        // ✅ Success Response
        echo json_encode([
            "status" => 200,
            "message" => "Login successful!",
            "user" => [
                "id" => $id,
                "name" => $name,
                "email" => $dbemail,
                "role_id" => $role_id,
                "role" => $role_name,
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