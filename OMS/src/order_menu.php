<?php 
include '../conn.php';  // Order management database connection
$inventoryConn = new mysqli('localhost', 'root', '', 'kb_inventory');  // Inventory database connection

session_start();

// Check if the user is logged in (Staff user)
if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] !== 'Staff') {
    echo "You are not authorized to access this page.";
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
        COALESCE(FLOOR(MIN(i.Quantity / ii.QuantityRequired)), 0) AS stock -- Handle NULLs with COALESCE
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

// Handle placing an order (for example, using POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $menuItemID = $_POST['menuItemID'];
    $orderQuantity = $_POST['quantity']; // Quantity of the menu item ordered

    // Step 1: Fetch the ingredients for the ordered menu item from inventory database
    $ingredientsQuery = "
        SELECT ii.IngredientID, i.ItemName, ii.QuantityRequired, i.Quantity as AvailableQuantity 
        FROM order_management.menu_item_ingredients ii  -- Referencing the correct database (order_management)
        LEFT JOIN kb_inventory.inventory i ON ii.IngredientID = i.InventoryID  -- Referencing the inventory table in the kb_inventory database
        WHERE ii.MenuItemID = ?";
    
    $stmt = $inventoryConn->prepare($ingredientsQuery);
    $stmt->bind_param("i", $menuItemID);
    $stmt->execute();
    $ingredientsResult = $stmt->get_result();
    $ingredients = [];

    while ($ingredient = $ingredientsResult->fetch_assoc()) {
        $ingredients[] = $ingredient;
    }

    // Step 2: Check if inventory has enough ingredients to fulfill the order
    foreach ($ingredients as $ingredient) {
        $ingredientID = $ingredient['IngredientID'];
        $quantityRequired = $ingredient['QuantityRequired'] * $orderQuantity;

        // Fetch current stock from inventory database
        $stockQuery = "SELECT Quantity FROM kb_inventory.inventory WHERE InventoryID = ?";  // Referencing the inventory table correctly
        $stockStmt = $inventoryConn->prepare($stockQuery);
        $stockStmt->bind_param("i", $ingredientID);
        $stockStmt->execute();
        $stockResult = $stockStmt->get_result()->fetch_assoc();
        $currentStock = $stockResult['Quantity'];

        // If there is not enough stock, show error message
        if ($currentStock < $quantityRequired) {
            echo "Not enough stock for ingredient: " . $ingredient['ItemName'];
            exit;
        }
    }

    // Step 3: Update inventory by reducing the stock in the inventory database
    $inventoryConn->begin_transaction();

    try {
        foreach ($ingredients as $ingredient) {
            $ingredientID = $ingredient['IngredientID'];
            $quantityRequired = $ingredient['QuantityRequired'] * $orderQuantity;

            // Update inventory quantity in the inventory database
            $updateStockQuery = "UPDATE kb_inventory.inventory SET Quantity = Quantity - ? WHERE InventoryID = ?";
            $updateStmt = $inventoryConn->prepare($updateStockQuery);
            $updateStmt->bind_param("di", $quantityRequired, $ingredientID);
            $updateStmt->execute();
        }

        // Step 4: Create the order record in the orders table (no customer ID needed)
        $totalAmount = 0; // Calculate total amount here based on ordered items
        $orderQuery = "INSERT INTO `order` (OrderDate, TotalAmount) VALUES (NOW(), ?)";
        $orderStmt = $conn->prepare($orderQuery);
        $orderStmt->bind_param("d", $totalAmount); // No customer ID for staff orders
        $orderStmt->execute();

        // Add order details (menu item details)
        $orderID = $conn->insert_id;
        $orderDetailQuery = "INSERT INTO order_details (OrderID, MenuItemID, Quantity, Price) VALUES (?, ?, ?, ?)";
        $orderDetailStmt = $conn->prepare($orderDetailQuery);

        foreach ($ingredients as $ingredient) {
            $orderDetailStmt->bind_param("iiid", $orderID, $menuItemID, $orderQuantity, $ingredient['Price']);
            $orderDetailStmt->execute();
        }

        // Commit transaction for inventory and orders
        $inventoryConn->commit();
        echo "Order successfully placed!";
    } catch (Exception $e) {
        // Rollback transaction in case of error
        $inventoryConn->rollback();
        echo "Failed to place the order. Please try again.";
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
            max-width: 100%;
            height: auto;
            border-radius: 10px;
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
    </style>
</head>
<body>

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
                                    Available stock: <?php echo $item['stock']; ?> <!-- Display the stock quantity -->
                                </div>
                                <form action="order_menu.php" method="POST">
                                    <input type="hidden" name="menuItemID" value="<?php echo $item['MenuItemID']; ?>">
                                    <input type="number" name="quantity" min="1" value="1" class="form-control mb-2">
                                    <button type="submit" class="btn btn-success">Add to Order</button>
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
