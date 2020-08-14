<?php

namespace Vodka2\VKAudioToken;

// Some ideas from https://github.com/python273/vk_api
class IFAuth {
    private $scope;
    private $login;
    private $pass;
    private $vkClient;
    private $params;
    private $ifAuth2FaData;

    public static function with2FA($commonParams, $vkClient, $ifAuth2FaData, $scope = "audio,offline") {
        return new IFAuth("", "", $commonParams, $vkClient, $scope, $ifAuth2FaData);
    }

    public function __construct($login, $pass, $commonParams, $vkClient, $scope = "audio,offline", $ifAuth2FaData = false) {
        $this->login = $login;
        $this->pass = $pass;
        $this->vkClient = $vkClient;
        $this->params = $commonParams;
        $this->ifAuth2FaData = $ifAuth2FaData;
        $this->scope = urlencode($scope);
    }

    private function populateCookies($result, &$cookies) {
        preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $result, $matches);
        foreach($matches[1] as $header) {
            foreach (explode(";", $header) as $cookie) {
                $cookies[] = $cookie;
            }
        }
        foreach ($cookies as $index => $cookie) {
            if (strpos($cookie, "=DELETED") !== false) {
                unset($cookies[$index]);
            }
        }
    }

    private function findFormData($result, $errCode = false) {
        $doc = new \DOMDocument();
        @$doc->loadHTML($result);
        $forms = $doc->getElementsByTagName("form");
        if (count($forms) == 0) {
            if ($errCode === false) {
                return array();
            } else {
                throw new IFAuthException($errCode, $result);
            }
        }
        $form = $forms[0];
        $inputs = $form->getElementsByTagName("input");
        $result = array ('action' => $form->getAttribute("action"));
        foreach ($inputs as $input) {
            if ($input->getAttribute('type') == "text" || $input->getAttribute('type') == "email") {
                $result['login'] = $input->getAttribute('name');
            } else if ($input->getAttribute('type') == "password") {
                $result['pass'] = $input->getAttribute('name');
            }
            if (!isset($result['first'])) {
                $result['first'] = $input->getAttribute('name');
            }
        }
        return $result;
    }

    private function checkRemixsid($cookies) {
        foreach ($cookies as $cookie) {
            if (strpos($cookie, 'remixsid') === 0) {
                return true;
            }
        }
        return false;
    }

    private function execWithCookies(&$cookies) {
        $this->setCookies($cookies);
        $result = curl_exec($this->params->curl);
        $this->populateCookies($result, $cookies);
        return $result;
    }

    private function check2FA($result, $cookies) {
        $formData = $this->findFormData($result);
        if (isset($formData['action'])) {
            $doc = new \DOMDocument();
            @$doc->loadHTML($result);
            $xpath = new \DOMXPath($doc);
            $message = "";
            $texts = $xpath->query("//form/preceding::div[string-length(text()[1]) > 5 and position() < 4]");
            foreach ($texts as $text) {
                $message .= $text->textContent;
            }
            throw new IFAuthException(IFAuthException::TWOFA_REQ, array(
                'message' => $message,
                'result' => $result,
                'base64State' => base64_encode(json_encode(
                    array('action' => $this->addPrefix($formData['action']),
                        'cookies' => $cookies, 'field' => $formData['first'])))
            ));
        }
    }

    private function getAuthCookies() {
        curl_reset($this->params->curl);
        $this->params->setCommonVK();
        curl_setopt($this->params->curl, CURLOPT_HEADER, 1);
        curl_setopt($this->params->curl, CURLOPT_URL, "https://m.vk.com");
        $cookies = array ();
        $result = $this->execWithCookies($cookies);
        $formData = $this->findFormData($result, IFAuthException::LOGIN_FORM_NOT_FOUND);
        if (empty($formData['login']) || empty($formData['pass'])) {
            throw new IFAuthException(IFAuthException::BAD_LOGIN_FORM, $result);
        }

        curl_setopt($this->params->curl, CURLOPT_URL, $formData['action']);
        curl_setopt($this->params->curl, CURLOPT_POST, 1);
        curl_setopt($this->params->curl, CURLOPT_POSTFIELDS,
            http_build_query(array ($formData['login'] => $this->login, $formData['pass'] => $this->pass)));
        curl_setopt($this->params->curl, CURLOPT_HTTPHEADER, array(
            'content-type: application/x-www-form-urlencoded',
        ));
        $result = $this->execWithCookies($cookies);
        $this->tryFollowLocation($result, $cookies);

        if (!$this->checkRemixsid($cookies)) {
            $this->tryFollowLocation($result, $cookies);
            $this->check2FA($result, $cookies);
        }

        if (!$this->checkRemixsid($cookies)) {
            throw new IFAuthException(IFAuthException::NO_REMIXSID, $result);
        }

        return $cookies;
    }

    private function addPrefix($path) {
        if ($path[0] == '/') {
            return $path = "https://m.vk.com" . $path;
        } else {
            return $path;
        }
    }

    private function tryFollowLocation(&$result, &$cookies) {
        if (preg_match('/^Location:\s*(.+)/mi', $result, $matches)) {
            $location = trim($matches[1]);
            $location = $this->addPrefix($location);
            curl_reset($this->params->curl);
            $this->params->setCommonVK();
            curl_setopt($this->params->curl, CURLOPT_HEADER, 1);
            curl_setopt($this->params->curl, CURLOPT_URL, $location);

            $result = $this->execWithCookies($cookies);
            return true;
        }
        return false;
    }

    private function checkTokenLocation($result, &$token, &$userId) {
        if (preg_match('~^Location:.+?#access_token=(.+?)&.+?&user_id=(\d+)~mi', $result, $matches)) {
            $token = $matches[1];
            $userId = $matches[2];
            return true;
        }
        return false;
    }

    private function setCookies($cookies) {
        curl_setopt($this->params->curl, CURLOPT_COOKIE, join("; ", $cookies));
    }

    private function do2FA() {
        curl_reset($this->params->curl);
        $this->params->setCommonVK();
        curl_setopt($this->params->curl, CURLOPT_HEADER, 1);
        $cookies = $this->ifAuth2FaData->getCookies();
        if ($this->ifAuth2FaData->isRetry()) {
            curl_setopt(
                $this->params->curl, CURLOPT_URL,
                "https://m.vk.com/login?act=authcheck&help_opened"
            );
            $result = $this->execWithCookies($cookies);
            if (preg_match("~authcheck_sms&hash=([_A-Za-z0-9]+)~", $result, $matches)) {
                $hash = $matches[1];
                curl_setopt(
                    $this->params->curl, CURLOPT_URL,
                    "https://m.vk.com/login?act=authcheck_sms&hash=$hash"
                );
                $result = $this->execWithCookies($cookies);
                $this->tryFollowLocation($result, $cookies);
                $this->check2FA($result, $cookies);
                throw new IFAuthException(IFAuthException::TWOFA_FORM_NOT_FOUND, $result);
            } else {
                throw new IFAuthException(IFAuthException::HASH_NOT_FOUND);
            }
        } else {
            curl_setopt($this->params->curl, CURLOPT_URL, $this->ifAuth2FaData->getAction());
            curl_setopt($this->params->curl, CURLOPT_POST, 1);
            curl_setopt($this->params->curl, CURLOPT_POSTFIELDS,
                http_build_query(array ($this->ifAuth2FaData->getField() => $this->ifAuth2FaData->getAuthCode())));
            curl_setopt($this->params->curl, CURLOPT_HTTPHEADER, array(
                'content-type: application/x-www-form-urlencoded',
            ));
            $result = $this->execWithCookies($cookies);
            $this->tryFollowLocation($result, $cookies);

            if (!$this->checkRemixsid($cookies)) {
                throw new IFAuthException(IFAuthException::NO_REMIXSID, $result);
            }

            return $cookies;
        }
    }

    public function getTokenAndId() {
        if ($this->ifAuth2FaData !== false) {
            $cookies = $this->do2FA();
        } else {
            $cookies = $this->getAuthCookies();
        }
        curl_reset($this->params->curl);
        $this->params->setCommonVK();
        curl_setopt($this->params->curl, CURLOPT_HEADER, 1);
        curl_setopt($this->params->curl, CURLOPT_URL,
            "https://oauth.vk.com/authorize?client_id={$this->vkClient->getClientId()}" .
            "&scope=$this->scope&redirect_uri=https://oauth.vk.com/blank.html&display=mobile" .
            "&v=5.95&response_type=token");
        $result = $this->execWithCookies($cookies);

        if (!$this->tryFollowLocation($result, $cookies)) {
            $formData = $this->findFormData($result, IFAuthException::CONFIRM_FORM_NOT_FOUND);
            curl_setopt($this->params->curl, CURLOPT_URL, $formData['action']);
            curl_setopt($this->params->curl, CURLOPT_POST, 1);
            $this->setCookies($cookies);
            curl_setopt($this->params->curl, CURLOPT_HTTPHEADER, array(
                'content-type: application/x-www-form-urlencoded',
            ));
            $result = curl_exec($this->params->curl);
        }
        if ($this->checkTokenLocation($result, $token, $userId)) {
            return array('token' => $token, 'userId' => $userId);
        } else {
            throw new IFAuthException(IFAuthException::TOKEN_NOT_FOUND, $result);
        }
    }
}