<?php
session_start();
include 'config.php';
header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    // Check if user exists
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $username, $hashedPassword, $role);
        $stmt->fetch();

        if (password_verify($password, $hashedPassword)) {
            // ✅ Store user data in PHP SESSION
            $_SESSION['id'] = $id;
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $role;

            // ✅ Send JSON response to frontend
            echo json_encode([
                "status" => 200,
                "message" => "Login successful!",
                "user" => [
                    "id" => $id,
                    "username" => $username,
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
}
?>