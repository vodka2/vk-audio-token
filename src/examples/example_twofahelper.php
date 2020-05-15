<?php

include __DIR__.'/../autoloader.php';

use Vodka2\VKAudioToken\TokenReceiverOfficial;
use Vodka2\VKAudioToken\CommonParams;
use Vodka2\VKAudioToken\SupportedClients;
use Vodka2\VKAudioToken\TwoFAHelper;

$login = $argv[1];
$pass = $argv[2];
$authCode = isset($argv[3]) ? $argv[3] : ""; // for 2 factor authentication with sms

// TwoFAHelper also works with TokenReceiver class and Kate User-Agent. See example_microg.php
$params = new CommonParams(SupportedClients::VkOfficial()->getUserAgent());

$receiver = new TokenReceiverOfficial($login, $pass, $params, $authCode);

// Get token or send SMS with code
try {
    echo join("\n", $receiver->getToken());
} catch (\Vodka2\VKAudioToken\TokenException $e) {
    if ($e->code == \Vodka2\VKAudioToken\TokenException::TWOFA_REQ && isset($e->extra->validation_sid)) {
        (new TwoFAHelper($params))->validatePhone($e->extra->validation_sid);
        echo "SMS should be sent\n";
    } else {
        throw $e;
    }
}