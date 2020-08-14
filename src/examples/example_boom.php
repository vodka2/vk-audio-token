<?php

include __DIR__.'/../autoloader.php';

use Vodka2\VKAudioToken\AndroidCheckin;
use Vodka2\VKAudioToken\IFAuth;
use Vodka2\VKAudioToken\IFAuth2FAData;
use Vodka2\VKAudioToken\IFAuthException;
use Vodka2\VKAudioToken\SmallProtobufHelper;
use Vodka2\VKAudioToken\CommonParams;
use Vodka2\VKAudioToken\MTalkClient;
use Vodka2\VKAudioToken\SupportedClients;
use Vodka2\VKAudioToken\TokenReceiverBoom;

$params = new CommonParams(SupportedClients::Boom()->getUserAgent());
$protobufHelper = new SmallProtobufHelper();

$checkin = new AndroidCheckin($params, $protobufHelper);
$authData = $checkin->doCheckin();

$mtalkClient = new MTalkClient($authData, $protobufHelper);
$mtalkClient->sendRequest();

//This array element is needed only for MTalk request
unset($authData['idStr']);

//You can get multiple tokens using this data
var_dump($authData);


$login = $argv[1];
$pass = $argv[2];
$base64State = isset($argv[3]) ? $argv[3] : ""; // for 2 factor authentication, initially empty
$authCode = isset($argv[4]) ? $argv[4] : ""; // for 2 factor authentication, initially empty

if ($base64State !== "") {
    // Retrying (receiving sms instead of call)
    $ifAuth2FaData = ($authCode === "") ? IFAuth2FAData::withRetry($base64State) : new IFAuth2FAData($base64State, $authCode);
    $ifAuth = IFAuth::with2FA($params, SupportedClients::Boom(), $ifAuth2FaData, "audio,messages,offline");
} else {
    $ifAuth = new IFAuth($login, $pass, $params, SupportedClients::Boom(), "audio,messages,offline");
}

try {
    $result = $ifAuth->getTokenAndId();
    $token = $result['token'];
    $userId = $result['userId'];
} catch (IFAuthException $ex) {
    if ($ex->code == IFAuthException::TWOFA_REQ) {
        echo $ex->extra['message']."\n";
        echo $ex->extra['base64State']."\n"; // pass this and code from sms next time
        exit(1);
    } else {
        throw $ex;
    }
}
$receiver = new TokenReceiverBoom($authData, $params);

// Token and pass key (pass key currently not used)
var_dump($receiver->getToken($token, $userId));