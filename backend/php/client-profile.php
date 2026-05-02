<?php
// Include database connection
include 'connect.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and get form data
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $country = mysqli_real_escape_string($conn, $_POST['country']);
    $state = mysqli_real_escape_string($conn, $_POST['state']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);

    // Validate form fields
    if (empty($first_name) || empty($last_name) || empty($phone) || empty($email) || empty($country) || empty($state) || empty($city)) {
        echo "All fields are required!";
        exit();
    }

    // Prepare SQL query to insert data into the database
    $sql = "INSERT INTO client_profile (first_name, last_name, phone, email, country, state, city) 
            VALUES ('$first_name', '$last_name', '$phone', '$email', '$country', '$state', '$city')";

    // Execute the query and check if it was successful
    if (mysqli_query($conn, $sql)) {
        // Redirect to client dashboard after successful submission
        header("Location: client-dashboard.php"); 
        exit();  // Ensure that the script stops here after redirection
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);  // Display any error if query fails
    }
}
?>
