<?php
session_start();

// Ensure this page is accessed only after feedback submission
if (!isset($_SESSION['success'])) {
    header('Location: feedback_form.php'); // Redirect back to the form if accessed directly
    exit;
}

// Optionally clear the success message after displaying it
$successMessage = $_SESSION['success'];
unset($_SESSION['success']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank You</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .thank-you-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 30px;
            text-align: center;
            max-width: 500px;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="thank-you-card">
        <h1 class="display-5">Thank You!</h1>
        <p class="mt-3"><?php echo htmlspecialchars($successMessage); ?></p>
        <p>You can now close this page.</p>
        <a href="../forms/feedback_form.php" class="btn btn-primary mt-4">Submit Another Feedback</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
