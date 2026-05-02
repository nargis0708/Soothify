<?php
include 'connect.php'; // Your database connection
require_once __DIR__ . '/../phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../phpmailer/src/SMTP.php';
require_once __DIR__ . '/../phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_email'])) {
    $email = trim($_POST['email']); // User's email

    // Check if the email exists in the database
    $stmt = $conn->prepare("SELECT * FROM user WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Email exists, generate a password reset token
        $resetToken = bin2hex(random_bytes(16)); // Generate a random token
        $expiryTime = time() + 3600; // 1 hour expiry time

        // Store the token in the database (in a 'password_resets' table)
        $stmt = $conn->prepare("INSERT INTO password_resets (email, token, expiry_time) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $email, $resetToken, $expiryTime);
        $stmt->execute();

        // Send the password reset email
        $resetLink = "http://localhost/Soothify/frontend/pages/reset-password.html?token=$resetToken"; // Localhost URL for testing
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'shaikhnargis784@gmail.com'; // Your email
            $mail->Password = 'tmilapepjvhfojls'; // Your app password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('shaikhnargis784@gmail.com', 'Soothify');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request';
            $mail->Body = "We received a request to reset your password. Click the link below to reset it:<br><a href='$resetLink'>$resetLink</a>";

            $mail->send();
            echo "Password reset link has been sent to your email.";
        } catch (Exception $e) {
            echo "Error sending email: {$mail->ErrorInfo}";
        }
    } else {
        echo "Email not found. Please check or sign up.";
    }
}
?>
