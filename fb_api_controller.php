<?php

define('GRAPH_URL', 'https://graph.facebook.com');

function fb_api_post($realtiveUrl, $postData)
{
    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => GRAPH_URL . $realtiveUrl,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_HEADER => false,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_POSTFIELDS => $postData,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);

    $output = curl_exec($ch);

    curl_close($ch);

    return $output;
}

function fb_api_get($realtiveUrl, $params)
{
    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => GRAPH_URL . $realtiveUrl . '?' . http_build_query($params),
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HEADER => false,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);

    $output = curl_exec($ch);

    curl_close($ch);

    return $output;
}