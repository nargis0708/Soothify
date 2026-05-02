<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../frontend/pages/index.html");
    exit();
}

require_once 'connect.php';

// Search Filters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Fetch User Progress Data
$user_progress_query = "SELECT user.name, user.email, user.role, 
    user_progress.mood, user_progress.task AS task_progress, 
    user_progress.streak, user_progress.date 
    FROM user_progress 
    JOIN user ON user.email = user_progress.user_email";

// If search is provided, add WHERE clause
if (!empty($search)) {
    $user_progress_query .= " WHERE user.name LIKE '%$search%' OR user.email LIKE '%$search%'";
}

// Append ORDER BY at the end
$user_progress_query .= " ORDER BY user_progress.date DESC";

$user_progress = mysqli_query($conn, $user_progress_query);


// Fetch Session Data with Filtering
$sessions_query = "SELECT sessions.user_email, sessions.session_date, 
    sessions.zoom_link, user.name AS user_name 
    FROM sessions 
    JOIN user ON user.email = sessions.user_email";

// Apply Filtering
if (!empty($search)) {
    $sessions_query .= " WHERE user.name LIKE '%$search%' OR user.email LIKE '%$search%'";
}
if (!empty($status_filter)) {
    $sessions_query .= !empty($search) ? " AND" : " WHERE";
    $sessions_query .= " sessions.status = '$status_filter'";
}

// Apply Ordering
$sessions_query .= " ORDER BY sessions.session_date DESC";

// Execute Query
$sessions = mysqli_query($conn, $sessions_query);

// Error Handling
if (!$sessions) {
    die("Query Failed: " . mysqli_error($conn));
}


// Handle Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $session_id = $_POST['session_id'];
    $new_status = $_POST['new_status'];
    mysqli_query($conn, "UPDATE sessions SET status='$new_status' WHERE id=$session_id");
    header("Location: progress_session_tracking.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Progress & Session Tracking</title>
    <link rel="stylesheet" href="../../frontend/assets/css/admin/progress_session_tracking.css">
</head>
<body>

<div class="container">
    <h1 class="title">Progress & Session Tracking</h1>

    <!-- Search & Filter -->
    <div class="filter-container">
        <form method="GET">
            <input type="text" name="search" placeholder="Search by name or email..." value="<?php echo $search; ?>">
            <select name="status">
                <option value="">All Status</option>
                <option value="attended" <?php if ($status_filter == 'attended') echo 'selected'; ?>>Attended</option>
                <option value="missed" <?php if ($status_filter == 'missed') echo 'selected'; ?>>Missed</option>
            </select>
            <button type="submit">Search</button>
        </form>
    </div>

    <!-- User Progress Section -->
    <div class="section">
        <h2>User Progress</h2>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Mood</th>
                    <th>Task Completion</th>
                    <th>Streaks</th>
                    <th>Last Updated</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($user_progress)) { ?>
                <tr>
                    <td><?php echo $row['name']; ?></td>
                    <td><?php echo $row['email']; ?></td>
                    <td><?php echo ucfirst($row['role']); ?></td>
                    <td><?php echo ucfirst($row['mood']); ?></td>
                    <td><?php echo $row['streak']; ?> Days</td>
                    <td><?php echo $row['date']; ?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- Session Tracking Section -->
    <div class="section">
        <h2>Session Tracking</h2>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Therapist</th>
                    <th>Session Date</th>
                    <th>Status</th>
                    <th>Update Status</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = mysqli_fetch_assoc($sessions)) { ?>
<tr>
    <td><?php echo htmlspecialchars($row['user_name']); ?></td>
    <td><?php echo htmlspecialchars($row['user_email']); ?></td>
    <td><?php echo isset($row['therapist_name']) ? htmlspecialchars($row['therapist_name']) : 'N/A'; ?></td>
    <td><?php echo htmlspecialchars($row['session_date']); ?></td>
    <td class="<?php echo strtolower($row['status']); ?>">
        <?php echo ucfirst($row['status']); ?>
    </td>
    <td>
        <form method="POST">
            <input type="hidden" name="session_id" value="<?php echo $row['session_id']; ?>">
            <select name="new_status">
                <option value="attended" <?php if ($row['status'] == 'attended') echo 'selected'; ?>>Attended</option>
                <option value="missed" <?php if ($row['status'] == 'missed') echo 'selected'; ?>>Missed</option>
            </select>
            <button type="submit" name="update_status">Update</button>
        </form>
    </td>
</tr>
<?php } ?>

            </tbody>
        </table>
    </div>
</div>

</body>
</html>
