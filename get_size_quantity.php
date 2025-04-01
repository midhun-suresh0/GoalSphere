<?php
require_once 'includes/db.php';
header('Content-Type: application/json');

$jersey_id = isset($_GET['jersey_id']) ? intval($_GET['jersey_id']) : 0;
$size = isset($_GET['size']) ? $_GET['size'] : '';

if (!$jersey_id || !$size) {
    die(json_encode(['error' => 'Missing parameters']));
}

$sql = "SELECT quantity FROM jersey_sizes 
        WHERE jersey_id = ? AND size = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $jersey_id, $size);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode([
        'success' => true,
        'quantity' => $row['quantity']
    ]);
} else {
    echo json_encode([
        'success' => false,
        'quantity' => 0
    ]);
}
?> 