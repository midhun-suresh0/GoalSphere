<?php
header('Content-Type: application/json');
session_start();

// Update the admin check to match your session variable
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Update database connection to match your setup
$host = 'localhost';
$dbname = 'goalsphere';
$username = 'root';
$password = '';

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch($action) {
    case 'create':
        $title = $conn->real_escape_string($_POST['title']);
        $content = $conn->real_escape_string($_POST['content']);
        $author = $conn->real_escape_string($_POST['author']);
        $image_url = $conn->real_escape_string($_POST['image_url']);
        $is_published = isset($_POST['is_published']) ? 1 : 0;
        
        $sql = "INSERT INTO news (title, content, author, image_url, is_published) 
                VALUES ('$title', '$content', '$author', '$image_url', $is_published)";
        
        if ($conn->query($sql)) {
            echo json_encode(['success' => true, 'id' => $conn->insert_id]);
        } else {
            echo json_encode(['error' => $conn->error]);
        }
        break;

    case 'read':
        $sql = "SELECT * FROM news ORDER BY created_at DESC";
        $result = $conn->query($sql);
        $news = [];
        
        while($row = $result->fetch_assoc()) {
            $news[] = $row;
        }
        
        echo json_encode($news);
        break;

    case 'update':
        $id = (int)$_POST['id'];
        $title = $conn->real_escape_string($_POST['title']);
        $content = $conn->real_escape_string($_POST['content']);
        $author = $conn->real_escape_string($_POST['author']);
        $image_url = $conn->real_escape_string($_POST['image_url']);
        $is_published = isset($_POST['is_published']) ? 1 : 0;
        
        $sql = "UPDATE news 
                SET title='$title', content='$content', 
                    author='$author', image_url='$image_url',
                    is_published=$is_published 
                WHERE id=$id";
        
        if ($conn->query($sql)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => $conn->error]);
        }
        break;

    case 'delete':
        $id = (int)$_POST['id'];
        $sql = "DELETE FROM news WHERE id=$id";
        
        if ($conn->query($sql)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => $conn->error]);
        }
        break;

    default:
        echo json_encode(['error' => 'Invalid action']);
        break;
}

$conn->close();
?> 