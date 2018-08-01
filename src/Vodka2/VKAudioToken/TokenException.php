<?php
namespace Vodka2\VKAudioToken;

class TokenException extends \Exception
{
    const REGISTRATION_ERROR = 0;
    const TOKEN_NOT_REFRESHED = 1;
    const TOKEN_NOT_RECEIVED = 2;

    public $extra;

    public function __construct($code, $extra = false){
        if($code == self::REGISTRATION_ERROR){
            parent::__construct('Registration error', $code);
        } else if($code == self::TOKEN_NOT_REFRESHED){
            parent::__construct('Token was not refreshed, tokens are the same', $code);
        } else if($code == self::TOKEN_NOT_RECEIVED){
            $description = $extra->error_description ?: "Unknown error";
            parent::__construct("Can't obtain token. Error description: $description", $code);
        }
        $this->extra = $extra;
    }
}