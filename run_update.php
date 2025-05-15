<?php
// Script to run the SQL update
include 'backend/config.php';

// Read the SQL file
$sql = file_get_contents('update_statuses_and_schema.sql');

// Execute the SQL
try {
    // Split the SQL into individual statements
    $statements = explode(';', $sql);
    
    // Execute each statement
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            $result = $conn->query($statement);
            if (!$result) {
                echo "Error executing statement: " . $conn->error . "<br>";
                echo "Statement: " . $statement . "<br><br>";
            }
        }
    }
    
    echo "Database update completed successfully!";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

$conn->close();
?>
