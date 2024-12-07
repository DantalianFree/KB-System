<?php 
include '..\src\conn.php';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $itemName = $_POST['item_name'];
    $category = $_POST['category'];
    $quantity = $_POST['quantity'];
    $unit = $_POST['unit'];
    $reorder_level = $_POST['reorder_level'];
    $last_updated = $_POST['last_updated'];

    $stmt = $conn->prepare("INSERT INTO inventory (ItemName ,Category, Quantity, Unit, ReorderLevel, LastUpdated) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssisss", $itemName, $category, $quantity, $unit, $reorder_level, $last_updated);
    $stmt->execute();

    header("Location: inventory_form.php");
    exit();

}
?>