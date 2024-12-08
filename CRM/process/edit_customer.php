<?php
include '..\conn.php';  // Make sure the path is correct

session_start();

// Check if the user is logged in and has the correct user type
if ($_SESSION['usertype'] !== 'Manager' && $_SESSION['usertype'] !== 'Admin') {
    echo "You don't have access to this page.";
    exit;
}

// Check if form data is set
if (isset($_POST['customer_id'], $_POST['first_name'], $_POST['last_name'], $_POST['email'])) {
    // Sanitize and store the data
    $customerId = (int)$_POST['customer_id'];
    $firstName = $conn->real_escape_string($_POST['first_name']);
    $lastName = $conn->real_escape_string($_POST['last_name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = isset($_POST['phone']) ? $conn->real_escape_string($_POST['phone']) : NULL;

    // Update query
    $query = "UPDATE Customers SET FirstName = ?, LastName = ?, Email = ?, Phone = ? WHERE CustomerID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ssssi', $firstName, $lastName, $email, $phone, $customerId);

    // Execute the update query
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Customer updated successfully!';
    } else {
        $_SESSION['error'] = 'Failed to update customer. Please try again.';
    }

    $stmt->close();
} else {
    $_SESSION['error'] = 'Invalid request. Please try again.';
}

header('Location: ../src/customer_list.php');  // Redirect back to the customer list
exit;
?>
