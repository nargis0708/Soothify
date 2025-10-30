<?php
// Start the session
session_start();

// Unset all session variables to remove user data
session_unset();

// Destroy the session
session_destroy();

// Optional: Expire the session cookie if it exists
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/'); // Expire the cookie
}

// Redirect to the homepage (index.html) after logout
header("Location: ../index.html");
exit();
?>
