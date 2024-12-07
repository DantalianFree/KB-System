<?php
include '..\src\conn.php';

if (isset($_GET['id'])) {
    $inventory_id = $_GET['id'];

    $sql = "DELETE FROM inventory WHERE InventoryID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $inventory_id);

    if ($stmt->execute()) {
        header("Location: ../src/inventory_list.php?status=deleted");
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>
