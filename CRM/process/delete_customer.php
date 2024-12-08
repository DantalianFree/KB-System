<?php
include '..\conn.php';  // Make sure the path is correct

session_start();

// Check if the user is logged in and has the correct user type
if ($_SESSION['usertype'] !== 'Manager' && $_SESSION['usertype'] !== 'Admin') {
    echo "You don't have access to this page.";
    exit;
}

// Check if the customer_id is provided in the query string
if (isset($_GET['customer_id'])) {
    $customerId = (int)$_GET['customer_id']; // Cast to integer to avoid SQL injection

    // Delete query
    $query = "DELETE FROM Customers WHERE CustomerID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $customerId);

    // Execute the delete query
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Customer deleted successfully!';
    } else {
        $_SESSION['error'] = 'Failed to delete customer. Please try again.';
    }

    $stmt->close();
} else {
    $_SESSION['error'] = 'Invalid request. Customer ID is missing.';
}

header('Location: ../src/customer_list.php');  // Redirect back to the customer list
exit;
?>
