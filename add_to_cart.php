<?php
session_start();
require_once 'includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

// Get the POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$user_id = $_SESSION['user_id'];
$jersey_id = intval($data['jersey_id']);
$size = $conn->real_escape_string($data['size']);
$quantity = intval($data['quantity']);
$price = floatval($data['price']);

// Check if item already exists in cart
$check_sql = "SELECT id, quantity FROM cart 
              WHERE user_id = ? AND jersey_id = ? AND size = ?";
$stmt = $conn->prepare($check_sql);
$stmt->bind_param("iis", $user_id, $jersey_id, $size);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Update existing cart item
    $cart_item = $result->fetch_assoc();
    $new_quantity = $cart_item['quantity'] + $quantity;
    
    $update_sql = "UPDATE cart SET quantity = ? WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ii", $new_quantity, $cart_item['id']);
    $success = $stmt->execute();
} else {
    // Insert new cart item
    $insert_sql = "INSERT INTO cart (user_id, jersey_id, size, quantity, price) 
                   VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_sql);
    $stmt->bind_param("iisid", $user_id, $jersey_id, $size, $quantity, $price);
    $success = $stmt->execute();
}

// Get updated cart count
$count_sql = "SELECT SUM(quantity) as count FROM cart WHERE user_id = ?";
$stmt = $conn->prepare($count_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$count_result = $stmt->get_result();
$cart_count = $count_result->fetch_assoc()['count'] ?? 0;

echo json_encode([
    'success' => $success,
    'cartCount' => $cart_count,
    'message' => $success ? 'Added to cart successfully' : 'Failed to add to cart'
]);
?> 