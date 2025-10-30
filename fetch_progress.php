<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['email'])) {
    echo json_encode(["error" => "User not logged in"]);
    exit();
}

$user_email = $_SESSION['email'];

if (isset($_GET['date'])) {
    $selected_date = $_GET['date'];

    // Validate that the selected date is not in the future
    $current_date = date('Y-m-d');
    if ($selected_date > $current_date) {
        echo json_encode([]);
        exit();
    }

    // Fetch progress for the selected date
    $stmt = $conn->prepare("SELECT * FROM user_progress WHERE user_email = ? AND date = ?");
    $stmt->bind_param("ss", $user_email, $selected_date);
    $stmt->execute();
    $result = $stmt->get_result();

    $progress = [];
    while ($row = $result->fetch_assoc()) {
        $progress[] = [
            "mood" => $row["mood"],
            "gratitude_1" => $row["gratitude_1"],
            "gratitude_2" => $row["gratitude_2"],
            "gratitude_3" => $row["gratitude_3"],
            "affirmation" => $row["affirmation"],
            "reflection" => $row["reflection"]
        ];
    }

    echo json_encode($progress);
} else {
    echo json_encode(["error" => "No date provided"]);
}
?>
