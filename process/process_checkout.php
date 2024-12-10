<?php
include '../conn.php';

session_start();

// Check if the cart is not empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    $_SESSION['error'] = "Your cart is empty.";
    header("Location: ../src/checkout.php");
    exit;
}

// Fetch form data
$name = $_POST['name'];
$contactDetails = $_POST['contactDetails'];
$paymentType = $_POST['paymentType'];
$cartItems = $_SESSION['cart'];
$totalAmount = 0;

foreach ($cartItems as $item) {
    $totalAmount += $item['Price'] * $item['Quantity'];
}

// Insert customer details into the database
$customerQuery = "INSERT INTO customers (Name, ContactDetails) VALUES (?, ?)";
$customerStmt = $conn->prepare($customerQuery);
$customerStmt->bind_param("ss", $name, $contactDetails);
$customerStmt->execute();
$customerID = $conn->insert_id; // Fetch the last inserted CustomerID

// Insert payment details into the database
$paymentQuery = "INSERT INTO payment (OrderID, PaymentType, PaymentDate, AmountPaid) VALUES (?, ?, CURRENT_TIMESTAMP, ?)";
$orderID = rand(1000, 9999); // Replace with your order ID logic
$paymentStmt = $conn->prepare($paymentQuery);
$paymentStmt->bind_param("isd", $orderID, $paymentType, $totalAmount);
$paymentStmt->execute();

// Clear cart
$_SESSION['cart'] = [];
$_SESSION['success'] = "Order placed successfully!";

// Redirect to orders list or confirmation page
header("Location: ../src/orders_list.php");
exit;
?>
