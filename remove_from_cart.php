<?php
session_start();
require_once 'includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['cart_id'])) {
    die(json_encode([
        'success' => false,
        'message' => 'Cart ID not provided'
    ]));
}

$cart_id = $data['cart_id'];
$user_id = $_SESSION['user_id'];

// Delete the cart item
$sql = "DELETE FROM cart WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $cart_id, $user_id);
$success = $stmt->execute();

// Get updated cart count
$count_sql = "SELECT SUM(quantity) as count FROM cart WHERE user_id = ?";
$stmt = $conn->prepare($count_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$count_result = $stmt->get_result();
$cart_count = $count_result->fetch_assoc()['count'] ?? 0;

// Remove item from cart
if (isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array_filter($_SESSION['cart'], function($item) use ($cart_id) {
        return $item['id'] !== $cart_id;
    });
}

// Calculate new cart count
$cartCount = array_sum(array_column($_SESSION['cart'], 'quantity'));

echo json_encode([
    'success' => $success,
    'cartCount' => $cart_count,
    'message' => 'Item removed successfully',
    'newCartCount' => $cartCount
]); 