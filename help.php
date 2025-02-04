<?php
include 'connection.php';
session_start();

// Initialize error message
$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ($conn->connect_error) {
        error_log("Connection failed: " . $conn->connect_error);
        $_SESSION['error'] = "An error occurred during login. Please try again later.";
        header('Location: login.php');
        exit();
    }

    // Sanitize inputs
    $email = $conn->real_escape_string(filter_var($_POST['user_email'], FILTER_SANITIZE_EMAIL));
    $password = $_POST['user_password'];

    // Fetch user data
    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Verify hashed password
        if (password_verify($password, $user['password'])) {
            // Store session data
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['first_name'] = $user['first_name'];

            // Implement Remember Me (Optional)
            if (isset($_POST['remember_me'])) {
                setcookie("user_email", $email, time() + (86400 * 30), "/");
            }

            // Redirect to the dashboard
            header("Location: index.html");
            exit();
        } else {
            $_SESSION['error'] = "Invalid email or password!";
        }
    } else {
        $_SESSION['error'] = "Invalid email or password!";
    }

    $conn->close();
    header("Location: login.php");
    exit();
}
?>
