<?php
session_start();
require_once 'includes/language.php';

// Database connection
$host = 'localhost';
$dbname = 'goalsphere';
$username = 'root';
$password = '';

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch news articles
$sql = "SELECT * FROM news WHERE is_published = 1 ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News - GoalSphere</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="css/styles.css" rel="stylesheet">
</head>
<body class="bg-black min-h-screen">
    <!-- Include navbar -->
    <?php include 'includes/navbar.php'; ?>

    <!-- Include header -->
    <?php include 'includes/news_header.php'; ?>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-white mb-8">Latest News</h1>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php while($news = $result->fetch_assoc()): ?>
            <article class="bg-gray-800 rounded-lg overflow-hidden shadow-lg">
                <?php if($news['image_url']): ?>
                <img src="<?php echo htmlspecialchars($news['image_url']); ?>" 
                     alt="<?php echo htmlspecialchars($news['title']); ?>"
                     class="w-full h-48 object-cover">
                <?php else: ?>
                <img src="images/default-news.jpg" 
                     alt="Default news image"
                     class="w-full h-48 object-cover">
                <?php endif; ?>
                
                <div class="p-6">
                    <h2 class="text-xl font-semibold text-white mb-2">
                        <?php echo htmlspecialchars($news['title']); ?>
                    </h2>
                    
                    <div class="text-gray-400 text-sm mb-4">
                        <span>By <?php echo htmlspecialchars($news['author']); ?></span>
                        <span class="mx-2">•</span>
                        <span><?php echo date('F d, Y', strtotime($news['created_at'])); ?></span>
                    </div>
                    
                    <p class="text-gray-300 mb-4">
                        <?php 
                        $excerpt = substr(strip_tags($news['content']), 0, 150);
                        echo htmlspecialchars($excerpt) . '...'; 
                        ?>
                    </p>
                    
                    <a href="news-detail.php?id=<?php echo $news['id']; ?>" 
                       class="inline-block bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        Read More
                    </a>
                </div>
            </article>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <script src="js/news.js"></script>
</body>
</html> 