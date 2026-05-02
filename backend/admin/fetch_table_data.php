<?php
session_start();
require 'connect.php'; // Adjust path as needed

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}

$query = "SELECT id, user_name, plan, amount, signup_date FROM subscriptions ORDER BY signup_date DESC LIMIT 10";
$result = $conn->query($query);

$subscriptions = [];
while ($row = $result->fetch_assoc()) {
    $subscriptions[] = $row;
}

echo json_encode($subscriptions);
?>
