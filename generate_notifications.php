<?php
include 'connect.php';

// Get all user emails from the database
$userQuery = "SELECT email FROM users";
$userResult = $conn->query($userQuery);

if ($userResult->num_rows > 0) {
    while ($user = $userResult->fetch_assoc()) {
        $user_email = $user['email'];

        // Check if today's notifications already exist
        $checkQuery = "SELECT id FROM notifications WHERE user_email = ? AND DATE(created_at) = CURDATE()";
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param("s", $user_email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 0) { // If no notifications exist for today, insert them
            $insertQuery = "INSERT INTO notifications (user_email, message, type, is_read) VALUES
                (?, 'Believe in yourself! Every step counts. 💙', 'motivational', 0),
                (?, 'Stay strong! You are capable of amazing things. 🌟', 'motivational', 0),
                (?, 'Complete your daily check-in! 🌟', 'task', 0),
                (?, 'You have an upcoming session. Don’t forget! 📅', 'session', 0)";
            $insertStmt = $conn->prepare($insertQuery);
            $insertStmt->bind_param("ssss", $user_email, $user_email, $user_email, $user_email);
            $insertStmt->execute();
        }
    }
}

echo "Daily notifications added successfully!";
?>
