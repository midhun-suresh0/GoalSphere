<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "Please log in first";
    exit();
}

// Check if form data is received and not empty
if (empty($_POST['current_password']) || empty($_POST['new_password'])) {
    echo "Please fill in all password fields";
    exit();
}

// Get POST data
$current_password = trim($_POST['current_password']);
$new_password = trim($_POST['new_password']);

// Validate password length
if (strlen($new_password) < 6) {
    echo "New password must be at least 6 characters long";
    exit();
}

// Connect to database
require_once 'db_connection.php';

// Get user's current password from database
$stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Verify current password
if (!password_verify($current_password, $user['password'])) {
    echo "Current password is incorrect";
    exit();
}

// Hash new password and update
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
$update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
$update_stmt->bind_param("si", $hashed_password, $_SESSION['user_id']);

if ($update_stmt->execute()) {
    echo "Password updated successfully";
} else {
    echo "Failed to update password";
}

$stmt->close();
$update_stmt->close();
$conn->close();
?>