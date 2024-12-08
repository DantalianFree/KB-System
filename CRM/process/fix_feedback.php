.<?php
include '../conn.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $feedbackID = (int)$_POST['feedback_id'];
    $newCategory = $conn->real_escape_string($_POST['new_category']);

    $query = "UPDATE Feedback SET FeedbackType = ? WHERE FeedbackID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('si', $newCategory, $feedbackID);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Feedback category updated successfully!";
    } else {
        $_SESSION['error'] = "Failed to update feedback category. Please try again.";
    }

    $stmt->close();
    header('Location: ../src/feedbacks.php');
    exit;
}
?>
