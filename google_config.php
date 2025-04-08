<?php
require_once 'vendor/autoload.php';

function getGoogleClient() {
    $client = new Google\Client();
    $client->setClientId('79714922667-t0d6b3e2es474qac595mcq91evm3t9aa.apps.googleusercontent.com');
    $client->setClientSecret('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');
    $client->setRedirectUri('http://localhost/GS1/google_callback.php');
    $client->addScope('https://www.googleapis.com/auth/userinfo.profile');
    $client->addScope('https://www.googleapis.com/auth/userinfo.email');
    $client->setAccessType('offline');
    $client->setPrompt('select_account consent');
    
    return $client;
}
?>