<?php
include '..\conn.php';
session_start();

// Ensure the customer is logged in
if (!isset($_SESSION['customer_id'])) {
    $_SESSION['error'] = "Please log in to submit feedback.";
    header('Location: ../forms/login_form.php');
    exit;
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customerId = $_SESSION['customer_id'];
    $feedbackType = $conn->real_escape_string($_POST['feedback_type']);
    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : NULL;
    $comments = isset($_POST['comments']) ? $conn->real_escape_string($_POST['comments']) : NULL;

    // Insert feedback into the database
    $query = "INSERT INTO Feedback (CustomerID, FeedbackDate, FeedbackType, Rating, Comments) 
              VALUES (?, CURRENT_TIMESTAMP, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('isis', $customerId, $feedbackType, $rating, $comments);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Feedback submitted successfully!";
        $_SESSION['feedback_id'] = $stmt->insert_id;  // Store the feedback ID to show on the next page
        header('Location: thank_you.php');  // Redirect to thank you page
        exit;
    } else {
        $_SESSION['error'] = "Failed to submit feedback. Please try again.";
    }

    $stmt->close();
} else {
    $_SESSION['error'] = "Invalid request.";
    header('Location: ../forms/feedback_form.php');
    exit;
}
?>
