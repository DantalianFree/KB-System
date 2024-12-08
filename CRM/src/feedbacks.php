<?php
include '..\conn.php'; // Ensure this points to your connection file

session_start();

// Check if the user is logged in and has the correct user type
if ($_SESSION['usertype'] !== 'Manager' && $_SESSION['usertype'] !== 'Admin') {
    echo "You don't have access to this page.";
    exit;
}

// Function for auto-categorization
function categorizeFeedback($comments) {
    $comments = strtolower($comments); // Convert to lowercase for consistent comparison

    // Complaints: Keywords indicating issues or dissatisfaction
    $complaint_keywords = ['bad', 'issue', 'poor', 'worst', 'hate', 'disappoint'];
    foreach ($complaint_keywords as $word) {
        if (strpos($comments, $word) !== false) {
            return 'Complaint';
        }
    }

    // Suggestions: Keywords indicating improvement or additions
    $suggestion_keywords = ['recommend', 'suggest', 'should', 'improve', 'add', 'change'];
    foreach ($suggestion_keywords as $word) {
        if (strpos($comments, $word) !== false) {
            return 'Suggestion';
        }
    }

    // Compliments: Keywords indicating positive feedback
    $compliment_keywords = ['good', 'excellent', 'amazing', 'great', 'love', 'best', 'perfect', 'awesome'];
    foreach ($compliment_keywords as $word) {
        if (strpos($comments, $word) !== false) {
            return 'Compliment';
        }
    }

    // Default to Suggestion if no match
    return 'Suggestion';
}

// Fetch feedback from the database
$query = "SELECT * FROM Feedback";
$result = $conn->query($query);

$feedbacks = [];
while ($row = $result->fetch_assoc()) {
    $autoCategory = categorizeFeedback($row['Comments']);
    $row['AutoCategory'] = $autoCategory;
    $row['Discrepancy'] = ($row['FeedbackType'] !== $autoCategory);
    $feedbacks[] = $row;
}

// Summarize feedback
$totalFeedback = count($feedbacks);
$categories = ['Complaint' => 0, 'Suggestion' => 0, 'Compliment' => 0];
$averageRating = 0;
$totalRatingCount = 0;

foreach ($feedbacks as $feedback) {
    $categories[$feedback['AutoCategory']]++;
    if ($feedback['Rating']) {
        $averageRating += $feedback['Rating'];
        $totalRatingCount++;
    }
}
$averageRating = $totalRatingCount > 0 ? round($averageRating / $totalRatingCount, 2) : 'N/A';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" >
    <style>
        body {
            background-color: #f8f9fa;
        }
        .dashboard-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            text-align: center;
        }
        .form-select {
            width: auto;
            display: inline-block;
        }
        /* Sidebar Styles */
        .sidebar {
            height: 100%;
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            background-color: #343a40;
            color: white;
            padding-top: 20px;
            transition: all 0.3s ease;
            z-index: 1000;
            visibility: hidden; /* Initially hidden */
            opacity: 0; /* Fade out when hidden */
        }

        .sidebar.open {
            visibility: visible;
            opacity: 1;
            width: 250px;
        }

        .sidebar a {
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            display: block;
            transition: background-color 0.2s;
        }

        .sidebar a:hover {
            background-color: #007bff;
        }

        /* Sidebar close button */
        .sidebar .close-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 24px;
            color: white;
            cursor: pointer;
            z-index: 1010;
        }
        .navbar-brand i {
            margin-right: 10px;  /* Space between the icon and the text */
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="#" id="navbarBrand">
            <i class="bi bi-list"></i> KB's StopOver CRM
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="customer_list.php">Customer List</a>
                </li>
                <li>
                    <a class="nav-link" href="feedbacks.php">Feedbacks</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="feedback_qr.php">QR Form</a>
                </li>
            </ul>
        </div>
        <div>
            <a href="../../process/log_out.php" class="btn btn-danger btn-sm">Logout</a>
        </div>
    </div>
</nav>

<?php if ($_SESSION['usertype'] === 'Manager' || $_SESSION['usertype'] === 'Admin'): ?>
    <div class="sidebar" id="sidebar">
        <span class="close-btn" id="closeBtn">&times;</span>
        <h4 class="text-center">KB's Stopover</h4>
        <a href="../../Inventory/src/dashboard.php">Inventory</a>
        <a href="../../OMS/src/oms_dashboard.php">OMS (Order Management System)</a>
        <a href="customer_reg.php">CRM (Customer Relationship Management)</a>
    </div>
<?php endif; ?>

<div class="container mt-5">
    <h2 class="text-center mb-4">Feedback Management</h2>

    <!-- Display success or error messages -->
    <?php
    if (isset($_SESSION['error'])) {
        echo '<div class="alert alert-danger text-center" role="alert">' . $_SESSION['error'] . '</div>';
        unset($_SESSION['error']);
    }
    if (isset($_SESSION['success'])) {
        echo '<div class="alert alert-success text-center" role="alert">' . $_SESSION['success'] . '</div>';
        unset($_SESSION['success']);
    }
    ?>

    <!-- Summary Section -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="dashboard-card">
                <h4>Total Feedback</h4>
                <p class="display-6"><?php echo $totalFeedback; ?></p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="dashboard-card">
                <h4>Average Rating</h4>
                <p class="display-6"><?php echo $averageRating; ?></p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="dashboard-card">
                <h4>Feedback by Category</h4>
                <ul class="list-unstyled">
                    <li>Complaint: <?php echo $categories['Complaint']; ?></li>
                    <li>Suggestion: <?php echo $categories['Suggestion']; ?></li>
                    <li>Compliment: <?php echo $categories['Compliment']; ?></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Feedback Table -->
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
            <tr>
                <th scope="col">Feedback ID</th>
                <th scope="col">Customer ID</th>
                <th scope="col">Date</th>
                <th scope="col">Auto Category</th>
                <th scope="col">Feedback Type</th>
                <th scope="col">Mismatch</th>
                <th scope="col">Rating</th>
                <th scope="col">Comments</th>
                <th scope="col">Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($feedbacks as $feedback): ?>
                <tr>
                    <td><?php echo $feedback['FeedbackID']; ?></td>
                    <td><?php echo $feedback['CustomerID']; ?></td>
                    <td><?php echo $feedback['FeedbackDate']; ?></td>
                    <td>
                        <span class="badge bg-info"><?php echo $feedback['AutoCategory']; ?></span>
                    </td>
                    <td><?php echo $feedback['FeedbackType']; ?></td>
                    <td>
                        <?php if ($feedback['Discrepancy']): ?>
                            <span class="badge bg-danger">Mismatch</span>
                        <?php else: ?>
                            <span class="badge bg-success">OK</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo $feedback['Rating'] ?: 'N/A'; ?></td>
                    <td><?php echo $feedback['Comments']; ?></td>
                    <td>
                        <form action="../process/fix_feedback.php" method="POST">
                            <input type="hidden" name="feedback_id" value="<?php echo $feedback['FeedbackID']; ?>">
                            <input type="hidden" name="new_category" value="<?php echo $feedback['AutoCategory']; ?>">
                            <button type="submit" class="btn btn-sm btn-primary" <?php echo $feedback['Discrepancy'] ? '' : 'disabled'; ?>>
                                Fix
                            </button>
                        </form>
                        <a href="../process/delete_feedback.php?id=<?php echo $feedback['FeedbackID']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this feedback?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('navbarBrand').addEventListener('click', function() {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('open');
    });

    document.getElementById('closeBtn').addEventListener('click', function() {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.remove('open');
    });
</script>
</body>
</html>
