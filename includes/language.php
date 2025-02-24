<?php
function loadLanguage() {
    $language = $_SESSION['language'] ?? 'en';
    $langFile = __DIR__ . "/../languages/{$language}.php";
    
    if (file_exists($langFile)) {
        return require $langFile;
    }
    
    // Fallback to English if language file doesn't exist
    return require __DIR__ . "/../languages/en.php";
}

function __($key) {
    static $translations = null;
    if ($translations === null) {
        $translations = loadLanguage();
    }
    return $translations[$key] ?? $key;
} 