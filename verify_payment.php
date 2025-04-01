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
        // Start transaction to ensure data consistency
        $conn->begin_transaction();
        
        try {
            // Get order details to retrieve the order ID
            $order_query = "SELECT id FROM jersey_orders WHERE razorpay_order_id = ?";
            $order_stmt = $conn->prepare($order_query);
            $order_stmt->bind_param("s", $data['razorpay_order_id']);
            $order_stmt->execute();
            $order_result = $order_stmt->get_result();
            
            if ($order_result->num_rows === 0) {
                throw new Exception("Order not found");
            }
            
            $order = $order_result->fetch_assoc();
            $order_id = $order['id'];
            
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
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to update order status");
            }
            
            // Get cart items to update jersey quantities
            $cart_sql = "SELECT jersey_id, size, quantity FROM cart WHERE user_id = ?";
            $cart_stmt = $conn->prepare($cart_sql);
            $cart_stmt->bind_param("i", $_SESSION['user_id']);
            $cart_stmt->execute();
            $cart_result = $cart_stmt->get_result();
            
            while ($item = $cart_result->fetch_assoc()) {
                // Update jersey_sizes quantity
                $update_sql = "UPDATE jersey_sizes 
                              SET quantity = GREATEST(0, quantity - ?) 
                              WHERE jersey_id = ? AND size = ?";
                
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("iis", 
                    $item['quantity'], 
                    $item['jersey_id'], 
                    $item['size']
                );
                
                if (!$update_stmt->execute()) {
                    throw new Exception("Failed to update stock for jersey ID: " . $item['jersey_id']);
                }
                
                // Check if jersey is out of stock
                $check_sql = "SELECT SUM(quantity) as total FROM jersey_sizes WHERE jersey_id = ?";
                $check_stmt = $conn->prepare($check_sql);
                $check_stmt->bind_param("i", $item['jersey_id']);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                $total_stock = $check_result->fetch_assoc()['total'];
                
                // Update jersey status if out of stock
                if ($total_stock <= 0) {
                    $update_jersey = "UPDATE jerseys 
                                     SET status = 'sold_out', is_available = 0 
                                     WHERE id = ?";
                    
                    $jersey_stmt = $conn->prepare($update_jersey);
                    $jersey_stmt->bind_param("i", $item['jersey_id']);
                    $jersey_stmt->execute();
                }
            }
            
            // Clear cart after successful payment
            $clear_cart = "DELETE FROM cart WHERE user_id = ?";
            $clear_stmt = $conn->prepare($clear_cart);
            $clear_stmt->bind_param("i", $_SESSION['user_id']);
            $clear_stmt->execute();
            
            // Commit the transaction
            $conn->commit();
            
            echo json_encode([
                'success' => true,
                'message' => 'Payment successful and stock updated',
                'redirect' => 'orders.php'
            ]);
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            throw $e;
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