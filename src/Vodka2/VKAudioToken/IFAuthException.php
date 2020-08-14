<?php
namespace Vodka2\VKAudioToken;


class IFAuthException extends \Exception {
    const LOGIN_FORM_NOT_FOUND = 0;
    const TOKEN_NOT_FOUND = 1;
    const BAD_LOGIN_FORM = 2;
    const CONFIRM_FORM_NOT_FOUND = 3;
    const NO_REMIXSID = 4;
    const TWOFA_REQ = 5;
    const TWOFA_ERROR = 6;
    const TWOFA_FORM_NOT_FOUND = 7;
    const HASH_NOT_FOUND = 8;

    public $extra;
    public $code;

    public function __construct($code, $extra = false){
        $this->code = $code;
        $this->extra = $extra;
        $extraDump = var_export($extra, true);
        if ($code == self::LOGIN_FORM_NOT_FOUND) {
            parent::__construct("Login form was not found. Extra: $extraDump", $code);
        } else if ($code == self::TOKEN_NOT_FOUND) {
            parent::__construct("Token was not found. Extra: $extraDump", $code);
        } else if ($code == self::BAD_LOGIN_FORM) {
            parent::__construct("Bad login form, no login and/or pass fields. Extra: $extraDump", $code);
        } else if ($code == self::CONFIRM_FORM_NOT_FOUND) {
            parent::__construct("Confirmation form was not found. Extra: $extraDump", $code);
        } else if ($code == self::NO_REMIXSID) {
            parent::__construct("No remixsid. Extra: $extraDump", $code);
        } else if ($code == self::TWOFA_REQ) {
            parent::__construct("Two factor auth is required. Extra with cookies: $extraDump", $code);
        } else if ($code == self::TWOFA_ERROR) {
            parent::__construct("Two factor auth error. Extra: $extraDump", $code);
        } else if ($code == self::TWOFA_FORM_NOT_FOUND) {
            parent::__construct("Two factor form was not found. Extra: $extraDump", $code);
        } else if ($code == self::HASH_NOT_FOUND) {
            parent::__construct("Hash was not found. Extra: $extraDump", $code);
        }
    }
}