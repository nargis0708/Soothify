<?php 
// Include PHPMailer
require_once __DIR__ . '/../phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../phpmailer/src/SMTP.php';
require_once __DIR__ . '/../phpmailer/src/Exception.php';

// Use PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include 'connect.php';
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['signup'])) {
        // Signup form submission for client only
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $email = isset($_POST['email']) ? trim(htmlspecialchars($_POST['email'])) : '';
        $password = isset($_POST['password']) ? trim($_POST['password']) : '';

        // Validate that all fields are provided
        if (empty($name) || empty($email) || empty($password)) {
            echo "All fields are required!";
            exit();
        }

        // Check if email already exists in the database
        $stmt = $conn->prepare("SELECT * FROM user WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo "Email already exists!";
            exit();
        }

        // Hash the password for security
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Insert the new client user into the database
        $role = 'client'; // Hardcoded for client signup only
        $stmt = $conn->prepare("INSERT INTO user (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $hashedPassword, $role);

        if ($stmt->execute()) {
            // Send a signup email notification
            sendSignupEmail($email, $role);

            // Set session variables
            $_SESSION['role'] = $role;
            $_SESSION['email'] = $email;

            // Redirect to the client dashboard after successful signup
            header("Location: client-dashboard.php");
            exit();
        } else {
            echo "Error: Could not sign up. Please try again.";
        }
    } else if (isset($_POST['signin'])) {
        // Signin form submission
        $role = isset($_POST['role']) ? trim($_POST['role']) : '';
        $emailOrId = isset($_POST['email']) ? trim(htmlspecialchars($_POST['email'])) : (isset($_POST['id']) ? trim(htmlspecialchars($_POST['id'])) : '');
        $password = isset($_POST['password']) ? trim($_POST['password']) : '';

        // Validate that all fields are provided
        if (empty($role) || empty($emailOrId) || empty($password)) {
            echo "All fields are required!";
            exit();
        }

        // 🔹 **Admin Login Logic (Updated)**
        if (strcasecmp($role, 'admin') === 0) {
            $stmt = $conn->prepare("SELECT * FROM user WHERE name = ? AND role = ?");
            if (!$stmt) {
                die("Database error: " . $conn->error);
            }
            $stmt->bind_param("ss", $emailOrId, $role);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();

                // **Direct password comparison (no hashing)**
                if ($password === $row['password']) {
                    // Set session variables for admin
                    $_SESSION['role'] = $role;
                    $_SESSION['name'] = $row['name'];

                    // Redirect to admin panel
                    header("Location: ../admin/index.php");
                    exit();
                } else {
                    echo "Incorrect password!";
                }
            } else {
                echo "Admin user not found!";
            }
        } 
        // 🔹 **Client & Therapist Login Logic (Unchanged)**
        else {
            $stmt = $conn->prepare("SELECT * FROM user WHERE (email = ? OR id = ?) AND role = ?");
            if (!$stmt) {
                die("Database error: " . $conn->error);
            }
            $stmt->bind_param("sss", $emailOrId, $emailOrId, $role);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();

                // Verify the password
                if (password_verify($password, $row['password'])) {
                    // Set session variables
                    $_SESSION['role'] = $role;
                    $_SESSION['email'] = $row['email'];

                    // Redirect to the appropriate dashboard
                    if ($role === 'client') {
                        header("Location: client-dashboard.php");
                    } elseif ($role === 'therapist') {
                        header("Location: therapist-dashboard.php");
                    }
                    exit();
                } else {
                    echo "Incorrect password!";
                }
            } else {
                echo "User not found!";
            }
        }
    }
}

// Function to send signup email notification
function sendSignupEmail($recipientEmail, $role) {
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
        $mail->Subject = "$role Signup Notification";
        $mail->Body = "Dear $role,<br><br>You have successfully signed up at " . date("Y-m-d H:i:s") . ".<br><br>Welcome to Soothify!";

        $mail->send();
    } catch (Exception $e) {
        echo "Signup successful, but email notification failed. Error: {$mail->ErrorInfo}";
    }
}
?>
