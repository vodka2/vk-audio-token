<?php

namespace Vodka2\VKAudioToken;

class ProtobufException extends \Exception {
    const SYMBOL = 0;
    const NOT_FOUND = 1;
    public function __construct($code, $sym = ''){
        if($code == self::SYMBOL){
            parent::__construct("Unexpected symbol code: " . $sym, $code);
        } else if($code == self::NOT_FOUND){
            parent::__construct("Id and token were not found " . $sym, $code);
        }
    }
}