<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['email'])) {
    echo json_encode(["error" => "User not logged in"]);
    exit();
}

$user_email = $_SESSION['email'];

// Array of motivational quotes that change daily
$quotes = [
    "Believe in yourself! Every step counts. 💙",
    "You are capable of amazing things. 🌟",
    "Success starts with self-belief. 🚀",
    "Keep going! You're doing great. 💪",
    "Every day is a fresh start. ☀️",
    "Push yourself because no one else will. 🔥"
];

// Select a new quote based on the current day
$quote_of_the_day = $quotes[date('z') % count($quotes)];

// Check if today's notifications exist
$checkQuery = "SELECT id FROM notifications WHERE user_email = ? AND DATE(created_at) = CURDATE()";
$stmt = $conn->prepare($checkQuery);
$stmt->bind_param("s", $user_email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows == 0) { // Insert new notifications if not already present
    $insertQuery = "INSERT INTO notifications (user_email, message, type, is_read) VALUES
        (?, ?, 'motivational', 0),
        (?, 'Complete your daily check-in! 🌟', 'task', 0),
        (?, 'You have an upcoming session. Don’t forget! 📅', 'session', 0)";
    $insertStmt = $conn->prepare($insertQuery);
    $insertStmt->bind_param("sss", $user_email, $quote_of_the_day, $user_email, $user_email);
    $insertStmt->execute();
}

// Fetch notifications grouped by date
$sql = "SELECT * FROM notifications WHERE user_email = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_email);
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];
while ($row = $result->fetch_assoc()) {
    $date = date("Y-m-d", strtotime($row['created_at']));
    $notifications[$date][] = $row;
}

echo json_encode($notifications);
?>
