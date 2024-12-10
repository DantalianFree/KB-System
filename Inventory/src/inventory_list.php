<?php 
include '..\src\conn.php';

session_start();

if ($_SESSION['usertype'] !== 'Manager' && $_SESSION['usertype'] !== 'Admin') {
    echo "You don't have access to this page.";
    exit;
}

// Pagination settings
$itemsPerPage = 10; 
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1; 
$offset = ($currentPage - 1) * $itemsPerPage; 

// Fetch total number of items
$totalItemsQuery = "SELECT COUNT(*) as total FROM inventory";
$totalItemsResult = $conn->query($totalItemsQuery);
$totalItems = $totalItemsResult->fetch_assoc()['total'];
$totalPages = ceil($totalItems / $itemsPerPage);

// Fetch items for the current page
$sql = "SELECT InventoryID, ItemName, Category, Quantity, Unit, ReorderLevel, LastUpdated 
        FROM inventory 
        LIMIT $itemsPerPage OFFSET $offset";
$result = $conn->query($sql);

// Fetch low stock items
$lowStockQuery = "SELECT * FROM inventory WHERE Quantity <= ReorderLevel";
$lowStockResult = $conn->query($lowStockQuery);
$lowStockItems = $lowStockResult->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory List</title>
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
        .pagination {
            justify-content: center;
        }
        .notification-badge {
            position: absolute;
            top: 5px;
            right: 5px;
            background-color: red;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 12px;
        }
        .notification-box {
            position: absolute;
            top: 50px;
            right: 10px;
            background-color: white;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 300px;
            border-radius: 5px;
            padding: 10px;
            display: none;
            max-height: 300px;
            overflow-y: auto;
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
    <script>
        const itemDetails = {
            coke: { category: "Beverage", unit: "liters"},
            "mt. dew": { category: "Beverage", unit: "liters"},
            water: { category: "Beverage", unit: "liters"},
            "chicken wings": { category: "Meat", unit: "kg" },
            "chicken thighs": { category: "Meat", unit: "kg" },
            "chicken breast": { category: "Meat", unit: "kg" },
            "whole chicken": { category: "Meat", unit: "kg" },
            "bacon strips": { category: "Meat", unit: "kg" },
            "pork belly": { category: "Meat", unit: "kg" },
            beef: { category: "Meat", unit: "kg" },
            garlic: { category: "Vegetables", unit: "kg" },
            "sesame oil": { category: "Condiment", unit: "liters" },
            onion: { category: "Vegetables", unit: "kg" },
            lettuce: { category: "Vegetables", unit: "kg" },
            kimchi: { category: "Vegetables", unit: "kg" }
        };

        function updateCategoryAndUnit() {
            const itemName = document.getElementById("item_name").value.toLowerCase();

            const categoryField = document.getElementById("category");
            const unitField = document.getElementById("unit");

            if (itemDetails[itemName]) {
                categoryField.value = itemDetails[itemName].category; 
                unitField.value = itemDetails[itemName].unit;         
            } else {
                categoryField.value = "";
                unitField.value = "";
            }
        }
    </script>
</head>
<body>
    <!-- Navbar -->
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
                        <a class="nav-link active" href="inventory_list.php">Inventory</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="reports.php">Reports</a>
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
        <a href="../../CRM/src/customer_list.php">CRM (Customer Relationship Management)</a>
    </div>
    <?php endif; ?>

    <div class="container mt-5">
        <div class="row">
            <div class="col-12">
                <div class="table-container">
                    <h2 class="text-center mb-4">Inventory List</h2>
                    <a href="..\Forms\inventory_form.php" class="btn btn-primary mb-3">Add New Item</a>
                    <table class="table table-bordered table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>Item Name</th>
                                <th>Category</th>
                                <th>Quantity</th>
                                <th>Unit</th>
                                <th>Reorder Level</th>
                                <th>Last Updated</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $row['ItemName']; ?></td>
                                        <td><?php echo $row['Category']; ?></td>
                                        <td><?php echo $row['Quantity']; ?></td>
                                        <td><?php echo $row['Unit']; ?></td>
                                        <td><?php echo $row['ReorderLevel']; ?></td>
                                        <td><?php echo $row['LastUpdated']; ?></td>
                                        <td>
                                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal" onclick="populateModal(<?php echo htmlspecialchars(json_encode($row)); ?>)">Edit</button>
                                            <a href="../process/delete_inventory.php?id=<?php echo $row['InventoryID']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this item?');">Delete</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">No items found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                            
                    <nav>
                        <ul class="pagination">
                            <li class="page-item <?php echo ($currentPage == 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $currentPage - 1; ?>">Previous</a>
                            </li>
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?php echo ($currentPage == $i) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?php echo ($currentPage == $totalPages) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $currentPage + 1; ?>">Next</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="../process/edit_inventory.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">Edit Inventory Item</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="editInventoryID" name="InventoryID">

                        <div class="mb-3">
                            <?php
                                $query = "SHOW COLUMNS FROM inventory LIKE 'ItemName'";
                                $result = mysqli_query($conn, $query);
                                $row = mysqli_fetch_assoc($result);

                                $enumValues = str_replace(["enum(", ")", "'"], "", $row['Type']);
                                $enumArray = explode(",", $enumValues);
                            ?>
                            <label class="form-label" for="item_name">Item Name:</label>
                            <select class="form-control" id="item_name" name="item_name" onchange="updateCategoryAndUnit()" required>
                                <option value="" disabled selected>Select an item</option>
                                <?php foreach ($enumArray as $value): ?>
                                    <option value="<?= strtolower(trim($value)) ?>"><?= ucfirst($value) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="editCategory" class="form-label">Category</label>
                            <input type="text" class="form-control" id="editCategory" name="Category" required readonly>
                        </div>
                        <div class="mb-3">
                            <label for="editQuantity" class="form-label">Quantity</label>
                            <input type="number" class="form-control" id="editQuantity" name="Quantity" min="0" required>
                        </div>
                        <div class="mb-3">
                            <label for="editUnit" class="form-label">Unit</label>
                            <input type="text" class="form-control" id="editUnit" name="Unit" required>
                        </div>
                        <div class="mb-3">
                            <label for="editReorderLevel" class="form-label">Reorder Level</label>
                            <input type="number" class="form-control" id="editReorderLevel" name="ReorderLevel" min="0" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // JavaScript for modal population
        function populateModal(row) {
            document.getElementById('editInventoryID').value = row.InventoryID;
            document.getElementById('editItemName').value = row.ItemName;
            document.getElementById('editCategory').value = row.Category;
            document.getElementById('editQuantity').value = row.Quantity;
            document.getElementById('editUnit').value = row.Unit;
            document.getElementById('editReorderLevel').value = row.ReorderLevel;
        }

        // Toggle notifications
        const notificationBell = document.getElementById('notificationBell');
        const notificationBox = document.getElementById('notificationBox');
        notificationBell.addEventListener('click', () => {
            notificationBox.style.display = notificationBox.style.display === 'block' ? 'none' : 'block';
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
