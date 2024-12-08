<?php
include '..\conn.php';  // Ensure this points to your connection file

session_start();

// Check if the user is logged in and has the correct user type
if ($_SESSION['usertype'] !== 'Manager' && $_SESSION['usertype'] !== 'Admin') {
    echo "You don't have access to this page.";
    exit;
}

// Pagination variables
$limit = 10; // Number of customers per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Get the current page number
$offset = ($page - 1) * $limit; // Calculate the offset

// Fetch all customers from the database with LIMIT and OFFSET
$query = "SELECT * FROM Customers ORDER BY CustomerID DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('ii', $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();

// Fetch total customers for pagination
$totalQuery = "SELECT COUNT(*) as total FROM Customers";
$totalResult = $conn->query($totalQuery);
$totalRows = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit); // Calculate the total number of pages

// Check if there are any customers in the database
if ($result->num_rows > 0) {
    $customers = $result->fetch_all(MYSQLI_ASSOC); // Fetch all customers as an associative array
} else {
    $customers = [];
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        /* Sidebar Styles */
        .sidebar {
            height: 100%;
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            background-color: #343a40;
            color: white;
            padding-top: 20px;
            transition: all 0.3s ease;
            z-index: 1000;
            visibility: hidden; /* Initially hidden */
            opacity: 0; /* Fade out when hidden */
        }

        .sidebar.open {
            visibility: visible;
            opacity: 1;
            width: 250px;
        }

        .sidebar a {
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            display: block;
            transition: background-color 0.2s;
        }

        .sidebar a:hover {
            background-color: #007bff;
        }

        /* Sidebar close button */
        .sidebar .close-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 24px;
            color: white;
            cursor: pointer;
            z-index: 1010;
        }

        /* Content area */
        .content {
            margin-left: 250px;  /* Space for the sidebar */
            padding-top: 20px;
        }

        /* Table Styles */
        .table-container {
            margin-top: 20px;
        }

        /* Fix for centering the table */
        .table-responsive {
            margin: 0 auto;
            max-width: 90%;  /* Ensures the table is responsive and centers itself */
        }

        .navbar-brand i {
            margin-right: 10px;  /* Space between the icon and the text */
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="#" id="navbarBrand">
            <i class="bi bi-list"></i> KB's StopOver CRM
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="customer_list.php">Customer List</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../forms/customer_reg.php">Customer Registration</a>
                </li>
            </ul>
        </div>
        <div>
            <a href="../../process/log_out.php" class="btn btn-danger btn-sm">Logout</a>
        </div>
    </div>
</nav>

<!-- Sidebar -->
<?php if ($_SESSION['usertype'] === 'Manager' || $_SESSION['usertype'] === 'Admin'): ?>
    <div class="sidebar" id="sidebar">
        <span class="close-btn" id="closeBtn">&times;</span>
        <h4 class="text-center">KB's Stopover</h4>
        <a href="../../Inventory/src/dashboard.php">Inventory</a>
        <a href="../../OMS/src/oms_dashboard.php">OMS (Order Management System)</a>
        <a href="customer_reg.php">CRM (Customer Relationship Management)</a>
    </div>
<?php endif; ?>

<!-- Main Content Area -->
<div class="content">
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
    <div class="table-responsive">
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
                                <!-- Trigger Modal -->
                                <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal" data-id="<?php echo $customer['CustomerID']; ?>" data-firstname="<?php echo $customer['FirstName']; ?>" data-lastname="<?php echo $customer['LastName']; ?>" data-email="<?php echo $customer['Email']; ?>" data-phone="<?php echo $customer['Phone']; ?>">Edit</button>

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
    </div>

    <!-- Pagination -->
    <nav aria-label="Customer pagination">
        <ul class="pagination justify-content-center">
            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                <a class="page-link" href="?page=<?php echo $page - 1; ?>" aria-label="Previous">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                <a class="page-link" href="?page=<?php echo $page + 1; ?>" aria-label="Next">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        </ul>
    </nav>
</div>

<!-- Bootstrap Modal for Editing -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="../process/edit_customer.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="customer_id" id="customer_id">
                    <div class="mb-3">
                        <label for="edit_firstname" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="edit_firstname" name="first_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_lastname" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="edit_lastname" name="last_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="edit_email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_phone" class="form-label">Phone Number</label>
                        <input type="text" class="form-control" id="edit_phone" name="phone">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bootstrap JS and Popper.js -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- JavaScript for Modal -->
<script>
    const editButtons = document.querySelectorAll('[data-bs-toggle="modal"]');
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            document.getElementById('customer_id').value = this.getAttribute('data-id');
            document.getElementById('edit_firstname').value = this.getAttribute('data-firstname');
            document.getElementById('edit_lastname').value = this.getAttribute('data-lastname');
            document.getElementById('edit_email').value = this.getAttribute('data-email');
            document.getElementById('edit_phone').value = this.getAttribute('data-phone');
        });
    });

    // Toggle sidebar visibility when the navbar brand is clicked
    document.getElementById('navbarBrand').addEventListener('click', function() {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('open');
    });

    // Close sidebar when the close button is clicked
    document.getElementById('closeBtn').addEventListener('click', function() {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.remove('open');
    });
</script>

</body>
</html>
