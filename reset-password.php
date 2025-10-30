<?php
// Debugging line: Check if the token is passed via URL
var_dump($_GET); // This will print out the entire $_GET array

include 'connect.php'; // Database connection

// Check if the token is passed via GET
if (isset($_GET['token'])) {
    $token = $_GET['token']; // Get the token from URL

    // Get the current time
    $currentTime = time(); // Store time in a variable

    // Check if token exists and is not expired
    $stmt = $conn->prepare("SELECT * FROM password_resets WHERE token = ? AND expiry_time > ?");
    $stmt->bind_param("si", $token, $currentTime); // Pass the variable instead of the function
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Token is valid, display the password reset form
        echo '
        <form method="POST" action="reset-password.php">
            <input type="hidden" name="token" value="' . $token . '">
            <input type="password" name="password" placeholder="Enter new password" required>
            <button type="submit" name="reset_password">Reset Password</button>
        </form>
        ';
    } else {
        echo "Invalid or expired token.";
    }
} else {
    echo "No token provided.";
}

// If form is submitted for password reset
if (isset($_POST['reset_password'])) {
    // Get the token and the new password
    $token = $_POST['token']; // Assuming token is passed via a hidden input field
    $newPassword = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the new password

    // Get the current time
    $currentTime = time(); // Store time in a variable

    // Validate the token
    $stmt = $conn->prepare("SELECT * FROM password_resets WHERE token = ? AND expiry_time > ?");
    $stmt->bind_param("si", $token, $currentTime); // Pass the variable instead of the function
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Token is valid, get the email associated with it
        $row = $result->fetch_assoc();
        $email = $row['email'];

        // Update the user's password
        $stmt = $conn->prepare("UPDATE user SET password = ? WHERE email = ?");
        $stmt->bind_param("ss", $newPassword, $email);
        if ($stmt->execute()) {
            // Password successfully updated
            echo "Password has been successfully reset.";
        } else {
            echo "Failed to update password. Please try again.";
        }

        // Delete the used reset token (for security reasons)
        $stmt = $conn->prepare("DELETE FROM password_resets WHERE token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();
    } else {
        echo "Invalid or expired token.";
    }
}
?>
