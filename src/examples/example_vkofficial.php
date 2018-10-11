<?php

include '../autoloader.php';

use Vodka2\VKAudioToken\TokenReceiverOfficial;
use Vodka2\VKAudioToken\CommonParams;
use Vodka2\VKAudioToken\SupportedClients;

$login = $argv[1];
$pass = $argv[2];

$params = new CommonParams(SupportedClients::VkOfficial()->getUserAgent());

$receiver = new TokenReceiverOfficial($login, $pass, $params);

// Token, secret and device id
echo join("\n", $receiver->getToken());