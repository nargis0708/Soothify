<?php
session_start();
include 'connect.php';

// Removed the redirection check



// Fetch all notifications
$query = "SELECT id, user_email, message, type, status, DATE_FORMAT(created_at, '%d-%m-%Y %h:%i %p') AS formatted_date 
          FROM notifications 
          ORDER BY FIELD(status, 'unread', 'read'), created_at DESC";
$result = $conn->query($query);
$notifications = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Notifications</title>
    <link rel="stylesheet" href="../../frontend/assets/css/admin/admin_notifications.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
</head>
<body>

<div class="admin-container">
    <h2>📢 Manage Notifications</h2>

    <!-- Notification List -->
    <table>
        <thead>
            <tr>
                <th>User Email</th>
                <th>Message</th>
                <th>Type</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($notifications as $notif) : ?>
                <tr class="<?php echo $notif['status']; ?>" id="notif-<?php echo $notif['id']; ?>">
                    <td><?php echo $notif['user_email']; ?></td>
                    <td><?php echo $notif['message']; ?></td>
                    <td><?php echo ucfirst($notif['type']); ?></td>
                    <td><?php echo ucfirst($notif['status']); ?></td>
                    <td>
                        <button class="mark-read" data-id="<?php echo $notif['id']; ?>">
                            Mark as Read
                        </button>
                        <button class="delete-notif" data-id="<?php echo $notif['id']; ?>">❌</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Send New Notification -->
    <h3>Send Notification</h3>
    <form id="sendNotificationForm">
        <label>User Email:</label>
        <input type="email" name="user_email" required>
        
        <label>Message:</label>
        <textarea name="message" required></textarea>
        
        <label>Type:</label>
        <select name="type">
            <option value="motivational">Motivational</option>
            <option value="task">Task Reminder</option>
            <option value="session">Session Reminder</option>
            <option value="subscription">Subscription Alert</option>
        </select>

        <button type="submit">Send Notification</button>
    </form>
</div>

<script>
$(document).on("click", ".mark-read", function() {
    var notifId = $(this).data("id");
    $.post("update_notification.php", { id: notifId, status: "read" }, function(response) {
        if (response.success) {
            $("#notif-" + notifId).removeClass("unread").addClass("read");
        }
    }, "json");
});

$(document).on("click", ".delete-notif", function() {
    var notifId = $(this).data("id");
    $.post("delete_notification.php", { id: notifId }, function(response) {
        if (response.success) {
            $("#notif-" + notifId).fadeOut();
        }
    }, "json");
});

// Send New Notification
$("#sendNotificationForm").submit(function(e) {
    e.preventDefault();
    $.post("send_notification.php", $(this).serialize(), function(response) {
        if (response.success) {
            alert("Notification Sent Successfully!");
            location.reload();
        }
    }, "json");
});
</script>

</body>
</html>
