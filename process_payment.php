<?php
session_start();
include 'connect.php';

// Start output buffering to avoid premature output
ob_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['razorpay_payment_id'])) {
        echo "Payment Failed: No Payment ID received";
        exit();
    }

    $paymentId = $_POST['razorpay_payment_id'];
    $plan = $_POST['plan'];
    $amount = $_POST['amount'];
    $userName = $_POST['username'];
    $email = $_SESSION['email']; // Get logged-in user's email

    // Validate amount
    if (!is_numeric($amount) || $amount <= 0) {
        echo "Invalid payment amount";
        exit();
    }

    // Determine subscription duration
    $duration = match ($plan) {
        "Basic" => 30,
        "Standard" => 90,
        "Premium" => 180,
        "VIP" => 365,
        default => 30
    };
    $endDate = date('Y-m-d', strtotime("+$duration days"));

    // Store payment details in the database
    $stmt = $conn->prepare("INSERT INTO subscriptions (user_email, user_name, plan, amount, payment_id, end_date) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssiss", $email, $userName, $plan, $amount, $paymentId, $endDate);

    if ($stmt->execute()) {
        echo "success"; // Send success response
        exit();
    } else {
        echo "Error: " . $stmt->error;
        exit();
    }
}

// End output buffering and send any output to the browser
ob_end_flush();
?>
