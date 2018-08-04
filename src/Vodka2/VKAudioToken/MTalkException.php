<?php

namespace Vodka2\VKAudioToken;

class MTalkException extends \Exception {
    const CANT_OPEN_SOCKET = 0;
    const WRONG_RESPONSE = 1;
    private $extra;
    public function __construct($code, $extra = false){
        if($code == self::CANT_OPEN_SOCKET){
            parent::__construct("Can't open socket. " . $extra, $code);
        } else if($code == self::WRONG_RESPONSE){
            parent::__construct("Wrong response code: " . $extra, $code);
        }
        $this->extra = $extra;
    }
}