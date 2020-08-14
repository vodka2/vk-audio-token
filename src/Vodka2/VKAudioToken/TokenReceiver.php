<?php

namespace Vodka2\VKAudioToken;

class TokenReceiver {
    private $params;
    private $authCode;
    private $login;
    private $pass;
    private $authData;
    private $scope;
    private $client;

    public static function withoutCredentials($authData, CommonParams $params, $authCode = "", $scope = "audio,offline") {
        return new self("", "", $authData, $params, $authCode, $scope);
    }

    public function __construct($login, $pass, $authData, CommonParams $params, $authCode = "", $scope = "audio,offline") {
        $this->params = $params;
        $this->login = $login;
        $this->pass = $pass;
        $this->authCode = $authCode;
        $this->authData = $authData;
        $this->scope = urlencode($scope);
        $this->client = SupportedClients::Kate();
    }

    public function getToken($nonRefreshedToken = ""){
        $receipt = $this->getReceipt();
        $token = ($nonRefreshedToken === "") ? $this->getNonRefreshed() : $nonRefreshedToken;
        return $this->refreshToken($token, $receipt);
    }

    private function getNonRefreshed(){
        curl_reset($this->params->curl);
        $this->params->setCommonVK();
        curl_setopt(
            $this->params->curl,
            CURLOPT_URL,
            "https://oauth.vk.com/token?grant_type=password".
            "&client_id=".$this->client->getClientId().
            "&client_secret=".$this->client->getClientSecret().
            "&username=" . urlencode($this->login) . "&password=" . urlencode($this->pass) .
            "&v=5.95&scope=" . $this->scope . $this->params->getTwoFactorPart($this->authCode)
        );
        $dec = json_decode(curl_exec($this->params->curl));
        if(isset($dec->error) && $dec->error == 'need_validation') {
            throw new TokenException(TokenException::TWOFA_REQ, $dec);
        }
        if(!isset($dec->user_id)){
            throw new TokenException(TokenException::TOKEN_NOT_RECEIVED, $dec);
        }
        return $dec->access_token;
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
            "X-scope" => "GCM",
            "X-osv" => "23",
            "X-subtype" => "54740537194",
            "X-app_ver" => "460",
            "X-kid" => "|ID|1|",
            "X-appid" => $this->params->generateRandomString(11, '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_-'),
            "X-gmsv" => "200313005",
            "X-cliv" => "iid-12211000",
            "X-app_ver_name" => "56 lite",
            "X-X-kid" => "|ID|1|",
            "X-subscription" => "54740537194",
            "X-X-subscription" => "54740537194",
            "X-X-subtype" => "54740537194",
            "app" => "com.perm.kate_new_6",
            "sender" => "54740537194",
            "device" => $this->authData['id'],
            "cert" => "966882ba564c2619d55d0a9afd4327a38c327456",
            "app_ver" => "460",
            "info" => "U_ojcf1ahbQaUO6eTSP7b7WomakK_hY",
            "gcm_ver" => "200313005",
            "plat" => "0",
            "target_ver" => "28"
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

    private function refreshToken($token, $receipt){
        curl_reset($this->params->curl);
        $this->params->setCommonVK();
        curl_setopt(
            $this->params->curl,
            CURLOPT_URL,
            "https://api.vk.com/method/auth.refreshToken?access_token=" . $token .
                  "&receipt=" . $receipt . "&v=5.95"
        );
        $dec = json_decode(curl_exec($this->params->curl));
        $newToken = $dec->response->token;
        if($newToken == $token){
            throw new TokenException(TokenException::TOKEN_NOT_REFRESHED);
        }
        return $newToken;
    }
}