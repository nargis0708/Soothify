<?php
session_start();
include 'connect.php';

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['email'];

// Fetch Subscription Details
$stmt = $conn->prepare("
    SELECT plan, amount, payment_id, end_date 
    FROM subscriptions 
    WHERE user_email = ? 
    ORDER BY id DESC LIMIT 1
");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $plan = strtolower(trim($row['plan'])); // Normalize plan value
    $amount = $row['amount'];
    $payment_id = $row['payment_id'];
    $end_date = new DateTime($row['end_date']);

    // Define durations for each plan
    $durations = [
        "1 month" => 30,
        "3 months" => 90,
        "6 months" => 180,
        "12 months" => 365
    ];

    // Fetch correct duration (default to 30 days if no match)
    $subscription_duration = $durations[$plan] ?? 30;

    // **Set start date to today's date**
    $start_date = new DateTime(); // Today's date

    // Calculate Days Left
    $today = new DateTime();
    $days_left = max(0, $today->diff($end_date)->days);

} else {
    // Redirect if no active subscription
    header("Location: subscription.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Details</title>
    <link rel="stylesheet" href="subscriptions_payments.css">
</head>
<body>

    <div class="background">
        <div class="main-container">

            <!-- Subscription Details -->
            <div class="container">
                <h1>Subscription Details</h1>
                <div class="details">
                    <p><strong>Plan:</strong> <span><?php echo htmlspecialchars($plan); ?></span></p>
                    <p><strong>Payment ID:</strong> <span><?php echo htmlspecialchars($payment_id); ?></span></p>
                    <p><strong>Start Date:</strong> <span><?php echo $start_date->format('d F Y'); ?></span></p>
                    <p><strong>End Date:</strong> <span><?php echo $end_date->format('d F Y'); ?></span></p>
                    <p><strong>Amount Paid:</strong> ₹<?php echo htmlspecialchars($amount); ?></p>
                    <p><strong>Days Left:</strong> <span><?php echo $days_left; ?></span> Days</p>
                </div>
            </div>

            <!-- Therapist Details -->
            <div class="container therapist-container">
                <h1>Your Therapist</h1>
                <div class="therapist-image-container">
                    <img src="./images/therapist.png" alt="Therapist">
                </div>
                <div class="details">
                    <p><strong>Name:</strong> Dr. Aisha Kapoor</p>
                    <p><strong>Qualification:</strong> Ph.D. in Clinical Psychology</p>
                    <p class="motivation">"Healing is a journey, not a destination. Trust the process, and you will bloom!"</p>
                </div>
            </div>

        </div> <!-- Closing main-container -->

        <!-- Buttons Below Main Container -->
        <div class="button-container">
            <button class="journey-btn" onclick="window.location.href='progress.php'">Start Your Journey Now</button>
            <button class="book-session-btn" onclick="window.location.href='session_management.php'">Book Session</button>
        </div>

    </div> <!-- Closing background -->
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
