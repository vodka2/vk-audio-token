<?php
namespace Vodka2\VKAudioToken;


class IFAuth2FAData {
    private $cookies;
    private $field;
    private $authCode;

    public static function withRetry($base64State) {
        return new IFAuth2FAData($base64State, "");
    }

    function __construct($base64State, $authCode = "") {
        $state = json_decode(base64_decode($base64State), true);
        $this->action = $state['action'];
        $this->cookies = $state['cookies'];
        $this->field= $state['field'];
        $this->authCode = $authCode;
    }

    public function getAction() {
        return $this->action;
    }

    public function getField() {
        return $this->field;
    }

    public function getCookies() {
        return $this->cookies;
    }

    public function isRetry() {
        return ($this->authCode === "");
    }

    public function getAuthCode() {
        return $this->authCode;
    }
}