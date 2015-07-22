<?php

require_once 'config.php';
require_once 'fb_api_controller.php';

//subscribe page to app
$params = [
    'client_id' => APP_ID,
    'client_secret' => APP_SECRET,
    'access_token' => PAGE_VERIFY_TOKEN,
];
$response = fb_api_post('/' . PAGE_ID . '/subscribed_apps', $params);
echo "Subscribe page to app response: " . $response . "\n";

//subscribe app to page updates
$params = [
    'object' => 'page',
    'callback_url' => APP_CALLBACK_URL,
    'fields' => 'feed,conversations',
    'verify_token' => GENERATED_VERIFY_TOKEN,
    'client_id' => APP_ID,
    'client_secret' => APP_SECRET,
    'access_token' => APP_ACCESS_TOKEN,
];
$response = fb_api_post('/' . PAGE_ID . '/subscriptions', $params);
echo "Subscribe app to page updates response: " . $response . "\n";
