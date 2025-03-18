<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "maize_market";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

session_start();
?>