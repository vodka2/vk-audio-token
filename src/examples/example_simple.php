<?php

use Vodka2\VKAudioToken\IFAuthException;
use Vodka2\VKAudioToken\TokenException;
use Vodka2\VKAudioToken\TokenFacade;
use Vodka2\VKAudioToken\TwoFAHelper;
use Vodka2\VKAudioToken\CommonParams;
use Vodka2\VKAudioToken\SupportedClients;

include __DIR__.'/../autoloader.php';

$login = $argv[1];
$pass = $argv[2];
// for 2 factor authentication with sms
$authCode = isset($argv[3]) ? $argv[3] : "GET_CODE";

//for boom 2 factor authentication
$base64State = isset($argv[4]) ? $argv[4] : false;
$boomAuthCode = isset($argv[5]) ? $argv[5] : false;


try {
    var_export(TokenFacade::getKateToken($login, $pass, $authCode));
} catch (TokenException $e) {
    if ($e->code == TokenException::TWOFA_REQ) {
        echo "2FA request\n";
        echo "validation sid = " . $e->extra->validation_sid . "\n";
        // If sms is not being sent use TwoFAHelper
        // $params = new CommonParams(SupportedClients::Kate()->getUserAgent());
        // (new TwoFAHelper($params))->validatePhone($e->extra->validation_sid);
    } else {
        throw $e;
    }
}

try {
    var_export(TokenFacade::getVkOfficialToken($login, $pass, $authCode));
} catch (TokenException $e) {
    if ($e->code == TokenException::TWOFA_REQ) {
        echo "2FA request\n";
        echo "validation sid = " . $e->extra->validation_sid . "\n";
        // If sms is not being sent use TwoFAHelper
        // $params = new CommonParams(SupportedClients::VkOfficial()->getUserAgent());
        // (new TwoFAHelper($params))->validatePhone($e->extra->validation_sid);
    } else {
        throw $e;
    }
}

try {
    var_export(TokenFacade::getBoomToken($login, $pass, $base64State, $boomAuthCode));
} catch (IFAuthException $ex) {
    if ($ex->code == IFAuthException::TWOFA_REQ) {
        echo $ex->extra['message']."\n";
        echo $ex->extra['base64State']."\n"; // pass this and code from sms next time
    } else {
        throw $ex;
    }
}