<?php
session_start();
require_once 'includes/language.php';
require_once 'includes/db.php';

// Check if a specific player is requested or get a random one
$player_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$difficulty = isset($_GET['difficulty']) ? $_GET['difficulty'] : '';

// Build query to get a player
if ($player_id > 0) {
    // Get specific player
    $query = "SELECT * FROM guess_players WHERE id = ? AND status = 'active'";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $player_id);
} else {
    // Get random player based on difficulty
    $query = "SELECT * FROM guess_players WHERE status = 'active'";
    if (in_array($difficulty, ['easy', 'medium', 'hard'])) {
        $query .= " AND difficulty = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $difficulty);
    } else {
        $query .= " ORDER BY RAND() LIMIT 1";
        $stmt = $conn->prepare($query);
    }
}

$stmt->execute();
$result = $stmt->get_result();
$player = $result->fetch_assoc();

// If no player found, show error
if (!$player) {
    $error = true;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guess the Player - GoalSphere</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="css/styles.css" rel="stylesheet">
    <style>
        .blurred-image {
            filter: blur(30px);
            transition: filter 1s ease-in-out;
        }
        .partially-blurred {
            filter: blur(15px);
        }
        .slightly-blurred {
            filter: blur(5px);
        }
        .clear-image {
            filter: blur(0);
        }
    </style>
</head>
<body class="bg-black min-h-screen">
    <!-- Include header -->
    <?php include 'includes/header.php'; ?>

    <div class="container mx-auto px-4 py-12">
        <div class="max-w-3xl mx-auto">
            <h1 class="text-3xl font-bold text-white mb-6 text-center">Guess the Player</h1>
            
            <?php if (isset($error)): ?>
                <div class="bg-red-600 text-white p-6 rounded-lg text-center mb-8">
                    <p class="text-xl">No player available. Please try again later.</p>
                    <a href="gamezone.php" class="inline-block mt-4 bg-white text-red-600 px-6 py-2 rounded-lg font-semibold">Return to Game Zone</a>
                </div>
            <?php else: ?>
                <!-- Game Container -->
                <div class="bg-gray-900 rounded-lg overflow-hidden shadow-xl">
                    <!-- Image Section -->
                    <div class="relative">
                        <img id="playerImage" src="<?php echo htmlspecialchars($player['image_url']); ?>" alt="Football Player" class="w-full h-auto blurred-image">
                        
                        <!-- Overlay for Success -->
                        <div id="successOverlay" class="absolute inset-0 bg-green-600 bg-opacity-80 flex items-center justify-center hidden">
                            <div class="text-center p-6">
                                <svg class="w-16 h-16 text-white mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <h2 class="text-2xl font-bold text-white mb-2">Correct!</h2>
                                <p class="text-white text-xl mb-4" id="correctPlayerName"></p>
                                <button onclick="playAgain()" class="bg-white text-green-700 px-6 py-2 rounded-lg font-semibold hover:bg-green-100">
                                    Play Again
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Game Content -->
                    <div class="p-6">
                        <div class="mb-6">
                            <h2 class="text-xl font-semibold text-white mb-4">Guess who this player is:</h2>
                            
                            <!-- Hints Section -->
                            <div class="mb-4">
                                <div id="hint1" class="bg-gray-800 p-3 rounded-lg mb-2 hidden">
                                    <p class="text-gray-300"><span class="text-blue-400 font-semibold">Hint 1:</span> <span id="hint1Text"><?php echo htmlspecialchars($player['hint1']); ?></span></p>
                                </div>
                                <div id="hint2" class="bg-gray-800 p-3 rounded-lg mb-2 hidden">
                                    <p class="text-gray-300"><span class="text-blue-400 font-semibold">Hint 2:</span> <span id="hint2Text"><?php echo htmlspecialchars($player['hint2']); ?></span></p>
                                </div>
                                <div id="hint3" class="bg-gray-800 p-3 rounded-lg hidden">
                                    <p class="text-gray-300"><span class="text-blue-400 font-semibold">Hint 3:</span> <span id="hint3Text"><?php echo htmlspecialchars($player['hint3']); ?></span></p>
                                </div>
                            </div>
                            
                            <!-- Guess Input -->
                            <div class="mb-6">
                                <div class="flex">
                                    <input type="text" id="playerGuess" placeholder="Enter player name..." 
                                           class="flex-1 bg-gray-800 text-white px-4 py-3 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <button id="submitGuess" class="bg-blue-600 text-white px-6 py-3 rounded-r-lg hover:bg-blue-700">
                                        Submit
                                    </button>
                                </div>
                                <p id="feedbackMessage" class="mt-2 text-gray-400 hidden"></p>
                            </div>
                            
                            <!-- Game Controls -->
                            <div class="flex space-x-4">
                                <button id="showHint" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 flex-1">
                                    Show Hint
                                </button>
                                <button id="revealImage" class="bg-gray-700 text-white px-4 py-2 rounded-lg hover:bg-gray-600 flex-1">
                                    Gradually Reveal Image
                                </button>
                            </div>
                        </div>
                        
                        <!-- Attempts Tracker -->
                        <div class="flex justify-between items-center text-gray-400 text-sm">
                            <div>
                                Attempts: <span id="attempts">0</span>
                            </div>
                            <button id="giveUpBtn" class="text-red-400 hover:text-red-300">
                                Give Up
                            </button>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Include footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Hidden field with correct answer -->
    <input type="hidden" id="correctAnswer" value="<?php echo htmlspecialchars($player['name']); ?>">
    <input type="hidden" id="playerId" value="<?php echo $player['id']; ?>">

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Elements
        const playerImage = document.getElementById('playerImage');
        const playerGuess = document.getElementById('playerGuess');
        const submitGuess = document.getElementById('submitGuess');
        const feedbackMessage = document.getElementById('feedbackMessage');
        const showHintBtn = document.getElementById('showHint');
        const revealImageBtn = document.getElementById('revealImage');
        const attemptsElement = document.getElementById('attempts');
        const giveUpBtn = document.getElementById('giveUpBtn');
        const successOverlay = document.getElementById('successOverlay');
        const correctPlayerName = document.getElementById('correctPlayerName');
        
        // Hints elements
        const hint1 = document.getElementById('hint1');
        const hint2 = document.getElementById('hint2');
        const hint3 = document.getElementById('hint3');
        
        // Game variables
        const correctAnswer = document.getElementById('correctAnswer').value.toLowerCase();
        const playerId = document.getElementById('playerId').value;
        let attempts = 0;
        let hintsRevealed = 0;
        let imageLevel = 0;
        let gameOver = false;
        let startTime = Date.now();
        
        // Submit guess on button click
        submitGuess.addEventListener('click', checkGuess);
        
        // Submit guess on Enter key
        playerGuess.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                checkGuess();
            }
        });
        
        // Show hint button
        showHintBtn.addEventListener('click', function() {
            if (gameOver) return;
            
            if (hintsRevealed < 3) {
                hintsRevealed++;
                
                if (hintsRevealed === 1) {
                    hint1.classList.remove('hidden');
                } else if (hintsRevealed === 2) {
                    hint2.classList.remove('hidden');
                } else if (hintsRevealed === 3) {
                    hint3.classList.remove('hidden');
                    showHintBtn.setAttribute('disabled', 'true');
                    showHintBtn.classList.add('opacity-50', 'cursor-not-allowed');
                }
            }
        });
        
        // Reveal image button
        revealImageBtn.addEventListener('click', function() {
            if (gameOver) return;
            
            imageLevel++;
            
            if (imageLevel === 1) {
                playerImage.classList.remove('blurred-image');
                playerImage.classList.add('partially-blurred');
            } else if (imageLevel === 2) {
                playerImage.classList.remove('partially-blurred');
                playerImage.classList.add('slightly-blurred');
            } else if (imageLevel === 3) {
                playerImage.classList.remove('slightly-blurred');
                playerImage.classList.add('clear-image');
                revealImageBtn.setAttribute('disabled', 'true');
                revealImageBtn.classList.add('opacity-50', 'cursor-not-allowed');
            }
        });
        
        // Give up button
        giveUpBtn.addEventListener('click', function() {
            if (gameOver) return;
            
            // Reveal the answer
            playerImage.classList.remove('blurred-image', 'partially-blurred', 'slightly-blurred');
            playerImage.classList.add('clear-image');
            
            feedbackMessage.textContent = `The player was ${correctAnswer.toUpperCase()}.`;
            feedbackMessage.classList.remove('hidden', 'text-green-500');
            feedbackMessage.classList.add('text-red-500');
            
            // Disable inputs
            playerGuess.setAttribute('disabled', 'true');
            submitGuess.setAttribute('disabled', 'true');
            showHintBtn.setAttribute('disabled', 'true');
            revealImageBtn.setAttribute('disabled', 'true');
            giveUpBtn.setAttribute('disabled', 'true');
            
            // Show all hints
            hint1.classList.remove('hidden');
            hint2.classList.remove('hidden');
            hint3.classList.remove('hidden');
            
            gameOver = true;
            
            // Save the game result as incorrect
            saveGameResult(false);
        });
        
        function checkGuess() {
            if (gameOver) return;
            
            let guess = playerGuess.value.trim().toLowerCase();
            
            if (!guess) {
                feedbackMessage.textContent = "Please enter a player name.";
                feedbackMessage.classList.remove('hidden', 'text-green-500', 'text-red-500');
                feedbackMessage.classList.add('text-yellow-500');
                return;
            }
            
            attempts++;
            attemptsElement.textContent = attempts;
            
            // Check if the guess is correct
            if (guess === correctAnswer.toLowerCase()) {
                // Correct guess
                successOverlay.classList.remove('hidden');
                correctPlayerName.textContent = correctAnswer;
                
                playerImage.classList.remove('blurred-image', 'partially-blurred', 'slightly-blurred');
                playerImage.classList.add('clear-image');
                
                gameOver = true;
                
                // Save the game result as correct
                saveGameResult(true);
            } else {
                // Wrong guess
                feedbackMessage.textContent = "Sorry, that's not correct. Try again!";
                feedbackMessage.classList.remove('hidden', 'text-green-500');
                feedbackMessage.classList.add('text-red-500');
                
                playerGuess.value = ''; // Clear input for next guess
                playerGuess.focus();
                
                // If attempts reach a threshold, reveal a hint automatically
                if (attempts === 3 && hintsRevealed < 1) {
                    hint1.classList.remove('hidden');
                    hintsRevealed = 1;
                } else if (attempts === 6 && hintsRevealed < 2) {
                    hint2.classList.remove('hidden');
                    hintsRevealed = 2;
                } else if (attempts === 9 && hintsRevealed < 3) {
                    hint3.classList.remove('hidden');
                    hintsRevealed = 3;
                    showHintBtn.setAttribute('disabled', 'true');
                    showHintBtn.classList.add('opacity-50', 'cursor-not-allowed');
                }
            }
        }
        
        function saveGameResult(correct) {
            const timeTaken = Math.floor((Date.now() - startTime) / 1000);
            
            // Send result to server
            fetch('save_guess_result.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    player_id: playerId,
                    attempts: attempts,
                    correct: correct ? 1 : 0,
                    time_taken: timeTaken,
                    revealed_hints: hintsRevealed,
                    image_level: imageLevel
                })
            })
            .then(response => response.json())
            .then(data => {
                console.log('Game result saved:', data);
            })
            .catch(error => {
                console.error('Error saving game result:', error);
            });
        }
    });
    
    function playAgain() {
        // Redirect to new game with random player
        window.location.href = 'guess_player.php';
    }
    </script>
</body>
</html> 