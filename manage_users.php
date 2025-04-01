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

// Add these at the top with other includes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

function sendDeactivationEmail($userEmail) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'goalsphere79@gmail.com';
        $mail->Password   = 'akgn fadx wmqg dscf';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('goalsphere79@gmail.com', 'GoalSphere');
        $mail->addAddress($userEmail);
        $mail->Subject = 'Account Status Update';
        $mail->Body    = "Your GoalSphere account has been deactivated by an administrator. Please contact support if you believe this is an error.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email sending failed: " . $mail->ErrorInfo);
        return false;
    }
}

// Handle search
$search = isset($_GET['search']) ? $_GET['search'] : '';
$search_condition = $search ? "WHERE first_name LIKE '%$search%' OR email LIKE '%$search%'" : '';

// Handle user activation/deactivation
if (isset($_POST['toggle_status'])) {
    $user_id = $_POST['user_id'];
    $new_status = $_POST['new_status'];
    
    // Get user email before updating status
    $email_sql = "SELECT email FROM users WHERE id = ?";
    $stmt = $conn->prepare($email_sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_email = $result->fetch_assoc()['email'];
    
    $update_sql = "UPDATE users SET is_active = ? WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ii", $new_status, $user_id);
    
    if ($stmt->execute()) {
        // Log the activity
        $action = $new_status ? 'activated' : 'deactivated';
        $log_sql = "INSERT INTO activity_logs (type, description) VALUES ('USER_STATUS', 'User ID: $user_id was $action')";
        $conn->query($log_sql);
        
        // Send email if user is being deactivated
        if ($new_status == 0) {
            sendDeactivationEmail($user_email);
        }
    }
}

// Fetch users with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

$users_sql = "SELECT * FROM users $search_condition ORDER BY created_at DESC LIMIT $offset, $per_page";
$users_result = $conn->query($users_sql);

// Get total users for pagination
$total_sql = "SELECT COUNT(*) as count FROM users $search_condition";
$total_result = $conn->query($total_sql);
$total_users = $total_result->fetch_assoc()['count'];
$total_pages = ceil($total_users / $per_page);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - GoalSphere Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="css/styles.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Include your sidebar here -->
        <?php include 'includes/admin_sidebar.php'; ?>

        <!-- Main Content -->
        <div class="flex-1 overflow-auto">
            <div class="p-8">
                <div class="flex justify-between items-center mb-8">
                    <h1 class="text-3xl font-bold">Manage Users</h1>
                    
                    <!-- Search Bar -->
                    <form class="flex space-x-4">
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                               placeholder="Search users..." 
                               class="px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <button type="submit" 
                                class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                            Search
                        </button>
                    </form>
                </div>

                <!-- Users Table -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Joined Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php while($user = $users_result->fetch_assoc()): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                            <span class="text-xl font-medium text-gray-600">
                                                <?php echo strtoupper(substr($user['first_name'], 0, 1)); ?>
                                            </span>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                <?php echo htmlspecialchars($user['first_name']); ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo htmlspecialchars($user['email']); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        <?php echo date('M d, Y', strtotime($user['created_at'])); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?php echo $user['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                        <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <input type="hidden" name="new_status" value="<?php echo $user['is_active'] ? 0 : 1; ?>">
                                        <button type="submit" name="toggle_status" 
                                            class="text-indigo-600 hover:text-indigo-900 mr-4">
                                            <?php echo $user['is_active'] ? 'Deactivate' : 'Activate'; ?>
                                        </button>
                                    </form>
                                    
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <div class="mt-6 flex justify-center">
                    <div class="flex space-x-2">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>" 
                               class="px-4 py-2 rounded-lg <?php echo $page === $i ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html> 