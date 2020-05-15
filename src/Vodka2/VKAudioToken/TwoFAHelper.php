<?php

namespace Vodka2\VKAudioToken;

// If SMS for 2FA is not being sent, try using this class
class TwoFAHelper {
    private $params;

    public function __construct(CommonParams $params) {
        $this->params = $params;
    }

    public function validatePhone($validationSid) {
        curl_reset($this->params->curl);
        $this->params->setCommonVK();
        curl_setopt($this->params->curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->params->curl, CURLOPT_URL, "https://api.vk.com/method/auth.validatePhone?sid={$validationSid}&v=5.95");
        $dec = json_decode(curl_exec($this->params->curl));
        if(isset($dec->error) || !isset($dec->response) || $dec->response !== 1) {
            throw new TokenException(TokenException::TWOFA_ERR, $dec);
        }
    }
}