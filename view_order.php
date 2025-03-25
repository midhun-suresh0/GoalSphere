<?php
session_start();
require_once 'includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Check if order ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: orders.php');
    exit;
}

$order_id = $_GET['id'];

try {
    // Fetch order details with better error handling
    $sql = "SELECT * FROM jersey_orders WHERE id = ? AND user_id = ?";
    
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    if (!$stmt->bind_param("ii", $order_id, $_SESSION['user_id'])) {
        throw new Exception("Binding parameters failed: " . $stmt->error);
    }
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        // Order not found or doesn't belong to this user
        header('Location: orders.php');
        exit;
    }
    
    $order = $result->fetch_assoc();
    
    // Fetch order items with updated query
    $items_sql = "SELECT 
                    oi.*,
                    j.name as jersey_name
                 FROM order_items oi
                 LEFT JOIN jerseys j ON oi.jersey_id = j.id
                 WHERE oi.order_id = ?";
    
    $items_stmt = $conn->prepare($items_sql);
    if ($items_stmt === false) {
        throw new Exception("Items prepare failed: " . $conn->error);
    }
    
    if (!$items_stmt->bind_param("i", $order_id)) {
        throw new Exception("Items binding parameters failed: " . $items_stmt->error);
    }
    
    if (!$items_stmt->execute()) {
        throw new Exception("Items execute failed: " . $items_stmt->error);
    }
    
    $items_result = $items_stmt->get_result();
    
    $items = [];
    $subtotal = 0;
    
    while ($item = $items_result->fetch_assoc()) {
        $items[] = $item;
        $subtotal += $item['price'] * $item['quantity'];
    }
    
    // Use the total_amount from the database
    $total_amount = $order['total_amount'];
    
} catch (Exception $e) {
    error_log("Error in view_order.php: " . $e->getMessage());
    $error_message = "An error occurred while fetching the order details: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - GoalSphere</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Header with back button -->
        <div class="flex items-center mb-8">
            <a href="orders.php" class="text-blue-600 hover:text-blue-800 mr-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                </svg>
            </a>
            <h1 class="text-2xl font-bold">Order Details</h1>
        </div>

        <?php if (isset($error_message)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php else: ?>
            <!-- Order Summary -->
            <div class="bg-white rounded-lg shadow mb-6">
                <div class="p-6 border-b">
                    <div class="flex justify-between items-start">
                        <div>
                            <h2 class="text-lg font-semibold">Order #<?php echo htmlspecialchars($order['order_number']); ?></h2>
                            <p class="text-sm text-gray-500 mt-1">
                                <?php echo date('F d, Y', strtotime($order['created_at'])); ?>
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-semibold">₹<?php echo number_format($total_amount, 2); ?></p>
                            <span class="inline-block px-3 py-1 rounded-full text-sm mt-2
                                <?php echo $order['payment_status'] === 'success' ? 'bg-green-100 text-green-800' : 
                                    ($order['payment_status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'); ?>">
                                <?php echo ucfirst($order['payment_status']); ?>
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="p-6">
                    <!-- Payment Information -->
                    <div>
                        <h3 class="font-medium text-gray-900 mb-2">Payment Information</h3>
                        <div class="text-sm text-gray-600">
                            <p class="font-medium mt-2">Payment Method:</p>
                            <p>Razorpay</p>
                            <?php if ($order['payment_status'] === 'success' && !empty($order['razorpay_payment_id'])): ?>
                                <p class="font-medium mt-2">Payment ID:</p>
                                <p><?php echo htmlspecialchars($order['razorpay_payment_id']); ?></p>
                            <?php endif; ?>
                            <p class="font-medium mt-2">Status:</p>
                            <p><?php echo ucfirst($order['payment_status']); ?></p>
                            <p class="font-medium mt-2">Total Amount:</p>
                            <p class="text-green-600 font-medium">₹<?php echo number_format($total_amount, 2); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Order Items -->
            <div class="bg-white rounded-lg shadow mb-6">
                <div class="p-6 border-b">
                    <h3 class="font-medium text-gray-900">Order Items</h3>
                </div>
                
                <?php foreach ($items as $item): ?>
                    <div class="p-6 border-b flex items-center">
                        <div class="w-12 h-12 flex-shrink-0 bg-gray-200 rounded-full flex items-center justify-center">
                            <span class="text-gray-500 text-xl"><?php echo substr(htmlspecialchars($item['jersey_name'] ?? 'J'), 0, 1); ?></span>
                        </div>
                        <div class="ml-6 flex-1">
                            <h4 class="text-base font-medium text-gray-900">
                                <?php echo htmlspecialchars($item['jersey_name'] ?? 'Jersey'); ?>
                            </h4>
                            <div class="mt-2 text-sm text-gray-500">
                                <span>Size: <?php echo htmlspecialchars($item['size']); ?></span>
                                <span class="mx-2">|</span>
                                <span>Quantity: <?php echo $item['quantity']; ?></span>
                            </div>
                        </div>
                        <div class="ml-6 text-right">
                            <p class="text-base font-medium text-gray-900">
                                ₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                            </p>
                            <p class="mt-1 text-sm text-gray-500">
                                ₹<?php echo number_format($item['price'], 2); ?> each
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <!-- Order Total -->
                <div class="p-6 bg-gray-50">
                    <div class="flex justify-between items-center">
                        <p class="text-lg font-medium text-gray-900">Total Amount</p>
                        <p class="text-lg font-semibold text-gray-900">₹<?php echo number_format($total_amount, 2); ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex justify-end space-x-4">
                <a href="orders.php" class="px-6 py-2 border border-gray-300 rounded text-gray-700 hover:bg-gray-50">
                    Back to Orders
                </a>
                <?php if ($order['payment_status'] === 'success'): ?>
                    <button onclick="generateBill(<?php echo $order_id; ?>)"
                            class="bg-green-500 text-white px-6 py-2 rounded hover:bg-green-600">
                        Generate Bill
                    </button>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
    function generateBill(orderId) {
        window.location.href = `generate_bill.php?order_id=${orderId}`;
    }
    </script>
</body>
</html>