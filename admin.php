<?php
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['admin_id'])) {
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

// Fetch statistics
$total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$total_admins = $conn->query("SELECT COUNT(*) as count FROM admin")->fetch_assoc()['count'];
$recent_users = $conn->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - GoalSphere</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="css/styles.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <!-- Admin Navigation -->
    <nav class="bg-black text-white fixed w-full z-50">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <a href="admin.php" class="text-2xl font-bold">GoalSphere Admin</a>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="index.php" class="hover:text-gray-300">View Site</a>
                    <a href="logout.php" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Admin Content -->
    <div class="pt-16">
        <div class="container mx-auto px-4 py-8">
            <!-- Dashboard Overview -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- Total Users Card -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">Total Users</h3>
                    <p class="text-3xl font-bold text-blue-600"><?php echo $total_users; ?></p>
                </div>

                <!-- Total Admins Card -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">Total Admins</h3>
                    <p class="text-3xl font-bold text-green-600"><?php echo $total_admins; ?></p>
                </div>

                <!-- Quick Actions Card -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">Quick Actions</h3>
                    <div class="space-y-2">
                        <button onclick="location.href='manage_users.php'" class="w-full bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                            Manage Users
                        </button>
                        <button onclick="location.href='manage_admins.php'" class="w-full bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                            Manage Admins
                        </button>
                    </div>
                </div>
            </div>

            <!-- Recent Users -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">Recent Users</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Username
                                </th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Email
                                </th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Joined Date
                                </th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php while($user = $recent_users->fetch_assoc()): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php echo htmlspecialchars($user['first_name']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php echo htmlspecialchars($user['email']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php echo date('M d, Y', strtotime($user['created_at'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <button onclick="editUser(<?php echo $user['id']; ?>)" 
                                            class="text-blue-600 hover:text-blue-900">
                                        Edit
                                    </button>
                                    <button onclick="deleteUser(<?php echo $user['id']; ?>)" 
                                            class="ml-4 text-red-600 hover:text-red-900">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
    function editUser(userId) {
        // Implement user edit functionality
        window.location.href = `edit_user.php?id=${userId}`;
    }

    function deleteUser(userId) {
        if (confirm('Are you sure you want to delete this user?')) {
            fetch('delete_user.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ user_id: userId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error deleting user');
                }
            });
        }
    }
    </script>
</body>
</html>