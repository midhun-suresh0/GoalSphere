<?php
// Database connection
$host = 'localhost'; // Change this if your MySQL server is hosted somewhere else
$dbname = 'goalsphere';
$username = 'root'; // Your MySQL username
$password = ''; // Your MySQL password

// Create connection
$conn = new mysqli($host, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === TRUE) {
    // Database created successfully or already exists
   // echo "Database created or already exists.<br>";
} else {
    die("Error creating database: " . $conn->error);
}

// Select the database
$conn->select_db($dbname);

// Create table if not exists
$table_sql = "
    CREATE TABLE IF NOT EXISTS users (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        first_name VARCHAR(50) NOT NULL,
        last_name VARCHAR(50) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        terms_accepted TINYINT(1) NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
";
if ($conn->query($table_sql) === TRUE) {
    // Table created successfully or already exists
    //echo "Table 'users' created or already exists.<br>";
} else {
    die("Error creating table: " . $conn->error);
}

// Handle the form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the form data
    $first_name = mysqli_real_escape_string($conn, $_POST['first-name']);
    $last_name = mysqli_real_escape_string($conn, $_POST['last-name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm-password']);
    $terms = isset($_POST['terms']) ? 1 : 0;

    // Validate the form data
    if (empty($first_name) || empty($last_name) || empty($email) || empty($password) || empty($confirm_password) || !$terms) {
        die("Please fill in all fields and accept the terms.");
    }

    // Check if passwords match
    if ($password !== $confirm_password) {
        die("Passwords do not match.");
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert into the database
    // $sql = "INSERT INTO users (first_name, last_name, email, password, terms_accepted) 
    //         VALUES ('$first_name', '$last_name', '$email', '$hashed_password', '$terms')";

    

    $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password, terms_accepted) 
                        VALUES (?, ?, ?, ?, ?)");

// Debugging check
if (!$stmt) {
    die("Error in users table query: " . $conn->error);
}

// Bind parameters (s = string, i = integer)
$stmt->bind_param("ssssi", $first_name, $last_name, $email, $hashed_password, $terms);

if ($stmt->execute()) {
    $user_id = $stmt->insert_id; // Get inserted user ID

    // Prepare SQL statement for login table
    $stmt2 = $conn->prepare("INSERT INTO login (user_id, email, password) VALUES (?, ?, ?)");

    if (!$stmt2) {
        die("Error in login table query: " . $conn->error);
    }

    $stmt2->bind_param("iss", $user_id, $email, $hashed_password);

    if ($stmt2->execute()) {
        header("Location: signin.php");
        exit();
    } else {
        echo "Error inserting into login table: " . $stmt2->error;
    }
} else {
    echo "Error inserting into users table: " . $stmt->error;
}

// Close statements and connection
$stmt->close();
$stmt2->close();
$conn->close();
}
// Close statements and connection


// Close the database connection

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join GoalSphere - Your Ultimate Football Destination</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="css/styles.css" rel="stylesheet">
    <script src="js/register-validation.js"></script>
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
        <a href="index.php" class="flex items-center text-white hover:text-gray-300 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Home
        </a>
    </div>

    <div class="flex min-h-screen">
        <!-- Left Side - Form -->
        <div class="w-full lg:w-1/2 flex items-center justify-center p-8">
            <div class="max-w-md w-full space-y-8">
                <div class="text-center">
                    <h2 class="text-3xl font-extrabold text-white">Create your account</h2>
                    <p class="mt-2 text-sm text-gray-400">
                        Already have an account?
                        <a href="signin.php" class="font-medium text-green-500 hover:text-green-400">
                            Sign in
                        </a>
                    </p>
                </div>

                <form action="#" method="POST" class="mt-8 space-y-6">
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="first-name" class="text-sm font-medium text-gray-300">First name</label>
                                <input id="first-name" name="first-name" type="text" required 
                                    class="appearance-none relative block w-full px-3 py-3 mt-1 bg-gray-900 border border-gray-700 placeholder-gray-500 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                <div id="first-name-error" class="error-message hidden"></div>
                            </div>
                            <div>
                                <label for="last-name" class="text-sm font-medium text-gray-300">Last name</label>
                                <input id="last-name" name="last-name" type="text" required 
                                    class="appearance-none relative block w-full px-3 py-3 mt-1 bg-gray-900 border border-gray-700 placeholder-gray-500 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                <div id="last-name-error" class="error-message hidden"></div>
                            </div>
                        </div>

                        <div>
                            <label for="email" class="text-sm font-medium text-gray-300">Email address</label>
                            <input id="email" name="email" type="email" required 
                                class="appearance-none relative block w-full px-3 py-3 mt-1 bg-gray-900 border border-gray-700 placeholder-gray-500 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                            <div id="email-error" class="error-message hidden"></div>
                        </div>

                        <div>
                            <label for="password" class="text-sm font-medium text-gray-300 block mb-2">Password</label>
                            <input type="password" id="password" required 
                                class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg focus:outline-none focus:border-blue-500 text-white"
                                placeholder="Create a password"
                                name="password"
                                onkeyup="checkPasswords()">
                        </div>

                        <div>
                            <label for="confirm-password" class="text-sm font-medium text-gray-300 block mb-2">Confirm Password</label>
                            <input type="password" id="confirm-password" required 
                                class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg focus:outline-none focus:border-blue-500 text-white"
                                placeholder="Confirm your password"
                                name="confirm-password"
                                onkeyup="checkPasswords()">
                            <p id="password-match" class="text-sm mt-1 hidden"></p>
                        </div>

                    </div>

                    <div class="flex items-center">
                        <input id="terms" name="terms" type="checkbox" required
                            class="h-4 w-4 bg-gray-900 border-gray-700 rounded text-green-500 focus:ring-green-500">
                        <label for="terms" class="ml-2 block text-sm text-gray-300">
                            I agree to the
                            <a href="#" class="text-green-500 hover:text-green-400">Terms of Service</a>
                            and
                            <a href="#" class="text-green-500 hover:text-green-400">Privacy Policy</a>
                        </label>
                        <div id="terms-error" class="error-message hidden"></div>
                    </div>

                    <div>
                        <button type="submit" 
                            class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200">
                            Create Account
                        </button>
                    </div>

                    <div class="relative my-6">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-700"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-2 bg-black text-gray-400">Or join with</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <button type="button" class="flex justify-center items-center py-2 px-4 border border-gray-700 rounded-lg hover:bg-gray-900 transition-colors duration-200">
                            <img src="images/apple.svg" alt="Apple" class="h-5 w-5">
                        </button>
                        <button type="button" class="flex justify-center items-center py-2 px-4 border border-gray-700 rounded-lg hover:bg-gray-900 transition-colors duration-200">
                            <img src="images/google.svg" alt="Google" class="h-5 w-5">
                        </button>
                        <button type="button" class="flex justify-center items-center py-2 px-4 border border-gray-700 rounded-lg hover:bg-gray-900 transition-colors duration-200">
                            <img src="images/facebook.svg" alt="Facebook" class="h-5 w-5">
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right Side - Image -->
        <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-gray-900 to-black items-center justify-center relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-green-600/20 to-black"></div>
            <img src="images/celebration-dark.jpg" alt="Football Celebration" class="absolute inset-0 w-full h-full object-cover opacity-50">
            <div class="relative z-10 text-center p-8">
                <h2 class="text-4xl font-bold text-white mb-4">Join the Community</h2>
                <p class="text-gray-300 text-lg">Get exclusive access to match scores, player stats, and more!</p>
            </div>
        </div>
    </div>

    
</body>
</html>