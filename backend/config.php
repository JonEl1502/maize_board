<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "ecommerce_system";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

session_start();

// Helper functions for the application
function isLoggedIn() {
    return isset($_SESSION['id']);
}

function getUserRole() {
    return $_SESSION['role_id'] ?? null;
}

function isAdmin() {
    return getUserRole() == 1;
}

function isFarmer() {
    return getUserRole() == 2;
}

function isWholesaler() {
    return getUserRole() == 3;
}

function isCustomer() {
    return getUserRole() == 4;
}

function getUserId() {
    return $_SESSION['id'] ?? null;
}

function getUserName() {
    return $_SESSION['name'] ?? null;
}

function getEntityName() {
    return $_SESSION['entity_name'] ?? null;
}

// Function to log activities for reporting
function logActivity($userId, $action, $details = null) {
    global $conn;

    $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
    $ipAddress = $_SERVER['REMOTE_ADDR'];
    $stmt->bind_param("isss", $userId, $action, $details, $ipAddress);
    $stmt->execute();
    $stmt->close();
}

// Function to format currency
function formatCurrency($amount) {
    return 'Ksh ' . number_format($amount, 2);
}

// Function to sanitize input
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}
?>