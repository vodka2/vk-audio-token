<?php

namespace Vodka2\VKAudioToken;

class VkClient {
    private $userAgent;
    private $clientSecret;
    private $clientId;
    public function __construct($userAgent, $clientSecret, $clientId){
        $this->userAgent = $userAgent;
        $this->clientSecret = $clientSecret;
        $this->clientId = $clientId;
    }

    public function getUserAgent()
    {
        return $this->userAgent;
    }

    public function getClientSecret()
    {
        return $this->clientSecret;
    }

    public function getClientId()
    {
        return $this->clientId;
    }
}