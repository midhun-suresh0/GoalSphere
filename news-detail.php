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

// Get news article ID from URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch the news article
$sql = "SELECT * FROM news WHERE id = ? AND is_published = 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$news = $result->fetch_assoc();

// If article doesn't exist or isn't published, redirect to news page
if (!$news) {
    header('Location: news.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($news['title']); ?> - GoalSphere</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="css/styles.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
<?php include('includes/header.php'); ?>

    <main class="container mx-auto px-4 py-8">
        <article class="max-w-4xl mx-auto bg-white rounded-lg shadow-md overflow-hidden">
            <?php if($news['image_url']): ?>
            <img src="<?php echo htmlspecialchars($news['image_url']); ?>" 
                 alt="<?php echo htmlspecialchars($news['title']); ?>"
                         class="w-full h-96 object-cover">
            <?php endif; ?>
                    
                    <div class="p-8">
                        <h1 class="text-4xl font-bold mb-4">
                    <?php echo htmlspecialchars($news['title']); ?>
                        </h1>
                        
                <div class="flex items-center text-gray-500 mb-6">
                    <span class="mr-4">By <?php echo htmlspecialchars($news['author']); ?></span>
                    <span><?php echo date('F d, Y', strtotime($news['created_at'])); ?></span>
                        </div>
                        
                        <div class="prose max-w-none">
                    <?php echo nl2br(htmlspecialchars($news['content'])); ?>
                </div>
            </div>
        </article>
        
        <div class="max-w-4xl mx-auto mt-8">
            <a href="news.php" class="text-blue-600 hover:text-blue-800">
                ← Back to News
            </a>
        </div>
    </main>

</body>
</html> 