<?php
include '../conn.php';
session_start();

// Ensure only Manager and Admin can access
if ($_SESSION['usertype'] !== 'Manager' && $_SESSION['usertype'] !== 'Admin') {
    echo "You don't have access to this page.";
    exit;
}

// Fetch reports based on date filters (if set)
$dateFilter = isset($_GET['start_date']) && isset($_GET['end_date']);
if ($dateFilter) {
    $startDate = $_GET['start_date'];
    $endDate = $_GET['end_date'];
    $stmt = $conn->prepare("SELECT * FROM order_reports WHERE GeneratedDate BETWEEN ? AND ? ORDER BY GeneratedDate DESC");
    $stmt->bind_param('ss', $startDate, $endDate);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM order_reports ORDER BY GeneratedDate DESC");
}

// Handle report generation
if (isset($_POST['generate_report'])) {
    // Fetch orders that are not yet in reports
    $ordersQuery = $conn->query("
        SELECT OrderID, OrderDate, TotalAmount
        FROM `order`
        WHERE OrderID NOT IN (SELECT OrderID FROM order_reports)
    ");

    // Insert new reports for each order
    while ($order = $ordersQuery->fetch_assoc()) {
        $orderId = $order['OrderID'];
        $generatedDate = date('Y-m-d H:i:s');
        $summary = "Order Date: {$order['OrderDate']}, Total Amount: PHP " . number_format($order['TotalAmount'], 2);

        $stmt = $conn->prepare("INSERT INTO order_reports (OrderID, GeneratedDate, Summary) VALUES (?, ?, ?)");
        $stmt->bind_param('iss', $orderId, $generatedDate, $summary);
        $stmt->execute();
    }

    echo "<script>alert('Reports generated successfully.'); window.location.href = 'order_reports.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">KB's StopOver OMS</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link" href="order_menu.php">Menu</a></li>
                <li class="nav-item"><a class="nav-link" href="orders_list.php">Order List</a></li>
                <li class="nav-item"><a class="nav-link active" href="order_reports.php">Reports</a></li>
            </ul>
        </div>
        <div>
            <a href="oms_dashboard.php" class="btn btn-secondary btn-sm 
                <?php echo ($_SESSION['usertype'] == 'Staff') ? 'disabled-button' : ''; ?>"
                id="dashboardBtn" 
                <?php echo ($_SESSION['usertype'] == 'Staff') ? 'data-bs-toggle="modal" data-bs-target="#accessDeniedModal"' : ''; ?>>
                <?php echo ($_SESSION['usertype'] == 'Staff') ? 'Access Denied' : 'Dashboard'; ?>
            </a>
            <a href="../../process/log_out.php" class="btn btn-danger btn-sm">Logout</a>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <h2 class="text-center">Order Reports</h2>

    <!-- Generate Report Button -->
    <form method="POST" class="text-end mb-3">
        <button type="submit" name="generate_report" class="btn btn-success">Generate Report</button>
    </form>

    <!-- Date Filters -->
    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-5">
            <label for="start_date" class="form-label">Start Date</label>
            <input type="date" name="start_date" id="start_date" class="form-control" value="<?php echo htmlspecialchars($_GET['start_date'] ?? ''); ?>">
        </div>
        <div class="col-md-5">
            <label for="end_date" class="form-label">End Date</label>
            <input type="date" name="end_date" id="end_date" class="form-control" value="<?php echo htmlspecialchars($_GET['end_date'] ?? ''); ?>">
        </div>
        <div class="col-md-2 align-self-end">
            <button type="submit" class="btn btn-primary w-100">Filter</button>
        </div>
    </form>

    <!-- Reports Table -->
    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>Order ID</th>
                <th>Generated Date</th>
                <th>Summary</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['OrderID']); ?></td>
                        <td><?php echo htmlspecialchars($row['GeneratedDate']); ?></td>
                        <td><?php echo htmlspecialchars($row['Summary']); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="text-center">No reports found for the selected date range.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>
