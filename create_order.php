<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 0); // Turn off HTML error display
header('Content-Type: application/json');

use Razorpay\Api\Api;

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

try {
    require_once 'includes/db.php';
    
    // First check if vendor/autoload.php exists
    if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
        throw new Exception('Razorpay SDK not found. Please install using composer.');
    }
    
    require __DIR__ . '/vendor/autoload.php';
    
    // Check if Razorpay class exists
    if (!class_exists('Razorpay\Api\Api')) {
        throw new Exception('Razorpay class not found. Please reinstall the SDK.');
    }
    
    // Get input data
    $input = file_get_contents('php://input');
    if (!$input) {
        throw new Exception('No input data received');
    }

    $data = json_decode($input, true);
    if (!$data || !isset($data['amount'])) {
        throw new Exception('Invalid input format');
    }

    // Validate the amount
    if ($data['amount'] <= 0) {
        throw new Exception("Invalid amount");
    }

    // Generate unique order number
    $order_number = 'GS' . time() . rand(100, 999);
    
    // Initialize Razorpay API
    $api = new Api('rzp_test_PXCkaH2uhlAUBp', 'Yr0t8sy7WRicOGd28jjoW9Xq'); // Replace with your actual secret key

    // Create order data
    $orderData = [
        'receipt' => $order_number,
        'amount' => round($data['amount'] * 100), // Convert to paise
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
    error_log('Payment Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    exit;
}