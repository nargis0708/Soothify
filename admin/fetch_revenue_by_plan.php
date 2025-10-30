<?php
require 'connect.php';
header('Content-Type: application/json');

$query = "SELECT plan, SUM(amount) AS revenue FROM subscriptions GROUP BY plan";
$result = $conn->query($query);

$revenueData = [];
while ($row = $result->fetch_assoc()) {
    $revenueData[] = $row;
}

echo json_encode($revenueData);
?>
