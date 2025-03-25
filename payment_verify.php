<?php
session_start();
require_once 'includes/db.php';
require_once 'vendor/autoload.php'; // For Razorpay

use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

// Check if we have order data in session
if (!isset($_SESSION['order_id']) || !isset($_SESSION['order_total']) || !isset($_SESSION['order_number'])) {
    header('Location: shop.php');
    exit;
}

// Get order details from session
$order_id = $_SESSION['order_id'];
$order_total = $_SESSION['order_total'];
$order_number = $_SESSION['order_number'];
$user_id = $_SESSION['user_id'];

// Get Razorpay payment data from POST
$payment_id = $_POST['razorpay_payment_id'] ?? '';
$razorpay_order_id = $_POST['razorpay_order_id'] ?? '';
$signature = $_POST['razorpay_signature'] ?? '';

if (empty($payment_id) || empty($razorpay_order_id) || empty($signature)) {
    header('Location: payment.php?error=missing_data');
    exit;
}

try {
    // Initialize Razorpay API
    $api = new Api($_ENV['RAZORPAY_KEY_ID'], $_ENV['RAZORPAY_KEY_SECRET']);
    
    // Verify payment signature
    $attributes = [
        'razorpay_payment_id' => $payment_id,
        'razorpay_order_id' => $razorpay_order_id,
        'razorpay_signature' => $signature
    ];
    
    $api->utility->verifyPaymentSignature($attributes);
    
    // Start database transaction
    $conn->begin_transaction();
    
    // Update order with payment information
    $update_sql = "UPDATE jersey_orders SET 
                   razorpay_payment_id = ?, 
                   razorpay_order_id = ?, 
                   razorpay_signature = ?, 
                   payment_status = 'success' 
                   WHERE id = ? AND user_id = ?";
    
    $update_stmt = $conn->prepare($update_sql);
    if ($update_stmt === false) {
        throw new Exception("Failed to prepare update statement: " . $conn->error);
    }
    
    $update_stmt->bind_param("sssii", $payment_id, $razorpay_order_id, $signature, $order_id, $user_id);
    
    if (!$update_stmt->execute()) {
        throw new Exception("Failed to update order: " . $update_stmt->error);
    }
    
    // Get the items in this order
    $items_sql = "SELECT jersey_id, quantity FROM order_items WHERE order_id = ?";
    $items_stmt = $conn->prepare($items_sql);
    $items_stmt->bind_param("i", $order_id);
    $items_stmt->execute();
    $items_result = $items_stmt->get_result();
    
    // Update inventory for each item
    while ($item = $items_result->fetch_assoc()) {
        $jersey_id = $item['jersey_id'];
        $quantity = $item['quantity'];
        
        // Check if the jerseys table has a stock/quantity column
        $check_sql = "SHOW COLUMNS FROM jerseys LIKE 'stock'";
        $check_result = $conn->query($check_sql);
        
        if ($check_result->num_rows > 0) {
            // The stock column exists, update it
            $update_stock_sql = "UPDATE jerseys SET stock = stock - ? WHERE id = ? AND stock >= ?";
            $update_stock_stmt = $conn->prepare($update_stock_sql);
            $update_stock_stmt->bind_param("iii", $quantity, $jersey_id, $quantity);
            
            if (!$update_stock_stmt->execute()) {
                throw new Exception("Failed to update inventory: " . $update_stock_stmt->error);
            }
            
            // Check if any rows were affected (if stock was sufficient)
            if ($update_stock_stmt->affected_rows == 0) {
                // This could happen if stock fell below the ordered quantity
                // Log this but don't fail the transaction
                error_log("Warning: Could not update inventory for jersey ID $jersey_id. Insufficient stock.");
            }
        } else {
            // The stock column doesn't exist, we need to add it first
            $alter_table_sql = "ALTER TABLE jerseys ADD COLUMN stock INT DEFAULT 100 NOT NULL";
            $conn->query($alter_table_sql);
            
            // Now update the newly created stock column
            $update_stock_sql = "UPDATE jerseys SET stock = stock - ? WHERE id = ? AND stock >= ?";
            $update_stock_stmt = $conn->prepare($update_stock_sql);
            $update_stock_stmt->bind_param("iii", $quantity, $jersey_id, $quantity);
            $update_stock_stmt->execute();
        }
    }
    
    // Clear cart after successful payment
    if (isset($_SESSION['cart'])) {
        unset($_SESSION['cart']);
    }
    
    // Commit transaction
    $conn->commit();
    
    // Redirect to success page
    header('Location: payment_success.php?order_id=' . $order_id);
    exit;
    
} catch (SignatureVerificationError $e) {
    // Razorpay signature verification failed
    $conn->rollback();
    error_log("Payment verification failed: " . $e->getMessage());
    header('Location: payment.php?error=verification_failed');
    exit;
    
} catch (Exception $e) {
    // General error
    $conn->rollback();
    error_log("Payment process error: " . $e->getMessage());
    header('Location: payment.php?error=processing_failed');
    exit;
}
?> 