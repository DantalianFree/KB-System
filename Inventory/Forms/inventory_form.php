<?php 
include '../src/conn.php';

$existingItemsQuery = "SELECT ItemName FROM inventory";
$existingItemsResult = $conn->query($existingItemsQuery);
$existingItems = [];
while ($row = $existingItemsResult->fetch_assoc()) {
    $existingItems[] = strtolower($row['ItemName']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .form-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
    </style>
    <script>
        const itemDetails = {
            coke: { category: "Beverage", unit: "liters" },
            "mt. dew": { category: "Beverage", unit: "liters" },
            water: { category: "Beverage", unit: "liters" },
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
            kimchi: { category: "Vegetables", unit: "kg" },
            egg: { category: "Eggs", unit: "kg"},
            "pork tocino": { category: "Meat", unit: "kg" },
            longganisa: { category: "Meat", unit: "kg" },
            "corn beef": { category: "Meat", unit: "kg" }
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
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-6 col-lg-5 mx-auto">
                <div class="form-container">
                    <h2 class="text-center mb-4">Inventory Form</h2>
                    <form action="add_inventory.php" method="post">
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
                                    <?php if (!in_array(strtolower(trim($value)), $existingItems)): // Check if item is not in inventory ?>
                                        <option value="<?= strtolower(trim($value)) ?>"><?= ucfirst($value) ?></option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="category">Category:</label>
                            <input class="form-control" type="text" id="category" name="category" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="quantity">Quantity:</label>
                            <input class="form-control" type="number" id="quantity" name="quantity" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="unit">Unit:</label>
                            <input class="form-control" type="text" id="unit" name="unit" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="reorder_level">Reorder Level:</label>
                            <input class="form-control" type="number" id="reorder_level" name="reorder_level" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="last_updated">Last Updated:</label>
                            <input class="form-control" type="datetime-local" id="last_updated" name="last_updated" required>
                        </div>
                        <div class="d-flex justify-content-between">
                            <input type="submit" class="btn btn-primary" value="Save">
                            <input type="button" class="btn btn-secondary" value="Cancel" onclick="window.location.href='../src/inventory_list.php';">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
