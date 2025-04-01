<?php
session_start();
require_once 'includes/language.php';
require_once 'includes/db.php'; // Add database connection
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Football Quiz - GoalSphere</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="css/styles.css" rel="stylesheet">
</head>
<body class="bg-black min-h-screen">
    <!-- Include header -->
    <?php include 'includes/header.php'; ?>

    <div class="container mx-auto px-4 py-12">
        <div class="max-w-3xl mx-auto">
            <h1 class="text-3xl font-bold text-white mb-6 text-center">Football Quiz Challenge</h1>
            
            <!-- Quiz container -->
            <div class="bg-gray-900 rounded-lg shadow-lg p-6">
                <!-- Difficulty Selection -->
                <div id="difficulty-selection" class="mb-8">
                    <h2 class="text-xl font-semibold text-white mb-4">Select Difficulty</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <button class="bg-green-700 hover:bg-green-600 text-white py-3 px-4 rounded-lg transition-colors difficulty-btn" data-difficulty="easy">
                            Easy
                        </button>
                        <button class="bg-yellow-600 hover:bg-yellow-500 text-white py-3 px-4 rounded-lg transition-colors difficulty-btn" data-difficulty="medium">
                            Medium
                        </button>
                        <button class="bg-red-700 hover:bg-red-600 text-white py-3 px-4 rounded-lg transition-colors difficulty-btn" data-difficulty="hard">
                            Hard
                        </button>
                    </div>
                </div>
                
                <!-- Category Selection (new) -->
                <div id="category-selection" class="mb-8 hidden">
                    <h2 class="text-xl font-semibold text-white mb-4">Select Category</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php
                        // Fetch categories
                        $categories = $conn->query("SELECT * FROM quiz_categories ORDER BY name");
                        while ($category = $categories->fetch_assoc()):
                        ?>
                        <button class="bg-blue-700 hover:bg-blue-600 text-white py-3 px-4 rounded-lg transition-colors category-btn" data-category="<?php echo $category['id']; ?>">
                            <?php echo htmlspecialchars($category['name']); ?>
                        </button>
                        <?php endwhile; ?>
                    </div>
                </div>
                
                <!-- Quiz Game -->
                <div id="quiz-container" class="hidden">
                    <!-- Progress and Timer -->
                    <div class="flex justify-between items-center mb-6">
                        <div class="text-white">
                            Question <span id="current-question">1</span> of <span id="total-questions">10</span>
                        </div>
                        <div class="text-white font-bold">
                            Time: <span id="timer">60</span>s
                        </div>
                    </div>
                    
                    <!-- Question -->
                    <div class="mb-6">
                        <h3 id="question-text" class="text-xl text-white mb-4">Question text goes here?</h3>
                        
                        <!-- Answer Options -->
                        <div id="answer-options" class="space-y-3">
                            <!-- Answers will be populated by JavaScript -->
                        </div>
                    </div>
                    
                    <!-- Navigation Buttons -->
                    <div class="flex justify-between mt-8">
                        <button id="prev-btn" class="bg-gray-700 text-white py-2 px-6 rounded-lg hover:bg-gray-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            Previous
                        </button>
                        <button id="next-btn" class="bg-blue-600 text-white py-2 px-6 rounded-lg hover:bg-blue-500 transition-colors">
                            Next
                        </button>
                    </div>
                </div>
                
                <!-- Quiz Results -->
                <div id="results-container" class="hidden text-center">
                    <h2 class="text-2xl font-bold text-white mb-4">Quiz Completed!</h2>
                    <div class="text-6xl font-bold text-blue-500 mb-6">
                        <span id="score">0</span>/<span id="max-score">10</span>
                    </div>
                    <p id="result-message" class="text-lg text-white mb-8">
                        Great job! You've completed the quiz.
                    </p>
                    <div class="space-x-4">
                      
                        <button id="play-again-btn" class="bg-blue-600 text-white py-2 px-6 rounded-lg hover:bg-blue-500 transition-colors">
                            Play Again
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include footer -->
    <?php include 'includes/footer.php'; ?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const difficultySelection = document.getElementById('difficulty-selection');
        const categorySelection = document.getElementById('category-selection');
        const quizContainer = document.getElementById('quiz-container');
        const resultsContainer = document.getElementById('results-container');
        
        let currentQuestions = [];
        let currentQuestionIndex = 0;
        let score = 0;
        let timeLeft = 60;
        let timer;
        let selectedDifficulty = '';
        let selectedCategory = 0;
        
        // Event listeners for difficulty buttons
        document.querySelectorAll('.difficulty-btn').forEach(button => {
            button.addEventListener('click', function() {
                selectedDifficulty = this.dataset.difficulty;
                difficultySelection.classList.add('hidden');
                categorySelection.classList.remove('hidden');
            });
        });
        
        // Event listeners for category buttons
        document.querySelectorAll('.category-btn').forEach(button => {
            button.addEventListener('click', function() {
                selectedCategory = this.dataset.category;
                categorySelection.classList.add('hidden');
                loadQuestions();
            });
        });
        
        // Fetch questions from the server
        async function loadQuestions() {
            try {
                const response = await fetch(`get_quiz_questions.php?difficulty=${selectedDifficulty}&category=${selectedCategory}`);
                const data = await response.json();
                
                if (data.success && data.questions.length > 0) {
                    currentQuestions = data.questions;
                    startQuiz();
                } else {
                    alert('Failed to load questions. Please try again.');
                    difficultySelection.classList.remove('hidden');
                    categorySelection.classList.add('hidden');
                }
            } catch (error) {
                console.error('Error fetching questions:', error);
                alert('Failed to load questions. Please try again.');
                difficultySelection.classList.remove('hidden');
                categorySelection.classList.add('hidden');
            }
        }
        
        function startQuiz() {
            currentQuestionIndex = 0;
            score = 0;
            timeLeft = 60;
            
            // Reset any previous user answers
            currentQuestions.forEach(q => {
                q.userAnswer = null;
            });
            
            // Update total questions display
            document.getElementById('total-questions').textContent = currentQuestions.length;
            
            // Show quiz container
            quizContainer.classList.remove('hidden');
            
            // Load first question
            loadQuestion();
            
            // Start timer
            startTimer();
        }
        
        function loadQuestion() {
            const question = currentQuestions[currentQuestionIndex];
            
            // Update question number
            document.getElementById('current-question').textContent = currentQuestionIndex + 1;
            
            // Set question text
            document.getElementById('question-text').textContent = question.question_text;
            
            // Clear previous answer options
            const answerOptions = document.getElementById('answer-options');
            answerOptions.innerHTML = '';
            
            // Add answer options
            question.answers.forEach((answer, index) => {
                const button = document.createElement('button');
                button.className = 'w-full text-left bg-gray-800 hover:bg-gray-700 text-white px-4 py-3 rounded-lg transition-colors';
                button.textContent = answer.answer_text;
                button.dataset.index = index;
                
                // If user already answered this question, highlight their selection
                if (question.userAnswer !== null && question.userAnswer === index) {
                    button.classList.remove('bg-gray-800', 'hover:bg-gray-700');
                    button.classList.add('bg-blue-600', 'hover:bg-blue-500');
                }
                
                button.addEventListener('click', () => selectAnswer(index));
                answerOptions.appendChild(button);
            });
            
            // Update prev/next button states
            document.getElementById('prev-btn').disabled = currentQuestionIndex === 0;
        }
        
        function selectAnswer(index) {
            // Update button styling
            document.querySelectorAll('#answer-options button').forEach(button => {
                button.classList.remove('bg-blue-600', 'hover:bg-blue-500');
                button.classList.add('bg-gray-800', 'hover:bg-gray-700');
                
                if (parseInt(button.dataset.index) === index) {
                    button.classList.remove('bg-gray-800', 'hover:bg-gray-700');
                    button.classList.add('bg-blue-600', 'hover:bg-blue-500');
                }
            });
            
            // Store user's answer
            currentQuestions[currentQuestionIndex].userAnswer = index;
            
            // Check if it's correct
            if (currentQuestions[currentQuestionIndex].answers[index].is_correct) {
                score++;
            }
        }
        
        // Add event listeners for navigation buttons
        document.getElementById('next-btn').addEventListener('click', () => {
            currentQuestionIndex++;
            
            if (currentQuestionIndex < currentQuestions.length) {
                loadQuestion();
            } else {
                endQuiz();
            }
        });
        
        document.getElementById('prev-btn').addEventListener('click', () => {
            if (currentQuestionIndex > 0) {
                currentQuestionIndex--;
                loadQuestion();
            }
        });
        
        function startTimer() {
            clearInterval(timer);
            document.getElementById('timer').textContent = timeLeft;
            
            timer = setInterval(() => {
                timeLeft--;
                document.getElementById('timer').textContent = timeLeft;
                
                if (timeLeft <= 0) {
                    clearInterval(timer);
                    endQuiz();
                }
            }, 1000);
        }
        
        function endQuiz() {
            clearInterval(timer);
            
            // Hide quiz, show results
            quizContainer.classList.add('hidden');
            resultsContainer.classList.remove('hidden');
            
            // Update score display
            document.getElementById('score').textContent = score;
            document.getElementById('max-score').textContent = currentQuestions.length;
            
            // Update result message based on score
            const percentage = (score / currentQuestions.length) * 100;
            let message = '';
            
            if (percentage >= 90) {
                message = "Outstanding! You're a football expert!";
            } else if (percentage >= 70) {
                message = "Great job! You know your football well!";
            } else if (percentage >= 50) {
                message = "Good effort! You have decent football knowledge.";
            } else {
                message = "Keep learning! Try again to improve your score.";
            }
            
            document.getElementById('result-message').textContent = message;
            
            // Save results if user is logged in
            saveQuizResults();
        }
        
        async function saveQuizResults() {
            try {
                const response = await fetch('save_quiz_results.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        difficulty: selectedDifficulty,
                        category: selectedCategory,
                        score: score,
                        total: currentQuestions.length
                    })
                });
                
                const data = await response.json();
                console.log('Quiz results saved:', data);
            } catch (error) {
                console.error('Error saving quiz results:', error);
            }
        }
        
        // Play again button
        document.getElementById('play-again-btn').addEventListener('click', () => {
            difficultySelection.classList.remove('hidden');
            resultsContainer.classList.add('hidden');
        });
        
        // View answers functionality can be added here
    });
    </script>
</body>
</html> 