<?php
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: signin.php');
    exit();
}

// Database connection
require_once 'includes/db.php';

// Get filter parameters
$start_date = isset($_GET['start']) ? $_GET['start'] : date('Y-m-d', strtotime('-30 days'));
$end_date = isset($_GET['end']) ? $_GET['end'] : date('Y-m-d');
$user_filter = isset($_GET['user']) ? intval($_GET['user']) : 0;

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="purchases_' . $start_date . '_to_' . $end_date . '.csv"');

// Create output stream
$output = fopen('php://output', 'w');

// Add CSV header row
fputcsv($output, [
    'Order ID', 
    'Date', 
    'Customer Name', 
    'Email', 
    'Total Amount', 
    'Payment Method', 
    'Status', 
    'Items', 
    'Shipping Address'
]);

// Check if tables exist before proceeding
$orders_exist = $conn->query("SHOW TABLES LIKE 'orders'")->num_rows > 0;
$users_exist = $conn->query("SHOW TABLES LIKE 'users'")->num_rows > 0;

if (!$orders_exist || !$users_exist) {
    // If tables don't exist, write an empty export with a message
    fputcsv($output, ['No order data available. Database tables not found.']);
    fclose($output);
    exit;
}

// Build query with filters
$query = "SELECT o.*, u.first_name, u.last_name, u.email
          FROM orders o
          JOIN users u ON o.user_id = u.id
          WHERE o.order_date BETWEEN ? AND ?";

if ($user_filter > 0) {
    $query .= " AND o.user_id = ?";
}

$query .= " ORDER BY o.order_date DESC";

// Prepare and execute query
$stmt = $conn->prepare($query);

if (!$stmt) {
    // Handle preparation error
    fputcsv($output, ['Error preparing SQL statement: ' . $conn->error]);
    fclose($output);
    exit;
}

// Bind parameters
try {
    if ($user_filter > 0) {
        $stmt->bind_param("ssi", $start_date, $end_date, $user_filter);
    } else {
        $stmt->bind_param("ss", $start_date, $end_date);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    // If no results, write a message
    if ($result->num_rows == 0) {
        fputcsv($output, ['No orders found in the selected date range.']);
        fclose($output);
        exit;
    }
    
    // Check if jersey_orders table exists
    $jersey_orders_exist = $conn->query("SHOW TABLES LIKE 'jersey_orders'")->num_rows > 0;
    $jerseys_exist = $conn->query("SHOW TABLES LIKE 'jerseys'")->num_rows > 0;
    
    // Output each row
    while ($order = $result->fetch_assoc()) {
        $items_string = 'N/A'; // Default value
        
        // Get order items if tables exist
        if ($jersey_orders_exist && $jerseys_exist) {
            $items_query = "SELECT j.name, jo.size, jo.quantity, jo.price
                           FROM jersey_orders jo
                           JOIN jerseys j ON jo.jersey_id = j.id
                           WHERE jo.order_id = ?";
            $items_stmt = $conn->prepare($items_query);
            
            if ($items_stmt) {
                $items_stmt->bind_param("i", $order['id']);
                $items_stmt->execute();
                $items_result = $items_stmt->get_result();
                
                $items_list = [];
                while ($item = $items_result->fetch_assoc()) {
                    $items_list[] = $item['quantity'] . 'x ' . $item['name'] . ' (' . $item['size'] . ') @ ₹' . $item['price'];
                }
                
                if (!empty($items_list)) {
                    $items_string = implode('; ', $items_list);
                }
            }
        }
        
        // Format shipping address (handle missing fields)
        $address_parts = [];
        if (!empty($order['address'])) $address_parts[] = $order['address'];
        if (!empty($order['city'])) $address_parts[] = $order['city'];
        if (!empty($order['state'])) $address_parts[] = $order['state'];
        if (!empty($order['postal_code'])) $address_parts[] = $order['postal_code'];
        
        $shipping_address = !empty($address_parts) ? implode(', ', $address_parts) : 'N/A';
        
        // Default values for potentially missing fields
        $payment_method = $order['payment_method'] ?? 'N/A';
        $status = $order['status'] ?? 'N/A';
        
        // Write the order row
        fputcsv($output, [
            $order['id'],
            date('Y-m-d H:i:s', strtotime($order['order_date'])),
            $order['first_name'] . ' ' . $order['last_name'],
            $order['email'],
            $order['total_amount'],
            $payment_method,
            $status,
            $items_string,
            $shipping_address
        ]);
    }
    
} catch (Exception $e) {
    // Handle any exceptions
    fputcsv($output, ['Error processing export: ' . $e->getMessage()]);
}

// Close the output stream
fclose($output);
exit;