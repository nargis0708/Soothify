<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: ../../frontend/pages/book-appointment.html");
    exit();
}

include 'connect.php';

$user_email = $_SESSION['email'];
date_default_timezone_set('Asia/Kolkata');

// Fetch notifications (sync with admin-side updates)
$query = "SELECT id, type, message, DATE_FORMAT(created_at, '%d-%m-%Y') AS formatted_date, status 
          FROM notifications 
          WHERE user_email = ? 
          ORDER BY FIELD(status, 'unread', 'read'), created_at DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $user_email);
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}
$stmt->close();

// Fetch unread count
$unread_query = "SELECT COUNT(*) FROM notifications WHERE user_email = ? AND status = 'unread'";
$stmt = $conn->prepare($unread_query);
$stmt->bind_param("s", $user_email);
$stmt->execute();
$stmt->bind_result($unread_count);
$stmt->fetch();
$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <link rel="stylesheet" href="../../frontend/assets/css/notifications.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
</head>
<body>

<div class="notification-container">
    <div class="notification-header">
        <h2>🔔 Notifications</h2>
        <span class="notification-count" id="notification-count"><?php echo $unread_count; ?></span>
    </div>

    <div id="notifications-list">
        <?php foreach ($notifications as $notif) : ?>
            <div class="notification-box <?php echo ($notif['status'] === 'unread') ? 'unread' : ''; ?>" id="notif-<?php echo $notif['id']; ?>">
                <span class="notif-date"><?php echo $notif['formatted_date']; ?></span>
                <p><?php echo $notif['message']; ?></p>
                <?php if ($notif['status'] === 'unread') : ?>
                    <button class="mark-read" data-id="<?php echo $notif['id']; ?>">Mark as Read</button>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
$(document).on("click", ".mark-read", function() {
    var notifId = $(this).data("id");
    var notificationBox = $("#notif-" + notifId);

    $.ajax({
        url: "mark_notification_read.php",
        type: "POST",
        data: { id: notifId },
        dataType: "json",
        success: function(response) {
            if (response.success) {
                notificationBox.find(".mark-read").fadeOut(); // Hide button
                notificationBox.removeClass("unread"); // Remove unread styling
                
                // Move the notification to the last position
                $("#notifications-list").append(notificationBox);

                // Update unread count dynamically
                var count = parseInt($("#notification-count").text());
                if (count > 0) {
                    $("#notification-count").text(count - 1);
                }
            }
        }
    });
});
</script>

</body>
</html>
