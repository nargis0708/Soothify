<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: ../../frontend/pages/book-appointment.html");
    exit();
}

// Database connection details
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "client";

// Create connection
$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get user email from the session
$user_email = $_SESSION['email'];

// Fetch user ID based on the email
$stmt = $conn->prepare("SELECT id FROM user WHERE email = ?");
$stmt->bind_param("s", $user_email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $user_id = $row['id'];
} else {
    echo "User not found!";
    exit();
}

// Process form data
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $questions = [];
    for ($i = 1; $i <= 10; $i++) {
        $questions[] = $_POST['question' . $i] ?? null;
    }

    // Insert quiz data (no duplicate check now)
    $insertQuery = "INSERT INTO quiz_answers (user_id, question1, question2, question3, question4, question5, question6, question7, question8, question9, question10)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $insertStmt = $conn->prepare($insertQuery);
    $insertStmt->bind_param(
        "issssssssss",
        $user_id,
        ...$questions
    );

    if ($insertStmt->execute()) {
        header("Location: quiz_report.php?quiz_id=" . $conn->insert_id);
        exit();
    } else {
        echo "Error: Could not save your answers. Please try again.";
    }
}

$conn->close();
?>
