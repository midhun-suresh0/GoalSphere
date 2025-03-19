<?php
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: signin.php');
    exit();
}

// Database connection
$host = 'localhost';
$dbname = 'goalsphere';
$username = 'root';
$password = '';

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all news articles
$news_articles = $conn->query("SELECT * FROM news ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News Management - GoalSphere Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="css/styles.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="bg-gray-900 text-white w-64 flex-shrink-0">
            <!-- Copy the sidebar from admin.php -->
            <?php include('includes/admin_sidebar.php'); ?>
        </div>

        <!-- Main Content -->
        <div class="flex-1 overflow-auto p-8">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">News Management</h1>
                <button onclick="showAddNewsForm()" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    Add New Article
                </button>
            </div>

            <!-- Add/Edit News Form (Hidden by default) -->
            <div id="newsForm" class="hidden bg-white p-6 rounded-lg shadow-md mb-6">
                <h2 id="formTitle" class="text-xl font-semibold mb-4">Add New Article</h2>
                <form id="addEditNewsForm" onsubmit="handleNewsSubmit(event)">
                    <input type="hidden" id="newsId" name="id">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Title</label>
                            <input type="text" id="title" name="title" required
                                class="w-full p-2 border rounded">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Content</label>
                            <textarea id="content" name="content" required
                                class="w-full p-2 border rounded" rows="4"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Author</label>
                            <input type="text" id="author" name="author" required
                                class="w-full p-2 border rounded">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Image URL</label>
                            <input type="url" id="imageUrl" name="image_url"
                                class="w-full p-2 border rounded">
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" id="isPublished" name="is_published"
                                class="mr-2">
                            <label>Publish immediately</label>
                        </div>
                        <div class="flex space-x-2">
                            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                                Save Article
                            </button>
                            <button type="button" onclick="hideNewsForm()" 
                                class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">
                                Cancel
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- News Articles List -->
            <div class="bg-white rounded-lg shadow-md">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Author</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php while($article = $news_articles->fetch_assoc()): ?>
                            <tr>
                                <td class="px-6 py-4"><?php echo htmlspecialchars($article['title']); ?></td>
                                <td class="px-6 py-4"><?php echo htmlspecialchars($article['author']); ?></td>
                                <td class="px-6 py-4"><?php echo date('M d, Y', strtotime($article['created_at'])); ?></td>
                                <td class="px-6 py-4">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?php echo $article['is_published'] ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                        <?php echo $article['is_published'] ? 'Published' : 'Draft'; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <button onclick="editNews(<?php echo htmlspecialchars(json_encode($article)); ?>)"
                                        class="text-blue-600 hover:text-blue-900 mr-3">Edit</button>
                                    <button onclick="deleteNews(<?php echo $article['id']; ?>)"
                                        class="text-red-600 hover:text-red-900">Delete</button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showAddNewsForm() {
            document.getElementById('formTitle').textContent = 'Add New Article';
            document.getElementById('newsForm').classList.remove('hidden');
            document.getElementById('addEditNewsForm').reset();
            document.getElementById('newsId').value = '';
        }

        function hideNewsForm() {
            document.getElementById('newsForm').classList.add('hidden');
            document.getElementById('addEditNewsForm').reset();
        }

        function editNews(article) {
            document.getElementById('formTitle').textContent = 'Edit Article';
            document.getElementById('newsId').value = article.id;
            document.getElementById('title').value = article.title;
            document.getElementById('content').value = article.content;
            document.getElementById('author').value = article.author;
            document.getElementById('imageUrl').value = article.image_url || '';
            document.getElementById('isPublished').checked = article.is_published == 1;
            document.getElementById('newsForm').classList.remove('hidden');
        }

        function handleNewsSubmit(event) {
            event.preventDefault();
            const formData = new FormData(event.target);
            const newsId = document.getElementById('newsId').value;
            
            formData.append('action', newsId ? 'update' : 'create');
            if (newsId) formData.append('id', newsId);

            fetch('news_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + (data.error || 'Unknown error occurred'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while saving the article');
            });
        }

        function deleteNews(id) {
            if (confirm('Are you sure you want to delete this article?')) {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('id', id);

                fetch('news_handler.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + (data.error || 'Unknown error occurred'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting the article');
                });
            }
        }
    </script>
</body>
</html> 