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

// Handle player deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $player_id = intval($_GET['delete']);
    
    // Delete the player
    $stmt = $conn->prepare("DELETE FROM guess_players WHERE id = ?");
    $stmt->bind_param("i", $player_id);
    
    if ($stmt->execute()) {
        $success_message = "Player deleted successfully!";
    } else {
        $error_message = "Error deleting player: " . $conn->error;
    }
}

// Handle player submission/update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_player'])) {
    $name = trim($_POST['name']);
    $team = trim($_POST['team']);
    $position = trim($_POST['position']);
    $nationality = trim($_POST['nationality']);
    $age = intval($_POST['age']);
    $difficulty = trim($_POST['difficulty']);
    $hint1 = trim($_POST['hint1']);
    $hint2 = trim($_POST['hint2']);
    $hint3 = trim($_POST['hint3']);
    $status = $_POST['status'];
    
    // Check if we're editing or adding
    $player_id = isset($_POST['player_id']) ? intval($_POST['player_id']) : 0;
    
    // Handle image upload
    $image_url = '';
    $upload_success = false;
    
    if (!empty($_FILES['player_image']['name'])) {
        $target_dir = "uploads/players/";
        
        // Create directory if it doesn't exist
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $image_extension = pathinfo($_FILES["player_image"]["name"], PATHINFO_EXTENSION);
        $new_file_name = uniqid() . '.' . $image_extension;
        $target_file = $target_dir . $new_file_name;
        
        // Check if file is an actual image
        $check = getimagesize($_FILES["player_image"]["tmp_name"]);
        if ($check !== false) {
            if (move_uploaded_file($_FILES["player_image"]["tmp_name"], $target_file)) {
                $image_url = $target_file;
                $upload_success = true;
            } else {
                $error_message = "Sorry, there was an error uploading your file.";
            }
        } else {
            $error_message = "File is not an image.";
        }
    } elseif ($player_id > 0) {
        // If editing and no new image, keep the existing image
        $stmt = $conn->prepare("SELECT image_url FROM guess_players WHERE id = ?");
        $stmt->bind_param("i", $player_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $image_url = $row['image_url'];
            $upload_success = true;
        }
    }
    
    // Validate inputs
    if (empty($name) || empty($team) || empty($position) || empty($nationality) || $age <= 0) {
        $error_message = "All fields marked with * are required!";
    } elseif (!$upload_success && $player_id == 0) {
        $error_message = "Player image is required for new players!";
    } else {
        try {
            if ($player_id > 0) {
                // Update existing player
                $sql = "UPDATE guess_players SET 
                        name = ?, team = ?, position = ?, nationality = ?, 
                        age = ?, difficulty = ?, image_url = ?, 
                        hint1 = ?, hint2 = ?, hint3 = ?, status = ? 
                        WHERE id = ?";
                
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssissssssi", 
                    $name, $team, $position, $nationality, 
                    $age, $difficulty, $image_url, 
                    $hint1, $hint2, $hint3, $status, $player_id
                );
            } else {
                // Insert new player
                $sql = "INSERT INTO guess_players (
                        name, team, position, nationality, 
                        age, difficulty, image_url, 
                        hint1, hint2, hint3, status) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssissssss", 
                    $name, $team, $position, $nationality, 
                    $age, $difficulty, $image_url, 
                    $hint1, $hint2, $hint3, $status
                );
            }
            
            if ($stmt->execute()) {
                $success_message = $player_id > 0 ? "Player updated successfully!" : "Player added successfully!";
                // Clear form data after successful submission
                if ($player_id == 0) {
                    $_POST = array();
                }
            } else {
                $error_message = "Error saving player: " . $stmt->error;
            }
        } catch (Exception $e) {
            $error_message = "Error saving player: " . $e->getMessage();
        }
    }
}

// Fetch existing players
$players_query = "SELECT * FROM guess_players ORDER BY name ASC";
$players = $conn->query($players_query);

// Fetch player details if editing
$edit_player = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $player_id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM guess_players WHERE id = ?");
    $stmt->bind_param("i", $player_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $edit_player = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Players - Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <?php include 'includes/admin_sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="flex-1 p-8">
            <h1 class="text-2xl font-bold mb-6">Manage "Guess the Player" Game</h1>
            
            <!-- Messages -->
            <?php if ($success_message): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <!-- Add/Edit Player Form -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">
                    <?php echo $edit_player ? 'Edit Player' : 'Add New Player'; ?>
                </h2>
                
                <form method="POST" action="admin_players.php" enctype="multipart/form-data">
                    <?php if ($edit_player): ?>
                        <input type="hidden" name="player_id" value="<?php echo $edit_player['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="name" class="block text-gray-700 font-medium mb-2">Player Name *</label>
                            <input type="text" name="name" id="name" 
                                   class="w-full border border-gray-300 px-3 py-2 rounded-lg focus:outline-none focus:border-blue-500"
                                   value="<?php echo $edit_player ? htmlspecialchars($edit_player['name']) : ''; ?>"
                                   required>
                        </div>
                        
                        <div>
                            <label for="team" class="block text-gray-700 font-medium mb-2">Team/Club *</label>
                            <input type="text" name="team" id="team" 
                                   class="w-full border border-gray-300 px-3 py-2 rounded-lg focus:outline-none focus:border-blue-500"
                                   value="<?php echo $edit_player ? htmlspecialchars($edit_player['team']) : ''; ?>"
                                   required>
                        </div>
                        
                        <div>
                            <label for="position" class="block text-gray-700 font-medium mb-2">Position *</label>
                            <select name="position" id="position" 
                                   class="w-full border border-gray-300 px-3 py-2 rounded-lg focus:outline-none focus:border-blue-500" required>
                                <option value="">Select Position</option>
                                <option value="Goalkeeper" <?php echo ($edit_player && $edit_player['position'] == 'Goalkeeper') ? 'selected' : ''; ?>>Goalkeeper</option>
                                <option value="Defender" <?php echo ($edit_player && $edit_player['position'] == 'Defender') ? 'selected' : ''; ?>>Defender</option>
                                <option value="Midfielder" <?php echo ($edit_player && $edit_player['position'] == 'Midfielder') ? 'selected' : ''; ?>>Midfielder</option>
                                <option value="Forward" <?php echo ($edit_player && $edit_player['position'] == 'Forward') ? 'selected' : ''; ?>>Forward</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="nationality" class="block text-gray-700 font-medium mb-2">Nationality *</label>
                            <input type="text" name="nationality" id="nationality" 
                                   class="w-full border border-gray-300 px-3 py-2 rounded-lg focus:outline-none focus:border-blue-500"
                                   value="<?php echo $edit_player ? htmlspecialchars($edit_player['nationality']) : ''; ?>"
                                   required>
                        </div>
                        
                        <div>
                            <label for="age" class="block text-gray-700 font-medium mb-2">Age *</label>
                            <input type="number" name="age" id="age" min="15" max="50"
                                   class="w-full border border-gray-300 px-3 py-2 rounded-lg focus:outline-none focus:border-blue-500"
                                   value="<?php echo $edit_player ? htmlspecialchars($edit_player['age']) : ''; ?>"
                                   required>
                        </div>
                        
                        <div>
                            <label for="difficulty" class="block text-gray-700 font-medium mb-2">Difficulty Level</label>
                            <select name="difficulty" id="difficulty" 
                                   class="w-full border border-gray-300 px-3 py-2 rounded-lg focus:outline-none focus:border-blue-500">
                                <option value="easy" <?php echo ($edit_player && $edit_player['difficulty'] == 'easy') ? 'selected' : ''; ?>>Easy</option>
                                <option value="medium" <?php echo ($edit_player && $edit_player['difficulty'] == 'medium') ? 'selected' : ''; ?>>Medium</option>
                                <option value="hard" <?php echo ($edit_player && $edit_player['difficulty'] == 'hard') ? 'selected' : ''; ?>>Hard</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-6">
                        <label for="player_image" class="block text-gray-700 font-medium mb-2">
                            Player Image <?php echo $edit_player ? '(Leave empty to keep current image)' : '*'; ?>
                        </label>
                        <input type="file" name="player_image" id="player_image" 
                               class="w-full border border-gray-300 px-3 py-2 rounded-lg focus:outline-none focus:border-blue-500"
                               <?php echo $edit_player ? '' : 'required'; ?>>
                        
                        <?php if ($edit_player && !empty($edit_player['image_url'])): ?>
                            <div class="mt-2">
                                <p class="text-sm text-gray-600 mb-1">Current image:</p>
                                <img src="<?php echo htmlspecialchars($edit_player['image_url']); ?>" alt="Current player image" class="w-32 h-32 object-cover rounded">
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-6">
                        <label class="block text-gray-700 font-medium mb-2">Hints (to progressively reveal)</label>
                        
                        <div class="mb-3">
                            <label for="hint1" class="block text-gray-600 text-sm mb-1">Hint 1 (Hardest)</label>
                            <input type="text" name="hint1" id="hint1" 
                                   placeholder="E.g., 'This player has won a World Cup'"
                                   class="w-full border border-gray-300 px-3 py-2 rounded-lg focus:outline-none focus:border-blue-500"
                                   value="<?php echo $edit_player ? htmlspecialchars($edit_player['hint1']) : ''; ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="hint2" class="block text-gray-600 text-sm mb-1">Hint 2 (Medium)</label>
                            <input type="text" name="hint2" id="hint2" 
                                   placeholder="E.g., 'This player has played for Barcelona'"
                                   class="w-full border border-gray-300 px-3 py-2 rounded-lg focus:outline-none focus:border-blue-500"
                                   value="<?php echo $edit_player ? htmlspecialchars($edit_player['hint2']) : ''; ?>">
                        </div>
                        
                        <div>
                            <label for="hint3" class="block text-gray-600 text-sm mb-1">Hint 3 (Easiest)</label>
                            <input type="text" name="hint3" id="hint3" 
                                   placeholder="E.g., 'This player is from Argentina'"
                                   class="w-full border border-gray-300 px-3 py-2 rounded-lg focus:outline-none focus:border-blue-500"
                                   value="<?php echo $edit_player ? htmlspecialchars($edit_player['hint3']) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="mb-6">
                        <label for="status" class="block text-gray-700 font-medium mb-2">Status</label>
                        <select name="status" id="status" 
                               class="w-full border border-gray-300 px-3 py-2 rounded-lg focus:outline-none focus:border-blue-500">
                            <option value="active" <?php echo ($edit_player && $edit_player['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo ($edit_player && $edit_player['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                    
                    <div class="flex justify-end">
                        <?php if ($edit_player): ?>
                            <a href="admin_players.php" class="bg-gray-300 text-gray-800 px-4 py-2 rounded-lg mr-2 hover:bg-gray-400">
                                Cancel
                            </a>
                        <?php endif; ?>
                        <button type="submit" name="submit_player" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                            <?php echo $edit_player ? 'Update Player' : 'Add Player'; ?>
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Players List -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Players List</h2>
                
                <div class="overflow-x-auto">
                    <?php if ($players->num_rows > 0): ?>
                        <table class="min-w-full bg-white">
                            <thead>
                                <tr>
                                    <th class="py-3 px-4 border-b text-left">Player</th>
                                    <th class="py-3 px-4 border-b text-left">Team</th>
                                    <th class="py-3 px-4 border-b text-left">Position</th>
                                    <th class="py-3 px-4 border-b text-left">Nationality</th>
                                    <th class="py-3 px-4 border-b text-left">Difficulty</th>
                                    <th class="py-3 px-4 border-b text-left">Status</th>
                                    <th class="py-3 px-4 border-b text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($player = $players->fetch_assoc()): ?>
                                    <tr>
                                        <td class="py-3 px-4 border-b">
                                            <div class="flex items-center">
                                                <?php if (!empty($player['image_url'])): ?>
                                                    <img src="<?php echo htmlspecialchars($player['image_url']); ?>" alt="<?php echo htmlspecialchars($player['name']); ?>" class="w-10 h-10 rounded-full object-cover mr-3">
                                                <?php endif; ?>
                                                <span><?php echo htmlspecialchars($player['name']); ?></span>
                                            </div>
                                        </td>
                                        <td class="py-3 px-4 border-b"><?php echo htmlspecialchars($player['team']); ?></td>
                                        <td class="py-3 px-4 border-b"><?php echo htmlspecialchars($player['position']); ?></td>
                                        <td class="py-3 px-4 border-b"><?php echo htmlspecialchars($player['nationality']); ?></td>
                                        <td class="py-3 px-4 border-b capitalize"><?php echo htmlspecialchars($player['difficulty']); ?></td>
                                        <td class="py-3 px-4 border-b">
                                            <span class="px-2 py-1 rounded text-xs <?php echo $player['status'] == 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                                <?php echo ucfirst($player['status']); ?>
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 border-b text-center">
                                            <a href="admin_players.php?edit=<?php echo $player['id']; ?>" class="text-blue-600 hover:text-blue-800 mr-2">Edit</a>
                                            <a href="admin_players.php?delete=<?php echo $player['id']; ?>" class="text-red-600 hover:text-red-800" onclick="return confirm('Are you sure you want to delete this player?')">Delete</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="text-gray-600">No players added yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 