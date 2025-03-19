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
$active_users = $conn->query("SELECT COUNT(*) as count FROM users WHERE is_active = 1")->fetch_assoc()['count'];
$total_matches = $conn->query("SELECT COUNT(*) as count FROM matches")->fetch_assoc()['count'];
$total_news = $conn->query("SELECT COUNT(*) as count FROM news")->fetch_assoc()['count'];
$recent_users = $conn->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5");
$recent_activity = $conn->query("SELECT * FROM activity_logs ORDER BY created_at DESC LIMIT 5");
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
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="bg-gray-900 text-white w-64 flex-shrink-0 flex flex-col">
            <div class="p-4">
                <h2 class="text-2xl font-bold">GoalSphere</h2>
                <p class="text-sm text-gray-400">Admin Dashboard</p>
            </div>
            
            <nav class="mt-8 flex-grow">
                <div class="px-4">
                    <h3 class="text-xs uppercase text-gray-500 font-semibold">Management</h3>
                    <div class="mt-4 space-y-2">
                        <a href="admin.php" class="block px-4 py-2 rounded-lg bg-gray-800 text-white">Dashboard</a>
                        <a href="manage_users.php" class="block px-4 py-2 rounded-lg hover:bg-gray-800 text-gray-300">Users</a>
                        
                        <a href="admin_news.php" class="block px-4 py-2 rounded-lg hover:bg-gray-800 text-gray-300">News</a>
                        <a href="admin_jerseys.php" 
                   class="block px-4 py-2 rounded-lg <?php echo $current_page === 'admin_jerseys.php' ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-800'; ?>">
                    Manage Jerseys
                </a>
                    </div>
                </div>
                
                <div class="px-4 mt-8">
                    <h3 class="text-xs uppercase text-gray-500 font-semibold">Analytics</h3>
                    <div class="mt-4 space-y-2">
                        <a href="#traffic" class="block px-4 py-2 rounded-lg hover:bg-gray-800 text-gray-300">Traffic</a>
                        <a href="#activity" class="block px-4 py-2 rounded-lg hover:bg-gray-800 text-gray-300">Activity Logs</a>
                    </div>
                </div>
            </nav>

            <div class="p-4 border-t border-gray-800">
                <a href="index.php" class="block px-4 py-2 text-gray-400 hover:text-white mb-2">
                    ← Back to Site
                </a>
                <a href="logout.php" class="block px-4 py-2 text-red-400 hover:text-red-300 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Logout
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 overflow-auto">
            <div class="p-8">
                <!-- Stats Overview -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-gray-500 text-sm mb-2">Total Users</h3>
                        <p class="text-3xl font-bold"><?php echo $total_users; ?></p>
                        <p class="text-green-500 text-sm"><?php echo $active_users; ?> active</p>
                    </div>

                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-gray-500 text-sm mb-2">Matches Covered</h3>
                        <p class="text-3xl font-bold"><?php echo $total_matches; ?></p>
                    </div>

                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-gray-500 text-sm mb-2">News Articles</h3>
                        <p class="text-3xl font-bold"><?php echo $total_news; ?></p>
                    </div>

                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-gray-500 text-sm mb-2">Website Traffic</h3>
                        <p class="text-3xl font-bold">1.2K</p>
                        <p class="text-green-500 text-sm">↑ 12% this week</p>
                    </div>
                </div>

                <!-- Users Table -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">Recent Users</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Username</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php while($user = $recent_users->fetch_assoc()): ?>
                                <tr>
                                    <td class="px-6 py-4"><?php echo htmlspecialchars($user['first_name']); ?></td>
                                    <td class="px-6 py-4"><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?php echo $user['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                            <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">Recent Activity</h3>
                    <div class="space-y-4">
                        <?php while($activity = $recent_activity->fetch_assoc()): ?>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded-full">
                                    <?php echo htmlspecialchars($activity['type']); ?>
                                </span>
                                <p class="text-gray-600"><?php echo htmlspecialchars($activity['description']); ?></p>
                            </div>
                            <span class="text-sm text-gray-500">
                                <?php echo date('M d, H:i', strtotime($activity['created_at'])); ?>
                            </span>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>