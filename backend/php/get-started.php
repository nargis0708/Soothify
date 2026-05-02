<?php
// Database connection settings
$host = 'localhost'; // Update with your DB host
$dbname = 'client';  // Your database name
$username = 'root';  // Your database username
$password = '';      // Your database password

// Create a new PDO instance
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data from the POST request
    $q1 = $_POST['q1'];  // Relationship status
    $q2 = $_POST['q2'];  // Country
    $q3 = $_POST['q3'];  // Religious/spiritual
    $q4 = $_POST['q4'];  // Gender
    $q5 = $_POST['q5'];  // Reason for visiting Soothify
    $q6 = $_POST['q6'];  // Feel lonely
    $q7 = $_POST['q7'];  // Experience mood swings
    $q8 = $_POST['q8'];  // Handle conflicts
    $q9 = $_POST['q9'];  // Express emotions
    $q10 = $_POST['q10']; // Connected to family
    $q11 = $_POST['q11']; // Exercise
    $q12 = $_POST['q12']; // Balanced diet
    $q13 = $_POST['q13']; // Fatigue
    $q14 = $_POST['q14']; // Medical check-up
    $q15 = $_POST['q15']; // Feel physically healthy
    $q16 = $_POST['q16']; // Job satisfaction
    $q17 = $_POST['q17']; // Motivated at work
    $q18 = $_POST['q18']; // Work-related stress
    $q19 = $_POST['q19']; // Work-life balance
    $q20 = $_POST['q20']; // Feel appreciated at work
    $q21 = $_POST['q21']; // Set personal goals
    $q22 = $_POST['q22']; // Work on self-improvement
    $q23 = $_POST['q23']; // Seek feedback
    $q24 = $_POST['q24']; // Open to learning
    $q25 = $_POST['q25']; // Feel personal growth

    // Prepare SQL query to insert data into the database
    $sql = "INSERT INTO get_started_response (
                q1, q2, q3, q4, q5, q6, q7, q8, q9, q10, q11, q12, q13, q14, q15,
                q16, q17, q18, q19, q20, q21, q22, q23, q24, q25
            ) VALUES (
                :q1, :q2, :q3, :q4, :q5, :q6, :q7, :q8, :q9, :q10, :q11, :q12, :q13,
                :q14, :q15, :q16, :q17, :q18, :q19, :q20, :q21, :q22, :q23, :q24, :q25
            )";

    // Prepare the statement
    $stmt = $pdo->prepare($sql);

    // Bind the values
    $stmt->bindParam(':q1', $q1);
    $stmt->bindParam(':q2', $q2);
    $stmt->bindParam(':q3', $q3);
    $stmt->bindParam(':q4', $q4);
    $stmt->bindParam(':q5', $q5);
    $stmt->bindParam(':q6', $q6);
    $stmt->bindParam(':q7', $q7);
    $stmt->bindParam(':q8', $q8);
    $stmt->bindParam(':q9', $q9);
    $stmt->bindParam(':q10', $q10);
    $stmt->bindParam(':q11', $q11);
    $stmt->bindParam(':q12', $q12);
    $stmt->bindParam(':q13', $q13);
    $stmt->bindParam(':q14', $q14);
    $stmt->bindParam(':q15', $q15);
    $stmt->bindParam(':q16', $q16);
    $stmt->bindParam(':q17', $q17);
    $stmt->bindParam(':q18', $q18);
    $stmt->bindParam(':q19', $q19);
    $stmt->bindParam(':q20', $q20);
    $stmt->bindParam(':q21', $q21);
    $stmt->bindParam(':q22', $q22);
    $stmt->bindParam(':q23', $q23);
    $stmt->bindParam(':q24', $q24);
    $stmt->bindParam(':q25', $q25);

    // Execute the statement
    if ($stmt->execute()) {
        header("Location: get_started_results.php");
    } else {
        echo "Error submitting data.";
    }
}
?>
