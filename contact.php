<?php
session_start();
require_once 'includes/language.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - GoalSphere</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="css/styles.css" rel="stylesheet">
</head>
<body class="bg-black min-h-screen flex flex-col">
    <!-- Navbar -->
    <?php include 'includes/navbar.php'; ?>

    <!-- Main Content -->
    <div class="pt-16 flex-grow">
        <div class="container mx-auto px-4 py-8">
            <h1 class="text-3xl font-bold text-white mb-8">Contact Us</h1>
            
            <div class="grid md:grid-cols-2 gap-8">
                <div class="bg-gray-900 rounded-lg p-8">
                    <h2 class="text-2xl font-semibold text-white mb-6">Get in Touch</h2>
                    <form class="space-y-4">
                        <div>
                            <label class="block text-gray-400 mb-2">Name</label>
                            <input type="text" class="w-full bg-gray-800 rounded-lg px-4 py-2 text-white">
                        </div>
                        <div>
                            <label class="block text-gray-400 mb-2">Email</label>
                            <input type="email" class="w-full bg-gray-800 rounded-lg px-4 py-2 text-white">
                        </div>
                        <div>
                            <label class="block text-gray-400 mb-2">Message</label>
                            <textarea rows="4" class="w-full bg-gray-800 rounded-lg px-4 py-2 text-white"></textarea>
                        </div>
                        <button class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition-colors">
                            Send Message
                        </button>
                    </form>
                </div>

                <div class="bg-gray-900 rounded-lg p-8 text-gray-300">
                    <h2 class="text-2xl font-semibold text-white mb-6">Contact Information</h2>
                    <div class="space-y-4">
                        <div>
                            <h3 class="text-white font-semibold mb-2">Email</h3>
                            <p>goalsphere@gmail.com</p>
                        </div>
                        <div>
                            <h3 class="text-white font-semibold mb-2">Address</h3>
                            <p>Manchester Street</p>
                            <p>Aston District</p>
                            <p>leicter City, SP 12345</p>
                        </div>
                       
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>
</body>
</html> 