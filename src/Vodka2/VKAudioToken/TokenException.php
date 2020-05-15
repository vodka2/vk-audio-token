<?php
namespace Vodka2\VKAudioToken;

class TokenException extends \Exception
{
    const REGISTRATION_ERROR = 0;
    const TOKEN_NOT_REFRESHED = 1;
    const TOKEN_NOT_RECEIVED = 2;
    const REQUEST_ERR = 3;
    const TWOFA_REQ = 4;
    const TWOFA_ERR = 5;

    public $extra;
    public $code;

    public function __construct($code, $extra = false){
        $this->code = $code;
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
        } else if ($code === self::TWOFA_REQ) {
            $extraDump = var_export($extra, true);
            parent::__construct("Two factor auth is required. Extra: $extraDump", $code);
        } else if ($code === self::TWOFA_ERR) {
            $extraDump = var_export($extra, true);
            parent::__construct("2FA Error. Extra: $extraDump", $code);
        }
        $this->extra = $extra;
    }
}