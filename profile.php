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
    $user_id = $_SESSION['user_id'];
    
    // Update user information
    $update_sql = "UPDATE users SET first_name = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("si", $first_name, $user_id);
    
    if ($update_stmt->execute()) {
        // Update session data
        $_SESSION['username'] = $first_name;
        
        // Redirect to refresh the page
        header("Location: profile.php?success=1");
        exit();
    } else {
        $error_message = "Failed to update profile. Please try again.";
    }
}

// Fetch user data
$user_id = $_SESSION['user_id'];
$sql = "SELECT id, first_name, email, created_at FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Add success message display
$success_message = isset($_GET['success']) ? "Profile updated successfully!" : "";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - GoalSphere</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="css/styles.css" rel="stylesheet">
</head>
<body class="bg-black min-h-screen">
    <!-- Navigation Bar -->
    <nav class="bg-black text-white fixed w-full z-50">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <a href="index.php" class="text-2xl font-bold">GoalSphere</a>
            </div>
        </div>
    </nav>

    <div class="pt-16">
        <div class="container mx-auto px-4 py-8">
            <div class="max-w-4xl mx-auto">
                <!-- Profile Header -->
                <div class="bg-gray-900 rounded-lg shadow-xl p-6 mb-6">
                    <div class="flex items-center space-x-6">
                        <div class="w-24 h-24 bg-gray-700 rounded-full flex items-center justify-center">
                            <span class="text-white text-4xl font-semibold">U</span>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-white">
                                <?php echo htmlspecialchars($user['first_name']); ?>
                            </h1>
                            <p class="text-gray-400">Member since <?php echo date('F Y', strtotime($user['created_at'])); ?></p>
                        </div>
                    </div>

                    <!-- Personal Information -->
                    <div class="mt-8 border-t border-gray-700 pt-8">
                        <h2 class="text-xl font-semibold text-white mb-6">Personal Information</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <p class="text-gray-400 text-sm">Username</p>
                                <p class="text-white font-medium"><?php echo htmlspecialchars($user['first_name']); ?></p>
                            </div>
                            <div>
                                <p class="text-gray-400 text-sm">Email</p>
                                <p class="text-white font-medium"><?php echo htmlspecialchars($user['email']); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Add this after the Personal Information section and before the Edit Profile Button -->
                    <?php if ($success_message): ?>
                        <div class="mt-4 bg-green-600 text-white px-4 py-2 rounded-lg">
                            <?php echo $success_message; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($error_message)): ?>
                        <div class="mt-4 bg-red-600 text-white px-4 py-2 rounded-lg">
                            <?php echo $error_message; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Edit Profile Button -->
                    <div class="mt-8 flex justify-end">
                        <button onclick="toggleEditForm()" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                            Edit Profile
                        </button>
                    </div>
                </div>

                <!-- Edit Profile Form (Hidden by default) -->
                <div id="editForm" class="hidden bg-gray-900 rounded-lg shadow-xl p-6">
                    <h2 class="text-xl font-semibold text-white mb-6">Edit Profile</h2>
                    <form method="POST" action="profile.php" class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-400">Username</label>
                            <input type="text" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" 
                                   class="mt-1 w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-md text-white">
                        </div>
                        <div class="flex justify-end space-x-4">
                            <button type="button" onclick="toggleEditForm()" class="px-4 py-2 bg-gray-700 text-white rounded-lg hover:bg-gray-600">
                                Cancel
                            </button>
                            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    function toggleEditForm() {
        const editForm = document.getElementById('editForm');
        editForm.classList.toggle('hidden');
    }
    </script>
</body>
</html>
