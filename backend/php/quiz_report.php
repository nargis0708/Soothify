<?php 
include 'connect.php'; // Use appropriate connection file

// Get the quiz ID from the query string
$quiz_id = $_GET['quiz_id'] ?? null;

// Validate quiz ID
if (!$quiz_id || !is_numeric($quiz_id)) {
    echo "Invalid quiz ID.";
    exit();
}

// Fetch quiz answers
$sql = "SELECT * FROM quiz_answers WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "No quiz results found.";
    exit();
}

$answers = $result->fetch_assoc();

// Answer choices
$answer_choices = ['Always', 'Often', 'Sometimes', 'Rarely'];

// Calculate percentages
$answer_counts = [];
foreach ($answer_choices as $choice) {
    $answer_counts[$choice] = 0;
}
$total_answers = count($answers) - 2; // Exclude `id` and other metadata columns

foreach ($answers as $key => $value) {
    if (strpos($key, 'question') !== false && in_array($value, $answer_choices)) {
        $answer_counts[$value]++;
    }
}

$percentages = [];
foreach ($answer_counts as $choice => $count) {
    $percentages[$choice] = ($total_answers > 0) ? ($count / $total_answers) * 100 : 0;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../frontend/assets/css/quiz_report.css">
    <title>Quiz Results</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="result-container">
        <h2>Your Quiz Results</h2>
        <p>Below is the analysis of your quiz answers:</p>

        <!-- Flex container for table and chart -->
        <div class="result-content">
            <!-- Display percentages in a table -->
            <div class="result-table-container">
                <table class="result-table" cellpadding="10">
                    <thead>
                        <tr>
                            <th>Answer</th>
                            <th>Percentage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($percentages as $choice => $percent): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($choice); ?></td>
                                <td><?php echo round($percent, 2); ?>%</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Chart Section -->
            <div class="chart-container">
                <canvas id="resultChart" width="400" height="200"></canvas>
            </div>
        </div>

        <div class="motivational-line">
            <p>"Taking the first step toward change is often the hardest. Let us help you on your journey. Schedule your appointment today!"</p>
        </div>
        <!-- Action Buttons -->
        <div class="action-buttons">
            <button class="btn-appointment">Schedule an Appointment</button>
            <button class="btn-home" onclick="window.location.href='client-dashboard.php'">Back to Home Page</button>
        </div>
    </div>

    <script>
        var ctx = document.getElementById('resultChart').getContext('2d');
        var resultChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_keys($percentages)); ?>,
                datasets: [{
                    label: 'Quiz Results',
                    data: <?php echo json_encode(array_values($percentages)); ?>,
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 159, 64, 0.2)',
                        'rgba(153, 102, 255, 0.2)'
                    ],
                    borderColor: [
                        'rgba(75, 192, 192, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 159, 64, 1)',
                        'rgba(153, 102, 255, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
            }
        });
    </script>
</body>
</html>
