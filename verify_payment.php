<?php
session_start();
require_once 'includes/db.php';

header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Insert order into database
    $sql = "INSERT INTO jersey_orders (
        user_id,
        order_number,
        total_amount,
        shipping_cost,
        razorpay_payment_id,
        razorpay_order_id,
        razorpay_signature,
        payment_status
    ) VALUES (?, ?, ?, ?, ?, ?, ?, 'completed')";
    
    $order_number = 'GS' . time() . rand(100,999);
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isddssss", 
        $_SESSION['user_id'],
        $order_number,
        $data['amount'],
        $data['shipping_cost'],
        $data['payment_id'],
        $data['order_id'],
        $data['signature']
    );
    
    if ($stmt->execute()) {
        // Clear cart after successful order
        $clear_cart = "DELETE FROM cart WHERE user_id = ?";
        $cart_stmt = $conn->prepare($clear_cart);
        $cart_stmt->bind_param("i", $_SESSION['user_id']);
        $cart_stmt->execute();
        
        echo json_encode([
            'success' => true,
            'order_id' => $conn->insert_id
        ]);
    } else {
        throw new Exception("Failed to save order");
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 