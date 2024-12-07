<?php 
include 'conn.php'; // CONNECTION TO DATABASE INCLUDE FILE

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the data from the form
    $username = $_POST['username'];
    $password = $_POST['password'];
    $usertype = $_POST['usertype'];

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // SQL QUERY TO INSERT INTO DATABASE (UserID will auto-increment, no need to bind it)
    $sql = "INSERT INTO Authentication (userType, Username, `Password`, IsActive, CreatedAt, UpdatedAt)
            VALUES (:usertype, :username, :password, 1, NOW(), NOW())";

    $stmt = $pdo->prepare($sql);

    try {
        // Execute the query with the values
        $stmt->execute([
            ':usertype' => $usertype,
            ':username' => $username,
            ':password' => $hashedPassword
        ]);
        echo "<div class='alert alert-success text-center'>Registration successful!</div>";
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger text-center'>Error: " . $e->getMessage() . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css">
    <title>Registration</title>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <h2 class="text-center mb-4">Register</h2>
                <form action="register.php" method="POST">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="usertype" class="form-label">User Type</label>
                        <select class="form-select" name="usertype" id="usertype" required>
                            <option value="Admin">Admin</option>
                            <option value="Staff">Staff</option>
                            <option value="Manager">Manager</option>
                        </select>
                    </div>
                    <button class="btn btn-primary w-100" type="submit">Register</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
