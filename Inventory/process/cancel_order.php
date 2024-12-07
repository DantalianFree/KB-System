<?php
// Database connection
include '../src/conn.php';

// Get the Order ID from the query string
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if ($order_id > 0) {
    // Delete the order from the database
    $stmt = $conn->prepare("DELETE FROM `order` WHERE OrderID = ?");
    $stmt->bind_param("i", $order_id);
    
    if ($stmt->execute()) {
        // Redirect back to the orders list with a success message
        header("Location: ..\src\supplier_orders.php?message=Order deleted successfully");
        exit();
    } else {
        // Redirect back with an error message
        header("Location: ..\src\supplier_orders.php?error=Failed to delete the order");
        exit();
    }
} else {
    // Redirect back with an invalid ID error
    header("Location: ..\src\supplier_orders.php?error=Invalid Order ID");
    exit();
}
?>
