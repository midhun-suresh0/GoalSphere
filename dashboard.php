<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black min-h-screen p-8">
    <div class="max-w-4xl mx-auto">
        <div class="bg-gray-900 rounded-xl p-8">
            <h1 class="text-2xl font-bold text-white mb-4">Welcome, <?php echo htmlspecialchars($_SESSION['email']); ?></h1>
            <a href="logout.php" class="text-green-500 hover:text-green-400">Logout</a>
        </div>
    </div>
</body>
</html> 