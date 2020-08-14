<?php

namespace Vodka2\VKAudioToken;

class SupportedClients {
    public static function Kate(){
        return new VkClient(
            "KateMobileAndroid/56 lite-460 (Android 4.4.2; SDK 19; x86; unknown Android SDK built for x86; en)",
            "lxhD8OD7dMsqtXIm5IUY",
            "2685278"
        );
    }
    public static function VkOfficial(){
        return new VkClient(
            "VKAndroidApp/5.52-4543 (Android 5.1.1; SDK 22; x86_64; unknown Android SDK built for x86_64; en; 320x240)",
            "hHbZxrka2uZ6jB1inYsH",
            "2274003"
        );
    }
    public static function Boom(){
        return new VkClient(
            "VK_Music/4.2.1 (Android 5.1.1; SDK 22; x86_64; unknown Android SDK built for x86_64; en; 320x240)",
            "",
            "4705861"
        );
    }
}