<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: signin.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - GoalSphere</title>
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

    <div class="pt-20">
        <div class="container mx-auto px-4">
            <div class="max-w-3xl mx-auto">
                <h1 class="text-2xl font-bold text-white mb-8">Settings</h1>

                <!-- Settings Sections -->
                <div class="space-y-8">
                    <!-- Language Settings -->
                    <div class="bg-gray-900 rounded-lg p-6">
                        <h2 class="text-xl font-semibold text-white mb-4">Language</h2>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            <button onclick="updateSettings('language', 'en')" 
                                    class="flex items-center justify-between px-4 py-3 rounded-lg text-white hover:bg-gray-700 transition-colors <?php echo ($_SESSION['language'] ?? 'en') === 'en' ? 'bg-gray-700' : 'bg-gray-800'; ?>">
                                <span>English</span>
                                <?php if (($_SESSION['language'] ?? 'en') === 'en'): ?>
                                    <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                <?php endif; ?>
                            </button>
                            <button onclick="updateSettings('language', 'es')" 
                                    class="flex items-center justify-between px-4 py-3 rounded-lg text-white hover:bg-gray-700 transition-colors <?php echo ($_SESSION['language'] ?? '') === 'es' ? 'bg-gray-700' : 'bg-gray-800'; ?>">
                                <span>Español</span>
                                <?php if (($_SESSION['language'] ?? '') === 'es'): ?>
                                    <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                <?php endif; ?>
                            </button>
                        </div>
                    </div>

                    <!-- Change Password Section -->
                    <div class="bg-gray-900 rounded-lg p-6">
                        <h2 class="text-xl font-semibold text-white mb-4">Change Password</h2>
                        <form id="changePasswordForm" class="space-y-4" action="update_password.php" method="POST">
                            <div>
                                <label for="current_password" class="block text-white mb-2">Current Password</label>
                                <input type="password" id="current_password" name="current_password" 
                                    class="w-full bg-gray-800 text-white rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label for="new_password" class="block text-white mb-2">New Password</label>
                                <input type="password" id="new_password" name="new_password" 
                                    class="w-full bg-gray-800 text-white rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label for="confirm_password" class="block text-white mb-2">Confirm New Password</label>
                                <input type="password" id="confirm_password" name="confirm_password" 
                                    class="w-full bg-gray-800 text-white rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <button type="submit" 
                                class="w-full bg-blue-600 text-white rounded-lg px-4 py-2 hover:bg-blue-700 transition-colors">
                                Update Password
                            </button>
                            <div id="passwordMessage" class="text-center mt-2 hidden"></div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function updateSettings(type, value) {
        fetch('update_settings.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ type, value })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }

    document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const currentPassword = document.getElementById('current_password').value;
        const newPassword = document.getElementById('new_password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        const messageDiv = document.getElementById('passwordMessage');

        if (newPassword !== confirmPassword) {
            messageDiv.textContent = 'New passwords do not match';
            messageDiv.className = 'text-red-500 mt-2';
            messageDiv.classList.remove('hidden');
            return;
        }

        // Create form data
        const formData = new FormData();
        formData.append('current_password', currentPassword);
        formData.append('new_password', newPassword);

        // Submit the form using fetch
        fetch('update_password.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            messageDiv.classList.remove('hidden');
            if (data.includes('successfully')) {
                messageDiv.textContent = data;
                messageDiv.className = 'text-green-500 mt-2';
                document.getElementById('changePasswordForm').reset();
            } else {
                messageDiv.textContent = data;
                messageDiv.className = 'text-red-500 mt-2';
            }
        })
        .catch(error => {
            messageDiv.textContent = 'An error occurred';
            messageDiv.className = 'text-red-500 mt-2';
            messageDiv.classList.remove('hidden');
        });
    });
    </script>
</body>
</html> 