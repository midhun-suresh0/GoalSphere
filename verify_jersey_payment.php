<?php
session_start();
require_once 'includes/db.php';
require 'vendor/autoload.php';
use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Update order status in jersey_orders table
    $sql = "UPDATE jersey_orders SET 
            payment_status = 'completed',
            razorpay_payment_id = ?,
            razorpay_signature = ?
            WHERE id = ? AND razorpay_order_id = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", 
        $data['razorpay_payment_id'],
        $data['razorpay_signature'],
        $data['order_id'],
        $data['razorpay_order_id']
    );
    
    if ($stmt->execute()) {
        // Clear the cart after successful payment
        $clearCart = "DELETE FROM cart WHERE user_id = ?";
        $cartStmt = $conn->prepare($clearCart);
        $cartStmt->bind_param("i", $_SESSION['user_id']);
        $cartStmt->execute();
        
        echo json_encode(['success' => true]);
    } else {
        throw new Exception("Failed to update order status");
    }
    
} catch (Exception $e) {
    error_log("Payment Verification Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Payment verification failed. Please contact support.'
    ]);
} 