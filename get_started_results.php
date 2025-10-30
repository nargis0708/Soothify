<?php
// results.php

// Assuming data is fetched from the database here, for demonstration, we're using mock data.
$labels = ["Q1", "Q2", "Q3", "Q4", "Q5", "Q6", "Q7", "Q8", "Q9", "Q10", "Q11", "Q12", "Q13", "Q14", "Q15", "Q16", "Q17", "Q18", "Q19", "Q20", "Q21", "Q22", "Q23", "Q24", "Q25"];
$answers = [5, 4, 4, 3, 3, 2, 2, 1, 1, 1, 2, 2, 3, 3, 3, 2, 2, 1, 1, 1, 2, 2, 3, 3, 3]; // Static data for the answers (Decreasing trend)

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Results | Soothify</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

    <!-- Chart Container -->
    <div class="chart-container">
        <h2>Your Quiz Analysis</h2>
        <canvas id="myChart"></canvas>
    </div>

    <!-- CTA Container (below the chart) -->
    <div class="cta-container">
        <h2>Unlock More Features and Insights!</h2>
        <p>By subscribing, you'll gain access to exclusive content and personalized recommendations tailored just for you. Don't miss out on enhancing your experience!</p>
        <a href="subscription_page.php" class="cta-button">Get Started Now</a>
    </div>

    <script>
        // Get the context for the chart
        var ctx = document.getElementById('myChart').getContext('2d');

        // Chart.js Configuration for a static moderately decreasing line chart
        var myChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($labels); ?>, // Use PHP data as labels
                datasets: [{
                    label: 'Quiz Responses (Scale 1-5)',
                    data: <?php echo json_encode($answers); ?>, // Static data for answers (Decreasing trend)
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.4,
                    fill: true,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Your Quiz Response Trend',
                        font: {
                            size: 16
                        }
                    },
                    tooltip: {
                        enabled: true,
                        mode: 'nearest',
                        intersect: false
                    },
                },
                scales: {
                    x: {
                        grid: {
                            display: true,
                            color: '#ddd'
                        }
                    },
                    y: {
                        min: 0,
                        max: 5,
                        grid: {
                            display: true,
                            color: '#ddd'
                        }
                    }
                }
            }
        });
    </script>

    <!-- Styles -->
    <style>
        /* General Page Styles */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
            color: #333;
        }

        h2 {
            font-size: 2rem;
            color: #333;
            margin-bottom: 20px;
        }

        /* Chart Container Styles */
        .chart-container {
            padding: 20px;
            background-color: #fff;
            margin: 40px auto;
            max-width: 800px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            text-align: center;
        }

        /* CTA Container */
        .cta-container {
            background-color: #f8f9fa;
            padding: 20px;
            margin-top: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            animation: fadeInUp 1.5s ease-out;
            text-align: center;
        }

        /* CTA Heading */
        .cta-container h2 {
            font-size: 2rem;
            color: #333;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* CTA Description */
        .cta-container p {
            font-size: 1rem;
            color: #666;
            margin-bottom: 20px;
            line-height: 1.6;
        }

        /* CTA Button */
        .cta-button {
            display: inline-block;
            background-color: #28a745;
            color: white;
            padding: 12px 25px;
            font-size: 1.2rem;
            border-radius: 5px;
            text-decoration: none;
            text-align: center;
            transition: background-color 0.3s ease;
        }

        .cta-button:hover {
            background-color: #218838;
        }

        /* Keyframe animations for CTA */
        @keyframes fadeInUp {
            0% {
                opacity: 0;
                transform: translateY(30px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>

    <!-- Floating Dashboard Button -->
<a href="client-dashboard.php" class="dashboard-btn" title="Go to Dashboard">
    <i class="fas fa-home"></i>
</a>

<!-- Font Awesome (for icons) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>

<!-- Floating Button CSS -->
<style>
    .dashboard-btn {
        position: fixed;
        top: 20px;
        right: 20px;
        background: #6a9fb5; /* Pastel Blue */
        color: white;
        font-size: 22px;
        padding: 12px 16px;
        border-radius: 50%;
        text-align: center;
        text-decoration: none;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        transition: 0.3s ease;
    }

    .dashboard-btn:hover {
        background: #568a99; /* Slightly darker blue */
        transform: scale(1.1);
    }

    .dashboard-btn i {
        margin: 0;
    }
</style>

</body>
</html>
