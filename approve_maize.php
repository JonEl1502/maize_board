<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && $_SESSION['role'] == 'board_member') {
    $maize_id = $_POST['maize_id'];
    $action = $_POST['action'];
    
    $status = ($action == 'approve') ? 'approved' : 'rejected';
    
    $stmt = $conn->prepare("UPDATE maize_listings 
        SET status = ?, approved_by = ? 
        WHERE id = ?");
    $stmt->bind_param("sii", $status, $_SESSION['user_id'], $maize_id);
    
    if ($stmt->execute()) {
        header("Location: dashboard.php");
    } else {
        echo "Error: " . $conn->error;
    }
}
?>