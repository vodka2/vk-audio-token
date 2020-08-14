<?php

namespace Vodka2\VKAudioToken;

class TokenReceiverBoom {
    private $params;
    private $authData;
    private $client;

    public function __construct($authData, CommonParams $params) {
        $this->params = $params;
        $this->authData = $authData;
        $this->client = SupportedClients::Boom();
    }

    public function getToken($nonRefreshedToken, $userId){
        $receipt = $this->getReceipt();
        $passKey = $this->refreshToken($nonRefreshedToken, $userId, $receipt);
        return [$nonRefreshedToken, $passKey];
    }

    private function refreshToken($token, $userId, $receipt) {
        curl_reset($this->params->curl);
        $this->params->setCommonVK();
        curl_setopt($this->params->curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt(
            $this->params->curl,
            CURLOPT_URL,
            "https://api.um-agency.com/authtoken?token_type=vk&device_token=$receipt&".
            "vk_user_id=$userId&access_token=$token&partner_auth_key=JZVFN66TQ48QX4QRPC4B3AQNF8MEZMDB"
        );
        $dec = json_decode(curl_exec($this->params->curl));
        if (!isset($dec->response->pass_key)) {
            throw new TokenException(TokenException::REFRESH_ERROR, $dec);
        }
        return $dec->response->pass_key;
    }

    private function getReceipt(){
        curl_reset($this->params->curl);
        $this->params->setCommonGCM();
        curl_setopt(
            $this->params->curl,
            CURLOPT_URL,
            "https://android.clients.google.com/c2dm/register3"
        );
        curl_setopt($this->params->curl, CURLOPT_HTTPHEADER, array(
            'Authorization: AidLogin ' . $this->authData['id'] . ':' . $this->authData['token'],
        ));
        curl_setopt($this->params->curl, CURLOPT_POST, 1);
        $paramsArr = array(
            "X-scope" => "*",
            "X-osv" => "23",
            "X-subtype" => "415406574693",
            "X-app_ver" => "4749",
            "X-kid" => "|ID|1|",
            "X-appid" => $this->params->generateRandomString(11, '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_-'),
            "X-gmsv" => "14575019",
            "X-cliv" => "fiid-12451000",
            "X-app_ver_name" => "4.2.4749",
            "X-X-kid" => "|ID|1|",
            "app" => "com.uma.musicvk",
            "sender" => "415406574693",
            "device" => $this->authData['id'],
            "cert" => "d83d03d675dbb36717d7f43cc05932bfddcd1edb",
            "app_ver" => "4749",
            "info" => "8wTsr6BBZ94RUO6eTSP7b7XoYvFhYRY",
            "gcm_ver" => "14575019",
            "plat" => "0"
        );
        curl_setopt($this->params->curl, CURLOPT_POSTFIELDS,
            http_build_query($paramsArr));
        $str = curl_exec($this->params->curl);
        $res = explode('|ID|1|:', $str)[1];
        if($res == 'PHONE_REGISTRATION_ERROR'){
            throw new TokenException(TokenException::REGISTRATION_ERROR, $str);
        }
        return $res;
    }
}