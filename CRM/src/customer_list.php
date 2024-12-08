<?php
include '..\conn.php';  // Ensure this points to your connection file

session_start();

// Check if the user is logged in and has the correct user type
if ($_SESSION['usertype'] !== 'Manager' && $_SESSION['usertype'] !== 'Admin') {
    echo "You don't have access to this page.";
    exit;
}

// Fetch all customers from the database
$query = "SELECT * FROM Customers ORDER BY CustomerID DESC";
$result = $conn->query($query);

// Check if there are any customers in the database
if ($result->num_rows > 0) {
    $customers = $result->fetch_all(MYSQLI_ASSOC); // Fetch all customers as an associative array
} else {
    $customers = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        /* Custom styles here */
        .content {
            margin-left: 250px;  /* Keep space for the sidebar */
        }

        .table-container {
            margin-top: 20px;
        }
    </style>
</head>
<body>

<!-- Navbar and Sidebar Code -->

<div class="content container mt-5 table-container">
    <h2 class="text-center mb-4">Customer List</h2>

    <!-- Display success or error messages -->
    <?php
    if (isset($_SESSION['error'])) {
        echo '<div class="alert alert-danger text-center" role="alert">' . $_SESSION['error'] . '</div>';
        unset($_SESSION['error']);
    }
    if (isset($_SESSION['success'])) {
        echo '<div class="alert alert-success text-center" role="alert">' . $_SESSION['success'] . '</div>';
        unset($_SESSION['success']);
    }
    ?>

    <!-- Table to display customers -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th scope="col">Customer ID</th>
                <th scope="col">First Name</th>
                <th scope="col">Last Name</th>
                <th scope="col">Email</th>
                <th scope="col">Phone</th>
                <th scope="col">Registration Date</th>
                <th scope="col">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($customers)): ?>
                <?php foreach ($customers as $customer): ?>
                    <tr>
                        <td><?php echo $customer['CustomerID']; ?></td>
                        <td><?php echo $customer['FirstName']; ?></td>
                        <td><?php echo $customer['LastName']; ?></td>
                        <td><?php echo $customer['Email']; ?></td>
                        <td><?php echo $customer['Phone'] ?: 'N/A'; ?></td>
                        <td><?php echo $customer['RegistrationDate']; ?></td>
                        <td>
                            <a href="edit_customer.php?customer_id=<?php echo $customer['CustomerID']; ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="delete_customer.php?customer_id=<?php echo $customer['CustomerID']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this customer?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center">No customers found</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Pagination (if needed) -->
    <?php if ($result->num_rows > 10): ?>
        <nav aria-label="Customer pagination">
            <ul class="pagination justify-content-center">
                <li class="page-item">
                    <a class="page-link" href="#" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
                <li class="page-item"><a class="page-link" href="#">1</a></li>
                <li class="page-item"><a class="page-link" href="#">2</a></li>
                <li class="page-item">
                    <a class="page-link" href="#" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<!-- Bootstrap JS and Popper.js -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
