<?php
include '..\conn.php';  // Database connection

session_start();

if ($_SESSION['usertype'] !== 'Manager' && $_SESSION['usertype'] !== 'Admin' && $_SESSION['usertype'] !== 'Customer') {
    echo "You don't have access to this page.";
    exit;
}

$customer_id = $_SESSION['customer_id']; // CustomerID stored in session
$customer_name = isset($_SESSION['customer_name']) ? $_SESSION['customer_name'] : "Customer";

// Fetch the total loyalty points for the customer
$query = "SELECT SUM(PointsEarned) AS TotalPoints FROM loyaltypoints WHERE CustomerID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $customer_id);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($total_points);
$stmt->fetch();
$total_points = $total_points ?? 0; // Handle case where no points are earned yet

// Check reward eligibility (example threshold: 1,000 points for a reward)
$reward_threshold = 1000;
$reward_message = "";
if ($total_points >= $reward_threshold) {
    $reward_message = "Congratulations! You have enough points for a reward.";
} else {
    $reward_message = "You need " . ($reward_threshold - $total_points) . " more points to redeem a reward.";
}

// Fetch recent loyalty point transactions
$query = "SELECT TransactionDate, PointsEarned, Description FROM loyaltypoints WHERE CustomerID = ? ORDER BY TransactionDate DESC LIMIT 5";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $customer_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .table-container {
            margin-top: 30px;
        }
        .table {
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
        }
        .reward-cta {
            color: #155724;
            font-weight: bold;
            margin-top: 15px;
        }
        .reward-cta i {
            color: #28a745;
        }
        .dashboard-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 30px;
            color: #343a40;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Loyalty Dashboard</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="#">Welcome, <?php echo htmlspecialchars($customer_name); ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link btn btn-danger btn-sm" href="logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <h2 class="text-center dashboard-title">Welcome to Your Loyalty Dashboard</h2>

    <!-- Loyalty Points and Reward Eligibility Section -->
    <div class="row mb-4">
        <!-- Total Loyalty Points -->
        <div class="col-md-6">
            <div class="card text-center">
                <div class="card-header bg-primary text-white">Total Loyalty Points</div>
                <div class="card-body">
                    <h1 class="display-5"><?php echo $total_points; ?> Points</h1>
                    <p class="card-text">Track your points and unlock exclusive rewards.</p>
                </div>
            </div>
        </div>

        <!-- Reward Eligibility -->
        <div class="col-md-6">
            <div class="card text-center">
                <div class="card-header bg-success text-white">Reward Eligibility</div>
                <div class="card-body">
                    <p class="reward-cta"><i class="bi bi-gift-fill"></i> <?php echo $reward_message; ?></p>
                    <?php if ($total_points >= $reward_threshold): ?>
                        <a href="redeem_reward.php" class="btn btn-success">Redeem Reward</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="table-container">
        <h3 class="text-center">Recent Transactions</h3>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Date</th>
                        <th>Points Earned</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo date('F j, Y', strtotime($row['TransactionDate'])); ?></td>
                                <td><?php echo $row['PointsEarned']; ?></td>
                                <td><?php echo $row['Description']; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="text-center">No transactions found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
