<?php
session_start();
require_once 'includes/language.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - GoalSphere</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="css/styles.css" rel="stylesheet">
</head>
<body class="bg-black min-h-screen flex flex-col">
    <!-- Navbar -->
    <?php include 'includes/navbar.php'; ?>

    <!-- Main Content -->
    <div class="pt-16 flex-grow">
        <div class="container mx-auto px-4 py-8">
            <h1 class="text-3xl font-bold text-white mb-8">About Us</h1>
            
            <div class="bg-gray-900 rounded-lg p-8 text-gray-300 space-y-6">
                <div>
                    <h2 class="text-2xl font-semibold text-white mb-4">Our Story</h2>
                    <p>GoalSphere was founded in 2024 with a simple mission: to bring the beautiful game closer to fans around the world. We believe that football is more than just a sport - it's a universal language that connects people across cultures and continents.</p>
                </div>

                <div>
                    <h2 class="text-2xl font-semibold text-white mb-4">Our Mission</h2>
                    <p>To provide football enthusiasts with real-time updates, comprehensive statistics, and engaging content that enhances their connection to the sport they love.</p>
                </div>

                <div>
                    <h2 class="text-2xl font-semibold text-white mb-4">What We Offer</h2>
                    <ul class="list-disc list-inside space-y-2">
                        <li>match updates and scores</li>
                        <li>Detailed team </li>
                        <li>Comprehensive league tables</li>
                        <li>Latest football news and analysis</li>
                        <li>Global football community platform</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>
</body>
</html> 