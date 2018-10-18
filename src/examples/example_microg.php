<?php

include __DIR__.'/../autoloader.php';

use Vodka2\VKAudioToken\AndroidCheckin;
use Vodka2\VKAudioToken\SmallProtobufHelper;
use Vodka2\VKAudioToken\CommonParams;
use Vodka2\VKAudioToken\TokenReceiver;
use Vodka2\VKAudioToken\MTalkClient;

$params = new CommonParams();
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

$receiver = new TokenReceiver($login, $pass, $authData, $params);
echo $receiver->getToken();