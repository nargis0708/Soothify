<?php
session_start();
include 'connect.php';

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: ../../frontend/pages/book-appointment.html");
    exit();
}

// Fetch user details
$email = $_SESSION['email'];
$stmt = $conn->prepare("SELECT u.name, s.plan, s.status FROM user u LEFT JOIN subscriptions s ON u.email = s.user_email WHERE u.email = ? ORDER BY s.end_date DESC LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$name = $row['name'];
$plan = $row['plan'];
$subscription_status = $row['status'];

// Redirect if subscription is inactive
if ($subscription_status !== 'active') {
    header("Location: subscription_page.php");
    exit();
}

// Define session limits based on plan
$session_limits = [
    "Basic" => 5,
    "Standard" => 12,
    "Premium" => 20,
    "VIP" => 50
];

$session_limit = $session_limits[$plan] ?? 0;

// Zoom API credentials
$zoom_api_key = 'YOUR_ZOOM_API_KEY';
$zoom_api_secret = 'YOUR_ZOOM_API_SECRET';

// Function to create a Zoom meeting
function create_zoom_meeting($topic, $start_time, $duration = 60) {
    global $zoom_api_key, $zoom_api_secret;

    $jwt_payload = [
        'iss' => $zoom_api_key,
        'exp' => time() + 3600, // Token valid for 1 hour
    ];

    $jwt_token = generate_jwt($jwt_payload, $zoom_api_secret);

    $meeting_data = [
        'topic' => $topic,
        'type' => 2, // Scheduled meeting
        'start_time' => $start_time,
        'duration' => $duration,
        'timezone' => 'Asia/Kolkata',
    ];

    $request_url = 'https://api.zoom.us/v2/users/me/meetings';

    $headers = [
        'Authorization: Bearer ' . $jwt_token,
        'Content-Type: application/json',
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $request_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($meeting_data));

    $response = curl_exec($ch);
    curl_close($ch);

    $response_data = json_decode($response, true);

    return $response_data['join_url'] ?? null;
}

// Function to generate JWT
function generate_jwt($payload, $secret) {
    $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
    $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
    $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode($payload)));
    $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $secret, true);
    $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
    return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session Management</title>
    <link rel="stylesheet" href="../../frontend/assets/css/session_management.css">

</head>
<body>
    <div class="container">
        <h1>Welcome, <?php echo htmlspecialchars($name); ?>!</h1>
        <p>Your Plan: <strong><?php echo htmlspecialchars($plan); ?></strong></p>
        <p>Your Session Limit: <strong><?php echo $session_limit; ?></strong> sessions</p>

        <?php if ($session_limit > 0): ?>
            <div class="session-container">
                <h2>Schedule Your Appointments</h2>

                <label for="availability">Select Your Availability:</label>
                <select id="availability">
                    <option value="morning">Morning (6 AM - 10 AM)</option>
                    <option value="afternoon">Afternoon (12 PM - 4 PM)</option>
                    <option value="evening">Evening (6 PM - 10 PM)</option>
                </select>

                <div class="calendar-container">
                    <label>Select Up to 5 Different Dates</label>
                    <input type="date" class="date-picker" id="date1">
                    <input type="date" class="date-picker" id="date2">
                    <input type="date" class="date-picker" id="date3">
                    <input type="date" class="date-picker" id="date4">
                    <input type="date" class="date-picker" id="date5">
                </div>

                <button class="btn schedule-btn" id="schedule-btn">Schedule Appointment</button>

                <div id="session-links" class="session-links" style="display: none;">
                <h3>Your Meeting Links</h3>
                <div class="link-container" id="link1">
                    <p>Day 1: <span class="date-time"></span> <a href="#" target="_blank">Join Meeting</a></p>
                </div>
                <div class="link-container" id="link2">
                    <p>Day 2: <span class="date-time"></span> <a href="#" target="_blank">Join Meeting</a></p>
                </div>
                <div class="link-container" id="link3">
                    <p>Day 3: <span class="date-time"></span> <a href="#" target="_blank">Join Meeting</a></p>
                </div>
                <div class="link-container" id="link4">
                    <p>Day 4: <span class="date-time"></span> <a href="#" target="_blank">Join Meeting</a></p>
                </div>
                <div class="link-container" id="link5">
                    <p>Day 5: <span class="date-time"></span> <a href="#" target="_blank">Join Meeting</a></p>
                </div>
            </div>
            </div>
        <?php else: ?>
            <p style="color: red;">You have no sessions remaining. Upgrade your plan for more sessions.</p>
        <?php endif; ?>
    </div>
<script>
    document.getElementById('schedule-btn').addEventListener('click', function() {
    let availability = document.getElementById('availability').value;
    let dates = [
        document.getElementById('date1').value,
        document.getElementById('date2').value,
        document.getElementById('date3').value,
        document.getElementById('date4').value,
        document.getElementById('date5').value
    ].filter(date => date !== ""); // Remove empty dates

    let linksContainer = document.getElementById('session-links');

    if (dates.length > 0) {
        linksContainer.style.display = "block"; // Show the meeting links section

        dates.forEach((date, index) => {
            let zoomLink = "https://zoom.us/j/123456789"; // Replace with real API call
            let linkContainer = document.getElementById(`link${index + 1}`);

            if (linkContainer) {
                linkContainer.querySelector('.date-time').innerText = date;
                linkContainer.querySelector('a').href = zoomLink;
            }
        });
    } else {
        alert("Please select at least one date.");
    }
});
</script>


<!-- Floating Dashboard Button -->
<a href="client-dashboard.php" class="dashboard-btn" title="Go to Dashboard">
    <i class="fas fa-home"></i>
</a>

<!-- Font Awesome (for icons) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>

<!-- Floating Button CSS -->
<style>
    .dashboard-btn {
        position: fixed;
        top: 20px;
        right: 20px;
        background: #6a9fb5; /* Pastel Blue */
        color: white;
        font-size: 22px;
        padding: 12px 16px;
        border-radius: 50%;
        text-align: center;
        text-decoration: none;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        transition: 0.3s ease;
    }

    .dashboard-btn:hover {
        background: #568a99; /* Slightly darker blue */
        transform: scale(1.1);
    }

    .dashboard-btn i {
        margin: 0;
    }
</style>
</body>
</html>
