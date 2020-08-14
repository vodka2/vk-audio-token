<?php

namespace Vodka2\VKAudioToken;

class TokenReceiverOfficial {
    private $params;
    private $login;
    private $pass;
    private $scope;
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
        curl_setopt(
            $this->params->curl,
            CURLOPT_URL,
            "https://oauth.vk.com/token?grant_type=password".
            "&client_id=".$this->client->getClientId().
            "&client_secret=".$this->client->getClientSecret().
            "&username=".urlencode($this->login)."&password=".urlencode($this->pass) .
            "&v=5.116&scope=".$this->scope."&lang=en&".
            $this->params->getTwoFactorPart($this->authCode).
            "&lang=en&device_id=".$this->params->generateRandomString(16, '0123456789abcdef')
        );
        $dec = json_decode(curl_exec($this->params->curl));
        if(isset($dec->error) && $dec->error == 'need_validation') {
            throw new TokenException(TokenException::TWOFA_REQ, $dec);
        }
        if(!isset($dec->user_id)){
            throw new TokenException(TokenException::TOKEN_NOT_RECEIVED, $dec);
        }
        return [$dec->access_token];
    }
}