<?php
include '../src/conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inventory_id = $_POST['inventory_id'];
    $item_name = $_POST['item_name'];
    $category = $_POST['category'];
    $quantity = $_POST['quantity'];
    $unit = $_POST['unit'];
    $reorder_level = $_POST['reorder_level'];
    $last_updated = $_POST['last_updated'];

    $sql = "UPDATE inventory SET ItemName = ?, Category = ?, Quantity = ?, Unit = ?, ReorderLevel = ?, LastUpdated = ? WHERE InventoryID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssisssi", $item_name, $category, $quantity, $unit, $reorder_level, $last_updated, $inventory_id);

    if ($stmt->execute()) {
        header("Location: ../src/inventory_list.php?status=success");
    } else {
        echo "Error: " . $stmt->error;
    }
}

?>