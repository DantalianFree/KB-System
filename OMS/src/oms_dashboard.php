<?php 
include '../conn.php';
session_start();

if ($_SESSION['usertype'] !== 'Manager' && $_SESSION['usertype'] !== 'Admin') {
    echo "You don't have access to this page.";
    exit;
}

$totalOrdersQuery = "SELECT COUNT(*) as totalOrders FROM `order`";
$totalOrders = $conn->query($totalOrdersQuery)->fetch_assoc()['totalOrders'];

$totalRevenueQuery = "SELECT SUM(TotalAmount) as totalRevenue FROM `order`";
$totalRevenue = $conn->query($totalRevenueQuery)->fetch_assoc()['totalRevenue'] ?? 0;

$totalCustomersQuery = "SELECT COUNT(DISTINCT CustomerID) as totalCustomers FROM `order`";
$totalCustomers = $conn->query($totalCustomersQuery)->fetch_assoc()['totalCustomers'];

$recentOrderDateQuery = "SELECT MAX(OrderDate) as recentOrder FROM `order`";
$recentOrderDate = $conn->query($recentOrderDateQuery)->fetch_assoc()['recentOrder'];

$recentOrdersQuery = "SELECT OrderID, CustomerID, OrderDate, TotalAmount FROM `order` ORDER BY OrderDate DESC LIMIT 5";
$recentOrders = $conn->query($recentOrdersQuery);

$ordersByDateQuery = "SELECT DATE(OrderDate) as orderDate, SUM(TotalAmount) as dailyTotal 
                      FROM `order` 
                      GROUP BY DATE(OrderDate) 
                      ORDER BY orderDate ASC";
$ordersByDate = $conn->query($ordersByDateQuery);

$chartDates = [];
$chartTotals = [];
while ($row = $ordersByDate->fetch_assoc()) {
    $chartDates[] = $row['orderDate'];
    $chartTotals[] = $row['dailyTotal'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Dashboard</title>
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
            <i class="bi bi-list"></i> KB's StopOver OMS
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="order_menu.php">Menu</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="orders_list.php">Order List</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="order_reports.php">Reports</a>
                </li>
            </ul>
        </div>
        <div>
            <a href="oms_dashboard.php" class="btn btn-secondary btn-sm 
                <?php echo ($_SESSION['usertype'] == 'Staff') ? 'disabled-button' : ''; ?> "
                id="dashboardBtn" 
                <?php echo ($_SESSION['usertype'] == 'Staff') ? 'data-bs-toggle="modal" data-bs-target="#accessDeniedModal"' : ''; ?>>
                <?php echo ($_SESSION['usertype'] == 'Staff') ? 'Access Denied' : 'Dashboard'; ?>
            </a>
            <a href="../../process/log_out.php" class="btn btn-danger btn-sm">Logout</a>
        </div>
    </div>
</nav>

<?php if ($_SESSION['usertype'] === 'Manager' || $_SESSION['usertype'] === 'Admin'): ?>
    <div class="sidebar" id="sidebar">
        <span class="close-btn" id="closeBtn">&times;</span>
        <h4 class="text-center">KB's Stopover</h4>
        <a href="../../Inventory/src/dashboard.php">Inventory</a>
        <a href="oms_dashboard.php">OMS (Order Management System)</a>
        <a href="../../CRM/src/customer_list.php">CRM (Customer Relationship Management)</a>
    </div>
<?php endif; ?>

<div class="container mt-5">
    <h2 class="text-center mb-4">Order Dashboard</h2>
    <div class="row">
        <div class="col-md-3">
            <div class="dashboard-card">
                <h5>Total Orders</h5>
                <p class="display-6"><?php echo $totalOrders; ?></p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="dashboard-card">
                <h5>Total Revenue</h5>
                <p class="display-6">₱<?php echo number_format($totalRevenue, 2); ?></p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="dashboard-card">
                <h5>Total Customers</h5>
                <p class="display-6"><?php echo $totalCustomers; ?></p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="dashboard-card">
                <h5>Recent Order</h5>
                <p class="display-6"><?php echo $recentOrderDate ? $recentOrderDate : 'N/A'; ?></p>
            </div>
        </div>
    </div>

    <div class="row mt-5">
        <div class="col-md-8">
            <div class="dashboard-card">
                <h5>Recent Orders</h5>
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($order = $recentOrders->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $order['CustomerID']; ?></td>
                                <td><?php echo $order['OrderDate']; ?></td>
                                <td>₱<?php echo number_format($order['TotalAmount'], 2); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <a href="orders_list.php" class="btn btn-primary">View All Orders</a>
                <a href="order_reports.php" class="btn btn-primary">View Reports</a>
            </div>
        </div>

        <div class="col-md-4">
            <div class="dashboard-card">
                <h5>Order Totals by Date</h5>
                <canvas id="totalsChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
    const ctx = document.getElementById('totalsChart').getContext('2d');
    const totalsChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($chartDates); ?>,
            datasets: [{
                label: 'Daily Total (₱)',
                data: <?php echo json_encode($chartTotals); ?>,
                backgroundColor: '#007bff',
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false,
                }
            }
        }
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

