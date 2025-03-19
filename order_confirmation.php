<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['order_id'])) {
    header('Location: index.php');
    exit;
}

$order_id = intval($_GET['order_id']);
$user_id = $_SESSION['user_id'];

// Get order details
$order_sql = "SELECT * FROM orders WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($order_sql);
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - GoalSphere</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black min-h-screen">
    <?php include 'includes/header.php'; ?>

    <div class="container mx-auto px-4 py-12">
        <div class="bg-gray-900 p-8 rounded-lg max-w-2xl mx-auto">
            <div class="text-center mb-8">
                <svg class="w-16 h-16 text-green-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <h1 class="text-2xl font-bold text-white mb-2">Order Placed Successfully!</h1>
                <p class="text-gray-400">Order #<?php echo $order_id; ?></p>
            </div>

            <div class="space-y-4">
                <div class="border-b border-gray-700 pb-4">
                    <h2 class="text-xl font-semibold text-white mb-2">Order Details</h2>
                    <p class="text-gray-400">Total Amount: ₹<?php echo number_format($order['total_amount'], 2); ?></p>
                    <p class="text-gray-400">Status: <?php echo ucfirst($order['status']); ?></p>
                </div>

                <div class="border-b border-gray-700 pb-4">
                    <h2 class="text-xl font-semibold text-white mb-2">Shipping Address</h2>
                    <p class="text-gray-400"><?php echo htmlspecialchars($order['full_name']); ?></p>
                    <p class="text-gray-400"><?php echo htmlspecialchars($order['address']); ?></p>
                    <p class="text-gray-400">
                        <?php echo htmlspecialchars($order['city']); ?>, 
                        <?php echo htmlspecialchars($order['state']); ?> - 
                        <?php echo htmlspecialchars($order['postal_code']); ?>
                    </p>
                </div>

                <div class="text-center mt-8">
                    <a href="index.php" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                        Continue Shopping
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html> 