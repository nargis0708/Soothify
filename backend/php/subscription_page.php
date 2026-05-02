<?php
session_start();
include 'connect.php';

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: ../../frontend/pages/book-appointment.html");
    exit();
}

$email = $_SESSION['email'];

$stmt = $conn->prepare("SELECT name FROM user WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $userName = $row['name'];
} else {
    $userName = "Guest";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Plans</title>
    <link rel="stylesheet" href="../../frontend/assets/css/subscription.css">
</head>
<body>

    <h1>Welcome, <?php echo htmlspecialchars($userName); ?>!</h1>

    <div class="card-container">
        <?php
        $plans = [
            ["Basic", 399, "1 Month", "Access to essential features|5 Free Sessions|24/7 Support|Weekly Progress Reports|Basic Resources"],
            ["Standard", 699, "3 Months", "Premium Support|12 Free Sessions|Access to Exclusive Articles|Priority Support|Advanced Analytics"],
            ["Premium", 999, "6 Months", "Personalized Support|18 Free Sessions + 2 bonus sessions||Unlimited Sessions|Exclusive Premium Resources|Priority Email Response"],
            ["VIP", 1299, "12 Months", "VIP Support|Upto 50 sessions|Unlimited Resources|Advanced Analytical Tools|Exclusive Workshops"]
        ];

        foreach ($plans as $plan) {
            echo '
            <div class="card" data-features="'.$plan[3].'">
                <h2>'.$plan[0].' Plan</h2>
                <p>Includes exclusive features</p>
                <div class="price">₹'.$plan[1].'</div>
                <div class="time-limit">'.$plan[2].'</div>
                <div class="button-group">
                    <button class="btn subscribe" onclick="makePayment(\''.$plan[0].'\', '.$plan[1].', \''.$userName.'\')">Subscribe</button>
                    <button class="btn read-more" onclick="showFeatures(this)">Read More</button>
                </div>
            </div>';
        }
        ?>
    </div>

    <!-- Pop-up for Features -->
    <div class="overlay" onclick="hideFeatures()"></div>
    <div class="feature-popup">
        <h3>Plan Features</h3>
        <ul id="feature-details"></ul>
        <button class="close-btn" onclick="hideFeatures()">Close</button>
    </div>

    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
        function makePayment(plan, amount, username) {
            var options = {
                "key": "rzp_test_FyYyE55wTENTLj", // Replace with your Razorpay API Key
                "amount": amount * 100,
                "currency": "INR",
                "name": "Soothify",
                "description": plan + " Subscription",
                "handler": function(response) {
                    fetch('process_payment.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `razorpay_payment_id=${response.razorpay_payment_id}&plan=${plan}&amount=${amount}&username=${username}`
                    })
                    .then(response => response.text())
                    .then(data => {
                        if (data.trim() === 'success') {
                            window.location.href = 'subscriptions_payments.php'; // Redirect upon success
                        } else {
                            alert('Payment Failed: ' + data); // Show error if payment fails
                        }
                    })
                    .catch(error => {
                        alert('An error occurred: ' + error.message);
                    });
                },
                "prefill": {
                    "name": username,
                    "email": "<?php echo $email; ?>",
                    "contact": "" // Razorpay will ask for contact inside popup
                },
                "theme": { "color": "#3399cc" },
                "method": {
                    "netbanking": true,
                    "card": true,
                    "upi": true,
                    "wallet": true
                }
            };

            var rzp = new Razorpay(options);
            rzp.open();
        }

        function showFeatures(button) {
            const card = button.closest('.card');
            const features = card.getAttribute('data-features').split('|');
            const featureList = document.getElementById('feature-details');
            featureList.innerHTML = features.map(feature => `<li>${feature}</li>`).join('');
            document.querySelector('.feature-popup').style.display = 'block';
            document.querySelector('.overlay').style.display = 'block';
        }

        function hideFeatures() {
            document.querySelector('.feature-popup').style.display = 'none';
            document.querySelector('.overlay').style.display = 'none';
        }
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
