<?php

namespace Vodka2\VKAudioToken;

class TokenReceiver {
    private $params;
    private $login;
    private $pass;
    private $authData;
    private $scope;
    private $id;
    private $client;
    public function __construct($login, $pass, $authData, CommonParams $params, $scope = "audio,offline") {
        $this->params = $params;
        $this->login = $login;
        $this->pass = $pass;
        $this->authData = $authData;
        $this->scope = urlencode($scope);
        $this->client = SupportedClients::Kate();
    }

    public function getToken(){
        $receipt = $this->getReceipt();
        $token = $this->getNonRefreshed();
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
            "&v=5.72&scope=" . $this->scope
        );
        $dec = json_decode(curl_exec($this->params->curl));
        if(!isset($dec->user_id)){
            throw new TokenException(TokenException::TOKEN_NOT_RECEIVED, $dec);
        }
        $this->id = $dec->user_id;
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
            "X-app_ver" => "443",
            "X-kid" => "|ID|1|",
            "X-appid" => $this->generateRandomString(11),
            "X-gmsv" => "13283005",
            "X-cliv" => "iid-10084000",
            "X-app_ver_name" => "51.2 lite",
            "X-X-kid" => "|ID|1|",
            "X-subscription" => "54740537194",
            "X-X-subscription" => "54740537194",
            "X-X-subtype" => "54740537194",
            "app" => "com.perm.kate_new_6",
            "sender" => "54740537194",
            "device" => $this->authData['id'],
            "cert" => "966882ba564c2619d55d0a9afd4327a38c327456",
            "app_ver" => "443",
            "info" => "g57d5w1C4CcRUO6eTSP7b7VoT8yTYhY",
            "gcm_ver" => "13283005",
            "plat" => "0",
            "X-messenger2" => "1"
        );
        curl_setopt($this->params->curl, CURLOPT_POSTFIELDS,
            http_build_query($paramsArr));
        curl_exec($this->params->curl);
        $paramsArr["X-scope"] = "id" . $this->id;
        $paramsArr["X-kid"] = $paramsArr["X-X-kid"] = "|ID|2|";
        curl_setopt($this->params->curl, CURLOPT_POSTFIELDS,
            http_build_query($paramsArr));
        $str = curl_exec($this->params->curl);
        $res = explode('|ID|2|:', $str)[1];
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
                  "&receipt=" . $receipt . "&v=5.72"
        );
        $dec = json_decode(curl_exec($this->params->curl));
        $newToken = $dec->response->token;
        if($newToken == $token){
            throw new TokenException(TokenException::TOKEN_NOT_REFRESHED);
        }
        return $newToken;
    }

    private function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_-';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}