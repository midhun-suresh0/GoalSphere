<?php
session_start();
require_once 'includes/language.php';
require_once 'google_config.php';

// Database connection
$host = 'localhost'; // Change this if your MySQL server is hosted somewhere else
$dbname = 'goalsphere';
$username = 'root'; // Your MySQL username
$password = ''; // Your MySQL password

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Handle the form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $user_email = mysqli_real_escape_string($conn, $_POST['user_email']);
    $user_password = mysqli_real_escape_string($conn, $_POST['user_password']);
    $remember_me = isset($_POST['remember_me']) ? 1 : 0;

    // Check if the email exists in the database
    $sql = "SELECT * FROM users WHERE email = '$user_email'";
    $result = $conn->query($sql);
    $sql2 = "SELECT * FROM admin WHERE email = '$user_email'";
    $result2 = $conn->query($sql2);

    if ($result->num_rows > 0) {
        // Fetch the user data
        $user = $result->fetch_assoc();
        
        // First check if user is active
        if (!$user['is_active']) {
            $error_message = "Your account has been deactivated. Please contact support.";
            header("Location: signin.php?error=" . urlencode($error_message));
            exit();
        }
        
        // Verify the password only if user is active
        if (password_verify($user_password, $user['password'])) {
            // Password is correct, start session and login user
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            
            // Remember the user if the "Remember me" checkbox is checked
            if ($remember_me) {
                setcookie('user_email', $user_email, time() + (86400 * 30), "/");
                setcookie('user_id', $user['id'], time() + (86400 * 30), "/");
            }

            header("Location: index.php");
            exit();
        } else {
            $error_message = "Invalid email or password";
            header("Location: signin.php?error=" . urlencode($error_message));
            exit();
        }
    }
    else if($result2->num_rows > 0){
        $admin = $result2->fetch_assoc();
        if(password_verify($user_password, $admin['password'])){
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_email'] = $admin['email'];
            $_SESSION['user_id'] = $admin['id'];
            header("Location: admin.php");
            exit();
        } else {
            $error_message = "Invalid email or password";
            header("Location: signin.php?error=" . urlencode($error_message));
            exit();
        }
    } else {
        $error_message = "Invalid email or password";
        header("Location: signin.php?error=" . urlencode($error_message));
        exit();
    }
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - GoalSphere</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="css/styles.css" rel="stylesheet">
    <style>
        .error-message {
            color: #ef4444;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
    </style>
    <meta name="google-signin-client_id" content="YOUR_GOOGLE_CLIENT_ID.apps.googleusercontent.com">
    <script src="https://apis.google.com/js/platform.js" async defer></script>
</head>
<body class="bg-black min-h-screen">
    <!-- Back to Home Button -->
    <div class="absolute top-4 left-4">
        <a href="index.php" class="flex items-center text-white hover:text-gray-300 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Home
        </a>
    </div>

    <div class="flex min-h-screen">
        <!-- Left Side - Image -->
        <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-gray-900 to-black items-center justify-center relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-green-600/20 to-black"></div>
            <img src="images/stadium-dark.jpg" alt="Football Stadium" class="absolute inset-0 w-full h-full object-cover opacity-50">
            <div class="relative z-10 text-center p-8">
                <h2 class="text-4xl font-bold text-white mb-4">Welcome Back to GoalSphere</h2>
                <p class="text-gray-300 text-lg">Your gateway to the beautiful game</p>
            </div>
        </div>

        <!-- Right Side - Sign In Form -->
        <div class="w-full lg:w-1/2 flex items-center justify-center p-8">
            <div class="max-w-md w-full space-y-8">
                <div class="text-center">
                    <h2 class="text-3xl font-extrabold text-white">Sign in to your account</h2>
                    <p class="mt-2 text-sm text-gray-400">
                        Or
                        <a href="register.php" class="font-medium text-green-500 hover:text-green-400">
                            create a new account
                        </a>
                    </p>
                </div>

                <?php if (isset($_GET['error'])): ?>
                    <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                        <?php echo htmlspecialchars($_GET['error']); ?>
                    </div>
                <?php endif; ?>

                <form id="signin-form" class="mt-8 space-y-6" method="POST" action="signin.php">
                    <div class="space-y-4">
                        <div>
                            <label for="email" class="text-sm font-medium text-gray-300">Email address</label>
                            <input id="email" name="user_email" type="email" required 
                                class="appearance-none relative block w-full px-3 py-3 mt-1 bg-gray-900 border border-gray-700 placeholder-gray-500 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                            <div id="email-error" class="error-message hidden"></div>
                        </div>
                        <div>
                            <label for="password" class="text-sm font-medium text-gray-300">Password</label>
                            <input id="password" name="user_password" type="password" required 
                                class="appearance-none relative block w-full px-3 py-3 mt-1 bg-gray-900 border border-gray-700 placeholder-gray-500 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                            <div id="password-error" class="error-message hidden"></div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input id="remember_me" name="remember_me" type="checkbox" 
                                class="h-4 w-4 text-green-600 bg-gray-900 border-gray-700 rounded focus:ring-green-500">
                            <label for="remember_me" class="ml-2 text-sm text-gray-400">
                                Remember me
                            </label>
                        </div>
                        <div class="text-sm">
                            <a href="forgot.php" class="font-medium text-green-500 hover:text-green-400">
                                Forgot your password?
                            </a>
                        </div>
                    </div>

                    <div>
                        <button type="submit" 
                            class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200">
                            Sign in
                        </button>
                    </div>
                </form>

                <div class="mt-6">
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-700"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-2 bg-black text-gray-400">Or continue with</span>
                        </div>
                    </div>

                    <div class="mt-6">
                        <?php
                        try {
                            $client = getGoogleClient();
                            $googleLoginUrl = $client->createAuthUrl();
                        } catch (Exception $e) {
                            error_log("Google client error: " . $e->getMessage());
                            $googleLoginUrl = "#";
                        }
                        ?>
                        <a href="<?php echo htmlspecialchars($googleLoginUrl); ?>" 
                           class="w-full flex items-center justify-center px-4 py-3 border border-gray-700 rounded-lg shadow-sm bg-gray-900 hover:bg-gray-800 transition-colors duration-200">
                            <img src="images/google-logo.svg" alt="Google" class="h-5 w-5 mr-2">
                            <span class="text-white">Sign in with Google</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="js/auth.js"></script>
</body>
</html>