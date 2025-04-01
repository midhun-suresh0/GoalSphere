<?php
session_start();
require_once 'includes/language.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Game Zone - GoalSphere</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="css/styles.css" rel="stylesheet">
</head>
<body class="bg-black min-h-screen">
    <!-- Include header -->
    <?php include 'includes/header.php'; ?>

    <!-- Hero Banner -->
    <div class="relative overflow-hidden">
        <div class="absolute inset-0">
            <img src="images/Last.jpg" alt="Game Zone" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-black bg-opacity-60"></div>
        </div>
        <div class="container mx-auto px-4 py-16 relative z-10">
            <div class="max-w-3xl mx-auto text-center">
                <h1 class="text-4xl md:text-5xl font-bold text-white mb-4">Game Zone</h1>
                <p class="text-xl text-blue-200 mb-8">Test your football knowledge and have fun with our interactive games</p>
            </div>
        </div>
    </div>

    <!-- Games Section -->
    <section class="py-16">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-5xl mx-auto">
                <!-- Football Quiz Game Card -->
                <div class="bg-gradient-to-br from-blue-900 to-blue-700 rounded-xl shadow-lg overflow-hidden">
                    <div class="p-8">
                        <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mb-6">
                            <svg class="w-10 h-10 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-white mb-3">Football Quiz</h3>
                        <p class="text-blue-100 mb-8">Test your knowledge with questions about football history, players, teams, rules, and tournaments. Multiple difficulty levels available.</p>
                        
                        <div class="space-y-4 mb-8">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-green-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span class="text-white">Multiple choice questions</span>
                            </div>
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-green-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span class="text-white">Timed challenges</span>
                            </div>
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-green-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span class="text-white">Leaderboards</span>
                            </div>
                        </div>
                        
                        <a href="quiz.php" class="inline-block bg-white text-blue-700 font-semibold px-8 py-3 rounded-lg hover:bg-blue-50 transition-colors">
                            Start Quiz
                        </a>
                    </div>
                </div>
                
                <!-- Guess the Player Game Card -->
                <div class="bg-gradient-to-br from-purple-900 to-purple-700 rounded-xl shadow-lg overflow-hidden">
                    <div class="p-8">
                        <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mb-6">
                            <svg class="w-10 h-10 text-purple-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-white mb-3">Guess the Player</h3>
                        <p class="text-purple-100 mb-8">Can you identify famous football players from clues, blurred images, or partial statistics? Test your knowledge of football stars from around the world.</p>
                        
                        <div class="space-y-4 mb-8">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-green-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span class="text-white">Progressive image reveals</span>
                            </div>
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-green-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span class="text-white">Hint system</span>
                            </div>
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-green-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span class="text-white">Daily challenges</span>
                            </div>
                        </div>
                        
                        <a href="guess_player.php" class="inline-block bg-white text-purple-700 font-semibold px-8 py-3 rounded-lg hover:bg-purple-50 transition-colors">
                            Play Now
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Include footer -->
    <?php include 'includes/footer.php'; ?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Any JavaScript for the Game Zone page goes here
    });
    </script>
</body>
</html> 