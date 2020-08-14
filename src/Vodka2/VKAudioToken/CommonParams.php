<?php

namespace Vodka2\VKAudioToken;

class CommonParams {
    public $vk_ua;
    public $gcm_ua;
    public $curl;
    public function __construct($vk_ua = false, $gcm_ua = false, $curl = false) {
        if($vk_ua === false){
            $this->vk_ua =
                SupportedClients::Kate()->getUserAgent()
            ;
        } else {
            $this->vk_ua = $vk_ua;
        }

        if($gcm_ua === false){
            $this->gcm_ua =
                "Android-GCM/1.5 (generic_x86 KK)"
            ;
        } else {
            $this->gcm_ua = $gcm_ua;
        }

        if($curl === false){
            $this->curl = curl_init();
        } else {
            $this->curl = $curl;
        }
    }

    public function getTwoFactorPart($code) {
        return empty($code) ? "" : '&2fa_supported=1&force_sms=1' .
            (($code === 'GET_CODE') ? '' : '&code=' . urlencode($code));
    }

    public function setCommon(){
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
    }

    public function setCommonVK(){
        $this->setCommon();
        curl_setopt($this->curl, CURLOPT_USERAGENT, $this->vk_ua);
    }

    public function setCommonGCM(){
        $this->setCommon();
        curl_setopt($this->curl, CURLOPT_USERAGENT, $this->gcm_ua);
    }

    public function generateRandomString($length, $characters) {
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}
