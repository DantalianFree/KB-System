<?php
include '..\conn.php';  // Ensure this path is correct

session_start();

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Fetch user data by email
    $query = "SELECT * FROM Customers WHERE Email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the email exists
    if ($result->num_rows > 0) {
        $customer = $result->fetch_assoc();
        // Verify password
        if (password_verify($password, $customer['Password'])) {
            // Start session and log the user in
            $_SESSION['customer_id'] = $customer['CustomerID'];  // Store CustomerID in session
            $_SESSION['usertype'] = 'Customer';
            $_SESSION['email'] = $customer['Email'];
            header('Location: feedback_qr.php');  // Redirect to feedback page
            exit();
        } else {
            $_SESSION['error'] = 'Incorrect password.';
        }
    } else {
        $_SESSION['error'] = 'No account found with this email.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2 class="text-center mb-4">Customer Login</h2>
    <?php
    if (isset($_SESSION['error'])) {
        echo '<div class="alert alert-danger text-center">' . $_SESSION['error'] . '</div>';
        unset($_SESSION['error']);
    }
    ?>
    <form action="feedback_form.php" method="POST">
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>

    <div class="mt-4 text-center">
        <p>Don't have an account yet? <a href="registration_form.php" class="btn btn-link">Register now for loyalty points!</a></p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
