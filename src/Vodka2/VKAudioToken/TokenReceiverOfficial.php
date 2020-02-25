<?php

namespace Vodka2\VKAudioToken;

class TokenReceiverOfficial {
    private $params;
    private $login;
    private $pass;
    private $scope;
    private $id;
    private $client;
    private $authCode;
    public function __construct($login, $pass, CommonParams $params, $authCode = "", $scope = "all") {
        $this->params = $params;
        $this->login = $login;
        $this->pass = $pass;
        $this->authCode = $authCode;
        $this->scope = urlencode($scope);
        $this->client = SupportedClients::VkOfficial();
    }

    public function getToken(){
        return $this->getNonRefreshed();
    }

    private function getNonRefreshed(){
        curl_reset($this->params->curl);
        $this->params->setCommonVK();
        $deviceId = $this->generateRandomString(16, '0123456789abcdef');
        curl_setopt(
            $this->params->curl,
            CURLOPT_URL,
            "https://oauth.vk.com/token?grant_type=password".
            "&client_id=".$this->client->getClientId().
            "&client_secret=".$this->client->getClientSecret().
            "&username=".urlencode($this->login)."&password=".urlencode($this->pass) .
            "&v=5.116&scope=".$this->scope."&lang=en&".
            $this->params->getTwoFactorPart($this->authCode).
            "&lang=en&device_id=".$deviceId
        );
        $dec = json_decode(curl_exec($this->params->curl));
        if(isset($dec->error) && $dec->error == 'need_validation') {
            throw new TokenException(TokenException::TWOFA_REQ, $dec);
        }
        if(!isset($dec->user_id)){
            throw new TokenException(TokenException::TOKEN_NOT_RECEIVED, $dec);
        }
        $this->id = $dec->user_id;
        return [$dec->access_token, $deviceId];
    }

    private function generateRandomString($length, $characters) {
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}