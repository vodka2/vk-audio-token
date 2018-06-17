<?php
namespace Vodka2;

class TokenException extends \Exception
{
    const REGISTRATION_ERROR = 0;
    const TOKEN_NOT_REFRESHED = 1;
    public function __construct($code){
        if($code == self::REGISTRATION_ERROR){
            parent::__construct('Registration error', $code);
        } else if($code == self::TOKEN_NOT_REFRESHED){
            parent::__construct('Token was not refreshed, tokens are the same', $code);
        }
    }
}