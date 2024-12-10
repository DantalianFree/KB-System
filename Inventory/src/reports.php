<?php
include '../src/conn.php'; // Update with your actual connection file
session_start();

// Check user permissions
if ($_SESSION['usertype'] !== 'Manager' && $_SESSION['usertype'] !== 'Admin') {
    echo "You don't have access to this page.";
    exit;
}

// Get date range from form (if submitted)
$startDate = isset($_POST['start_date']) ? $_POST['start_date'] : date('Y-m-d', strtotime('-30 days'));
$endDate = isset($_POST['end_date']) ? $_POST['end_date'] : date('Y-m-d');

// Inventory Query (with date range)
$inventoryQuery = "SELECT ItemName, Category, Quantity, Unit, ReorderLevel, LastUpdated FROM inventory WHERE LastUpdated BETWEEN '$startDate' AND '$endDate'";
$inventoryResult = $conn->query($inventoryQuery);

// Order Query (with date range)
$orderQuery = "SELECT o.OrderID, o.OrderDate, o.Status, oi.QuantityOrdered, i.ItemName 
               FROM `order` o
               JOIN orderitem oi ON o.OrderID = oi.OrderID
               JOIN inventory i ON oi.InventoryID = i.InventoryID
               WHERE o.OrderDate BETWEEN '$startDate' AND '$endDate'";
$orderResult = $conn->query($orderQuery);

// Low Stock Query
$lowStockQuery = "SELECT ItemName, Quantity FROM inventory WHERE Quantity <= ReorderLevel";
$lowStockResult = $conn->query($lowStockQuery);
$lowStockItems = [];

if ($lowStockResult->num_rows > 0) {
    while ($row = $lowStockResult->fetch_assoc()) {
        $lowStockItems[] = $row;
    }
}

// Debugging: Check for errors
if (!$inventoryResult) {
    die("Inventory query error: " . $conn->error);
}

if (!$orderResult) {
    die("Order query error: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory and Usage Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .dashboard-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            text-align: center;
        }
        .navbar-nav.ml-auto {
            display: flex;
            align-items: center;
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
            visibility: hidden;
            opacity: 0;
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
        .navbar-brand {
            cursor: pointer;
            display: flex;
            align-items: center;
            font-size: 1.2rem;
        }
        .navbar-brand i {
            margin-right: 10px; 
        }
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
            display: none; 
        }
        .content {
            margin-top: 20px; 
            margin-left: 250px;  
            padding-top: 20px;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
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
                <li class="nav-item">
                    <a class="nav-link active" href="reports.php">Reports</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="supplierDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
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

<div class="container mt-5">
    <h2 class="text-center mb-4">Inventory and Order Report</h2>

    <!-- Date Range Filter -->
    <form method="POST" action="reports.php" class="mb-4">
        <div class="row">
            <div class="col-md-5">
                <input type="date" name="start_date" class="form-control" value="<?php echo htmlspecialchars($startDate); ?>" required>
            </div>
            <div class="col-md-5">
                <input type="date" name="end_date" class="form-control" value="<?php echo htmlspecialchars($endDate); ?>" required>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
        </div>
    </form>

    <!-- Inventory Report -->
    <h4>Inventory Summary</h4>
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Item Name</th>
                    <th>Category</th>
                    <th>Quantity</th>
                    <th>Unit</th>
                    <th>Reorder Level</th>
                    <th>Last Updated</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($inventoryResult->num_rows > 0): ?>
                    <?php while ($row = $inventoryResult->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['ItemName']); ?></td>
                            <td><?php echo htmlspecialchars($row['Category']); ?></td>
                            <td><?php echo htmlspecialchars($row['Quantity']); ?></td>
                            <td><?php echo htmlspecialchars($row['Unit']); ?></td>
                            <td><?php echo htmlspecialchars($row['ReorderLevel']); ?></td>
                            <td><?php echo htmlspecialchars($row['LastUpdated']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center">No inventory data found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Order Report -->
    <h4>Order Summary</h4>
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Order ID</th>
                    <th>Order Date</th>
                    <th>Status</th>
                    <th>Item Name</th>
                    <th>Quantity Ordered</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($orderResult->num_rows > 0): ?>
                    <?php while ($row = $orderResult->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['OrderID']); ?></td>
                            <td><?php echo htmlspecialchars($row['OrderDate']); ?></td>
                            <td><?php echo htmlspecialchars($row['Status']); ?></td>
                            <td><?php echo htmlspecialchars($row['ItemName']); ?></td>
                            <td><?php echo htmlspecialchars($row['QuantityOrdered']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center">No order data found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Optional JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>

<script>
    // Notification toggle functionality
    document.getElementById('notificationBell').addEventListener('click', function() {
        var notificationBox = document.getElementById('notificationBox');
        notificationBox.style.display = notificationBox.style.display === 'none' || notificationBox.style.display === '' ? 'block' : 'none';
    });

    // Sidebar toggle functionality
    var sidebar = document.querySelector('.sidebar');
    var sidebarToggler = document.querySelector('.sidebar-toggler');
    var closeSidebar = document.querySelector('.close-btn');

    sidebarToggler.addEventListener('click', function() {
        sidebar.classList.toggle('open');
    });

    closeSidebar.addEventListener('click', function() {
        sidebar.classList.remove('open');
    });
</script>

</body>
</html>
