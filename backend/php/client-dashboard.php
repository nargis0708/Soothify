<?php
// Start session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    // Redirect to login page if the session doesn't exist
    header("Location: ../../frontend/pages/book-appointment.html");
    exit();
}

// Include PHPMailer files
require_once __DIR__ . '/../phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../phpmailer/src/SMTP.php';
require_once __DIR__ . '/../phpmailer/src/Exception.php';

// Use PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include database connection
include 'connect.php';

// Retrieve the email from the session
$email = $_SESSION['email'];
$name = ''; // Default value for the name

// Fetch the user's name and subscription status from the database
$stmt = $conn->prepare("SELECT u.name, s.status 
                        FROM user u
                        LEFT JOIN subscriptions s ON u.email = s.user_email
                        WHERE u.email = ? 
                        ORDER BY s.end_date DESC LIMIT 1");

if ($stmt) {
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $name = $row['name']; // Get the client's name
        $subscription_status = $row['status']; // Get the subscription status
    }
    $stmt->close();
} else {
    die("Database query failed: " . $conn->error);
}

// Function to send login notification email (optional)
function sendLoginEmail($recipientEmail, $name) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'shanayashukri@gmail.com';
        $mail->Password = 'sbkmmblmpzmiqebt';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('shanayashukri@gmail.com', 'Soothify');
        $mail->addAddress($recipientEmail);

        $mail->isHTML(true);
        $mail->Subject = "Welcome Back, $name!";
        $mail->Body = "Dear $name,<br><br>You have successfully logged into your Soothify dashboard on " . date("Y-m-d H:i:s") . ".<br><br>If this wasn't you, please contact support immediately.";

        $mail->send();
    } catch (Exception $e) {
        echo "Email notification failed: {$mail->ErrorInfo}";
    }
}

// Send a lo.gin notification email (optional, call only when needed)
sendLoginEmail($email, $name);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../frontend/assets/css/dashboard.css">
</head>
<body>

   <!-- #region-->
  
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="welcome">
            <i class="fas fa-user-circle"></i> <!-- Profile icon -->
            <h1>Welcome, <?php echo htmlspecialchars($name); ?>!</h1> <!-- Display client's name -->
        </div>
        <ul>
            <li><a href="../../frontend/pages/client-profile.html"><i class="fas fa-user"></i> Profile</a></li>
            <li><a href="progress.php"><i class="fas fa-chart-line"></i> Progress Tracking</a></li>
            <li>
                <?php if ($subscription_status == 'active') { ?>
                      <a href="session_management.php"><i class="fas fa-calendar-check"></i> Session Management</a>
                <?php } else { ?>
                       <a href="subscription_page.php"><i class="fas fa-calendar-check"></i> Subscribe for Sessions</a>
                <?php } ?>
            </li>
            <li>
                <?php if ($subscription_status == 'active') { ?>
                    <a href="subscriptions_payments.php"><i class="fas fa-credit-card"></i> Subscriptions & Payments</a>
                <?php } else { ?>
                    <a href="subscription_page.php"><i class="fas fa-credit-card"></i> Subscriptions & Payments</a>
                <?php } ?>
            </li>
            <li><a href="../../frontend/pages/blog.html"><i class="fas fa-book"></i> Read Blogs</a></li>
            <li><a href="notifications.php"><i class="fas fa-bell"></i> Notifications & Reminders</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="content">
         <!-- Image Container -->
    <div  class="heroContainer"> <img src="../../frontend/assets/images/new14.jpg" alt="" class="heroImage"></div>
       <div class="contentContainer">
       <h1>Welcome to Soothify, <?php echo htmlspecialchars($name); ?>!</h1>
        <h1>Your First Step Towards a Better Tomorrow</h1>
        <p>India's #1 Organization for Mental Health and Holistic Wellness</p>

        <div class="text-box">  
          

            <div class="buttonContainer"> <span>Discover insights about your mental well-being by answering a few simple questions.</span></div>
            <div class="buttonContainer"><a href="../../frontend/pages/get-started.html" class="hero-btn">Get started</a></div>
        </div>
       </div>
    </div>
</body>
</html>
