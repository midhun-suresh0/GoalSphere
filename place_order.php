<?php
session_start();
require_once 'includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

try {
    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);
    $user_id = $_SESSION['user_id'];

    // Start transaction
    $conn->begin_transaction();

    // Get cart items and calculate total
    $cart_sql = "SELECT c.*, j.price 
                 FROM cart c 
                 JOIN jerseys j ON c.jersey_id = j.id 
                 WHERE c.user_id = ?";
    $stmt = $conn->prepare($cart_sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $cart_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $total = 0;
    foreach ($cart_items as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    $total += 100; // Adding shipping cost

    // Insert order
    $order_sql = "INSERT INTO orders (user_id, full_name, email, phone, address, city, 
                                    postal_code, state, total_amount) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($order_sql);
    $stmt->bind_param("isssssssd", 
        $user_id,
        $data['details']['fullName'],
        $data['details']['email'],
        $data['details']['phone'],
        $data['shipping']['address'],
        $data['shipping']['city'],
        $data['shipping']['postalCode'],
        $data['shipping']['state'],
        $total
    );
    $stmt->execute();
    $order_id = $conn->insert_id;

    // Insert order items
    foreach ($cart_items as $item) {
        $item_sql = "INSERT INTO order_items (order_id, jersey_id, size, quantity, price) 
                     VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($item_sql);
        $stmt->bind_param("iisid", 
            $order_id,
            $item['jersey_id'],
            $item['size'],
            $item['quantity'],
            $item['price']
        );
        $stmt->execute();

        // Update jersey quantity
        $update_qty_sql = "UPDATE jersey_sizes 
                          SET quantity = quantity - ? 
                          WHERE jersey_id = ? AND size = ?";
        $stmt = $conn->prepare($update_qty_sql);
        $stmt->bind_param("iis", 
            $item['quantity'],
            $item['jersey_id'],
            $item['size']
        );
        $stmt->execute();
    }

    // Clear cart
    $clear_cart_sql = "DELETE FROM cart WHERE user_id = ?";
    $stmt = $conn->prepare($clear_cart_sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    // Commit transaction
    $conn->commit();

    echo json_encode([
        'success' => true,
        'order_id' => $order_id,
        'message' => 'Order placed successfully'
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo json_encode([
        'success' => false,
        'message' => 'Error placing order: ' . $e->getMessage()
    ]);
} 