<?php
session_start(); 
include '../conn.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT `Password` FROM Authentication WHERE Username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($storedHash);
        $stmt->fetch();

        if (password_verify($password, $storedHash)) {
            $_SESSION['username'] = $username;
            header("Location: ../index.php"); // Redirect to dashboard or homepage
            exit;
        } else {
            $_SESSION['error'] = "Invalid password."; // Store error in session
        }
    } else {
        $_SESSION['error'] = "Username not found."; // Store error in session
    }

    $stmt->close();
    header("Location: ../forms/login_form.php"); // Redirect back to login form
    exit;
}
