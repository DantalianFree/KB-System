<?php
include '..\src\conn.php'; // Include your database connection

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inventory_id = $_POST['inventory_id']; // Item selected from the inventory
    $quantity_used = $_POST['quantity_used']; // Quantity of the item used
    $staff_id = $_POST['staff_id']; // The staff ID logging the usage
    $usage_date = $_POST['usage_date']; // The date of usage (optional, default is today)

    // Prepare SQL query to insert usage data into the database
    $sql = "INSERT INTO usage (InventoryID, QuantityUsed, UsageDate, StaffID) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisi", $inventory_id, $quantity_used, $usage_date, $staff_id); // "i" for integer, "s" for string

    if ($stmt->execute()) {
        echo "Usage recorded successfully!";
    } else {
        echo "Error recording usage: " . $conn->error;
    }

    $stmt->close();
}
?>
