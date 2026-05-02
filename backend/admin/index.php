<?php
session_start();
require 'connect.php'; // Ensure this file exists

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../../frontend/assets/css/admin/admin_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="profile-section">
            <i class="fa-solid fa-user-circle profile-icon"></i>
            <div class="admin-text">Admin Panel</div>
        </div>
        <ul>
            <li><i class="fa-solid fa-users sidebar-icon"></i> <a href="user_management.php">User Management</a></li>
            <li><i class="fa-solid fa-credit-card sidebar-icon"></i> <a href="subscription_management.php">Subscription Management</a></li>
            <li><i class="fa-solid fa-chart-line sidebar-icon"></i> <a href="progress_session_tracking.php">Progress Tracking</a></li>
            <li><i class="fa-solid fa-bell sidebar-icon"></i> <a href="admin_notifications.php">Notifications & Reminders</a></li>
            <li><i class="fa-solid fa-sign-out-alt"></i><a href="logout.php">Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="content" id="main-content">
        <h1>Welcome to Admin Panel</h1>

        <!-- Calendar Selection for User Growth Chart -->
        <div class="calendar-container">
            <label for="monthPicker">Select Month:</label>
            <input type="month" id="monthPicker" value="<?= date('Y-m') ?>">
        </div>

        <div class="charts-container" style="display: flex; justify-content: space-between; gap: 20px; flex-wrap: wrap;">
    <!-- User Growth Chart -->
    <div class="chart-card" style="width: 33%;">
        <canvas id="userChart"></canvas>
    </div>

    <!-- Subscription Trends Chart -->
    <div class="chart-card" style="width: 33%;">
        <canvas id="subscriptionChart"></canvas>
    </div>

    <!-- Revenue Trends Chart -->
    <div class="chart-card" style="width: 33%;">
        <canvas id="revenueChart"></canvas>
    </div>
</div>




        <!-- Table Section -->
        <div class="table-container">
            <h2>Recent Subscriptions</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Plan</th>
                        <th>Amount</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody id="subscriptionTable">
                    <!-- Dynamic rows will be added here -->
                </tbody>
            </table>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            fetchChartData($('#monthPicker').val());
            fetchTableData();

            $('#monthPicker').on('change', function() {
                fetchChartData($(this).val());
            });
        });

        function fetchChartData(month) {
            $.ajax({
                url: 'fetch_user_growth.php',
                method: 'GET',
                data: { month: month },
                dataType: 'json',
                success: function(data) {
                    const labels = data.map(entry => entry.date);
                    const counts = data.map(entry => entry.count);

                    const ctx = document.getElementById('userChart').getContext('2d');
                    
                    if (window.userChartInstance) {
                        window.userChartInstance.destroy();
                    }

                    window.userChartInstance = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'New Users',
                                data: counts,
                                borderColor: '#1e90ff',
                                backgroundColor: 'rgba(30, 144, 255, 0.2)',
                                borderWidth: 2
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            },
                            plugins: {
                                legend: { display: true }
                            }
                        }
                    });
                },
                error: function() {
                    console.log("Error fetching chart data.");
                }
            });
        }

        function fetchTableData() {
            $.ajax({
                url: 'fetch_table_data.php',
                method: 'GET',
                dataType: 'json',
                success: function(data) {
                    const tableBody = $('#subscriptionTable');
                    tableBody.empty();
                    data.forEach(row => {
                        tableBody.append(`
                            <tr>
                                <td>${row.id}</td>
                                <td>${row.user_name}</td>
                                <td>${row.plan}</td>
                                <td>${row.amount}</td>
                                <td>${row.signup_date}</td>
                            </tr>
                        `);
                    });
                }
            });
        }

        $(document).ready(function() {
    fetchSubscriptionTrends();
});

function fetchSubscriptionTrends() {
    $.ajax({
        url: 'fetch_subscription_trends.php',
        method: 'GET',
        dataType: 'json',
        success: function(data) {
            const labels = data.map(entry => entry.plan);
            const counts = data.map(entry => entry.count);

            const ctx = document.getElementById('subscriptionChart').getContext('2d');

            if (window.subscriptionChartInstance) {
                window.subscriptionChartInstance.destroy();
            }

            window.subscriptionChartInstance = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Number of Subscriptions',
                        data: counts,
                        backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0'],
                        borderColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0'],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        },
        error: function() {
            console.log("Error fetching subscription trend data.");
        }
    });
}
function fetchRevenueByPlan() {
    $.ajax({
        url: 'fetch_revenue_by_plan.php',
        method: 'GET',
        dataType: 'json',
        success: function(data) {
            const labels = data.map(entry => entry.plan);
            const revenues = data.map(entry => entry.revenue);

            const ctx = document.getElementById('revenueChart').getContext('2d');

            if (window.revenueChartInstance) {
                window.revenueChartInstance.destroy();
            }

            window.revenueChartInstance = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Revenue',
                        data: revenues,
                        backgroundColor: ['#1E90FF', '#FF5733', '#2ECC71', '#F1C40F']
                    }]
                },
                options: {
                    responsive: true
                }
            });
        }
    });
}

$(document).ready(function() {
    fetchRevenueByPlan();
});

    </script>
</body>
</html>
