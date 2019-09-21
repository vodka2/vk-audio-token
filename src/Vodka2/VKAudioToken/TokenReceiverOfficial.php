<?php

namespace Vodka2\VKAudioToken;

class TokenReceiverOfficial {
    private $params;
    private $login;
    private $pass;
    private $scope;
    private $id;
    private $client;
    private $authCode;
    public function __construct($login, $pass, CommonParams $params, $authCode = "", $scope = "nohttps,all") {
        $this->params = $params;
        $this->login = $login;
        $this->pass = $pass;
        $this->authCode = $authCode;
        $this->scope = urlencode($scope);
        $this->client = SupportedClients::VkOfficial();
    }

    public function getToken(){
        list($token, $secret, $deviceId) = $this->getNonRefreshed();
        return [$this->refreshToken($token, $secret, $deviceId), $secret, $deviceId];
    }

    private function getNonRefreshed(){
        curl_reset($this->params->curl);
        $this->params->setCommonVK();
        $deviceId = $this->generateRandomString(16, '0123456789abcdef');
        curl_setopt(
            $this->params->curl,
            CURLOPT_URL,
            "https://oauth.vk.com/token?grant_type=password".
            "&client_id=".$this->client->getClientId().
            "&client_secret=".$this->client->getClientSecret().
            "&username=".urlencode($this->login)."&password=".urlencode($this->pass) .
            "&v=5.93&scope=".$this->scope."&lang=en&".
            $this->params->getTwoFactorPart($this->authCode).
            "&lang=en&device_id=".$deviceId
        );
        $dec = json_decode(curl_exec($this->params->curl));
        if(isset($dec->error) && $dec->error == 'need_validation') {
            throw new TokenException(TokenException::TWOFA_REQ, $dec);
        }
        if(!isset($dec->user_id)){
            throw new TokenException(TokenException::TOKEN_NOT_RECEIVED, $dec);
        }
        $this->id = $dec->user_id;
        return [$dec->access_token, $dec->secret, $deviceId];
    }

    private function refreshToken($token, $secret, $deviceId){
        $this->performRequest(
            'execute.getUserInfo',
            [
                "v" => "5.93",
                "https" => "1",
                "androidVersion" => "19",
                "androidModel" => "Android SDK built for x86",
                "info_fields" => "audio_ads,audio_background_limit,country,discover_design_version,discover_preload,discover_preload_not_seen,gif_autoplay,https_required,inline_comments,intro,lang,menu_intro,money_clubs_p2p,money_p2p,money_p2p_params,music_intro,audio_restrictions,profiler_settings,raise_to_record_enabled,stories,masks,subscriptions,support_url,video_autoplay,video_player,vklive_app,community_comments,webview_authorization,story_replies,animated_stickers,community_stories,live_section,playlists_download,calls,security_issue,eu_user,wallet,vkui_community_create,vkui_profile_edit,vkui_community_manage,vk_apps,stories_photo_duration,stories_reposts,live_streaming,live_masks,camera_pingpong,role,video_discover",
                "device_id" => $deviceId,
                "lang" => "en",
                "func_v" => "11",
                "androidManufacturer" => "unknown",
                "fields" => "photo_100,photo_50,exports,country,sex,status,bdate,first_name_gen,last_name_gen,verified,trending",
                "access_token" => $token
            ],
            $secret
        );
        return $this->performRequest(
            'auth.refreshToken',
            [
                "v" => "5.93",
                "https" => "1",
                "timestamp" => "0",
                "receipt2" => "",
                "device_id" => $deviceId,
                "receipt" => "",
                "lang" => "en",
                "nonce" => "",
                "access_token" => $token
            ],
            $secret
        )->response->token;
    }

    private function performRequest($method, $postFields, $secret){
        $curl = $this->params->curl;
        curl_reset($curl);
        $this->params->setCommonVK();
        curl_setopt($curl, CURLOPT_URL, "https://api.vk.com/method/$method");
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS,
            http_build_query($postFields)."&sig=".
            md5("/method/$method?".http_build_query($postFields).$secret)
        );
        $res = json_decode(curl_exec($curl));
        if(isset($res->error)){
            throw new TokenException(TokenException::REQUEST_ERR, $res);
        }
        return $res;
    }

    private function generateRandomString($length, $characters) {
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}