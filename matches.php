<?php
session_start();
require_once 'includes/language.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Matches - GoalSphere</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="css/styles.css" rel="stylesheet">
</head>
<body class="bg-black min-h-screen">
    <!-- Navbar -->
    <nav class="bg-black text-white fixed w-full z-50">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <!-- Logo and Main Nav -->
                <div class="flex items-center space-x-8">
                    <a href="index.php" class="text-2xl font-bold">GoalSphere</a>
                    <div class="hidden md:flex space-x-8">
                        <a href="matches.php" class="hover:text-gray-300"><?php echo __('matches'); ?></a>
                        <a href="#teams" class="hover:text-gray-300"><?php echo __('teams'); ?></a>
                        <a href="#competitions" class="hover:text-gray-300"><?php echo __('competitions'); ?></a>
                    </div>
                </div>

                <!-- Right Actions -->
                <div class="flex items-center space-x-4">
                    <button class="p-2 hover:bg-gray-800 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </button>
                    <div class="relative">
                        <a href="settings.php" class="p-2 hover:bg-gray-800 rounded-full inline-flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </a>
                        
                        <!-- Settings Panel -->
                        <div id="settingsPanel" class="hidden absolute right-0 mt-2 w-80 bg-gray-900 rounded-lg shadow-xl z-50">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold text-white mb-4">Settings</h3>
                                
                                <!-- Language Settings -->
                                <div class="mb-6">
                                    <h4 class="text-sm font-medium text-gray-400 mb-3">Language</h4>
                                    <div class="grid grid-cols-2 gap-2">
                                        <button onclick="updateSettings('language', 'en')" class="flex items-center justify-between px-3 py-2 bg-gray-800 rounded-lg text-white hover:bg-gray-700 transition-colors">
                                            <span>English</span>
                                            <?php if (($_SESSION['language'] ?? 'en') === 'en'): ?>
                                                <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                </svg>
                                            <?php endif; ?>
                                        </button>
                                        <button onclick="updateSettings('language', 'es')" class="flex items-center justify-between px-3 py-2 bg-gray-800 rounded-lg text-white hover:bg-gray-700 transition-colors">
                                            <span>Español</span>
                                            <?php if (($_SESSION['language'] ?? '') === 'es'): ?>
                                                <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                </svg>
                                            <?php endif; ?>
                                        </button>
                                    </div>
                                </div>

                                <!-- Display Mode -->
                                <div>
                                    <h4 class="text-sm font-medium text-gray-400 mb-3">Display Mode</h4>
                                    <div class="grid grid-cols-3 gap-2">
                                        <button onclick="updateSettings('display_mode', 'auto')" class="flex items-center justify-between px-3 py-2 bg-gray-800 rounded-lg text-white hover:bg-gray-700 transition-colors">
                                            <span>Auto</span>
                                            <?php if (($_SESSION['display_mode'] ?? 'auto') === 'auto'): ?>
                                                <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                </svg>
                                            <?php endif; ?>
                                        </button>
                                        <button onclick="updateSettings('display_mode', 'dark')" class="flex items-center justify-between px-3 py-2 bg-gray-800 rounded-lg text-white hover:bg-gray-700 transition-colors">
                                            <span>Dark</span>
                                            <?php if (($_SESSION['display_mode'] ?? '') === 'dark'): ?>
                                                <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                </svg>
                                            <?php endif; ?>
                                        </button>
                                        <button onclick="updateSettings('display_mode', 'light')" class="flex items-center justify-between px-3 py-2 bg-gray-800 rounded-lg text-white hover:bg-gray-700 transition-colors">
                                            <span>Light</span>
                                            <?php if (($_SESSION['display_mode'] ?? '') === 'light'): ?>
                                                <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                </svg>
                                            <?php endif; ?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="flex items-center space-x-4">
                            <!-- User Avatar -->
                            <div class="relative inline-block text-left">
                                <a href="profile.php" class="flex items-center">
                                    <div class="w-10 h-10 bg-gray-700 rounded-full flex items-center justify-center cursor-pointer hover:bg-gray-600 transition-colors">
                                        <span class="text-white font-semibold text-lg">
                                            <?php echo strtoupper(substr($_SESSION['email'] ?? 'G', 0, 1)); ?>
                                        </span>
                                    </div>
                                </a>
                            </div>

                            <!-- Logout Button -->
                            <a href="logout.php" class="bg-gray-700 text-red-400 px-4 py-2 rounded-lg hover:bg-gray-600 transition-colors flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                <span>Logout</span>
                            </a>
                        </div>
                    <?php else: ?>
                        <a href="signin.php" class="bg-gray-800 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">Sign In</a>
                        <a href="register.php" class="bg-gray-700 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition-colors border border-gray-600">Join</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="pt-16">
        <!-- Match Scores Section -->
        <section class="py-12 text-white min-h-screen">
            <div class="container mx-auto px-4">
                <!-- Calendar Navigation -->
                <div class="mb-8">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-semibold">Calendar month: January</h2>
                    </div>
                    
                    <div class="flex items-center space-x-2 overflow-x-auto pb-2">
                        <button class="p-2 rounded-full hover:bg-gray-800 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                        
                        <button class="px-4 py-2 rounded-lg hover:bg-gray-800 transition-colors">Yesterday</button>
                        <button class="px-4 py-2 rounded-lg bg-green-600 text-white">Today</button>
                        <button class="px-4 py-2 rounded-lg hover:bg-gray-800 transition-colors">Tomorrow</button>
                        
                        <div class="flex space-x-2">
                            <button class="px-4 py-2 rounded-lg hover:bg-gray-800 transition-colors">
                                <span class="font-semibold">17</span> Fri
                            </button>
                            <button class="px-4 py-2 rounded-lg hover:bg-gray-800 transition-colors">
                                <span class="font-semibold">18</span> Sat
                            </button>
                            <button class="px-4 py-2 rounded-lg hover:bg-gray-800 transition-colors">
                                <span class="font-semibold">19</span> Sun
                            </button>
                            <button class="px-4 py-2 rounded-lg hover:bg-gray-800 transition-colors">
                                <span class="font-semibold">20</span> Mon
                            </button>
                        </div>
                        
                        <button class="p-2 rounded-full hover:bg-gray-800 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Today's Matches -->
                <div class="mb-8">
                    <h3 class="text-2xl font-bold mb-4">Today's Matches</h3>
                    <p class="text-gray-400 mb-4">Wednesday, 15 January 2025</p>
                    
                    <div id="loading" class="text-center text-gray-400 text-lg py-8">Loading matches...</div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="matchesContainer">
                        <!-- Match cards will be inserted here by JavaScript -->
                    </div>
                </div>

                <!-- Upcoming Matches -->
                <div class="mb-8">
                    <h3 class="text-2xl font-bold mb-4">Upcoming Matches</h3>
                    <div class="space-y-4" id="upcomingMatches">
                        <!-- Upcoming match cards will be inserted here by JavaScript -->
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-xl font-bold mb-4">GoalSphere</h3>
                    <p>Your ultimate football destination</p>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Quick Links</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="hover:text-green-400">About Us</a></li>
                        <li><a href="#" class="hover:text-green-400">Contact</a></li>
                        <li><a href="#" class="hover:text-green-400">Privacy Policy</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>

    <script src="js/matches.js"></script>
    <script>
    function toggleSettings(event) {
        event.preventDefault();
        const panel = document.getElementById('settingsPanel');
        if (panel) {
            panel.classList.toggle('hidden');
            setTimeout(() => {
                document.addEventListener('click', closeSettingsOnClickOutside);
            }, 0);
        }
    }

    function closeSettingsOnClickOutside(event) {
        const panel = document.getElementById('settingsPanel');
        const settingsButton = event.target.closest('button');
        
        if (panel && !panel.contains(event.target) && !settingsButton?.contains(event.target)) {
            panel.classList.add('hidden');
            document.removeEventListener('click', closeSettingsOnClickOutside);
        }
    }

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            const panel = document.getElementById('settingsPanel');
            if (panel && !panel.classList.contains('hidden')) {
                panel.classList.add('hidden');
            }
        }
    });

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
    </script>
</body>
</html>
