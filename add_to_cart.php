<?php
session_start();
header('Content-Type: application/json');

// Database connection
$host = 'localhost';
$dbname = 'goalsphere';
$username = 'root';
$password = '';

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode([
        'success' => false,
        'message' => 'Database connection failed'
    ]));
}

// Get the POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['jersey_id']) || !isset($data['size']) || !isset($data['quantity']) || !isset($data['price'])) {
    die(json_encode([
        'success' => false,
        'message' => 'Missing required data'
    ]));
}

$jersey_id = intval($data['jersey_id']);
$size = $conn->real_escape_string($data['size']);
$quantity = intval($data['quantity']);
$price = floatval($data['price']);

// Check if the requested quantity is available
$stock_check = $conn->query("SELECT quantity FROM jersey_sizes 
                            WHERE jersey_id = $jersey_id AND size = '$size'");

if ($stock_check && $row = $stock_check->fetch_assoc()) {
    if ($row['quantity'] < $quantity) {
        die(json_encode([
            'success' => false,
            'message' => 'Not enough stock available'
        ]));
    }
} else {
    die(json_encode([
        'success' => false,
        'message' => 'Size not available'
    ]));
}

// Get jersey details
$jersey_query = $conn->query("SELECT name FROM jerseys WHERE id = $jersey_id");
$jersey = $jersey_query->fetch_assoc();

// Get the primary image
$image_query = $conn->query("SELECT image_url FROM jersey_images 
                            WHERE jersey_id = $jersey_id 
                            ORDER BY is_primary DESC LIMIT 1");
$image = $image_query->fetch_assoc();

// Initialize cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Generate a unique cart item ID
$cart_id = uniqid();

// Add item to cart
$cart_item = [
    'id' => $cart_id,
    'jersey_id' => $jersey_id,
    'name' => $jersey['name'],
    'size' => $size,
    'quantity' => $quantity,
    'price' => $price,
    'image_url' => $image['image_url'] ?? 'assets/images/default-jersey.jpg'
];

// Calculate new cart count
$cartCount = 0;

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    
    // Add to database
    $sql = "INSERT INTO cart (user_id, jersey_id, size, quantity, price) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisid", $user_id, $jersey_id, $size, $quantity, $price);
    $stmt->execute();
    $cart_item['id'] = $stmt->insert_id;
    
    // Get updated cart count from database
    $count_query = $conn->query("SELECT SUM(quantity) as count FROM cart WHERE user_id = $user_id");
    if ($count_result = $count_query->fetch_assoc()) {
        $cartCount = $count_result['count'] ?? 0;
    }
} else {
    // Add to session cart
    $_SESSION['cart'][] = $cart_item;
    
    // Calculate session cart count
    $cartCount = array_sum(array_column($_SESSION['cart'], 'quantity'));
}

// Return success response
echo json_encode([
    'success' => true,
    'message' => 'Item added to cart successfully',
    'cartCount' => $cartCount
]);

$conn->close();
?> 