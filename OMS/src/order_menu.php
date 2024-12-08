<?php 
include '../conn.php';  // Order management database connection
$inventoryConn = new mysqli('localhost', 'root', '', 'kb_inventory');  // Inventory database connection

session_start();

// Initialize the cart array in session if not set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Check if the user is logged in (Staff user)
if (!isset($_SESSION['usertype'])) {
    echo "You are not logged in to access this page.";
    exit;
}

// Fetch menu categories from the order management system (kb_oms)
$menuQuery = "SELECT * FROM menu";
$menuResult = $conn->query($menuQuery);

// Fetch menu items with inventory info (stock)
$menuItemsQuery = "
    SELECT 
        mi.MenuItemID, 
        mi.Name, 
        mi.Price, 
        mi.Type, 
        mi.MenuID, 
        m.MenuName, 
        COALESCE(FLOOR(MIN(i.Quantity / ii.QuantityRequired)), 0) AS stock
    FROM 
        menu_item mi
    JOIN 
        menu m ON mi.MenuID = m.MenuID
    LEFT JOIN 
        menu_item_ingredients ii ON mi.MenuItemID = ii.MenuItemID
    LEFT JOIN 
        kb_inventory.inventory i ON ii.IngredientID = i.InventoryID
    GROUP BY 
        mi.MenuItemID";

$menuItemsResult = $conn->query($menuItemsQuery);
$menuItems = [];

while ($row = $menuItemsResult->fetch_assoc()) {
    $menuItems[$row['MenuID']][] = $row;
}

// Handle actions (add to cart or remove from cart)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'add_to_cart') {
        $menuItemID = $_POST['menuItemID'];
        $orderQuantity = $_POST['quantity']; // Quantity of the menu item ordered

        // Fetch the menu item details
        $itemQuery = "SELECT Name, Price FROM menu_item WHERE MenuItemID = ?";
        $stmt = $conn->prepare($itemQuery);
        $stmt->bind_param("i", $menuItemID);
        $stmt->execute();
        $itemResult = $stmt->get_result()->fetch_assoc();

        // Check if the item is already in the cart
        $itemExists = false;
        foreach ($_SESSION['cart'] as &$cartItem) {
            if ($cartItem['MenuItemID'] == $menuItemID) {
                $cartItem['Quantity'] += $orderQuantity;  // Update the quantity
                $itemExists = true;
                break;
            }
        }

        // If the item is not in the cart, add it
        if (!$itemExists) {
            $cartItem = [
                'MenuItemID' => $menuItemID,
                'Name' => $itemResult['Name'],
                'Price' => $itemResult['Price'],
                'Quantity' => $orderQuantity
            ];
            $_SESSION['cart'][] = $cartItem;  // Add to cart session
        }
    }

    // Handle removing items from the cart
    if ($_POST['action'] == 'remove_from_cart' && isset($_POST['index'])) {
        $index = $_POST['index'];

        // Remove the item from the cart
        unset($_SESSION['cart'][$index]);

        // Re-index the array to prevent gaps in the indexes
        $_SESSION['cart'] = array_values($_SESSION['cart']);
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Menu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .menu-item-card {
            border: 1px solid #ddd;
            border-radius: 10px;
            margin-bottom: 20px;
            padding: 15px;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .menu-item-card img {
            width: 200px;
            height: 200px;
            object-fit: contain;
        }
        .menu-item-card .menu-item-name {
            font-weight: bold;
            margin-top: 10px;
        }
        .menu-item-card .menu-item-price {
            color: green;
            font-size: 1.2rem;
            margin-top: 5px;
        }
        .menu-item-card .menu-item-stock {
            color: red;
            font-size: 1rem;
            margin-top: 5px;
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
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link active" href="order_menu.php">Menu</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="orders_list.php">Order List</a>
                </li>
                <?php if ($_SESSION['usertype'] !== 'Staff'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="order_reports.php">Reports</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
        <div class="d-flex">
            <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#cartModal">
                <i class="fas fa-shopping-cart"></i> Cart
            </button>
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

<!-- Cart Modal -->
<div class="modal fade" id="cartModal" tabindex="-1" aria-labelledby="cartModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cartModalLabel">Your Cart</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
    <?php if (!empty($_SESSION['cart'])): ?>
        <ul class="list-group">
            <?php foreach ($_SESSION['cart'] as $key => $cartItem): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <?php echo $cartItem['Name']; ?> 
                    x <?php echo $cartItem['Quantity']; ?> 
                    - ₱<?php echo number_format($cartItem['Price'] * $cartItem['Quantity'], 2); ?>
                    <form action="order_menu.php" method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="remove_from_cart">
                        <input type="hidden" name="index" value="<?php echo $key; ?>">
                        <button type="submit" class="btn btn-sm btn-danger">X</button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>
        <hr>
        <p><strong>Total: ₱<?php 
            $total = 0;
            foreach ($_SESSION['cart'] as $cartItem) {
                $total += $cartItem['Price'] * $cartItem['Quantity'];
            }
            echo number_format($total, 2);
        ?></strong></p>
    <?php else: ?>
        <p>Your cart is empty.</p>
    <?php endif; ?>
</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="window.location.href='checkout.php'">Proceed to Checkout</button>
            </div>
        </div>
    </div>
</div>

<div class="container mt-5">
    <h2 class="text-center mb-4">Select Items for Order</h2>

    <?php while ($menuCategory = $menuResult->fetch_assoc()): ?>
        <div class="menu-category">
            <h3 class="mb-4"><?php echo $menuCategory['MenuName']; ?></h3>
            <div class="row">
                <?php if (isset($menuItems[$menuCategory['MenuID']])): ?>
                    <?php foreach ($menuItems[$menuCategory['MenuID']] as $item): ?>
                        <div class="col-md-4">
                            <div class="menu-item-card">
                                <img src="../imgs/menu_items/<?php echo $item['MenuItemID']; ?>.png" alt="Menu Item Image">
                                <div class="menu-item-name"><?php echo $item['Name']; ?></div>
                                <div class="menu-item-price">₱<?php echo number_format($item['Price'], 2); ?></div>
                                <div class="menu-item-stock">
                                    Available stock: <?php echo $item['stock']; ?>
                                </div>
                                <form action="order_menu.php" method="POST">
                                    <input type="hidden" name="menuItemID" value="<?php echo $item['MenuItemID']; ?>">
                                    <input type="number" name="quantity" min="1" value="1" class="form-control mb-2">
                                    <input type="hidden" name="action" value="add_to_cart">
                                    <button type="submit" class="btn btn-success">Add to Cart</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No items available in this category.</p>
                <?php endif; ?>
            </div>
        </div>
    <?php endwhile; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
