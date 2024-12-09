<?php 
include '..\src\conn.php';
session_start();

if ($_SESSION['usertype'] !== 'Manager' && $_SESSION['usertype'] !== 'Admin') {
    echo "You don't have access to this page.";
    exit;
}

$sql = "SELECT SupplierID, SupplierName, ContactNumber, email, Address, LeadTime, LastUpdated FROM supplier";
$result = $conn->query($sql);

$lowStockQuery = "SELECT * FROM inventory WHERE Quantity <= ReorderLevel";
$lowStockResult = $conn->query($lowStockQuery);
$lowStockItems = $lowStockResult->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .table-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        .notification-badge {
            position: absolute;
            top: 0px;
            right: 0px;
            background-color: red;
            color: white;
            border-radius: 50%;
            padding: 1px 7px;
            font-size: 12px;
        }
        .notification-box {
            position: absolute;
            top: 40px;
            right: 0;
            background-color: white;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 300px;
            border-radius: 5px;
            padding: 10px;
            display: none;
            max-height: 300px;
            overflow-y: auto;
        }
        .notification-box ul {
            list-style-type: none;
            padding: 0;
        }
        .notification-box ul li {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        .notification-box ul li:last-child {
            border-bottom: none;
        }
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
        .navbar-brand i {
            margin-right: 10px;  /* Space between the icon and the text */
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <!-- Add the ID for the sidebar toggle -->
        <a class="navbar-brand" href="#" id="navbarBrand">
            <i class="bi bi-list"></i>KB's Stopover</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="inventory_list.php">Inventory</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle active" href="#" id="supplierDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Suppliers
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="supplierDropdown">
                        <li><a class="dropdown-item" href="supplier_list.php">Supplier List</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="supplier_orders.php">Supplier Orders</a></li>
                    </ul>
                </li>
            </ul>
        </div>
        <div class="navbar-nav ms-auto position-relative">
            <li class="nav-item">
                <a class="nav-link position-relative" href="javascript:void(0);" id="notificationBell">
                    <i class="bi bi-bell-fill" style="font-size: 1.5rem;"></i>
                    <?php if (count($lowStockItems) > 0): ?>
                        <span class="notification-badge"><?php echo count($lowStockItems); ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <div class="notification-box" id="notificationBox">
                <h5>Low Stock Alerts</h5>
                <ul>
                    <?php if (count($lowStockItems) > 0): ?>
                        <?php foreach ($lowStockItems as $item): ?>
                            <li><?php echo $item['ItemName']; ?> (<?php echo $item['Quantity']; ?> remaining)</li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li>No low stock items</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</nav>

<?php if ($_SESSION['usertype'] === 'Manager' || $_SESSION['usertype'] === 'Admin'): ?>
    <div class="sidebar" id="sidebar">
        <span class="close-btn" id="closeBtn">&times;</span>
        <h4 class="text-center">KB's Stopover</h4>
        <a href="../../Inventory/src/dashboard.php">Inventory</a>
        <a href="../../OMS/src/oms_dashboard.php">OMS (Order Management System)</a>
        <a href="../../CRM/src/customer_list.php">CRM (Customer Relationship Management)</a>
    </div>
<?php endif; ?>

    <div class="container mt-5">
        <div class="row">
            <div class="col-12">
                <div class="table-container">
                    <h2 class="text-center mb-4">Supplier List</h2>
                    <a href="..\Forms\supplier_form.php" class="btn btn-primary mb-3">Add New Supplier</a>
                    <table class="table table-bordered table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>Supplier Name</th>
                                <th>Contact Number</th>
                                <th>Email</th>
                                <th>Address</th>
                                <th>Lead Time</th>
                                <th>Last Updated</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $row['SupplierName']; ?></td>
                                        <td><?php echo $row['ContactNumber']; ?></td>
                                        <td><?php echo $row['email']; ?></td>
                                        <td><?php echo $row['Address']; ?></td>
                                        <td><?php echo $row['LeadTime']; ?> days</td>
                                        <td><?php echo $row['LastUpdated']; ?></td>
                                        <td>
                                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal" 
                                                data-id="<?php echo $row['SupplierID']; ?>" 
                                                data-name="<?php echo $row['SupplierName']; ?>"
                                                data-contact="<?php echo $row['ContactNumber']; ?>"
                                                data-email="<?php echo $row['email']; ?>"
                                                data-address="<?php echo $row['Address']; ?>"
                                                data-leadtime="<?php echo $row['LeadTime']; ?>">
                                                Edit
                                            </button>
                                            <a href="../process/delete_supplier.php?id=<?php echo $row['SupplierID']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?');">Delete</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">No suppliers found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Supplier</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="../process/edit_supplier.php" method="post">
                    <div class="modal-body">
                        <input type="hidden" id="editSupplierID" name="supplier_id">
                        <div class="mb-3">
                            <label for="editSupplierName" class="form-label">Supplier Name:</label>
                            <input type="text" class="form-control" id="editSupplierName" name="supplier_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="editContactNumber" class="form-label">Contact Number:</label>
                            <input type="text" class="form-control" id="editContactNumber" name="contact_number" required>
                        </div>
                        <div class="mb-3">
                            <label for="editEmail" class="form-label">Email:</label>
                            <input type="email" class="form-control" id="editEmail" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="editAddress" class="form-label">Address:</label>
                            <textarea class="form-control" id="editAddress" name="address" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="editLeadTime" class="form-label">Lead Time (days):</label>
                            <input type="number" class="form-control" id="editLeadTime" name="lead_time" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('notificationBell').addEventListener('click', function() {
            const notificationBox = document.getElementById('notificationBox');
            notificationBox.style.display = notificationBox.style.display === 'block' ? 'none' : 'block';
        });

        const editModal = document.getElementById('editModal');
        editModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            document.getElementById('editSupplierID').value = button.getAttribute('data-id');
            document.getElementById('editSupplierName').value = button.getAttribute('data-name');
            document.getElementById('editContactNumber').value = button.getAttribute('data-contact');
            document.getElementById('editEmail').value = button.getAttribute('data-email');
            document.getElementById('editAddress').value = button.getAttribute('data-address');
            document.getElementById('editLeadTime').value = button.getAttribute('data-leadtime');
        });

        document.getElementById('navbarBrand').addEventListener('click', function() {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('open');
        });

        document.getElementById('closeBtn').addEventListener('click', function() {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.remove('open');
        });
    </script>
</body>
</html>
