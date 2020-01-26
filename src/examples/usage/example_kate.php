<?php

include __DIR__.'/../../autoloader.php';

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
    "&v=5.95"
);

echo json_encode(json_decode(curl_exec($ch)), JSON_PRETTY_PRINT)."\n\n";

curl_setopt(
    $ch,
    CURLOPT_URL,
    "https://api.vk.com/method/audio.search?access_token=".TOKEN.
    "&q=".urlencode("Justin Bieber - Baby")."&count=10&v=5.95"
);

echo json_encode(json_decode(curl_exec($ch)), JSON_PRETTY_PRINT)."\n\n";

curl_setopt(
    $ch,
    CURLOPT_URL,
    "https://api.vk.com/method/audio.get?access_token=".TOKEN."&count=10&v=5.95"
);

echo json_encode(json_decode(curl_exec($ch)), JSON_PRETTY_PRINT);

$ownerId = 238615607;

curl_setopt(
    $ch,
    CURLOPT_URL,
    "https://api.vk.com/method/audio.getPlaylists?access_token=".TOKEN."&owner_id=$ownerId&count=10&v=5.95"
);

echo json_encode($playlists = json_decode(curl_exec($ch)), JSON_PRETTY_PRINT);

if ($playlists->response->count > 0) {
    $playlistId = $playlists->response->items[0]->id;
    curl_setopt(
        $ch,
        CURLOPT_URL,
        "https://api.vk.com/method/audio.get?access_token=".TOKEN.
        "&owner_id=$ownerId&album_id=$playlistId&count=10&v=5.95"
    );

    echo json_encode(json_decode(curl_exec($ch)), JSON_PRETTY_PRINT);
}

curl_setopt(
    $ch,
    CURLOPT_URL,
    "https://api.vk.com/method/audio.getRecommendations?access_token=".TOKEN."&count=10&v=5.95"
);

echo json_encode(json_decode(curl_exec($ch)), JSON_PRETTY_PRINT);

curl_setopt(
    $ch,
    CURLOPT_URL,
    "https://api.vk.com/method/audio.getPopular?access_token=".TOKEN."&count=10&v=5.95"
);

echo json_encode(json_decode(curl_exec($ch)), JSON_PRETTY_PRINT);
