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
        .navbar-nav.ml-auto {
                display: flex;
                align-items: center;
            }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="oms_dashboard.php">KB's StopOver OMS</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="oms_dashboard.php">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="orders_list.php">Order List</a>
                </li>
            </ul>
        </div>
        <div>
            <a href="../../process/log_out.php" class="btn btn-danger btn-sm">Logout</a>
        </div>
    </div>
</nav>

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
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
