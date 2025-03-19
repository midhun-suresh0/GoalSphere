<?php
session_start();
require_once 'includes/db.php';
require 'vendor/autoload.php';
use Razorpay\Api\Api;

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

try {
    // Get input data
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        throw new Exception("Invalid input data");
    }

    // Validate the amount
    if (!isset($data['amount']) || $data['amount'] <= 0) {
        throw new Exception("Invalid amount");
    }

    // Generate unique order number
    $order_number = 'GS' . time() . rand(100, 999);
    
    // Initialize Razorpay API
    $api = new Api('rzp_test_PXCkaH2uhlAUBp', 'Yr0t8sy7WRicOGd28jjoW9Xq');

    // Create order data
    $orderData = [
        'receipt' => $order_number,
        'amount' => intval($data['amount'] * 100), // amount in paisa
        'currency' => 'INR',
        'payment_capture' => 1
    ];
    
    // Create order with Razorpay
    $razorpayOrder = $api->order->create($orderData);
    
    if (!$razorpayOrder || !isset($razorpayOrder->id)) {
        throw new Exception("Failed to create Razorpay order");
    }

    // Insert order into your database (optional)
    $sql = "INSERT INTO jersey_orders (user_id, order_number, total_amount, razorpay_order_id) 
            VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isds", $_SESSION['user_id'], $order_number, $data['amount'], $razorpayOrder->id);
    $stmt->execute();

    // Return success response with Razorpay order ID
    echo json_encode([
        'success' => true,
        'order_id' => $razorpayOrder->id,
        'amount' => $data['amount']
    ]);

} catch (Exception $e) {
    error_log("Payment Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Payment initialization failed: ' . $e->getMessage()
    ]);
}
