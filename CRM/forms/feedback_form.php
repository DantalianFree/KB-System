<?php
session_start();

// Ensure the customer is logged in
if (!isset($_SESSION['customer_id'])) {
    $_SESSION['error'] = "Please log in to submit feedback.";
    header('Location: login_form.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Feedback</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .form-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2 class="text-center">Customer Feedback</h2>

    <?php
    if (isset($_SESSION['error'])) {
        echo '<div class="alert alert-danger text-center">' . $_SESSION['error'] . '</div>';
        unset($_SESSION['error']);
    }
    if (isset($_SESSION['success'])) {
        echo '<div class="alert alert-success text-center">' . $_SESSION['success'] . '</div>';
        unset($_SESSION['success']);
    }
    ?>

    <div class="form-container">
        <form action="../process/feedback_process.php" method="POST">
            <div class="mb-3">
                <label for="feedback_type" class="form-label">Feedback Type</label>
                <select class="form-select" id="feedback_type" name="feedback_type" required>
                    <option value="Complaint">Complaint</option>
                    <option value="Suggestion">Suggestion</option>
                    <option value="Compliment">Compliment</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="rating" class="form-label">Rating (1 to 5)</label>
                <input type="number" class="form-control" id="rating" name="rating" min="1" max="5" required>
            </div>

            <div class="mb-3">
                <label for="comments" class="form-label">Comments</label>
                <textarea class="form-control" id="comments" name="comments" rows="4" placeholder="Your feedback here..."></textarea>
            </div>

            <button type="submit" class="btn btn-primary w-100">Submit Feedback</button>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
