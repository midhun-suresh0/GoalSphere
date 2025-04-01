<?php
require_once 'includes/db.php';
header('Content-Type: application/json');

$jersey_id = isset($_GET['jersey_id']) ? intval($_GET['jersey_id']) : 0;

if (!$jersey_id) {
    die(json_encode(['success' => false, 'message' => 'Invalid jersey ID']));
}

$sql = "SELECT id, jersey_id, image_url, is_primary 
        FROM jersey_images 
        WHERE jersey_id = ? 
        ORDER BY is_primary DESC, id ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $jersey_id);
$stmt->execute();
$result = $stmt->get_result();

$images = [];
while ($row = $result->fetch_assoc()) {
    $images[] = $row;
}

echo json_encode([
    'success' => true,
    'images' => $images
]);
?> 