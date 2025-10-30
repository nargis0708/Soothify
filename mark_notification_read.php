<?php
include 'connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["id"])) {
    $notif_id = $_POST["id"];

    $update_query = "UPDATE notifications SET status = 'read' WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("i", $notif_id);
    
    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false]);
    }

    $stmt->close();
}
?>
