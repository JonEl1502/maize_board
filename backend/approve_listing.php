<?php
include 'config.php';
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $maize_id = $_POST['maize_id'];
    $action = $_POST['action'];
    $comments = $_POST['comments'];
    $board_member_id = $_POST['board_member_id'];
    $moisture_percentage = $_POST['moisture_percentage'];
    $aflatoxin_level = $_POST['aflatoxin_level'];

    $status = ($action == 'approve') ? 'approved' : 'rejected';

    $stmt = $conn->prepare("UPDATE maize_listings 
        SET status = ?, approved_by = ?, approval_comments = ?, moisture_percentage = ?, aflatoxin_level = ? 
        WHERE id = ?");
    $stmt->bind_param("sisddi", $status, $board_member_id, $comments, $moisture_percentage, $aflatoxin_level, $maize_id);

    if ($stmt->execute()) {
        echo json_encode(["status" => 200, "message" => "Listing successfully " . $status . "!"]);
    } else {
        echo json_encode(["status" => 500, "message" => "Error updating listing."]);
    }
}
?>