<?php
session_start();
require_once 'includes/language.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy - GoalSphere</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="css/styles.css" rel="stylesheet">
</head>
<body class="bg-black min-h-screen flex flex-col">
    <!-- Navbar -->
    <?php include 'includes/navbar.php'; ?>

    <!-- Main Content -->
    <div class="pt-16 flex-grow">
        <div class="container mx-auto px-4 py-8">
            <h1 class="text-3xl font-bold text-white mb-8">Privacy Policy</h1>
            
            <div class="bg-gray-900 rounded-lg p-8 text-gray-300 space-y-6">
                <div>
                    <h2 class="text-2xl font-semibold text-white mb-4">Information We Collect</h2>
                    <p>We collect information that you provide directly to us, including when you create an account, make a purchase, or contact us for support. This may include:</p>
                    <ul class="list-disc list-inside mt-2 space-y-1">
                        <li>Name and email address</li>
                        <li>Account login credentials</li>
                        <li>Payment information</li>
                        <li>Communication preferences</li>
                    </ul>
                </div>

                <div>
                    <h2 class="text-2xl font-semibold text-white mb-4">How We Use Your Information</h2>
                    <p>We use the information we collect to:</p>
                    <ul class="list-disc list-inside mt-2 space-y-1">
                        <li>Provide and maintain our services</li>
                        <li>Process your transactions</li>
                        <li>Send you updates and marketing communications</li>
                        <li>Improve our services and develop new features</li>
                    </ul>
                </div>

                <div>
                    <h2 class="text-2xl font-semibold text-white mb-4">Data Security</h2>
                    <p>We implement appropriate security measures to protect your personal information. However, no method of transmission over the Internet is 100% secure, and we cannot guarantee absolute security.</p>
                </div>

                <div>
                    <h2 class="text-2xl font-semibold text-white mb-4">Contact Us</h2>
                    <p>If you have any questions about this Privacy Policy, please contact us at privacy@goalsphere.com</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>
</body>
</html> 