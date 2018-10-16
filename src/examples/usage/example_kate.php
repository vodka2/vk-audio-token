<?php

include '../../autoloader.php';

use Vodka2\VKAudioToken\SupportedClients;

//Token obtained by example_microg.php script
define('TOKEN', $argv[1]);
define('USER_AGENT', SupportedClients::Kate()->getUserAgent());
$ch = curl_init();

curl_setopt($ch,CURLOPT_HTTPHEADER, array('User-Agent: '.USER_AGENT));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

curl_setopt(
    $ch,
    CURLOPT_URL,
    "https://api.vk.com/method/audio.getById?access_token=".TOKEN.
    "&audios=".urlencode("371745461_456289486,-41489995_202246189").
    "&v=5.72"
);

echo json_encode(json_decode(curl_exec($ch)), JSON_PRETTY_PRINT)."\n\n";

curl_setopt(
    $ch,
    CURLOPT_URL,
    "https://api.vk.com/method/audio.search?access_token=".TOKEN.
    "&q=".urlencode("Justin Bieber - Baby")."&v=5.72"
);

echo json_encode(json_decode(curl_exec($ch)), JSON_PRETTY_PRINT)."\n\n";

curl_setopt(
    $ch,
    CURLOPT_URL,
    "https://api.vk.com/method/audio.get?access_token=".TOKEN."&v=5.72"
);

echo json_encode(json_decode(curl_exec($ch)), JSON_PRETTY_PRINT);

