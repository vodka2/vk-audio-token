<?php
namespace Vodka2\VKAudioToken;

class MTalkClient{
    private $authData;
    private $protoHelper;
    const SUCCESS_RESPONSE_CODE = 3;

    public function __construct($authData, $protoHelper){
        $this->authData = $authData;
        $this->protoHelper = $protoHelper;
    }

    public function sendRequest(){
        $socket = stream_socket_client("ssl://mtalk.google.com:5228", $errno, $errstr);
        if($socket){
            fwrite($socket, $this->protoHelper->getMTalkRequest($this->authData));
            fflush($socket);
            fread($socket, 1);
            $respCode = ord(fread($socket, 1));
            fclose($socket);
            if($respCode != self::SUCCESS_RESPONSE_CODE){
                throw new MTalkException(MTalkException::WRONG_RESPONSE, $respCode);
            }
        } else {
            throw new MTalkException(MTalkException::CANT_OPEN_SOCKET, $errno . " ". $errstr);
        }
    }
}