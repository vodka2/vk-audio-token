<?php

namespace Vodka2\VKAudioToken;

class CommonParams {
    public $vk_ua;
    public $gcm_ua;
    public $curl;
    public function __construct($vk_ua = false, $gcm_ua = false, $curl = false) {
        if($vk_ua === false){
            $this->vk_ua =
                "KateMobileAndroid/51.2 lite-443 (Android 4.4.2; SDK 19; x86; unknown Android SDK built for x86; en)"
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

    public function setCommon(){
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, 0);
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
}
