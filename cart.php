<?php
session_start();
require_once 'includes/db.php';

// Initialize cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle removing items from cart
if (isset($_GET['remove']) && isset($_SESSION['cart'][$_GET['remove']])) {
    unset($_SESSION['cart'][$_GET['remove']]);
    $_SESSION['success'] = "Item removed from cart";
    header('Location: cart.php');
    exit;
}

// Handle updating quantities
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    foreach ($_POST['quantity'] as $item_key => $quantity) {
        if (isset($_SESSION['cart'][$item_key])) {
            $quantity = max(1, intval($quantity)); // Ensure quantity is at least 1
            $_SESSION['cart'][$item_key]['quantity'] = $quantity;
        }
    }
    $_SESSION['success'] = "Cart updated successfully";
    header('Location: cart.php');
    exit;
}

// Fetch cart items from database
$cart_items = [];
$total = 0;

foreach ($_SESSION['cart'] as $item_key => $item) {
    $jersey_id = $item['jersey_id'];
    
    $stmt = $conn->prepare("SELECT id, name, price, image FROM jerseys WHERE id = ?");
    $stmt->bind_param("i", $jersey_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($jersey = $result->fetch_assoc()) {
        $subtotal = $jersey['price'] * $item['quantity'];
        
        $cart_items[$item_key] = [
            'id' => $jersey['id'],
            'name' => $jersey['name'],
            'image' => $jersey['image'],
            'price' => $jersey['price'],
            'size' => $item['size'],
            'quantity' => $item['quantity'],
            'subtotal' => $subtotal
        ];
        
        $total += $subtotal;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart - GoalSphere</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <?php include 'header.php'; ?>
    
    <div class="max-w-7xl mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-2xl font-bold">Your Cart</h1>
            <a href="shop.php" class="text-blue-600 hover:text-blue-800">Continue Shopping</a>
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
        
        <?php if (empty($cart_items)): ?>
            <div class="bg-white rounded-lg p-8 text-center shadow">
                <p class="text-gray-500 mb-4">Your cart is empty.</p>
                <a href="shop.php" class="inline-block bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                    Start Shopping
                </a>
            </div>
        <?php else: ?>
            <form method="POST" action="cart.php">
                <div class="bg-white rounded-lg shadow mb-6 overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Product
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Size
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Price
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Quantity
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Subtotal
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($cart_items as $item_key => $item): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-16 w-16">
                                                <?php if (!empty($item['image'])): ?>
                                                    <img class="h-16 w-16 rounded object-cover" src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                                <?php else: ?>
                                                    <div class="h-16 w-16 rounded bg-gray-200"></div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($item['name']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900"><?php echo htmlspecialchars($item['size']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">₹<?php echo number_format($item['price'], 2); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="number" name="quantity[<?php echo $item_key; ?>]" value="<?php echo $item['quantity']; ?>" min="1" class="w-20 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        ₹<?php echo number_format($item['subtotal'], 2); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="cart.php?remove=<?php echo $item_key; ?>" class="text-red-600 hover:text-red-900">Remove</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="flex justify-between mb-6">
                    <button type="submit" name="update_cart" value="1" class="bg-gray-200 text-gray-800 py-2 px-4 rounded hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                        Update Cart
                    </button>
                    
                    <div class="text-right">
                        <div class="text-lg font-medium">Total: ₹<?php echo number_format($total, 2); ?></div>
                        <p class="text-sm text-gray-500">Shipping calculated at checkout</p>
                    </div>
                </div>
                
                <div class="flex justify-end">
                    <a href="checkout.php" class="bg-blue-600 text-white py-2 px-6 rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Proceed to Checkout
                    </a>
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>