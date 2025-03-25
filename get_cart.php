<?php
session_start();
require_once 'includes/db.php';

header('Content-Type: application/json');

// Initialize cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$cart_items = [];
$total = 0;
$total_count = 0;

// If user is logged in, get items from database
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    
    $sql = "SELECT c.*, j.name, j.price, 
            (SELECT image_url FROM jersey_images 
             WHERE jersey_id = j.id 
             ORDER BY is_primary DESC 
             LIMIT 1) as image_url 
            FROM cart c 
            JOIN jerseys j ON c.jersey_id = j.id 
            WHERE c.user_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($item = $result->fetch_assoc()) {
        if (!$item['image_url']) {
            $item['image_url'] = 'assets/images/default-jersey.jpg';
        }
        
        $item['subtotal'] = $item['price'] * $item['quantity'];
        $total += $item['subtotal'];
        $total_count += $item['quantity'];
        $cart_items[] = $item;
    }
} else {
    // Use session cart for non-logged in users
    foreach ($_SESSION['cart'] as $item) {
        $total += $item['price'] * $item['quantity'];
        $total_count += $item['quantity'];
        $cart_items[] = $item;
    }
}

// Return single JSON response
echo json_encode([
    'success' => true,
    'items' => $cart_items,
    'total' => $total,
    'cartCount' => $total_count
]);
?> 