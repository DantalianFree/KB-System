<?php
include '..\conn.php';  // Fixed the include path
session_start();

// Check user type
if ($_SESSION['usertype'] !== 'Manager' && $_SESSION['usertype'] !== 'Admin') {
    echo "You don't have access to this page.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Registration</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .form-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            text-align: center;
        }

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

        /* Navbar Brand Styles */
        .navbar-brand {
            cursor: pointer;
            display: flex;
            align-items: center;
            font-size: 1.2rem;
        }

        .navbar-brand i {
            margin-right: 10px;  /* Space between the icon and the text */
        }

        /* Close Button for Sidebar */
        .sidebar .close-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 24px;
            color: white;
            cursor: pointer;
            z-index: 1010;
        }
        .sidebar-toggler {
            display: none;  /* Hide this, as the navbar brand will toggle the sidebar */
        }
        .content {
            margin-top: 20px; /* Adjust this value as needed to avoid overlap */
            margin-left: 250px;  /* Keep space for the sidebar */
            padding-top: 20px;
        }
    </style>
</head>
<body>

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
                    <a class="nav-link" href="../src/customer_list.php">Customer List</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="customer_reg.php">Customer Registration</a>
                </li>
            </ul>
        </div>
        <div>
            <a href="../../process/log_out.php" class="btn btn-danger btn-sm">Logout</a>
        </div>
    </div>
</nav>

<?php if ($_SESSION['usertype'] === 'Manager' || $_SESSION['usertype'] === 'Admin'): ?>
    <div class="sidebar" id="sidebar">
        <span class="close-btn" id="closeBtn">&times;</span>
        <h4 class="text-center">KB's Stopover</h4>
        <a href="../../Inventory/src/dashboard.php">Inventory</a>
        <a href="../../OMS/src/oms_dashboard.php">OMS (Order Management System)</a>
        <a href="customer_reg.php">CRM (Customer Relationship Management)</a>
    </div>
<?php endif; ?>

<div class="container mt-5">
    <h2 class="text-center mb-4">Customer Registration</h2>
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="form-card">
                <form action="../process/register_process.php" method="POST">
                    <div class="mb-3">
                        <label for="first_name" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="last_name" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="text" class="form-control" id="phone" name="phone" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Register Customer</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS and Popper.js -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- JavaScript for Sidebar Toggle -->
<script>
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
