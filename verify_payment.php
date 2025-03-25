<?php
session_start();
require_once 'includes/db.php';
require 'vendor/autoload.php';
use Razorpay\Api\Api;

header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Log received data for debugging
    error_log('Payment verification data received: ' . print_r($data, true));
    
    // Verify the payment signature
    $api = new Api('rzp_test_PXCkaH2uhlAUBp', 'Yr0t8sy7WRicOGd28jjoW9Xq'); // Replace with your actual secret key
    
    $attributes = array(
        'razorpay_order_id' => $data['razorpay_order_id'],
        'razorpay_payment_id' => $data['razorpay_payment_id'],
        'razorpay_signature' => $data['razorpay_signature']
    );
    
    try {
        $api->utility->verifyPaymentSignature($attributes);
        $payment_verified = true;
    } catch (Exception $e) {
        error_log('Signature verification failed: ' . $e->getMessage());
        $payment_verified = false;
    }
    
    if ($payment_verified) {
        // Update existing order in database
        $sql = "UPDATE jersey_orders 
                SET payment_status = 'success',
                    razorpay_payment_id = ?,
                    razorpay_signature = ?
                WHERE razorpay_order_id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", 
            $data['razorpay_payment_id'],
            $data['razorpay_signature'],
            $data['razorpay_order_id']
        );
        
        if ($stmt->execute()) {
            // Clear cart after successful payment
            $clear_cart = "DELETE FROM cart WHERE user_id = ?";
            $cart_stmt = $conn->prepare($clear_cart);
            $cart_stmt->bind_param("i", $_SESSION['user_id']);
            $cart_stmt->execute();
            
            echo json_encode([
                'success' => true,
                'message' => 'Payment successful',
                'redirect' => 'orders.php'
            ]);
        } else {
            throw new Exception("Failed to update order status");
        }
    } else {
        throw new Exception("Payment signature verification failed");
    }
    
} catch (Exception $e) {
    error_log('Payment verification error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Payment verification failed: ' . $e->getMessage()
    ]);
} 