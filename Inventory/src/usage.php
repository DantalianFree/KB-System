<?php
include '..\src\conn.php'; // Include your database connection

// Simulate fetching the current user's StaffID from the session (update this as per your authentication system)
$current_staff_id = $_SESSION['StaffID'] ?? 1; // Default to 1 for testing (replace with actual session variable)

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inventory_id = $_POST['inventory_id']; // Item selected from the inventory
    $quantity_used = $_POST['quantity_used']; // Quantity of the item used
    $usage_date = $_POST['usage_date']; // The date of usage

    // Prepare SQL query to insert usage data into the database
    $sql = "INSERT INTO `usage` (InventoryID, QuantityUsed, UsageDate, StaffID) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisi", $inventory_id, $quantity_used, $usage_date, $current_staff_id);

    if ($stmt->execute()) {
        $success_message = "Usage recorded successfully!";
    } else {
        $error_message = "Error recording usage: " . $stmt->error;
    }

    $stmt->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Item Usage</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            max-width: 800px;
            margin: 50px auto;
        }
        .card {
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .btn-primary {
            width: 100%;
            padding: 10px;
            font-size: 1rem;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">KB's Stopover</a>
        </div>
    </nav>

    <div class="container">
        <div class="card">
            <div class="card-header bg-primary text-white text-center">
                <h3>Track Item Usage</h3>
            </div>
            <div class="card-body">
                <!-- Display messages -->
                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php elseif (!empty($error_message)): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>

                <!-- Form to log usage -->
                <form action="usage.php" method="POST">
                    <!-- Item Selection -->
                    <div class="mb-3">
                        <label for="inventoryID" class="form-label">Item Name</label>
                        <select class="form-select" id="inventoryID" name="inventory_id" required>
                            <option value="">Select Item</option>
                            <?php
                            // Query to fetch inventory items
                            $inventoryQuery = "SELECT InventoryID, ItemName FROM inventory";
                            $inventoryResult = $conn->query($inventoryQuery);
                            while ($row = $inventoryResult->fetch_assoc()) {
                                echo "<option value='{$row['InventoryID']}'>{$row['ItemName']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <!-- Quantity Used -->
                    <div class="mb-3">
                        <label for="quantityUsed" class="form-label">Quantity Used</label>
                        <input type="number" class="form-control" id="quantityUsed" name="quantity_used" min="1" required>
                    </div>

                    <!-- Usage Date -->
                    <div class="mb-3">
                        <label for="usageDate" class="form-label">Usage Date</label>
                        <input type="date" class="form-control" id="usageDate" name="usage_date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary">Submit Usage</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
