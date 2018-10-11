<?php

namespace Vodka2\VKAudioToken;

class SupportedClients {
    public static function Kate(){
        return new VkClient(
            "KateMobileAndroid/51.2 lite-443 (Android 4.4.2; SDK 19; x86; unknown Android SDK built for x86; en)",
            "lxhD8OD7dMsqtXIm5IUY",
            "2685278"
        );
    }
    public static function VkOfficial(){
        return new VkClient(
            "VKAndroidApp/5.19-2792 (Android 4.4.2; SDK 19; x86; unknown Android SDK built for x86; en; 320x240)",
            "hHbZxrka2uZ6jB1inYsH",
            "2274003"
        );
    }
}