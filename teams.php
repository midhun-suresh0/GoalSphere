<?php
session_start();
require_once 'includes/language.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teams - GoalSphere</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="css/styles.css" rel="stylesheet">
</head>
<body class="bg-black min-h-screen flex flex-col">
    <!-- Navbar -->
    <?php include 'includes/navbar.php'; ?>

    <!-- Main Content -->
    <div class="pt-16 flex-grow">
        <div class="container mx-auto px-4 py-8">
            <h1 class="text-3xl font-bold text-white mb-8">Featured Teams</h1>
            
            <!-- Teams Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Barcelona Card -->
                <a href="team.php?team=barcelona" class="bg-gray-900 rounded-lg overflow-hidden hover:bg-gray-800 transition-colors cursor-pointer">
                    <div class="p-6">
                        <div class="flex items-center space-x-4 mb-4">
                            <img src="images/barcelona.png" alt="Barcelona" class="w-16 h-16 object-contain">
                            <div>
                                <h4 class="text-xl font-semibold text-white">FC Barcelona</h4>
                                <p class="text-gray-400">La Liga</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-3 gap-4 text-center mb-4">
                            <div>
                                <p class="text-sm text-gray-400">Position</p>
                                <p class="text-lg font-bold text-white">2nd</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-400">Played</p>
                                <p class="text-lg font-bold text-white">21</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-400">Points</p>
                                <p class="text-lg font-bold text-white">47</p>
                            </div>
                        </div>
                    </div>
                </a>

                <!-- Real Madrid Card -->
                <a href="team.php?team=real-madrid" class="bg-gray-900 rounded-lg overflow-hidden hover:bg-gray-800 transition-colors cursor-pointer">
                    <div class="p-6">
                        <div class="flex items-center space-x-4 mb-4">
                            <img src="images/real-madrid.png" alt="Real Madrid" class="w-16 h-16 object-contain">
                            <div>
                                <h4 class="text-xl font-semibold text-white">Real Madrid</h4>
                                <p class="text-gray-400">La Liga</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-3 gap-4 text-center mb-4">
                            <div>
                                <p class="text-sm text-gray-400">Position</p>
                                <p class="text-lg font-bold text-white">1st</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-400">Played</p>
                                <p class="text-lg font-bold text-white">21</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-400">Points</p>
                                <p class="text-lg font-bold text-white">51</p>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8 mt-auto">
    <?php include 'includes/footer.php'; ?>
    </footer>
</body>
</html> 