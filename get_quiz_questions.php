<?php
session_start();
require_once 'includes/db.php';

header('Content-Type: application/json');

// Get parameters
$difficulty = isset($_GET['difficulty']) ? $_GET['difficulty'] : 'medium';
$category = isset($_GET['category']) ? intval($_GET['category']) : 0;

// Validate difficulty
if (!in_array($difficulty, ['easy', 'medium', 'hard'])) {
    $difficulty = 'medium';
}

// Build query
$query = "SELECT q.id, q.question_text, q.difficulty, q.category_id
          FROM quiz_questions q
          WHERE q.status = 'active'";

// Add filters
if ($difficulty) {
    $query .= " AND q.difficulty = '$difficulty'";
}

if ($category > 0) {
    $query .= " AND q.category_id = $category";
}

// Add limit and randomize
$query .= " ORDER BY RAND() LIMIT 10";

$stmt = $conn->prepare($query);
$stmt->execute();
$questions_result = $stmt->get_result();

$questions = [];

while ($question = $questions_result->fetch_assoc()) {
    // Get answers for this question
    $answer_query = "SELECT id, answer_text, is_correct 
                     FROM quiz_answers 
                     WHERE question_id = ?
                     ORDER BY id";
    
    $answer_stmt = $conn->prepare($answer_query);
    $answer_stmt->bind_param("i", $question['id']);
    $answer_stmt->execute();
    $answers_result = $answer_stmt->get_result();
    
    $answers = [];
    while ($answer = $answers_result->fetch_assoc()) {
        $answers[] = [
            'id' => $answer['id'],
            'answer_text' => $answer['answer_text'],
            'is_correct' => (bool)$answer['is_correct']
        ];
    }
    
    $questions[] = [
        'id' => $question['id'],
        'question_text' => $question['question_text'],
        'difficulty' => $question['difficulty'],
        'category_id' => $question['category_id'],
        'answers' => $answers
    ];
}

echo json_encode([
    'success' => true,
    'questions' => $questions
]);
?> 