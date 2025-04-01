<?php
session_start();
require_once 'includes/db.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$user_id = $_SESSION['user_id'];
$difficulty = $data['difficulty'] ?? 'medium';
$category = intval($data['category'] ?? 0);
$score = intval($data['score'] ?? 0);
$total = intval($data['total'] ?? 0);

// Validate data
if (!in_array($difficulty, ['easy', 'medium', 'hard'])) {
    $difficulty = 'medium';
}

if ($score < 0 || $score > $total) {
    echo json_encode(['success' => false, 'message' => 'Invalid score']);
    exit;
}

// Create quiz_results table if it doesn't exist
$create_table = "CREATE TABLE IF NOT EXISTS quiz_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category_id INT,
    difficulty VARCHAR(20) NOT NULL,
    score INT NOT NULL,
    total INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (category_id) REFERENCES quiz_categories(id)
)";

$conn->query($create_table);

// Insert the result
$stmt = $conn->prepare("INSERT INTO quiz_results (user_id, category_id, difficulty, score, total) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("iisii", $user_id, $category, $difficulty, $score, $total);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Quiz result saved successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to save quiz result']);
}
?> 