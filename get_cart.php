<?php
session_start();
require_once 'includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

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

$cart_items = [];
$total = 0;

while ($item = $result->fetch_assoc()) {
    if (!$item['image_url']) {
        $item['image_url'] = 'assets/images/default-jersey.jpg';
    }
    
    $item['subtotal'] = $item['price'] * $item['quantity'];
    $total += $item['subtotal'];
    $cart_items[] = $item;
}

echo json_encode([
    'success' => true,
    'items' => $cart_items,
    'total' => $total
]); 