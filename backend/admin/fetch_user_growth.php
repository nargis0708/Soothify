<?php
session_start();
require 'connect.php'; // Ensure this points to the correct connection file

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}

// Get the selected month from the frontend (default: current month)
$selectedMonth = isset($_GET['month']) ? $_GET['month'] : date('Y-m');

// Fetch user registrations per day for the selected month
$query = "SELECT DATE(signup_date) as date, COUNT(*) as count 
          FROM subscriptions 
          WHERE DATE_FORMAT(signup_date, '%Y-%m') = ?
          GROUP BY DATE(signup_date)
          ORDER BY DATE(signup_date) ASC";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $selectedMonth);
$stmt->execute();
$result = $stmt->get_result();

$user_growth = [];
while ($row = $result->fetch_assoc()) {
    $user_growth[] = $row;
}

if (empty($user_growth)) {
    echo json_encode(["error" => "No data found for selected month: $selectedMonth"]);
} else {
    echo json_encode($user_growth);
}
?>
