<?php

namespace Vodka2;

class CommonParams {
    public $ua;
    public $curl;
    public function __construct($ua = false, $curl = false) {
        if($ua === false){
            $this->ua =
                "KateMobileAndroid/40.4 lite-394 (Android 4.4.2; SDK 19; x86; unknown Android SDK built for x86; en)"
            ;
        } else {
            $this->ua = $ua;
        }

        if($curl === false){
            $this->curl = curl_init();
        } else {
            $this->curl = $curl;
        }
    }

    public function setCommon(){
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($this->curl, CURLOPT_USERAGENT, $this->ua);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
    }
}
