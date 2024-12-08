<?php
session_start();
// Include database connection
include '../conn.php';

// Check if the cart is not empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    echo "Your cart is empty.";
    exit;
}

// Fetch cart items from session
$cartItems = $_SESSION['cart'];
$totalAmount = 0;
foreach ($cartItems as $item) {
    $totalAmount += $item['Price'] * $item['Quantity'];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand" href="order_menu.php">KB's StopOver OMS</a>
    <div class="collapse navbar-collapse">
        <ul class="navbar-nav">
            <li class="nav-item"><a class="nav-link" href="orders_list.php">Orders List</a></li>
        </ul>
    </div>
</nav>

<div class="container mt-4">
    <h2 class="text-center mb-4">Checkout</h2>
    
    <!-- Cart Items -->
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>Item</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($cartItems as $item): ?>
                <tr>
                    <td><?php echo $item['Name']; ?></td>
                    <td><?php echo $item['Quantity']; ?></td>
                    <td>₱<?php echo number_format($item['Price'], 2); ?></td>
                    <td>₱<?php echo number_format($item['Price'] * $item['Quantity'], 2); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <div class="d-flex justify-content-between">
        <h4>Total: ₱<?php echo number_format($totalAmount, 2); ?></h4>
    </div>

    <!-- Checkout Form -->
    <form method="POST" action="../process/process_checkout.php">
    <div class="mb-3">
        <label for="name" class="form-label">Full Name</label>
        <input type="text" class="form-control" id="name" name="name" required>
    </div>
    <div class="mb-3">
        <label for="contactDetails" class="form-label">Contact Details (Phone/Email)</label>
        <input type="text" class="form-control" id="contactDetails" name="contactDetails" required>
    </div>
    <button type="submit" class="btn btn-primary">Place Order</button>
</form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
