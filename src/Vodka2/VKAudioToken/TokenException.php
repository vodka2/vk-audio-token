<?php
namespace Vodka2\VKAudioToken;

class TokenException extends \Exception
{
    const REGISTRATION_ERROR = 0;
    const TOKEN_NOT_REFRESHED = 1;
    const TOKEN_NOT_RECEIVED = 2;
    const REQUEST_ERR = 3;

    public $extra;

    public function __construct($code, $extra = false){
        if($code == self::REGISTRATION_ERROR){
            parent::__construct('Registration error', $code);
        } else if($code == self::TOKEN_NOT_REFRESHED){
            parent::__construct('Token was not refreshed, tokens are the same', $code);
        } else if($code == self::TOKEN_NOT_RECEIVED){
            $extraDump = var_export($extra, true);
            parent::__construct("Can't obtain token. Error extra: $extraDump", $code);
        } else if($code == self::REQUEST_ERR){
            $extraDump = var_export($extra, true);
            parent::__construct("Error when making request. Error extra: $extraDump", $code);
        }
        $this->extra = $extra;
    }
}