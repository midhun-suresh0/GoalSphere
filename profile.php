<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: signin.php');
    exit();
}

// Database connection
$host = 'localhost';
$dbname = 'goalsphere';
$username = 'root';
$password = '';

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $user_id = $_SESSION['user_id'];
    
    // Validation
    $errors = [];
    
    // Check first name
    if (strlen($first_name) < 2) {
        $errors[] = "First name must be at least 2 characters long";
    }
    if (preg_match('/^\s*$/', $first_name)) {
        $errors[] = "First name cannot be only spaces";
    }
    
    // Check last name
    if (strlen($last_name) < 2) {
        $errors[] = "Last name must be at least 2 characters long";
    }
    if (preg_match('/^\s*$/', $last_name)) {
        $errors[] = "Last name cannot be only spaces";
    }
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address";
    } else {
        // Check if email already exists for another user
        $check_email_sql = "SELECT id FROM users WHERE email = ? AND id != ?";
        $check_stmt = $conn->prepare($check_email_sql);
        $check_stmt->bind_param("si", $email, $user_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows > 0) {
            $errors[] = "This email is already in use";
        }
    }
    
    if (empty($errors)) {
        // Check if any information has changed
        $check_sql = "SELECT first_name, last_name, email FROM users WHERE id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("i", $user_id);
        $check_stmt->execute();
        $current_user = $check_stmt->get_result()->fetch_assoc();
        
        if ($first_name === $current_user['first_name'] && 
            $last_name === $current_user['last_name'] && 
            $email === $current_user['email']) {
            $info_message = "No changes were made to your profile.";
        } else {
            // Update user information
            $update_sql = "UPDATE users SET first_name = ?, last_name = ?, email = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("sssi", $first_name, $last_name, $email, $user_id);
            
            if ($update_stmt->execute()) {
                $_SESSION['username'] = $first_name;
                $_SESSION['email'] = $email;
                $success_message = "Profile updated successfully!";
            } else {
                $error_message = "Failed to update profile. Please try again.";
            }
        }
    } else {
        $error_message = implode("<br>", $errors);
    }
}

// Fetch current user data
$user_id = $_SESSION['user_id'];
$sql = "SELECT first_name, last_name, email FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - GoalSphere</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="css/styles.css" rel="stylesheet">
</head>
<body class="bg-black min-h-screen">
    <!-- Navigation Bar -->
    <?php include 'includes/navbar.php'; ?>

    <!-- Update the container div to include padding-top for fixed navbar -->
    <div class="pt-20">
        <div class="container mx-auto px-4">
            <div class="max-w-2xl mx-auto bg-gray-900 rounded-lg shadow-xl p-8">
                <h1 class="text-2xl font-bold text-white mb-8">Edit Profile</h1>

                <?php if (isset($success_message)): ?>
                    <div class="mb-6 bg-green-600 text-white px-4 py-2 rounded-lg">
                        <?php echo $success_message; ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($info_message)): ?>
                    <div class="mb-6 bg-blue-600 text-white px-4 py-2 rounded-lg">
                        <?php echo $info_message; ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($error_message)): ?>
                    <div class="mb-6 bg-red-600 text-white px-4 py-2 rounded-lg">
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-6" onsubmit="return validateForm()">
                    <div>
                        <label for="first-name" class="block text-sm font-medium text-gray-300">First Name</label>
                        <input type="text" id="first-name" name="first_name" 
                               value="<?php echo htmlspecialchars($user['first_name']); ?>"
                               onkeypress="return validateInput(event)"
                               onpaste="return validatePaste(event)"
                               class="mt-1 block w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-green-500">
                        <span id="first-name-error" class="text-red-500 text-sm hidden"></span>
                    </div>

                    <div>
                        <label for="last-name" class="block text-sm font-medium text-gray-300">Last Name</label>
                        <input type="text" id="last-name" name="last_name" 
                               value="<?php echo htmlspecialchars($user['last_name']); ?>"
                               onkeypress="return validateInput(event)"
                               onpaste="return validatePaste(event)"
                               class="mt-1 block w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-green-500">
                        <span id="last-name-error" class="text-red-500 text-sm hidden"></span>
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-300">Email Address</label>
                        <input type="email" id="email" name="email" 
                               value="<?php echo htmlspecialchars($user['email']); ?>"
                               class="mt-1 block w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-green-500">
                        <span id="email-error" class="text-red-500 text-sm hidden"></span>
                    </div>

                    <div class="flex justify-end space-x-4">
                        <a href="index.php" class="px-4 py-2 bg-gray-700 text-white rounded-lg hover:bg-gray-600">
                            Cancel
                        </a>
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    function validateInput(event) {
        // Allow letters and single space between words
        const char = String.fromCharCode(event.keyCode || event.which);
        const input = event.target;
        const currentValue = input.value;
        
        // Don't allow space at the beginning
        if (currentValue.length === 0 && char === ' ') {
            return false;
        }
        
        // Don't allow consecutive spaces
        if (char === ' ' && currentValue.slice(-1) === ' ') {
            return false;
        }
        
        // Only allow letters and single spaces
        return /^[a-zA-Z ]$/.test(char);
    }

    function validatePaste(event) {
        const pastedText = (event.clipboardData || window.clipboardData).getData('text');
        
        // Check if pasted text contains only letters and single spaces
        if (!/^[a-zA-Z ]+$/.test(pastedText)) {
            event.preventDefault();
            return false;
        }
        
        // Prevent pasting if it would result in consecutive spaces
        const input = event.target;
        const currentValue = input.value;
        const selectionStart = input.selectionStart;
        const newValue = currentValue.slice(0, selectionStart) + pastedText + currentValue.slice(input.selectionEnd);
        
        if (/\s\s/.test(newValue)) {
            event.preventDefault();
            return false;
        }
        
        return true;
    }

    function validateForm() {
        let isValid = true;
        const firstName = document.getElementById('first-name').value.trim();
        const lastName = document.getElementById('last-name').value.trim();
        const email = document.getElementById('email').value.trim();
        
        // Reset error messages
        document.getElementById('first-name-error').classList.add('hidden');
        document.getElementById('last-name-error').classList.add('hidden');
        document.getElementById('email-error').classList.add('hidden');
        
        // Validate first name
        if (firstName.length < 2) {
            document.getElementById('first-name-error').textContent = 'First name must be at least 2 characters long';
            document.getElementById('first-name-error').classList.remove('hidden');
            isValid = false;
        } else if (!/^[a-zA-Z ]+$/.test(firstName)) {
            document.getElementById('first-name-error').textContent = 'First name can only contain letters and spaces';
            document.getElementById('first-name-error').classList.remove('hidden');
            isValid = false;
        }
        
        // Validate last name
        if (lastName.length < 2) {
            document.getElementById('last-name-error').textContent = 'Last name must be at least 2 characters long';
            document.getElementById('last-name-error').classList.remove('hidden');
            isValid = false;
        } else if (!/^[a-zA-Z ]+$/.test(lastName)) {
            document.getElementById('last-name-error').textContent = 'Last name can only contain letters and spaces';
            document.getElementById('last-name-error').classList.remove('hidden');
            isValid = false;
        }
        
        // Validate email
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            document.getElementById('email-error').textContent = 'Please enter a valid email address';
            document.getElementById('email-error').classList.remove('hidden');
            isValid = false;
        }
        
        return isValid;
    }

    // Add input event listeners to show real-time validation
    document.getElementById('first-name').addEventListener('input', function() {
        validateForm();
    });

    document.getElementById('last-name').addEventListener('input', function() {
        validateForm();
    });

    // Add real-time email validation
    document.getElementById('email').addEventListener('input', function() {
        const email = this.value.trim();
        const emailError = document.getElementById('email-error');
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (email.length > 0) {
            if (!emailRegex.test(email)) {
                emailError.textContent = 'Please enter a valid email address';
                emailError.classList.remove('hidden');
            } else {
                emailError.classList.add('hidden');
            }
        } else {
            emailError.classList.add('hidden');
        }
    });
    </script>
</body>
</html>
