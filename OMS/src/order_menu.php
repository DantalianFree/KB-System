<?php 
    include '../conn.php';

    session_start();

    if (!isset($_SESSION['usertype'])) {
        echo "You are not logged in.";
        exit;
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="orders_list.php">KB's StopOver OMS</a>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link active" href="order_menu.php">Menu</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="orders_list.php">Order List</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</body>
</html>