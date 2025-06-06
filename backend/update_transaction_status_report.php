<?php
header('Content-Type: application/json');
include 'config.php';

// Get data from request
$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (!isset($data['transaction_id']) || !isset($data['new_status_id']) || !isset($data['user_id'])) {
    echo json_encode([
        'status' => 400,
        'message' => 'Missing required fields: transaction_id, new_status_id, and user_id are required'
    ]);
    exit;
}

$transactionId = intval($data['transaction_id']);
$newStatusId = intval($data['new_status_id']);
$userId = intval($data['user_id']);
$transactionType = isset($data['transaction_type']) ? $data['transaction_type'] : null;

try {
    // Start transaction
    $conn->begin_transaction();

    // First, check if the transaction exists and the user has permission to update it
    $checkQuery = "SELECT p.*, t.id as transaction_id, t.transaction_type, t.payment_status
                  FROM purchases p
                  LEFT JOIN transactions t ON (t.reference_id = p.id AND t.transaction_type IN ('purchase', 'sale'))
                  WHERE (p.id = ? OR t.id = ?)
                  AND (p.buyer_id = ? OR p.seller_id = ?)";

    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param("iiii", $transactionId, $transactionId, $userId, $userId);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if ($result->num_rows === 0) {
        // If no direct match, check if it's a transaction that the user has permission for
        $checkTransactionQuery = "SELECT t.* FROM transactions t
                                 WHERE t.id = ? AND t.user_id = ?";
        $checkTransactionStmt = $conn->prepare($checkTransactionQuery);
        $checkTransactionStmt->bind_param("ii", $transactionId, $userId);
        $checkTransactionStmt->execute();
        $transactionResult = $checkTransactionStmt->get_result();

        if ($transactionResult->num_rows === 0) {
            throw new Exception("Transaction not found or you don't have permission to update it");
        }

        $transaction = $transactionResult->fetch_assoc();
    } else {
        $record = $result->fetch_assoc();
        $transaction = [
            'id' => $record['transaction_id'] ?? $transactionId,
            'transaction_type' => $record['transaction_type'] ?? $transactionType ?? 'purchase',
            'reference_id' => $record['id']
        ];
    }

    // Check if the status exists
    $checkStatusQuery = "SELECT * FROM statuses WHERE id = ?";
    $checkStatusStmt = $conn->prepare($checkStatusQuery);
    $checkStatusStmt->bind_param("i", $newStatusId);
    $checkStatusStmt->execute();
    $statusResult = $checkStatusStmt->get_result();

    if ($statusResult->num_rows === 0) {
        throw new Exception("Invalid status ID");
    }

    $status = $statusResult->fetch_assoc();

    // Update the transaction status if it's a transaction record
    if (isset($transaction['id'])) {
        // Define payment status mappings that match the enum values in the database
        // The transactions table payment_status is an enum('pending','completed','failed','refunded')
        $paymentStatusMappings = [
            1 => 'pending',    // Listed
            2 => 'pending',    // Pending Payment
            3 => 'completed',  // Paid
            4 => 'completed',  // Completed
            5 => 'failed',     // Cancelled
            6 => 'refunded'    // Refunded
        ];

        // Get the appropriate payment_status value based on the status ID
        // Ensure it's one of the valid enum values
        $validEnumValues = ['pending', 'completed', 'failed', 'refunded'];

        if (isset($paymentStatusMappings[$newStatusId])) {
            $paymentStatus = $paymentStatusMappings[$newStatusId];
        } else {
            // Default to 'pending' if no valid mapping exists
            $paymentStatus = 'pending';
        }

        // Final validation to ensure we're using a valid enum value
        if (!in_array($paymentStatus, $validEnumValues)) {
            $paymentStatus = 'pending'; // Default to pending if somehow we got an invalid value
        }

        // Update the transaction record
        $updateTransactionQuery = "UPDATE transactions SET payment_status = ?, updated_at = NOW() WHERE id = ?";
        $updateTransactionStmt = $conn->prepare($updateTransactionQuery);
        $updateTransactionStmt->bind_param("si", $paymentStatus, $transaction['id']);

        if (!$updateTransactionStmt->execute()) {
            throw new Exception("Failed to update transaction status: " . $updateTransactionStmt->error);
        }
    }

    // Update the purchase/listing status
    if (isset($transaction['reference_id'])) {
        // Check if the purchases table has a status_id column
        $checkColumnQuery = "SHOW COLUMNS FROM purchases LIKE 'status_id'";
        $checkColumnResult = $conn->query($checkColumnQuery);

        if ($checkColumnResult->num_rows > 0) {
            // If status_id column exists, update it
            $updatePurchaseQuery = "UPDATE purchases SET status_id = ?, updated_at = NOW() WHERE id = ?";
            $updatePurchaseStmt = $conn->prepare($updatePurchaseQuery);
            $updatePurchaseStmt->bind_param("ii", $newStatusId, $transaction['reference_id']);
        } else {
            // If status_id column doesn't exist, use the status column (which is an enum)
            // Map the status ID to the appropriate enum value
            $statusMap = [
                1 => 'pending',   // Listed
                2 => 'pending',   // Pending Payment
                3 => 'paid',      // Paid
                4 => 'delivered', // Completed
                5 => 'cancelled', // Cancelled
                6 => 'refunded'   // Refunded
            ];

            $statusValue = isset($statusMap[$newStatusId]) ? $statusMap[$newStatusId] : 'pending';

            $updatePurchaseQuery = "UPDATE purchases SET status = ?, updated_at = NOW() WHERE id = ?";
            $updatePurchaseStmt = $conn->prepare($updatePurchaseQuery);
            $updatePurchaseStmt->bind_param("si", $statusValue, $transaction['reference_id']);
        }

        if (!$updatePurchaseStmt->execute()) {
            throw new Exception("Failed to update purchase status: " . $updatePurchaseStmt->error);
        }

        // Get the listing ID from the purchase
        $listingQuery = "SELECT listing_id FROM purchases WHERE id = ?";
        $listingStmt = $conn->prepare($listingQuery);
        $listingStmt->bind_param("i", $transaction['reference_id']);

        if (!$listingStmt->execute()) {
            throw new Exception("Failed to fetch listing ID: " . $listingStmt->error);
        }

        $listingResult = $listingStmt->get_result();

        if ($listingResult->num_rows > 0) {
            $listing = $listingResult->fetch_assoc();
            $listingId = $listing['listing_id'];

            // Update the product listing status ONLY for this specific transaction
            // We need to make sure we're only updating the listing for this specific purchase
            $updateListingQuery = "UPDATE product_listings SET status_id = ?, updated_at = NOW()
                                  WHERE id = ? AND id IN (
                                      SELECT listing_id FROM purchases WHERE id = ?
                                  )";
            $updateListingStmt = $conn->prepare($updateListingQuery);
            $updateListingStmt->bind_param("iii", $newStatusId, $listingId, $transaction['reference_id']);

            if (!$updateListingStmt->execute()) {
                throw new Exception("Failed to update product listing status: " . $updateListingStmt->error);
            }
        }
    }

    // Log the action (if activity_logs table exists)
    try {
        // Check if activity_logs table exists
        $tableCheckQuery = "SHOW TABLES LIKE 'activity_logs'";
        $tableCheckResult = $conn->query($tableCheckQuery);

        if ($tableCheckResult->num_rows > 0) {
            $logQuery = "INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES (?, 'update_status', ?, ?)";
            $logStmt = $conn->prepare($logQuery);
            $details = "Updated transaction #$transactionId status to " . $status['name'];
            $ipAddress = $_SERVER['REMOTE_ADDR'];
            $logStmt->bind_param("iss", $userId, $details, $ipAddress);
            $logStmt->execute();
        }
    } catch (Exception $e) {
        // Silently ignore logging errors - don't let them affect the main transaction
        error_log("Error logging activity: " . $e->getMessage());
    }

    // Commit the transaction
    $conn->commit();

    echo json_encode([
        'status' => 200,
        'message' => 'Status updated successfully',
        'transaction_id' => $transactionId,
        'new_status' => $status['name'],
        'new_status_id' => $newStatusId
    ]);

} catch (Exception $e) {
    // Rollback the transaction in case of error
    $conn->rollback();

    echo json_encode([
        'status' => 500,
        'message' => 'An error occurred: ' . $e->getMessage()
    ]);
}

$conn->close();
