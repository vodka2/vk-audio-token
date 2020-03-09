<?php

include __DIR__.'/../autoloader.php';

use Vodka2\VKAudioToken\TokenReceiverOfficial;
use Vodka2\VKAudioToken\CommonParams;
use Vodka2\VKAudioToken\SupportedClients;

$login = $argv[1];
$pass = $argv[2];
$authCode = isset($argv[3]) ? $argv[3] : ""; // for 2 factor authentication with sms, or pass GET_CODE to get code

$params = new CommonParams(SupportedClients::VkOfficial()->getUserAgent());

$receiver = new TokenReceiverOfficial($login, $pass, $params, $authCode);

// Token
echo join("\n", $receiver->getToken());