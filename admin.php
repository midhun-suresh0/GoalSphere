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

// Use the id column instead of user_id for the join
$recent_activity = $conn->query("SELECT a.*, u.first_name 
                                FROM activity_logs a 
                                LEFT JOIN users u ON a.id = u.id 
                                ORDER BY a.created_at DESC LIMIT 5");

if (!$recent_activity) {
    // If query fails, fall back to a simpler query
    $recent_activity = $conn->query("SELECT * FROM activity_logs ORDER BY created_at DESC LIMIT 5");
}

// Set current page for sidebar highlighting
$current_page = basename($_SERVER['PHP_SELF']);
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
        <!-- Include Sidebar -->
        <?php include 'includes/admin_sidebar.php'; ?>

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
                        <h3 class="text-gray-500 text-sm mb-2">Total News</h3>
                        <p class="text-3xl font-bold"><?php echo $total_news; ?></p>
                        <p class="text-blue-500 text-sm">Published today: 3</p>
                    </div>

                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-gray-500 text-sm mb-2">System Status</h3>
                        <p class="text-3xl font-bold text-green-500">Online</p>
                        <p class="text-blue-500 text-sm">All systems operational</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Recent Users -->
                    <div class="bg-white rounded-lg shadow-md p-6">
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
                                    <p class="text-gray-600">
                                        <?php 
                                        // Only show first name if it exists in the result set
                                        if (isset($activity['first_name']) && !empty($activity['first_name'])) {
                                            echo htmlspecialchars($activity['first_name']) . ': ';
                                        }
                                        echo htmlspecialchars($activity['description']); 
                                        ?>
                                    </p>
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
    </div>
</body>
</html>