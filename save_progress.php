<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['email'])) {
    echo "User not logged in.";
    exit();
}

$user_email = $_SESSION['email'];
$mood = $_POST['mood'] ?? '';
$gratitude_1 = $_POST['gratitude_1'] ?? '';
$gratitude_2 = $_POST['gratitude_2'] ?? '';
$gratitude_3 = $_POST['gratitude_3'] ?? '';
$affirmation = $_POST['affirmation'] ?? '';
$reflection = $_POST['reflection'] ?? '';

// Handling Daily Task Checkboxes
$task = isset($_POST['task']) ? implode(", ", $_POST['task']) : '';

$stmt = $conn->prepare("INSERT INTO user_progress (user_email, mood, gratitude_1, gratitude_2, gratitude_3, affirmation, reflection, task, date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
$stmt->bind_param("ssssssss", $user_email, $mood, $gratitude_1, $gratitude_2, $gratitude_3, $affirmation, $reflection, $task);

if ($stmt->execute()) {
    echo "Progress saved successfully!";
} else {
    echo "Error saving progress.";
}

$stmt->close();
$conn->close();
?>
