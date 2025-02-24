<?php
session_start();
require_once 'includes/language.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GoalSphere - Your Ultimate Football Destination</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="css/styles.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
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
                                            <?php
                                            
                                            if(isset($_SESSION['email'])&& !empty($_SESSION['email'])){
                                                echo strtoupper(substr($_SESSION['email'],0,1));
                                            }else{
                                                echo'G';
                                            }
                                            ?>
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
                        <a href="signin.php" class="bg-gray-800 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                            <?php echo __('signin'); ?>
                        </a>
                        <a href="register.php" class="bg-gray-700 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition-colors border border-gray-600">
                            <?php echo __('join'); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Add padding to the body to prevent content from hiding behind fixed navbar -->
    <div class="pt-16">
        <!-- Hero Section -->
        <div class="relative bg-cover bg-center min-h-[500px] flex items-center" style="background-image: url('images/hero-bg.jpg');">
            <!-- Overlay to ensure text is readable -->
            <div class="absolute inset-0 bg-black bg-opacity-50"></div>
            
            <div class="container mx-auto px-4 relative z-10">
                <div class="max-w-3xl">
                    <h1 class="text-4xl md:text-5xl font-bold mb-6 text-white"><?php echo __('welcome'); ?></h1>
                    <p class="text-xl mb-8 text-gray-200"><?php echo __('subtitle'); ?></p>
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <a href="signin.php" class="inline-block bg-black text-white px-8 py-3 rounded-full hover:bg-gray-800 transition-colors border border-white">
                            <?php echo __('get_started'); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Top News Section -->
        <section class="py-16 bg-black">
            <div class="container mx-auto px-4">
                <h2 class="text-3xl font-bold text-white mb-8">Top News</h2>
                
                <!-- Featured News Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
                    <a href="news-detail.php?id=1" class="bg-gray-900 rounded-lg overflow-hidden transition-transform duration-300 hover:transform hover:scale-105">
                        <img src="images/news1.jpg" alt="News 1" class="w-full h-48 object-cover">
                        <div class="p-6">
                            <h3 class="text-xl font-semibold text-white mb-2">Manchester United Secures Dramatic Win</h3>
                            <p class="text-gray-400 mb-4">A last-minute goal from Marcus Rashford sends United through...</p>
                            <span class="inline-block bg-green-600 text-white px-4 py-2 rounded-lg">Read More</span>
                        </div>
                    </a>

                    <a href="news-detail.php?id=2" class="bg-gray-900 rounded-lg overflow-hidden transition-transform duration-300 hover:transform hover:scale-105">
                        <img src="images/news2.jpg" alt="News 2" class="w-full h-48 object-cover">
                        <div class="p-6">
                            <h3 class="text-xl font-semibold text-white mb-2">Liverpool Announces New Signing</h3>
                            <p class="text-gray-400 mb-4">The Reds strengthen their squad with promising young midfielder...</p>
                            <span class="inline-block bg-green-600 text-white px-4 py-2 rounded-lg">Read More</span>
                        </div>
                    </a>

                    <a href="news-detail.php?id=3" class="bg-gray-900 rounded-lg overflow-hidden transition-transform duration-300 hover:transform hover:scale-105">
                        <img src="images/news3.jpg" alt="News 3" class="w-full h-48 object-cover">
                        <div class="p-6">
                            <h3 class="text-xl font-semibold text-white mb-2">Champions League Draw Results</h3>
                            <p class="text-gray-400 mb-4">Exciting matchups ahead in the knockout stages...</p>
                            <span class="inline-block bg-green-600 text-white px-4 py-2 rounded-lg">Read More</span>
                        </div>
                    </a>
                </div>

                <!-- Additional News List -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <a href="news-detail.php?id=4" class="bg-gray-900 rounded-lg p-4 flex gap-4 hover:bg-gray-800 transition-colors">
                        <img src="images/news4.jpg" alt="News 4" class="w-24 h-24 object-cover rounded">
                        <div>
                            <h3 class="text-white font-semibold mb-2">Premier League Transfer Updates</h3>
                            <p class="text-gray-400 text-sm">Latest transfer news and rumors from the Premier League...</p>
                        </div>
                    </a>

                    <a href="news-detail.php?id=5" class="bg-gray-900 rounded-lg p-4 flex gap-4 hover:bg-gray-800 transition-colors">
                        <img src="images/news5.jpg" alt="News 5" class="w-24 h-24 object-cover rounded">
                        <div>
                            <h3 class="text-white font-semibold mb-2">World Cup 2026 Preparations</h3>
                            <p class="text-gray-400 text-sm">Updates on the preparations for the upcoming World Cup...</p>
                        </div>
                    </a>
                </div>
            </div>
        </section>

        <!-- News Detail Modal -->
        <div id="newsModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
            <div class="bg-gray-900 rounded-lg max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                <div class="p-6">
                    <div class="flex justify-end">
                        <button onclick="closeNewsDetail()" class="text-gray-400 hover:text-white">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    <img id="modalImage" src="" alt="News Image" class="w-full h-64 object-cover rounded-lg mb-6">
                    <h2 id="modalTitle" class="text-2xl font-bold text-white mb-4"></h2>
                    <p id="modalDate" class="text-gray-400 mb-4"></p>
                    <p id="modalDescription" class="text-gray-300 mb-6"></p>
                </div>
            </div>
        </div>
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

    <script src="js/auth.js"></script>
    <script src="js/matches.js"></script>
    <script src="js/news.js"></script>
    <script>
const apiKey = '4f298553743744078783c4c6593aa043'; // Replace with your API key
const url = `https://newsapi.org/v2/top-headlines?category=sports&language=en&apiKey=${apiKey}`;

async function fetchSportsNews() {
    try {
        const response = await fetch(url);
        const data = await response.json();
        
        if (data.status === 'ok') {
            displayNews(data.articles);
        } else {
            console.error('Error fetching news:', data);
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

function displayNews(articles) {
    const newsContainer = document.getElementById('news-container');
    newsContainer.innerHTML = ''; // Clear previous content
    
    articles.forEach(article => {
        const newsItem = document.createElement('div');
        newsItem.className = 'news-item bg-gray-900 text-white p-4 mb-4 rounded-lg';
        newsItem.innerHTML = `
            <h3 class="text-lg font-semibold">${article.title}</h3>
            <p class="text-gray-300">${article.description || 'No description available.'}</p>
            <a href="${article.url}" target="_blank" class="text-green-400 hover:underline">Read More</a>
        `;
        newsContainer.appendChild(newsItem);
    });
}

// Call the function to fetch news
fetchSportsNews();

// User menu functionality
function toggleDropdown() {
    const dropdown = document.getElementById('userDropdown');
    if (dropdown) {
        dropdown.classList.toggle('hidden');
    }
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const dropdown = document.getElementById('userDropdown');
    const button = event.target.closest('button');
    
    if (dropdown && !button && !dropdown.contains(event.target)) {
        dropdown.classList.add('hidden');
    }
});

// Close dropdown when pressing Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const dropdown = document.getElementById('userDropdown');
        if (dropdown) {
            dropdown.classList.add('hidden');
        }
    }
});

function toggleSettings(event) {
    event.preventDefault(); // Prevent the default link behavior
    const panel = document.getElementById('settingsPanel');
    if (panel) {
        panel.classList.toggle('hidden');
        // Add event listener for clicking outside
        setTimeout(() => {
            document.addEventListener('click', closeSettingsOnClickOutside);
        }, 0);
    }
}

// Function to close settings when clicking outside
function closeSettingsOnClickOutside(event) {
    const panel = document.getElementById('settingsPanel');
    const settingsButton = event.target.closest('button');
    
    if (panel && !panel.contains(event.target) && !settingsButton?.contains(event.target)) {
        panel.classList.add('hidden');
        document.removeEventListener('click', closeSettingsOnClickOutside);
    }
}

// Close settings panel when pressing Escape
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

function showNewsDetail(title, description, image, date, fullContent) {
    const modal = document.getElementById('newsModal');
    const modalImage = document.getElementById('modalImage');
    const modalTitle = document.getElementById('modalTitle');
    const modalDescription = document.getElementById('modalDescription');
    const modalDate = document.getElementById('modalDate');

    modalImage.src = image;
    modalTitle.textContent = title;
    modalDescription.textContent = fullContent;
    modalDate.textContent = date;

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';

    // Close modal when clicking outside
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeNewsDetail();
        }
    });
}

function closeNewsDetail() {
    const modal = document.getElementById('newsModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.style.overflow = 'auto';
}

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeNewsDetail();
    }
});

    </script>
</body>
</html> 