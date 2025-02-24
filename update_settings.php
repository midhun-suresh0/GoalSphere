<?php
session_start();

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if ($data && isset($data['type']) && isset($data['value'])) {
    switch ($data['type']) {
        case 'language':
            if (in_array($data['value'], ['en', 'es'])) {  // Only allow valid languages
                $_SESSION['language'] = $data['value'];
            }
            break;
        case 'display_mode':
            $_SESSION['display_mode'] = $data['value'];
            break;
    }
    
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid data']);
} 