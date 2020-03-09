<?php

include __DIR__.'/../../autoloader.php';

use Vodka2\VKAudioToken\SupportedClients;

//Credentials obtained by example_vkofficial.php script
define('TOKEN', $argv[1]);
define('USER_AGENT', SupportedClients::VkOfficial()->getUserAgent());
$ch = curl_init();

curl_setopt($ch,CURLOPT_HTTPHEADER, array('User-Agent: '.USER_AGENT));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);

/* Search for a song */
curl_setopt(
    $ch,
    CURLOPT_URL,
    "https://api.vk.com/method/audio.getCatalog"
);

curl_setopt($ch,
    CURLOPT_POSTFIELDS,
    "v=5.116&https=1&ref=search&extended=1&lang=en&query=".
    urlencode("Justin Bieber - Baby")."&access_token=".TOKEN
);

/* Response with m3u8 urls */
//echo json_encode(json_decode(curl_exec($ch)), JSON_PRETTY_PRINT)."\n\n";

$tempJson = json_decode(curl_exec($ch));

/* Example of getting mp3 from m3u8 url */
function getMp3FromM3u8($url) {
    // Not a m3u8 url
    if (!strpos($url, "index.m3u8?")) {
        return $url;
    }
    if (strpos($url, "/audios/")) {
        return preg_replace('~^(.+?)/[^/]+?/audios/([^/]+)/.+$~', '\\1/audios/\\2.mp3', $url);
    } else {
        return preg_replace('~^(.+?)/(p[0-9]+)/[^/]+?/([^/]+)/.+$~', '\\1/\\2/\\3.mp3', $url);
    }
}

$items = $tempJson->response->items;
foreach ($items as $item) {
    if ($item->type == 'audios_list') {
        $allAudios = $item->audios;
        foreach($allAudios as $audio) {
            $audio->url = getMp3FromM3u8($audio->url);
        }
        break;
    }
}

/* Response with mp3 urls */
echo json_encode($tempJson, JSON_PRETTY_PRINT)."\n\n";

/* Playlists */
$items = $tempJson->response->items;
foreach ($items as $item) {
    if ($item->type == 'playlists') {
        $albumsId = $item->id;
        break;
    }
}

curl_setopt(
    $ch,
    CURLOPT_URL,
    "https://api.vk.com/method/audio.getCatalogBlockById"
);

curl_setopt($ch,
    CURLOPT_POSTFIELDS,
    "v=5.116&https=1&extended=1".
    "&lang=en&block_id=$albumsId&count=3&start_from=2".
    "&access_token=".TOKEN
);

echo json_encode($tempJson = json_decode(curl_exec($ch)), JSON_PRETTY_PRINT)."\n\n";

/* Tracks from the first playlist */
$playlist = $tempJson->response->block->playlists[0];

curl_setopt(
    $ch,
    CURLOPT_URL,
    "https://api.vk.com/method/execute.getPlaylist"
);

$ownerId = $playlist->owner_id;
$accessKey = $playlist->access_key;
$albumId = $playlist->id;

curl_setopt($ch,
    CURLOPT_POSTFIELDS,
    "v=5.116&https=1&extended=1".
    "&lang=en&owner_id=$ownerId&access_key=$accessKey&id=$albumId&need_playlist=1&need_owner=1".
    "&audio_count=3&audio_offset=1".
    "&access_token=".TOKEN
);

echo json_encode(json_decode(curl_exec($ch)), JSON_PRETTY_PRINT)."\n\n";

/* Find recommendations */
curl_setopt(
    $ch,
    CURLOPT_URL,
    "https://api.vk.com/method/audio.getCatalog"
);

curl_setopt($ch,
    CURLOPT_POSTFIELDS,
    "v=5.116&https=1&ref=recommendations&count=7&extended=1".
    "&lang=en&fields=".urlencode("first_name_gen,photo_50,photo_100,photo_200").
    "&access_token=".TOKEN
);

echo json_encode(json_decode(curl_exec($ch)), JSON_PRETTY_PRINT)."\n\n";


curl_setopt(
    $ch,
    CURLOPT_URL,
    "https://api.vk.com/method/execute.getMusicPage"
);

/* Get my audios */
curl_setopt($ch,
    CURLOPT_POSTFIELDS,
    "v=5.116&https=1&audio_offset=0&need_owner=1&owner_id=358618411".
    "&audio_count=100&playlists_count=12&lang=en&need_playlists=1&func_v=3".
    "&access_token=".TOKEN
);

echo json_encode(json_decode(curl_exec($ch)), JSON_PRETTY_PRINT)."\n\n";