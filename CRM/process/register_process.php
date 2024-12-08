<?php
include '..\conn.php';  // Ensure this points to your connection file

session_start();

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the form input data
    $firstName = $_POST['first_name'];
    $lastName = $_POST['last_name'];
    $email = $_POST['email'];
    $phone = isset($_POST['phone']) ? $_POST['phone'] : null; // Phone is optional

    // Validate the data (simple example)
    if (empty($firstName) || empty($lastName) || empty($email)) {
        $_SESSION['error'] = "First Name, Last Name, and Email are required!";
        header("Location: ../forms/customer_reg.php"); // Redirect back to the registration form
        exit();
    }

    // Sanitize input (for security reasons)
    $firstName = htmlspecialchars($firstName);
    $lastName = htmlspecialchars($lastName);
    $email = htmlspecialchars($email);
    $phone = htmlspecialchars($phone);

    // Check if email already exists
    $emailCheckQuery = "SELECT * FROM Customers WHERE Email = ?";
    $stmt = $conn->prepare($emailCheckQuery);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['error'] = "Email is already registered!";
        header("Location: ../forms/customer_reg.php");
        exit();
    }

    // Prepare SQL to insert the customer into the database
    $sql = "INSERT INTO Customers (FirstName, LastName, Email, Phone, RegistrationDate, TotalLoyaltyPoints) 
            VALUES (?, ?, ?, ?, NOW(), 0)";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        // Bind the parameters and execute the query
        $stmt->bind_param("ssss", $firstName, $lastName, $email, $phone);

        if ($stmt->execute()) {
            // If the customer is successfully inserted
            $_SESSION['success'] = "Customer successfully registered!";
            header("Location: ../forms/customer_reg.php"); // Redirect back to the form
        } else {
            // If there was an error inserting into the database
            $_SESSION['error'] = "Error: " . $stmt->error;
            header("Location: ../forms/customer_reg.php");
        }
    } else {
        $_SESSION['error'] = "Error preparing the statement!";
        header("Location: ../forms/customer_reg.php");
    }

    // Close the statement and the connection
    $stmt->close();
    $conn->close();
}
?>
