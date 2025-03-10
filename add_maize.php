<?php include 'config.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Maize Listing</title>
</head>
<body>
    <h2>Add Maize Listing</h2>
    <form action="add_maize_process.php" method="POST">
        <input type="number" name="quantity" placeholder="Quantity (kg)" required>
        <input type="text" name="quality" placeholder="Quality Grade" required>
        <input type="number" step="0.01" name="price" placeholder="Price per kg" required>
        <button type="submit">Submit Listing</button>
    </form>
</body>
</html>