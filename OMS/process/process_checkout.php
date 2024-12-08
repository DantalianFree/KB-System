<?php
    session_start();
    // Include database connection (order_management)
    include '../conn.php'; 
    
    // Check if the cart is not empty
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        echo "Your cart is empty.";
        exit;
    }
    
    // Fetch customer details from the checkout form
    $customerName = $_POST['name'];
    $contactDetails = $_POST['contactDetails'];
    
    // Check if the customer already exists in the order_management database
    $query = "SELECT CustomerID FROM order_management.customer WHERE ContactDetails = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $contactDetails);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        // Customer exists, fetch their CustomerID
        $stmt->bind_result($customerId);
        $stmt->fetch();
    } else {
        // New customer, insert into the order_management.customers table
        $query = "INSERT INTO order_management.customer (Name, ContactDetails) VALUES (?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $customerName, $contactDetails);
        $stmt->execute();
        $customerId = $stmt->insert_id;  // Get the new CustomerID
    }
    
    // Default StaffID (can be set to a specific staff ID if no staff is logged in)
    $staffId = 1; // Assuming "1" is a valid staff member ID in the staff table, or assign as needed
    
    // Process the cart and calculate total
    $cartItems = $_SESSION['cart'];
    $totalAmount = 0;
    foreach ($cartItems as $item) {
        $totalAmount += $item['Price'] * $item['Quantity'];
    }
    
    // Insert the order into the order table
    $orderQuery = "INSERT INTO `order` (CustomerID, StaffID, OrderDate, TotalAmount) VALUES (?, ?, NOW(), ?)";
    $stmt = $conn->prepare($orderQuery);
    $stmt->bind_param("iis", $customerId, $staffId, $totalAmount); // Use staff ID here (set to default or real staff)
    $stmt->execute();
    $orderId = $conn->insert_id; // Get the last inserted OrderID
    
    // Insert order details for each item in the cart
    foreach ($cartItems as $item) {
        $subtotal = $item['Price'] * $item['Quantity'];
        $orderDetailsQuery = "INSERT INTO orderdetails (OrderID, MenuItemID, Quantity, Subtotal) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($orderDetailsQuery);
        $stmt->bind_param("iiii", $orderId, $item['MenuItemID'], $item['Quantity'], $subtotal);
        $stmt->execute();
    }
    
    // Clear the cart after successful order processing
    $_SESSION['cart'] = [];
    
    // Redirect to the orders list or a success page
    header("Location: ../src/orders_list.php?status=success");
    exit;    
    
?>