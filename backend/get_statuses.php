<?php
header('Content-Type: application/json');
include 'config.php';

try {
    // Query to get all statuses
    $query = "SELECT id, name, description FROM statuses ORDER BY id";
    $result = $conn->query($query);

    if (!$result) {
        throw new Exception("Error fetching statuses: " . $conn->error);
    }

    // Define standard payment status mappings
    $paymentStatusMappings = [
        1 => 'pending',    // Assuming ID 1 is Pending
        2 => 'completed',  // Assuming ID 2 is Completed
        3 => 'cancelled',  // Assuming ID 3 is Cancelled
        4 => 'processing', // Assuming ID 4 is Processing
        5 => 'refunded',   // Assuming ID 5 is Refunded
        6 => 'failed'      // Assuming ID 6 is Failed
    ];

    $statuses = [];
    while ($row = $result->fetch_assoc()) {
        $statusId = (int)$row['id'];
        $paymentStatus = isset($paymentStatusMappings[$statusId])
            ? $paymentStatusMappings[$statusId]
            : strtolower(substr($row['name'], 0, 20));

        $statuses[] = [
            'id' => $statusId,
            'name' => $row['name'],
            'description' => $row['description'] ?? null,
            'payment_status' => $paymentStatus
        ];
    }

    echo json_encode([
        'status' => 200,
        'message' => 'Statuses fetched successfully',
        'statuses' => $statuses,
        'payment_status_mappings' => $paymentStatusMappings
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 500,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
