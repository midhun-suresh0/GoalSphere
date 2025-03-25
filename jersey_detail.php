<?php
session_start();
require_once 'includes/db.php';

// Get jersey ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: shop.php');
    exit;
}

$jersey_id = $_GET['id'];

// Fetch jersey details
$stmt = $conn->prepare("SELECT * FROM jerseys WHERE id = ?");
$stmt->bind_param("i", $jersey_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: shop.php');
    exit;
}

$jersey = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($jersey['name']); ?> - GoalSphere</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <?php include 'header.php'; ?>
    
    <div class="max-w-7xl mx-auto px-4 py-8">
        <div class="mb-8">
            <a href="shop.php" class="text-blue-600 hover:text-blue-800">← Back to Shop</a>
        </div>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6">
                <?php 
                echo $_SESSION['success']; 
                unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6">
                <?php 
                echo $_SESSION['error']; 
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>
        
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="md:flex">
                <!-- Jersey Image -->
                <div class="md:w-1/2">
                    <?php if (!empty($jersey['image'])): ?>
                        <img class="w-full h-auto object-cover" src="<?php echo htmlspecialchars($jersey['image']); ?>" alt="<?php echo htmlspecialchars($jersey['name']); ?>">
                    <?php else: ?>
                        <div class="bg-gray-200 w-full h-96 flex items-center justify-center">
                            <span class="text-gray-500">No Image Available</span>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Jersey Details -->
                <div class="md:w-1/2 p-8">
                    <h1 class="text-2xl font-bold mb-2"><?php echo htmlspecialchars($jersey['name']); ?></h1>
                    <p class="text-xl text-gray-900 mb-4">₹<?php echo number_format($jersey['price'], 2); ?></p>
                    
                    <?php if (isset($jersey['description']) && !empty($jersey['description'])): ?>
                        <div class="mb-6">
                            <h2 class="text-lg font-semibold mb-2">Description</h2>
                            <p class="text-gray-700"><?php echo nl2br(htmlspecialchars($jersey['description'])); ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Simple Add to Cart Form -->
                    <div class="border-t pt-6">
                        <h2 class="text-lg font-semibold mb-4">Add to Cart</h2>
                        
                        <form action="add_to_cart.php" method="POST">
                            <input type="hidden" name="jersey_id" value="<?php echo $jersey['id']; ?>">
                            
                            <div class="mb-4">
                                <label for="size" class="block text-sm font-medium text-gray-700 mb-1">Size</label>
                                <select id="size" name="size" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50" required>
                                    <option value="">Select Size</option>
                                    <option value="S">Small (S)</option>
                                    <option value="M">Medium (M)</option>
                                    <option value="L">Large (L)</option>
                                    <option value="XL">Extra Large (XL)</option>
                                    <option value="XXL">Double XL (XXL)</option>
                                </select>
                            </div>
                            
                            <div class="mb-6">
                                <label for="quantity" class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                                <input type="number" id="quantity" name="quantity" min="1" value="1" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50" required>
                            </div>
                            
                            <div class="flex space-x-4">
                                <button type="submit" class="flex-1 bg-blue-600 text-white py-3 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                    Add to Cart
                                </button>
                                
                                <button type="submit" name="redirect_to_cart" value="1" class="flex-1 bg-gray-800 text-white py-3 px-4 rounded-md hover:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                                    Buy Now
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>