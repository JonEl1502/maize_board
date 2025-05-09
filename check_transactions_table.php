<?php
include 'backend/config.php';

// Check the transactions table structure
$query = "DESCRIBE transactions";
$result = $conn->query($query);

if ($result) {
    echo "<h2>Transactions Table Structure</h2>";
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . ($row['Default'] ?? 'NULL') . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    // Check for enum values if payment_status is an enum
    $query = "SHOW COLUMNS FROM transactions LIKE 'payment_status'";
    $result = $conn->query($query);
    if ($result && $row = $result->fetch_assoc()) {
        if (strpos($row['Type'], 'enum') !== false) {
            echo "<h3>Payment Status Enum Values</h3>";
            preg_match("/^enum\(\'(.*)\'\)$/", $row['Type'], $matches);
            if (isset($matches[1])) {
                $enum_values = explode("','", $matches[1]);
                echo "<ul>";
                foreach ($enum_values as $value) {
                    echo "<li>" . $value . "</li>";
                }
                echo "</ul>";
            }
        }
    }
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?>
