<?php
include '../conn.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['usertype'])) {
    echo "You are not logged in.";
    exit;
}

// Fetch search and sorting parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'OrderDate';
$order = isset($_GET['order']) ? $_GET['order'] : 'DESC';

// Pagination settings
$limit = 10; // Number of rows per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Build the query
$whereClause = $search ? "WHERE o.OrderID LIKE '%$search%' OR o.CustomerID LIKE '%$search%'" : '';
$orderByClause = "ORDER BY $sort $order";

$totalOrdersQuery = "SELECT COUNT(*) as total FROM `order` o $whereClause";
$totalOrders = $conn->query($totalOrdersQuery)->fetch_assoc()['total'];

$ordersQuery = "
    SELECT 
        o.OrderID, 
        o.CustomerID, 
        o.OrderDate, 
        (SELECT SUM(od.Subtotal) FROM orderdetails od WHERE od.OrderID = o.OrderID) AS TotalAmount
    FROM `order` o 
    $whereClause 
    $orderByClause 
    LIMIT $limit OFFSET $offset";

$ordersResult = $conn->query($ordersQuery);

// Total pages calculation
$totalPages = ceil($totalOrders / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        table {
            border-collapse: collapse;
        }
        th, td {
            border: none;
        }
        a {
            color: inherit;
            text-decoration: none;
        }

        .pagination a {
            margin: 0 5px;
        }

        .sort-icon {
            font-size: 0.8rem;
            margin-left: 5px;
        }
        .disabled-button {
            pointer-events: none;
            opacity: 0.5;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="order_menu.php">KB's StopOver OMS</a>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="order_menu.php">Menu</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="orders_list.php">Order List</a>
                </li>
                <?php if ($_SESSION['usertype'] !== 'Staff'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="order_reports.php">Reports</a>
                    </li>
                <?php endif; ?>
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

<div class="container mt-4">
    <h2 class="text-center mb-4">Orders List</h2>
    <form method="GET" class="mb-3">
        <div class="input-group">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" class="form-control" placeholder="Search by Order ID or Customer ID">
            <button type="submit" class="btn btn-primary">Search</button>
        </div>
    </form>
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>
                    <a href="?sort=OrderID&order=<?php echo $order === 'ASC' ? 'DESC' : 'ASC'; ?>">
                        Order ID <?php if ($sort === 'OrderID') echo $order === 'ASC' ? '↑' : '↓'; ?>
                    </a>
                </th>
                <th>
                    <a href="?sort=CustomerID&order=<?php echo $order === 'ASC' ? 'DESC' : 'ASC'; ?>">
                        Customer ID <?php if ($sort === 'CustomerID') echo $order === 'ASC' ? '↑' : '↓'; ?>
                    </a>
                </th>
                <th>
                    <a href="?sort=OrderDate&order=<?php echo $order === 'ASC' ? 'DESC' : 'ASC'; ?>">
                        Order Date <?php if ($sort === 'OrderDate') echo $order === 'ASC' ? '↑' : '↓'; ?>
                    </a>
                </th>
                <th>
                    <a href="?sort=TotalAmount&order=<?php echo $order === 'ASC' ? 'DESC' : 'ASC'; ?>">
                        Total Amount <?php if ($sort === 'TotalAmount') echo $order === 'ASC' ? '↑' : '↓'; ?>
                    </a>
                </th>
            </tr>
        </thead>
        <tbody>
            <?php if ($ordersResult->num_rows > 0): ?>
                <?php while ($row = $ordersResult->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['OrderID']; ?></td>
                        <td><?php echo $row['CustomerID']; ?></td>
                        <td><?php echo $row['OrderDate']; ?></td>
                        <td>₱<?php echo number_format($row['TotalAmount'], 2); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="text-center">No orders found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <!-- Pagination -->
    <nav>
        <ul class="pagination justify-content-center">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>&search=<?php echo $search; ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
</div>

<!-- Modal para sa Staff kung mag try sila access sa dashboard -->
<div class="modal fade" id="accessDeniedModal" tabindex="-1" aria-labelledby="accessDeniedModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="accessDeniedModalLabel">Access Denied</h5>
      </div>
      <div class="modal-body">
        You do not have access to the Dashboard.
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Initialize the modal for the staff user when they try to access the dashboard button
    var dashboardBtn = document.getElementById('dashboardBtn');
    if (dashboardBtn) {
        dashboardBtn.addEventListener('click', function(event) {
            if (<?php echo $_SESSION['usertype'] == 'Staff' ? 'true' : 'false'; ?>) {
                event.preventDefault();  // Prevent the default action
                var myModal = new bootstrap.Modal(document.getElementById('accessDeniedModal'));
                myModal.show();  // Show the modal
            }
        });
    }
</script>

</body>
</html>
