<?php

require_once 'config.php';
require_once 'fb_api_controller.php';

//request longLived access token, to get longLive pageAccessToken
$params = [
    'grant_type' => 'fb_exchange_token',
    'fb_exchange_token' => USER_ACCESS_TOKEN,
    'client_id' => APP_ID,
    'client_secret' => APP_SECRET,
];
var_dump(fb_api_get('/oauth/access_token', $params));
$longLivedAccessToken = explode('=', fb_api_get('/oauth/access_token', $params))[1];

//get page tokens and ids for each page
$params = [
    'client_id' => APP_ID,
    'client_secret' => APP_SECRET,
    'access_token' => $longLivedAccessToken,
];
$accounts = json_decode(fb_api_get('/me/accounts', $params));
foreach ($accounts->data as $page) {
    echo $page->name . " : " . $page->access_token . "\n";
    echo "Page ID: " . $page->id . "\n";
}

//get app access token
$params = [
    'grant_type' => 'client_credentials',
    'client_id' => APP_ID,
    'client_secret' => APP_SECRET,
];
$appAccessToken = explode('=', fb_api_get('/oauth/access_token', $params))[1];
echo "APP access token: " . $appAccessToken . "\n";
