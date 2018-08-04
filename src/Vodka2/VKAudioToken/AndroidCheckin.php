<?php

namespace Vodka2\VKAudioToken;

class AndroidCheckin {
    private $params;
    private $protoHelper;
    private $str24;
    public function __construct(CommonParams $params, $protoHelper, $str24 = false) {
        $this->params = $params;
        $this->protoHelper = $protoHelper;
        $this->str24 = $str24;
    }

    public function doCheckin(){
        curl_reset($this->params->curl);
        $this->params->setCommonGCM();
        curl_setopt(
            $this->params->curl,
            CURLOPT_URL,
            "https://android.clients.google.com/checkin"
        );
        curl_setopt($this->params->curl, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/x-protobuffer",
            "Content-Encoding: gzip",
            "Expect:"
        ));
        curl_setopt($this->params->curl, CURLOPT_POST, 1);
        curl_setopt($this->params->curl, CURLOPT_ENCODING, 'gzip');
        curl_setopt($this->params->curl, CURLOPT_POSTFIELDS, gzencode($this->protoHelper->getQueryMessage($this->str24)));
        return $this->protoHelper->decodeRespMessage(curl_exec($this->params->curl), $this->str24 === false);
    }
}