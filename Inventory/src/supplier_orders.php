<?php
// Database connection
include '../src/conn.php';

$lowStockQuery = "SELECT * FROM inventory WHERE Quantity <= ReorderLevel";
$lowStockResult = $conn->query($lowStockQuery);
$lowStockItems = $lowStockResult->fetch_all(MYSQLI_ASSOC);

// Fetch orders with supplier names
$query = "SELECT o.OrderID, o.OrderDate, o.Status, s.SupplierName 
          FROM `order` o 
          JOIN supplier s ON o.SupplierID = s.SupplierID 
          ORDER BY o.OrderDate DESC";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
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
        .btn-primary {
            background: linear-gradient(45deg, #007bff, #00aaff);
            border: none;
        }

        .btn-primary:hover {
            background: linear-gradient(45deg, #00aaff, #007bff);
        }

        .modal-header, .modal-footer {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">KB's Stopover</a>
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
                            <li><a class="dropdown-item" href="..\src\supplier_list.php">Supplier List</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="supplier_orders.php">Supplier Orders</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
            <div class="navbar-nav ms-auto position-relative">
                <li class="nav-item">
                    <a class="nav-link position-relative" href="javascript:void(0)" id="notificationBell">
                        <i class="bi bi-bell-fill" style="font-size: 1.5rem;"></i>
                        <?php if(count($lowStockItems) > 0): ?>
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
        <h1 class="mb-4">Supplier Orders</h1>
        <?php if ($result->num_rows > 0): ?>
            <table class="table table-bordered table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Supplier Name</th>
                        <th>Order Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['SupplierName']; ?></td>
                            <td><?php echo $row['OrderDate']; ?></td>
                            <td>
                                <span class="badge text-bg-<?php 
                                    echo $row['Status'] == 'Pending' ? 'warning' : 
                                         ($row['Status'] == 'Completed' ? 'success' : 'danger'); ?>">
                                    <?php echo $row['Status']; ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($row['Status'] == 'Pending'): ?>
                                    <!-- Trigger Cancel Modal -->
                                    <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#cancelOrderModal" data-order-id="<?php echo $row['OrderID']; ?>">
                                        Cancel
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-secondary btn-sm" disabled>Cancel</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-warning">No orders found.</div>
        <?php endif; ?>

        <!-- Ordering Form -->
        <h2 class="mt-5">Place New Order</h2>
        <form action="..\Forms\add_order.php" method="POST">
            <div class="mb-3">
                <label for="supplier" class="form-label">Supplier</label>
                <select class="form-select" id="supplier" name="supplier_id" required>
                    <option value="" selected disabled>Choose a supplier</option>
                    <?php
                    // Fetch suppliers from the database
                    $supplierQuery = "SELECT SupplierID, SupplierName FROM supplier";
                    $supplierResult = $conn->query($supplierQuery);
                    while ($supplier = $supplierResult->fetch_assoc()) {
                        echo "<option value='{$supplier['SupplierID']}'>{$supplier['SupplierName']}</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="product" class="form-label">Product</label>
                <input type="text" class="form-control" id="product" name="product" required>
            </div>
            <div class="mb-3">
                <label for="quantity" class="form-label">Quantity</label>
                <input type="number" class="form-control" id="quantity" name="quantity" min="1" required>
            </div>
            <button type="submit" class="btn btn-primary">Place Order</button>
        </form>
    </div>

    <!-- Cancel Order Modal -->
    <div class="modal fade" id="cancelOrderModal" tabindex="-1" aria-labelledby="cancelOrderModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cancelOrderModalLabel">Cancel Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to cancel this order?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a href="#" id="cancelOrderLink" class="btn btn-danger">Cancel Order</a>
                </div>
            </div>
        </div>
    </div>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Set the correct order ID for the cancel action in the modal
    var cancelButtons = document.querySelectorAll('[data-bs-toggle="modal"]');
    cancelButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            var orderId = this.getAttribute('data-order-id');
            var cancelLink = document.getElementById('cancelOrderLink');
            cancelLink.setAttribute('href', '../process/cancel_order.php?order_id=' + orderId);
        });
    });

    // Show/Hide notifications
    document.getElementById('notificationBell').addEventListener('click', function() {
        var notificationBox = document.getElementById('notificationBox');
        notificationBox.style.display = notificationBox.style.display === 'none' ? 'block' : 'none';
    });
</script>
</html>
