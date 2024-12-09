<?php
include '..\conn.php';
session_start();

// Ensure the customer is logged in
if (!isset($_SESSION['customer_id'])) {
    $_SESSION['error'] = "Please log in to view your feedback.";
    header('Location: login_form.php');
    exit;
}

$customerId = $_SESSION['customer_id']; // Retrieve customer ID from session

// Fetch the most recent feedback submitted by the customer
$query = "SELECT FeedbackDate, FeedbackType, Rating, Comments FROM Feedback WHERE CustomerID = ? ORDER BY FeedbackDate DESC LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $customerId);
$stmt->execute();
$result = $stmt->get_result();

// Check if feedback exists
if ($result->num_rows > 0) {
    $feedback = $result->fetch_assoc();
} else {
    $_SESSION['error'] = "No feedback found.";
    header('Location: feedback_form.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Feedback</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center mb-4">Your Submitted Feedback</h2>

        <div class="card text-center">
            <div class="card-header">
                Feedback Details
            </div>
            <div class="card-body">
                <h5 class="card-title">Feedback Type: <?php echo $feedback['FeedbackType']; ?></h5>
                <p class="card-text">Rating: <?php echo $feedback['Rating']; ?> / 5</p>
                <p class="card-text">Comments: <?php echo $feedback['Comments']; ?></p>
                <p class="card-text">Submitted on: <?php echo $feedback['FeedbackDate']; ?></p>
                <a href="customer_dashboard.php" class="btn btn-primary">Go to Dashboard</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$stmt->close();
?>
