<?php
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

// Handle the form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $user_email = mysqli_real_escape_string($conn, $_POST['user_email']);
    $user_password = mysqli_real_escape_string($conn, $_POST['user_password']);
    $remember_me = isset($_POST['remember_me']) ? 1 : 0;

    // Validate form data
    if (empty($user_email) || empty($user_password)) {
        echo "Please fill in both email and password.";
        exit();
    }

    // Check if the email exists in the database
    $sql = "SELECT * FROM users WHERE email = '$user_email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Fetch the user data
        $user = $result->fetch_assoc();
        
        // Verify the password
        if (password_verify($user_password, $user['password'])) {
            // Password is correct, start session and login user
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            
            // Remember the user if the "Remember me" checkbox is checked
            if ($remember_me) {
                setcookie('user_email', $user_email, time() + (86400 * 30), "/"); // 1 month cookie
                setcookie('user_id', $user['id'], time() + (86400 * 30), "/");
            }

            // Redirect to the dashboard or home page after successful login
            header("Location: index.html"); // Change this to your desired page
            exit();
        } else {
            header("Location: signin.php");
        }
    } else {
        header("Location: signin.php");
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
</head>
<body class="bg-black min-h-screen">
    <!-- Back to Home Button -->
    <div class="absolute top-4 left-4">
        <a href="index.html" class="flex items-center text-white hover:text-gray-300 transition-colors">
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
                            <input id="remember-me" name="remember_me" type="checkbox" value="1"
                                class="h-4 w-4 bg-gray-900 border-gray-700 rounded text-green-500 focus:ring-green-500">
                            <label for="remember-me" class="ml-2 block text-sm text-gray-300">
                                Remember me
                            </label>
                        </div>

                        <div class="text-sm">
                            <a href="#" class="font-medium text-green-500 hover:text-green-400">
                                Forgot your password?
                            </a>
                        </div>
                    </div>

                    <div>
                        <input type="submit" 
                            class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200"
                           value="Sign in"
                        />
                    </div>

                    <div class="relative my-6">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-700"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-2 bg-black text-gray-400">Or continue with</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <button type="button" data-provider="google"
                            class="flex justify-center items-center py-2 px-4 border border-gray-700 rounded-lg hover:bg-gray-900 transition-colors duration-200">
                            <img src="images/google.svg" alt="Google" class="h-5 w-5">
                        </button>
                        <button type="button" data-provider="apple"
                            class="flex justify-center items-center py-2 px-4 border border-gray-700 rounded-lg hover:bg-gray-900 transition-colors duration-200">
                            <img src="images/apple.svg" alt="Apple" class="h-5 w-5">
                        </button>
                        <button type="button" data-provider="facebook"
                            class="flex justify-center items-center py-2 px-4 border border-gray-700 rounded-lg hover:bg-gray-900 transition-colors duration-200">
                            <img src="images/facebook.svg" alt="Facebook" class="h-5 w-5">
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
   
</body>
</html>