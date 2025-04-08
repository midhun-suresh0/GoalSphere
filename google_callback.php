<?php
session_start();
require_once 'vendor/autoload.php';
require_once 'db_connect.php';
require_once 'google_config.php';

try {
    $client = getGoogleClient();
    
    if (isset($_GET['code'])) {
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        
        if (!isset($token['error'])) {
            $client->setAccessToken($token);
            $_SESSION['access_token'] = $token;

            $oauth2 = new Google\Service\Oauth2($client);
            $google_account = $oauth2->userinfo->get();
            
            $email = $google_account->getEmail();
            $name = $google_account->getName();
            $google_id = $google_account->getId();
            
            // Split full name into first and last name
            $name_parts = explode(' ', $name);
            $first_name = $name_parts[0];
            $last_name = isset($name_parts[1]) ? $name_parts[1] : '';

            // Check if user exists
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? OR google_id = ?");
            if ($stmt === false) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            $stmt->bind_param("ss", $email, $google_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // Existing user
                $user = $result->fetch_assoc();
                
                // Update Google ID if not set
                if (empty($user['google_id'])) {
                    $update_stmt = $conn->prepare("UPDATE users SET google_id = ?, is_google_user = 1 WHERE id = ?");
                    $update_stmt->bind_param("si", $google_id, $user['id']);
                    $update_stmt->execute();
                }

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['email'] = $user['email'];
                
                header("Location: index.php");
                exit();
            } else {
                // New user - create account
                $random_password = bin2hex(random_bytes(8));
                $hashed_password = password_hash($random_password, PASSWORD_DEFAULT);
                
                $insert_stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password, is_active, is_google_user, google_id, terms_accepted) VALUES (?, ?, ?, ?, 1, 1, ?, 1)");
                if ($insert_stmt === false) {
                    throw new Exception("Prepare failed: " . $conn->error);
                }
                
                $insert_stmt->bind_param("sssss", $first_name, $last_name, $email, $hashed_password, $google_id);
                
                if ($insert_stmt->execute()) {
                    $_SESSION['user_id'] = $conn->insert_id;
                    $_SESSION['first_name'] = $first_name;
                    $_SESSION['email'] = $email;
                    
                    // TODO: Send welcome email
                    
                    header("Location: index.php");
                    exit();
                } else {
                    throw new Exception("Error creating new user account: " . $insert_stmt->error);
                }
            }
        } else {
            throw new Exception("Google OAuth Error: " . ($token['error_description'] ?? $token['error']));
        }
    }
} catch (Exception $e) {
    error_log("Google Sign-In Error: " . $e->getMessage());
    $_SESSION['error'] = "Authentication failed. Please try again.";
    header("Location: signin.php");
    exit();
}
?>