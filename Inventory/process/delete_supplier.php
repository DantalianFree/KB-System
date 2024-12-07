<?php 
include '..\src\conn.php';

if (isset($_GET['id'])) {
    $supplier_id = $_GET['id'];

    $sql = "DELETE FROM supplier WHERE SupplierID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $supplier_id);

    if ($stmt->execute()) {
        header("Location: ../src/supplier_list.php?status=deleted");
    } else {
        echo "Error: " . $stmt->error;
    }
}

?>