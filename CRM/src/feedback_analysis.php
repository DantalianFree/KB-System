<?php
include '..\conn.php';  // Include database connection file

session_start();

// Check if the user is logged in and has the correct user type
if ($_SESSION['usertype'] !== 'Manager' && $_SESSION['usertype'] !== 'Admin') {
    echo "You don't have access to this page.";
    exit;
}

// Sentiment analysis function (basic approach using keywords)
function analyzeSentiment($comments) {
    $comments = strtolower($comments);
    $positive_keywords = ['good', 'excellent', 'amazing', 'great', 'love', 'best'];
    $negative_keywords = ['bad', 'poor', 'worst', 'hate', 'disappointed'];
    
    $positive_count = 0;
    $negative_count = 0;
    
    // Check for positive keywords
    foreach ($positive_keywords as $keyword) {
        if (strpos($comments, $keyword) !== false) {
            $positive_count++;
        }
    }

    // Check for negative keywords
    foreach ($negative_keywords as $keyword) {
        if (strpos($comments, $keyword) !== false) {
            $negative_count++;
        }
    }

    // Determine sentiment based on keyword count
    if ($positive_count > $negative_count) {
        return 'Positive';
    } elseif ($negative_count > $positive_count) {
        return 'Negative';
    } else {
        return 'Neutral';
    }
}

// Query to fetch feedback data with dates
$query = "SELECT * FROM Feedback ORDER BY FeedbackDate DESC";
$result = $conn->query($query);

$feedbacks = [];
$categories = ['Complaint' => 0, 'Suggestion' => 0, 'Compliment' => 0];
$sentiment_counts = ['Positive' => 0, 'Negative' => 0, 'Neutral' => 0];
$keywords = [];

// Process each feedback record
while ($row = $result->fetch_assoc()) {
    // Analyze sentiment
    $sentiment = analyzeSentiment($row['Comments']);
    $sentiment_counts[$sentiment]++;

    // Count feedback categories
    $categories[$row['FeedbackType']]++;

    // Count frequency of issues (keywords)
    $words = explode(" ", strtolower($row['Comments']));
    foreach ($words as $word) {
        if (strlen($word) > 3) {  // Ignore short words
            if (isset($keywords[$word])) {
                $keywords[$word]++;
            } else {
                $keywords[$word] = 1;
            }
        }
    }

    $feedbacks[] = $row;
}

// Aggregate feedback by date (trends over time)
$date_feedbacks = [];
foreach ($feedbacks as $feedback) {
    $date = substr($feedback['FeedbackDate'], 0, 10);  // Extract date (YYYY-MM-DD)
    if (!isset($date_feedbacks[$date])) {
        $date_feedbacks[$date] = ['Complaint' => 0, 'Suggestion' => 0, 'Compliment' => 0];
    }
    $date_feedbacks[$date][$feedback['FeedbackType']]++;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Analysis</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
    </style>
</head>
<body>

<div class="container mt-5">
    <h2 class="text-center mb-4">Feedback Analysis</h2>

    <!-- Display feedback summary -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="dashboard-card">
                <h4>Total Feedback</h4>
                <p class="display-6"><?php echo count($feedbacks); ?></p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="dashboard-card">
                <h4>Feedback by Category</h4>
                <ul class="list-unstyled">
                    <li>Complaints: <?php echo $categories['Complaint']; ?></li>
                    <li>Suggestions: <?php echo $categories['Suggestion']; ?></li>
                    <li>Compliments: <?php echo $categories['Compliment']; ?></li>
                </ul>
            </div>
        </div>
        <div class="col-md-4">
            <div class="dashboard-card">
                <h4>Sentiment Breakdown</h4>
                <ul class="list-unstyled">
                    <li>Positive: <?php echo $sentiment_counts['Positive']; ?></li>
                    <li>Negative: <?php echo $sentiment_counts['Negative']; ?></li>
                    <li>Neutral: <?php echo $sentiment_counts['Neutral']; ?></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Display feedback trends over time -->
    <div class="dashboard-card">
        <h4>Feedback Trends Over Time</h4>
        <canvas id="feedbackTrendsChart"></canvas>
    </div>

    <!-- Display most frequent issues -->
    <div class="dashboard-card mt-4">
        <h4>Most Frequent Issues</h4>
        <ul class="list-unstyled">
            <?php
            arsort($keywords);
            foreach ($keywords as $word => $count) {
                echo "<li>$word: $count</li>";
            }
            ?>
        </ul>
    </div>
</div>

<script>
// Chart.js for feedback trends over time
const ctx = document.getElementById('feedbackTrendsChart').getContext('2d');
const feedbackTrendsData = {
    labels: <?php echo json_encode(array_keys($date_feedbacks)); ?>,
    datasets: [{
        label: 'Complaints',
        data: <?php echo json_encode(array_column($date_feedbacks, 'Complaint')); ?>,
        backgroundColor: 'rgba(255, 99, 132, 0.2)',
        borderColor: 'rgba(255, 99, 132, 1)',
        borderWidth: 1
    }, {
        label: 'Suggestions',
        data: <?php echo json_encode(array_column($date_feedbacks, 'Suggestion')); ?>,
        backgroundColor: 'rgba(54, 162, 235, 0.2)',
        borderColor: 'rgba(54, 162, 235, 1)',
        borderWidth: 1
    }, {
        label: 'Compliments',
        data: <?php echo json_encode(array_column($date_feedbacks, 'Compliment')); ?>,
        backgroundColor: 'rgba(75, 192, 192, 0.2)',
        borderColor: 'rgba(75, 192, 192, 1)',
        borderWidth: 1
    }]
};

const feedbackTrendsChart = new Chart(ctx, {
    type: 'line',
    data: feedbackTrendsData,
    options: {
        responsive: true,
        scales: {
            x: {
                title: {
                    display: true,
                    text: 'Date'
                }
            },
            y: {
                title: {
                    display: true,
                    text: 'Number of Feedbacks'
                }
            }
        }
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
