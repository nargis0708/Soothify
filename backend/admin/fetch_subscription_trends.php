<?php
session_start();
require 'connect.php'; // Ensure the database connection is included

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}

$query = "SELECT plan, COUNT(*) as count FROM subscriptions GROUP BY plan";
$result = $conn->query($query);

$subscriptionData = [];
while ($row = $result->fetch_assoc()) {
    $subscriptionData[] = $row;
}

echo json_encode($subscriptionData);
?>
