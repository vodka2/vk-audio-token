<?php

namespace Vodka2\VKAudioToken;


class TokenFacade
{
    public static function getKateToken($login, $pass, $authCode = 'GET_CODE', $nonRefreshedToken = "", $scope = "audio,offline") {
        $params = new CommonParams(SupportedClients::Kate()->getUserAgent());
        $protobufHelper = new SmallProtobufHelper();

        $checkin = new AndroidCheckin($params, $protobufHelper);
        $authData = $checkin->doCheckin();

        $mtalkClient = new MTalkClient($authData, $protobufHelper);
        $mtalkClient->sendRequest();

        $receiver = new TokenReceiver($login, $pass, $authData, $params, $authCode, $scope);
        return array(
            "token" => $receiver->getToken($nonRefreshedToken),
            "userAgent" => SupportedClients::Kate()->getUserAgent()
        );
    }

    public static function getVkOfficialToken($login, $pass, $authCode = 'GET_CODE', $scope = "audio,offline") {
        $params = new CommonParams(SupportedClients::VkOfficial()->getUserAgent());

        $receiver = new TokenReceiverOfficial($login, $pass, $params, $authCode, $scope);
        return array(
            "token" => $receiver->getToken()[0],
            "userAgent" => SupportedClients::VkOfficial()->getUserAgent()
        );
    }

    public static function getBoomToken($login, $pass, $base64State = false, $authCode = false,
                                        $token = false, $userId = false, $scope = "audio,messages,offline") {
        $params = new CommonParams(SupportedClients::Boom()->getUserAgent());
        $protobufHelper = new SmallProtobufHelper();

        $checkin = new AndroidCheckin($params, $protobufHelper);
        $authData = $checkin->doCheckin();

        $mtalkClient = new MTalkClient($authData, $protobufHelper);
        $mtalkClient->sendRequest();

        if ($token === false || $userId === false) {
            if ($base64State !== false) {
                $ifAuth2FaData = ($authCode === false) ? IFAuth2FAData::withRetry($base64State) : new IFAuth2FAData($base64State, $authCode);
                $ifAuth = IFAuth::with2FA($params, SupportedClients::Boom(), $ifAuth2FaData, $scope);
            } else {
                $ifAuth = new IFAuth($login, $pass, $params, SupportedClients::Boom(), $scope);
            }

            $result = $ifAuth->getTokenAndId();
            $token = $result['token'];
            $userId = $result['userId'];
        }
        $receiver = new TokenReceiverBoom($authData, $params);

        return array(
            "token" => $receiver->getToken($token, $userId)[0],
            "userAgent" => SupportedClients::Boom()->getUserAgent()
        );
    }
}