<?php
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: signin.php');
    exit();
}

// Database connection
require_once 'includes/db.php';

// Handle form submissions
$success_message = '';
$error_message = '';

// Handle question deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $question_id = intval($_GET['delete']);
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // First delete associated answers
        $stmt = $conn->prepare("DELETE FROM quiz_answers WHERE question_id = ?");
        $stmt->bind_param("i", $question_id);
        $stmt->execute();
        
        // Then delete the question
        $stmt = $conn->prepare("DELETE FROM quiz_questions WHERE id = ?");
        $stmt->bind_param("i", $question_id);
        $stmt->execute();
        
        // Commit transaction
        $conn->commit();
        
        $success_message = "Question deleted successfully!";
    } catch (Exception $e) {
        // Rollback in case of error
        $conn->rollback();
        $error_message = "Error deleting question: " . $e->getMessage();
    }
}

// Handle question submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_question'])) {
    $question_text = trim($_POST['question_text']);
    $category_id = intval($_POST['category_id']);
    $difficulty = trim($_POST['difficulty']);
    
    // Check if we're editing or adding
    $question_id = isset($_POST['question_id']) ? intval($_POST['question_id']) : 0;
    
    // Validate inputs
    if (empty($question_text)) {
        $error_message = "Question text cannot be empty!";
    } else {
        $conn->begin_transaction();
        
        try {
            if ($question_id > 0) {
                // Update existing question
                $stmt = $conn->prepare("UPDATE quiz_questions SET question_text = ?, category_id = ?, difficulty = ? WHERE id = ?");
                $stmt->bind_param("sisi", $question_text, $category_id, $difficulty, $question_id);
                $stmt->execute();
                
                // Delete existing answers for this question
                $stmt = $conn->prepare("DELETE FROM quiz_answers WHERE question_id = ?");
                $stmt->bind_param("i", $question_id);
                $stmt->execute();
            } else {
                // Insert new question
                $stmt = $conn->prepare("INSERT INTO quiz_questions (question_text, category_id, difficulty, status) VALUES (?, ?, ?, 'active')");
                $stmt->bind_param("sis", $question_text, $category_id, $difficulty);
                $stmt->execute();
                $question_id = $conn->insert_id;
            }
            
            // Now handle the answers
            for ($i = 0; $i < 4; $i++) {
                $answer_text = trim($_POST['answer_' . $i]);
                $is_correct = isset($_POST['correct_answer']) && $_POST['correct_answer'] == $i ? 1 : 0;
                
                if (!empty($answer_text)) {
                    $stmt = $conn->prepare("INSERT INTO quiz_answers (question_id, answer_text, is_correct) VALUES (?, ?, ?)");
                    $stmt->bind_param("isi", $question_id, $answer_text, $is_correct);
                    $stmt->execute();
                }
            }
            
            $conn->commit();
            $success_message = $question_id > 0 ? "Question updated successfully!" : "Question added successfully!";
            
            // Clear form data after successful submission
            if ($question_id == 0) {
                $_POST = array();
            }
        } catch (Exception $e) {
            $conn->rollback();
            $error_message = "Error saving question: " . $e->getMessage();
        }
    }
}

// Fetch categories for the dropdown
$categories = $conn->query("SELECT * FROM quiz_categories ORDER BY name");

// Fetch existing questions with their categories
$questions_query = "SELECT q.*, c.name as category_name 
                   FROM quiz_questions q 
                   LEFT JOIN quiz_categories c ON q.category_id = c.id 
                   ORDER BY q.id DESC";
$questions = $conn->query($questions_query);

// Fetch question details if editing
$edit_question = null;
$edit_answers = [];

if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    
    $stmt = $conn->prepare("SELECT * FROM quiz_questions WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $edit_question = $stmt->get_result()->fetch_assoc();
    
    if ($edit_question) {
        $stmt = $conn->prepare("SELECT * FROM quiz_answers WHERE question_id = ? ORDER BY id");
        $stmt->bind_param("i", $edit_id);
        $stmt->execute();
        $edit_answers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Management - GoalSphere Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Include Sidebar -->
        <?php include 'includes/admin_sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="flex-1 overflow-auto">
            <div class="p-8">
                <h1 class="text-2xl font-bold text-gray-800 mb-6">Quiz Management</h1>
                
                <!-- Success/Error Messages -->
                <?php if (!empty($success_message)): ?>
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                        <p><?php echo $success_message; ?></p>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($error_message)): ?>
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                        <p><?php echo $error_message; ?></p>
                    </div>
                <?php endif; ?>
                
                <!-- Add/Edit Question Form -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">
                        <?php echo $edit_question ? 'Edit Question' : 'Add New Question'; ?>
                    </h2>
                    
                    <form method="POST" action="admin_quiz.php">
                        <?php if ($edit_question): ?>
                            <input type="hidden" name="question_id" value="<?php echo $edit_question['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="mb-4">
                            <label for="question_text" class="block text-gray-700 font-medium mb-2">Question</label>
                            <textarea name="question_text" id="question_text" rows="3" 
                                     class="w-full border border-gray-300 px-3 py-2 rounded-lg focus:outline-none focus:border-blue-500" 
                                     required><?php echo $edit_question ? htmlspecialchars($edit_question['question_text']) : ''; ?></textarea>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="category_id" class="block text-gray-700 font-medium mb-2">Category</label>
                                <select name="category_id" id="category_id" 
                                       class="w-full border border-gray-300 px-3 py-2 rounded-lg focus:outline-none focus:border-blue-500">
                                    <?php while ($category = $categories->fetch_assoc()): ?>
                                        <option value="<?php echo $category['id']; ?>" 
                                                <?php echo ($edit_question && $edit_question['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div>
                                <label for="difficulty" class="block text-gray-700 font-medium mb-2">Difficulty</label>
                                <select name="difficulty" id="difficulty" 
                                       class="w-full border border-gray-300 px-3 py-2 rounded-lg focus:outline-none focus:border-blue-500">
                                    <option value="easy" <?php echo ($edit_question && $edit_question['difficulty'] == 'easy') ? 'selected' : ''; ?>>Easy</option>
                                    <option value="medium" <?php echo ($edit_question && $edit_question['difficulty'] == 'medium') ? 'selected' : ''; ?>>Medium</option>
                                    <option value="hard" <?php echo ($edit_question && $edit_question['difficulty'] == 'hard') ? 'selected' : ''; ?>>Hard</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-800 mb-2">Answer Options</h3>
                            <p class="text-gray-600 text-sm mb-4">Select the correct answer by clicking the radio button.</p>
                            
                            <?php for ($i = 0; $i < 4; $i++): ?>
                                <div class="flex items-center mb-3">
                                    <input type="radio" name="correct_answer" id="correct_<?php echo $i; ?>" value="<?php echo $i; ?>" 
                                           class="mr-2"
                                           <?php echo ($edit_question && isset($edit_answers[$i]) && $edit_answers[$i]['is_correct'] == 1) ? 'checked' : ''; ?>>
                                    <input type="text" name="answer_<?php echo $i; ?>" 
                                           placeholder="Answer option <?php echo $i + 1; ?>" 
                                           class="flex-1 border border-gray-300 px-3 py-2 rounded-lg focus:outline-none focus:border-blue-500"
                                           value="<?php echo ($edit_question && isset($edit_answers[$i])) ? htmlspecialchars($edit_answers[$i]['answer_text']) : ''; ?>"
                                           required>
                                </div>
                            <?php endfor; ?>
                        </div>
                        
                        <div class="flex justify-end">
                            <?php if ($edit_question): ?>
                                <a href="admin_quiz.php" class="bg-gray-300 text-gray-800 px-4 py-2 rounded-lg mr-2 hover:bg-gray-400">
                                    Cancel
                                </a>
                            <?php endif; ?>
                            <button type="submit" name="submit_question" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                                <?php echo $edit_question ? 'Update Question' : 'Add Question'; ?>
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Questions List -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Existing Questions</h2>
                    
                    <div class="overflow-x-auto">
                        <?php if ($questions->num_rows > 0): ?>
                            <table class="min-w-full bg-white">
                                <thead>
                                    <tr>
                                        <th class="py-3 px-4 border-b text-left">ID</th>
                                        <th class="py-3 px-4 border-b text-left">Question</th>
                                        <th class="py-3 px-4 border-b text-left">Category</th>
                                        <th class="py-3 px-4 border-b text-left">Difficulty</th>
                                        <th class="py-3 px-4 border-b text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($question = $questions->fetch_assoc()): ?>
                                        <tr>
                                            <td class="py-3 px-4 border-b"><?php echo $question['id']; ?></td>
                                            <td class="py-3 px-4 border-b"><?php echo htmlspecialchars(substr($question['question_text'], 0, 100)) . (strlen($question['question_text']) > 100 ? '...' : ''); ?></td>
                                            <td class="py-3 px-4 border-b"><?php echo htmlspecialchars($question['category_name']); ?></td>
                                            <td class="py-3 px-4 border-b capitalize"><?php echo htmlspecialchars($question['difficulty']); ?></td>
                                            <td class="py-3 px-4 border-b text-center">
                                                <a href="admin_quiz.php?edit=<?php echo $question['id']; ?>" class="text-blue-600 hover:text-blue-800 mr-2">Edit</a>
                                                <a href="admin_quiz.php?delete=<?php echo $question['id']; ?>" class="text-red-600 hover:text-red-800" onclick="return confirm('Are you sure you want to delete this question?')">Delete</a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p class="text-gray-600">No questions added yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 