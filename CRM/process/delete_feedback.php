<?php
include '..\conn.php';  // Ensure this points to your connection file

session_start();

// Check if the user is logged in and has the correct user type
if ($_SESSION['usertype'] !== 'Manager' && $_SESSION['usertype'] !== 'Admin') {
    echo "You don't have access to this page.";
    exit;
}

// Check if the feedback ID is provided
if (isset($_GET['id'])) {
    $feedbackId = (int)$_GET['id'];

    // Delete the feedback record
    $query = "DELETE FROM Feedback WHERE FeedbackID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $feedbackId);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Feedback deleted successfully!";
    } else {
        $_SESSION['error'] = "Failed to delete feedback. Please try again.";
    }
    $stmt->close();
} else {
    $_SESSION['error'] = "Invalid feedback ID provided.";
}

header('Location: ../src/feedbacks.php');  // Redirect back to the feedback management page
exit;
?>
