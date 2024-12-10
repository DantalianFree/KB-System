<?php
include '../conn.php';
session_start();

// Ensure the user is logged in and is an admin, manager, or staff
if (!isset($_SESSION['usertype']) || !in_array($_SESSION['usertype'], ['Admin', 'Manager', 'Staff'])) {
    echo "You don't have access to this page.";
    exit;
}

// Get the OrderID from the query parameter
$orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

if ($orderId <= 0) {
    echo "Invalid Order ID.";
    exit;
}

// Fetch order details from the orderdetails table, join with the menu_item table to get the item name
$query = "
    SELECT 
        od.OrderDetailID, 
        mi.Name, 
        od.Quantity, 
        od.Subtotal
    FROM orderdetails od
    JOIN menu_item mi ON od.MenuItemID = mi.MenuItemID
    WHERE od.OrderID = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $orderId);
$stmt->execute();
$result = $stmt->get_result();

// Fetch the current status of the order
$statusQuery = "SELECT OrderStatus FROM `order` WHERE OrderID = ?";
$statusStmt = $conn->prepare($statusQuery);
$statusStmt->bind_param('i', $orderId);
$statusStmt->execute();
$statusResult = $statusStmt->get_result();
$orderStatus = $statusResult->fetch_assoc()['OrderStatus'];

if (!$result) {
    echo "Failed to fetch order details.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            margin-top: 30px;
        }
        .table th, .table td {
            text-align: center;
        }
        .card {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            border-radius: 10px;
            background-color: white;
        }
        .card-header {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="text-center mb-4">Order Details for Order ID: <?php echo $orderId; ?></h2>

        <!-- Order Status Dropdown for updating status -->
        <?php if (in_array($_SESSION['usertype'], ['Admin', 'Manager', 'Staff'])): ?>
            <div class="card mb-4">
                <div class="card-header">Update Order Status</div>
                <div class="card-body">
                    <form action="../process/update_order_status.php" method="POST">
                        <input type="hidden" name="order_id" value="<?php echo $orderId; ?>">
                        <label for="order_status" class="form-label">Order Status</label>
                        <select name="order_status" class="form-select" id="order_status" onchange="this.form.submit()">
                            <option value="Pending" <?php echo $orderStatus == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="Ready to Deliver" <?php echo $orderStatus == 'Ready to Deliver' ? 'selected' : ''; ?>>Ready to Deliver</option>
                            <option value="Delivered" <?php echo $orderStatus == 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
                        </select>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <!-- Order Items Table -->
        <div class="card">
            <div class="card-header">Order Items</div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>Menu Item</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>
                                <td>" . $row['Name'] . "</td>
                                <td>" . $row['Quantity'] . "</td>
                                <td>₱" . number_format($row['Subtotal'], 2) . "</td>
                            </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3 text-center">
            <a href="orders_list.php" class="btn btn-secondary">Back to Orders List</a>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
