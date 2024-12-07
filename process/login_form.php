<?php
session_start();

$error = isset($_SESSION['error']) ? $_SESSION['error'] : null;
unset($_SESSION['error']); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f0f4f8; 
        }
        .login-container {
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .login-form {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        .login-form .form-label {
            color: #5a5a5a;
        }
        .btn-primary {
            background-color: #007bff;
            border: none;
            padding: 12px;
            font-size: 16px;
            border-radius: 6px;
            width: 100%;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .alert {
            margin-bottom: 20px;
        }
        .logo-container {
            position: absolute;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
        }
        .logo-container img {
            width: 100px; 
            border-radius: 100px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-form">
            <h3 class="text-center mb-4">Login</h3>
            <?php if ($error): ?>
                <div class="alert alert-danger text-center">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            <form action="../OMS/process/login_process.php" method="POST">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary">Login</button>
            </form>
        </div>
        
        <div class="logo-container">
            <img src="../OMS/imgs/KB's StopOver Logo.jpg" alt="KB's StopOver Logo">
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
