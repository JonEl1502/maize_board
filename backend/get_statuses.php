<?php
header('Content-Type: application/json');
include 'config.php';

try {
    // Query to get all statuses
    $query = "SELECT id, name FROM statuses ORDER BY id";
    $result = $conn->query($query);

    if (!$result) {
        throw new Exception("Error fetching statuses: " . $conn->error);
    }

    // Define payment status mappings that match the enum values in the database
    // The transactions table payment_status is an enum('pending','completed','failed','refunded')
    $paymentStatusMappings = [
        1 => 'pending',    // Listed/Pending
        2 => 'completed',  // Spoken For/Completed
        3 => 'completed',  // Paid For maps to completed
        4 => 'completed',  // Sold maps to completed
        5 => 'refunded',   // If status ID 5 exists and means refunded
        6 => 'failed'      // If status ID 6 exists and means failed
    ];

    $statuses = [];
    while ($row = $result->fetch_assoc()) {
        $statusId = (int)$row['id'];

        // Valid enum values for payment_status
        $validEnumValues = ['pending', 'completed', 'failed', 'refunded'];

        // Get payment status from mapping or default to 'pending'
        $paymentStatus = isset($paymentStatusMappings[$statusId])
            ? $paymentStatusMappings[$statusId]
            : 'pending';

        // Ensure it's a valid enum value
        if (!in_array($paymentStatus, $validEnumValues)) {
            $paymentStatus = 'pending';
        }

        $statuses[] = [
            'id' => $statusId,
            'name' => $row['name'],
            'description' => null, // No description column in the database
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
