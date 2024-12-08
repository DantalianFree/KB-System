<?php
session_start();
include '../conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // First, fetch UserType and Password from the Authentication table
    $stmt = $conn->prepare("SELECT `Password`, UserType FROM Authentication WHERE Username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($storedHash, $usertype);
        $stmt->fetch();

        if (password_verify($password, $storedHash)) {
            // Set common session variables
            $_SESSION['username'] = $username;
            $_SESSION['usertype'] = $usertype;

            // If user is a staff, retrieve their StaffID
            if ($usertype === 'Staff') {
                $staffStmt = $conn->prepare("SELECT StaffID FROM Staff WHERE Name = ?");
                $staffStmt->bind_param("s", $username);
                $staffStmt->execute();
                $staffResult = $staffStmt->get_result();

                if ($staffRow = $staffResult->fetch_assoc()) {
                    $_SESSION['StaffID'] = $staffRow['StaffID']; // Save StaffID in session
                } else {
                    $_SESSION['error'] = 'Staff record not found.';
                    header("Location: ../../process/login_form.php");
                    exit;
                }
                $staffStmt->close();
            }

            // Redirect based on UserType
            if ($usertype === 'Staff') {
                header("Location: ../src/orders_list.php");
            } elseif ($usertype === 'Manager') {
                header("Location: ../../Inventory/src/dashboard.php");
            } else {
                header("Location: ../../Inventory/src/dashboard.php");
            }
            exit;
        } else {
            $_SESSION['error'] = 'Invalid password.';
        }
    } else {
        $_SESSION['error'] = 'Username not found.';
    }

    $stmt->close();
    header("Location: ../../process/login_form.php");
    exit;
}
?>
