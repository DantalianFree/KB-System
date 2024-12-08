<?php 
include '..\src\conn.php';

session_start();

if ($_SESSION['usertype'] !== 'Manager' && $_SESSION['usertype'] !== 'Admin') {
    echo "You don't have access to this page.";
    exit;
}

$totalItemsQuery = "SELECT COUNT(*) as totalItems FROM inventory";
$totalItemsResult = $conn->query($totalItemsQuery);
$totalItems = $totalItemsResult->fetch_assoc()['totalItems'];

$lowStockQuery = "SELECT * FROM inventory WHERE Quantity <= ReorderLevel";
$lowStockResult = $conn->query($lowStockQuery);
$lowStockItems = $lowStockResult->fetch_all(MYSQLI_ASSOC);

$categoriesQuery = "SELECT Category, COUNT(*) as count FROM inventory GROUP BY Category";
$categoriesResult = $conn->query($categoriesQuery);

$activities = [
    "New order received from Supplier A.",
    "Stock updated for Item X.",
    "Order #123 has been completed."
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        /* Sidebar Toggler Icon */
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
            <!-- Add the ID for the sidebar toggle -->
            <a class="navbar-brand" href="#" id="navbarBrand">
                <i class="bi bi-list"></i>KB's Stopover</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="inventory_list.php">Inventory</a>
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

    <?php if ($_SESSION['usertype'] === 'Manager' || $_SESSION['usertype'] === 'Admin'): ?>
    <div class="sidebar" id="sidebar">
        <span class="close-btn" id="closeBtn">&times;</span>
        <h4 class="text-center">KB's Stopover</h4>
        <a href="../../Inventory/src/dashboard.php">Inventory</a>
        <a href="../../OMS/src/oms_dashboard.php">OMS (Order Management System)</a>
    </div>
    <?php endif; ?>

    <div class="container mt-5">
        <h2 class="text-center mb-4">Dashboard</h2>
        <div class="row">
            <div class="col-md-4">
                <div class="dashboard-card">
                    <h4>Total Items</h4>
                    <p class="display-4"><?php echo $totalItems; ?></p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="dashboard-card">
                    <h4>Low Stock Items</h4>
                    <p class="display-4"><?php echo count($lowStockItems); ?></p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="dashboard-card">
                    <h4>Categories</h4>
                    <ul class="list-unstyled">
                        <?php while ($category = $categoriesResult->fetch_assoc()): ?>
                            <li><?php echo $category['Category']; ?>: <?php echo $category['count']; ?></li>
                        <?php endwhile; ?>
                    </ul>
                </div>
            </div>
        </div>
        <div class="row mt-5">
            <div class="col-md-6">
                <div class="dashboard-card">
                    <h4>Low Stock Item Categories</h4>
                    <canvas id="lowStockChart"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <div class="dashboard-card">
                    <h4>Recent Activities</h4>
                    <ul id="activityFeed" class="list-unstyled">
                        <?php echo implode('', array_map(fn($activity) => "<li>$activity</li>", $activities)); ?>
                    </ul>
                </div>
            </div>
        </div>
        <div class="row mt-5">
            <div class="col-md-4">
                <div class="dashboard-card">
                    <h4>Quick Actions</h4>
                    <a href="..\Forms\inventory_form.php" class="btn btn-success mb-2">Add New Item</a>
                    <a href="supplier_orders.php" class="btn btn-primary mb-2">Place Order</a>
                    <a href="inventory_list.php" class="btn btn-warning mb-2">View Inventory</a>
                </div>
            </div>
            <div class="col-md-8">
                <div class="dashboard-card">
                    <h4>Inventory Overview</h4>
                    <table class="table table-striped table-sm">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Category</th>
                                <th>Stock</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $inventoryOverviewQuery = "SELECT ItemName, Category, Quantity FROM inventory LIMIT 5";
                            $inventoryOverviewResult = $conn->query($inventoryOverviewQuery);
                            while ($row = $inventoryOverviewResult->fetch_assoc()) {
                                echo "<tr><td>{$row['ItemName']}</td><td>{$row['Category']}</td><td>{$row['Quantity']}</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                    <a href="inventory_list.php" class="btn btn-primary btn-sm">View Full Inventory</a>
                </div>
            </div>
        </div>
    </div>

<script>
    const notificationBell = document.getElementById('notificationBell');
    const notificationBox = document.getElementById('notificationBox');
    const sidebar = document.getElementById('sidebar');
    const navbarBrand = document.getElementById('navbarBrand');
    const closeBtn = document.getElementById('closeBtn');

    notificationBell.addEventListener('click', () => {
        notificationBox.style.display = (notificationBox.style.display === 'block') ? 'none' : 'block';
    });

    navbarBrand.addEventListener('click', () => {
        sidebar.classList.toggle('open');
    });

    closeBtn.addEventListener('click', () => {
        sidebar.classList.remove('open');
    });
</script>

</body>
</html>
