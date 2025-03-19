<?php
session_start();

// Clear all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Revoke Google token if exists
if (isset($_SESSION['access_token'])) {
    $client->revokeToken($_SESSION['access_token']);
}

// Redirect to login page
header("Location: signin.php");
exit();
?> 