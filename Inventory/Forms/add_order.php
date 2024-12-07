<?php
include '../src/conn.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $supplier_id = intval($_POST['supplier_id']);
    $product = $conn->real_escape_string($_POST['product']);
    $quantity = intval($_POST['quantity']);
    $order_date = date('Y-m-d');
    $status = 'Pending';

    // Insert new order
    $stmt = $conn->prepare("INSERT INTO `order` (SupplierID, Product, Quantity, OrderDate, Status) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("isiss", $supplier_id, $product, $quantity, $order_date, $status);

    if ($stmt->execute()) {
        header("Location: ..\src\supplier_orders.php?success=Order placed successfully!");
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}
?>
