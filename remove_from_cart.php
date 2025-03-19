<?php
session_start();
require_once 'includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$cart_id = intval($data['cart_id']);
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

echo json_encode([
    'success' => $success,
    'cartCount' => $cart_count
]); 