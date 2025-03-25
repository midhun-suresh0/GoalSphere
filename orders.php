<?php
session_start();
require_once 'includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

try {
    // Fetch orders from jersey_orders table
    $sql = "SELECT id, order_number, total_amount, payment_status, created_at 
            FROM jersey_orders 
            WHERE user_id = ?
            ORDER BY created_at DESC";
    
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    if (!$stmt->bind_param("i", $_SESSION['user_id'])) {
        throw new Exception("Binding parameters failed: " . $stmt->error);
    }
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $orders = [];
    
    while ($row = $result->fetch_assoc()) {
        $orderId = $row['id'];
        $orders[$orderId] = [
            'id' => $row['id'],
            'order_number' => $row['order_number'],
            'total_amount' => $row['total_amount'],
            'payment_status' => $row['payment_status'],
            'created_at' => $row['created_at'],
            'items' => []
        ];
        
        // Fetch items for this order
        $items_sql = "SELECT 
                        oi.*,
                        j.name as jersey_name
                     FROM order_items oi
                     LEFT JOIN jerseys j ON oi.jersey_id = j.id
                     WHERE oi.order_id = ?";
        
        $items_stmt = $conn->prepare($items_sql);
        if ($items_stmt !== false) {
            $items_stmt->bind_param("i", $orderId);
            $items_stmt->execute();
            $items_result = $items_stmt->get_result();

            while ($item = $items_result->fetch_assoc()) {
                $orders[$orderId]['items'][] = [
                    'name' => $item['jersey_name'],
                    'quantity' => $item['quantity'],
                    'size' => $item['size'],
                    'price' => $item['price']
                ];
            }
            $items_stmt->close();
        }
    }

} catch (Exception $e) {
    error_log("Error in orders.php: " . $e->getMessage());
    $error_message = "An error occurred while fetching your orders. Please try again later.";
    $orders = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Orders - GoalSphere</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-white">
    <div class="max-w-7xl mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-2xl font-bold">Your Orders</h1>
            <a href="shop.php" class="text-blue-600 hover:text-blue-800">Continue Shopping</a>
        </div>

        <?php if (isset($error_message)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($orders)): ?>
            <div class="bg-white rounded-lg p-8 text-center border">
                <p class="text-gray-500 mb-4">You haven't placed any orders yet.</p>
                <a href="shop.php" class="inline-block bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                    Start Shopping
                </a>
            </div>
        <?php else: ?>
            <div class="space-y-6">
                <?php foreach ($orders as $orderId => $order): ?>
                    <div class="bg-white border rounded-lg overflow-hidden">
                        <!-- Order Header -->
                        <div class="p-6 border-b">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h2 class="text-lg font-semibold">Order #<?php echo htmlspecialchars($order['order_number']); ?></h2>
                                    <p class="text-sm text-gray-500 mt-1">
                                        <?php echo date('F d, Y', strtotime($order['created_at'])); ?>
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-lg font-semibold">₹<?php echo number_format($order['total_amount'], 2); ?></p>
                                    <span class="inline-block px-3 py-1 rounded-full text-sm mt-2
                                        <?php echo $order['payment_status'] === 'success' ? 'bg-green-100 text-green-800' : 
                                            ($order['payment_status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'); ?>">
                                        <?php echo ucfirst($order['payment_status']); ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Order Items -->
                        <?php if (!empty($order['items'])): ?>
                            <div class="divide-y divide-gray-200">
                                <?php foreach ($order['items'] as $item): ?>
                                    <div class="p-6 flex items-center">
                                        <div class="w-12 h-12 flex-shrink-0 bg-gray-200 rounded-full flex items-center justify-center">
                                            <span class="text-gray-500 text-xl"><?php echo substr(htmlspecialchars($item['name'] ?? 'J'), 0, 1); ?></span>
                                        </div>
                                        <div class="ml-6 flex-1">
                                            <h3 class="text-base font-medium text-gray-900">
                                                <?php echo htmlspecialchars($item['name']); ?>
                                            </h3>
                                            <div class="mt-1 text-sm text-gray-500">
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
                            </div>
                        <?php endif; ?>

                        <!-- Order Footer -->
                        <div class="p-6 bg-gray-50 flex justify-between items-center">
                            <a href="view_order.php?id=<?php echo $orderId; ?>" 
                               class="text-blue-600 hover:text-blue-800">
                                View Details
                            </a>
                            <?php if ($order['payment_status'] === 'success'): ?>
                                <button onclick="generateBill(<?php echo $orderId; ?>)"
                                        class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                                    Generate Bill
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
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