<?php
include '../conn.php';
session_start();

// Ensure the user is logged in and is an admin, manager, or staff
if (!isset($_SESSION['usertype']) || !in_array($_SESSION['usertype'], ['Admin', 'Manager', 'Staff'])) {
    echo "You don't have access to this page.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
    $orderStatus = isset($_POST['order_status']) ? $_POST['order_status'] : '';

    if ($orderId <= 0 || empty($orderStatus)) {
        $_SESSION['error'] = "Invalid order status.";
        header('Location: ../src/order_details.php?order_id=' . $orderId);
        exit;
    }

    // Update the order status
    $updateQuery = "UPDATE `order` SET OrderStatus = ? WHERE OrderID = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param('si', $orderStatus, $orderId);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Order status updated successfully.";
    } else {
        $_SESSION['error'] = "Failed to update order status.";
    }

    header('Location: ../src/order_details.php?order_id=' . $orderId);
    exit;
}
