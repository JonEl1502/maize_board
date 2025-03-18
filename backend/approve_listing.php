<?php
include 'config.php';
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $maize_id = $_POST['maize_id'];
    $action = $_POST['action'];
    $comments = $_POST['comments'];
    $board_member_id = $_POST['board_member_id'];

    $status = ($action == 'approve') ? 'approved' : 'rejected';

    $stmt = $conn->prepare("UPDATE maize_listings 
        SET status = ?, approved_by = ?, approval_comments = ? 
        WHERE id = ?");
    $stmt->bind_param("sisi", $status, $board_member_id, $comments, $maize_id);

    if ($stmt->execute()) {
        echo json_encode(["status" => 200, "message" => "Listing successfully " . $status . "!"]);
    } else {
        echo json_encode(["status" => 500, "message" => "Error updating listing."]);
    }
}
?>