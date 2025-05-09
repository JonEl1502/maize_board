<?php
include 'backend/config.php';

// Check the statuses table
$query = "SELECT * FROM statuses";
$result = $conn->query($query);

if ($result) {
    echo "<h2>Available Statuses</h2>";
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Name</th><th>Description</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['name'] . "</td>";
        echo "<td>" . ($row['description'] ?? 'N/A') . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?>
