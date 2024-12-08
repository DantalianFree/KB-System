<?php
include '..\conn.php';  // Ensure this points to your connection file

session_start();

// Check if the user is logged in and has the correct user type
if ($_SESSION['usertype'] !== 'Manager' && $_SESSION['usertype'] !== 'Admin') {
    echo "You don't have access to this page.";
    exit;
}

// Check if the request is a POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $feedbackId = isset($_POST['feedback_id']) ? (int)$_POST['feedback_id'] : null;
    $feedbackType = isset($_POST['feedback_type']) ? $conn->real_escape_string($_POST['feedback_type']) : null;

    // Validate input
    if ($feedbackId && $feedbackType && in_array($feedbackType, ['Complaint', 'Suggestion', 'Compliment'])) {
        // Update the feedback type in the database
        $query = "UPDATE Feedback SET FeedbackType = ? WHERE FeedbackID = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('si', $feedbackType, $feedbackId);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Feedback type updated successfully!";
        } else {
            $_SESSION['error'] = "Failed to update feedback type. Please try again.";
        }
        $stmt->close();
    } else {
        $_SESSION['error'] = "Invalid feedback data provided.";
    }
} else {
    $_SESSION['error'] = "Invalid request.";
}

header('Location: ../src/feedbacks.php');  // Redirect back to the feedback management page
exit;
?>
