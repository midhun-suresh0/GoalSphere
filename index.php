<?php
session_start();
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
                        <a href="matches.html" class="hover:text-gray-300">Matches</a>
                        <a href="#teams" class="hover:text-gray-300">Teams</a>
                        <a href="#competitions" class="hover:text-gray-300">Competitions</a>
                    </div>
                </div>

                <!-- Right Actions -->
                <div class="flex items-center space-x-4">
                    <button class="p-2 hover:bg-gray-800 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </button>
                    <button class="p-2 hover:bg-gray-800 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </button>
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
                        <a href="signin.php" class="bg-gray-800 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">Sign In</a>
                        <a href="register.php" class="bg-gray-700 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition-colors border border-gray-600">Join</a>
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
                    <h1 class="text-4xl md:text-5xl font-bold mb-6 text-white">Welcome to GoalSphere</h1>
                    <p class="text-xl mb-8 text-gray-200">Your ultimate destination for football updates, news, and more.</p>
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <a href="signin.php" class="inline-block bg-black text-white px-8 py-3 rounded-full hover:bg-gray-800 transition-colors border border-white">Get Started</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Top News Section -->
        <section class="py-16 bg-black">
            <div class="container mx-auto px-4">
                <h2 class="text-3xl font-bold text-white mb-8">Top News</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <?php if ($newsData && $newsData['status'] === 'ok'): ?>
                        <?php foreach (array_slice($newsData['articles'], 0, 3) as $article): ?>
                            <div class="bg-gray-900 rounded-lg overflow-hidden transition-transform duration-300 hover:transform hover:scale-105">
                                <img src="<?php echo htmlspecialchars($article['urlToImage'] ?? 'images/default-news.jpg'); ?>" 
                                     alt="News Image" 
                                     class="w-full h-48 object-cover"
                                     onerror="this.src='images/default-news.jpg'">
                                <div class="p-6">
                                    <h3 class="text-xl font-semibold text-white mb-2">
                                        <?php echo htmlspecialchars($article['title']); ?>
                                    </h3>
                                    <p class="text-gray-400 mb-4">
                                        <?php echo htmlspecialchars(substr($article['description'], 0, 100)) . '...'; ?>
                                    </p>
                                    <a href="<?php echo htmlspecialchars($article['url']); ?>" 
                                       target="_blank"
                                       class="inline-block bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                                        Read More
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <!-- Fallback content in case API fails -->
                        <div class="bg-gray-900 rounded-lg overflow-hidden">
                            <img src="images/news1.jpg" alt="News 1" class="w-full h-48 object-cover">
                            <div class="p-6">
                                <h3 class="text-xl font-semibold text-white mb-2">Latest Match Updates</h3>
                                <p class="text-gray-400 mb-4">Stay updated with the latest football matches and scores...</p>
                                <a href="#" class="inline-block bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">Read More</a>
                            </div>
                        </div>
                        <div id="news-container"></div>
                        <!-- Add two more fallback news items similar to your original design -->
                    <?php endif; ?>
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

    </script>
</body>
</html> 