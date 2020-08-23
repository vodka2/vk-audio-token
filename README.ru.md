# Библиотека для получения токена VK для VK audio API

Версия на Python: [vodka2/vkaudiotoken-python](https://github.com/vodka2/vkaudiotoken-python)

Библиотека позволяет получить токен VK, который подходит для работы с VK audio API: запрашивания URI аудиозаписей, поиска исполнителей, альбомов и т.д. Поддерживается эмуляция Kate Mobile, Boom, официального клиента VK. (Спасибо YTKABOBR за реверсинг Boom)

Есть две версии API: которую использует Kate Mobile и официальный клиент VK. Boom использует ту же версию, что и Kate, но в ней есть ограничения, не все методы поддерживаются. Более того, требуется разрешение `messages` (анализируются личные сообщения?) и иногда возвращаются 500 ошибки. С другой стороны, Boom поддерживает и другой API, помимо VK API, и может использоваться как «запасной вариант».

## Установка

```
composer require vodka2/vk-audio-token
```

... или просто скопируйте куда-нибудь загруженный исходный код и подключите `src/autoloader.php`. Библиотека не требует никаких зависимостей.

## Получение токенов

Простейший пример:

```php
<?php

use Vodka2\VKAudioToken\TokenFacade;

$login = "+71234567890";
$pass = "12345";

// выводит токен и User-Agent. 
// нужно всегда устанавливать User-Agent при запросах к API!
var_export(TokenFacade::getKateToken($login, $pass));
```

Более сложные примеры находятся в каталоге `examples`. Можно начать с `example_simple.php`.


## Использование токенов

Простейший пример:

```php
<?php

define('TOKEN', 'токен из предыдущего примера');
define('USER_AGENT', 'User-Agent из предыдущего примера');
$ch = curl_init();

curl_setopt($ch, CURLOPT_HTTPHEADER, array('User-Agent: '.USER_AGENT));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

curl_setopt(
    $ch,
    CURLOPT_URL,
    "https://api.vk.com/method/audio.getById?access_token=".TOKEN.
    "&audios=".urlencode("371745461_456289486").
    "&v=5.95"
);

echo json_encode(json_decode(curl_exec($ch)), JSON_PRETTY_PRINT)."\n\n";
```

Другие примеры, показывающие, как использовать токены с различными методами API находятся в каталоге `usage`. Более подробное описание API VK: [https://vodka2.github.io/vk-audio-token/](https://vodka2.github.io/vk-audio-token/) (пока что в процессе составления)

## Скрипт командной строки

Есть специальный скрипт со следующими опциями (эмулирующий Kate Mobile):
```
Usage: src/cli/vk-audio-token.php [options] vk_login vk_pass
       src/cli/vk-audio-token.php [options] non_refreshed_kate_token
Options:
-s file             - save GMS ID and token to the file
-l file             - load GMS ID and token from file
-g gms_id:gms_token - use specified GMS ID and token
-d file             - use droidguard string from file
                      instead of hardcoded one
-m                  - make microG checkin (default)
-o                  - old checkin with droidguard string
                      that may expire
                      with droidguard string is made
-t code             - use two factor authentication
                      pass GET_CODE to get code or
                      pass code received in SMS
-h                  - print this help
```

## Docker
```
docker build -t vk-audio-tokens src/
docker run -t vk-audio-tokens:latest php src/cli/vk-audio-token.php -m vk_login vk_pass
docker run -t vk-audio-tokens:latest php src/examples/usage/example_kate.php token
```

## 2FA

Для официального клиента и Kate поддерживается двухфакторная авторизация, но сервер VK не всегда отправляет SMS. В этом случае можно использовать класс `TwoFAHelper` для переотправки. Пример использования — `example_twofahelper.php`.

Для Boom библиотека использует implicit flow и обращается к сайту VK. VK может позвонить, отправить SMS или личное сообщение аккаунту. Также можно авторизовать Boom самому и передать библиотеке только токен и id пользователя.

Кроме того можно создать пароль для приложения в настройках ВК и использовать его вместо пароля аккаунта.

## Данные GMS

При получении токенов дополнительно получаются данные GMS. Есть два способа их получения. 

Можно вытащить их из Android устройства с root, на котором установлены сервисы Google. Токен находится в `/data/data/com.google.android.gsf/shared_prefs/CheckinService.xml`, ID в `/data/data/com.google.android.gms/shared_prefs/Checkin.xml`. Можно установить на устройство приложение [GMS Credentials](https://github.com/vodka2/gms-credentials) посмотреть значения токена и ID.

Также можно самим получить эти данные GMS, сделав «правильный» Checkin. Для этого предназначен класс `AndroidCheckin`. Он предоставляет два способа: checkin с помощью строки droidguard и как в проекте [microG](https://github.com/microg). Для первого способа нужна строка, которую генерирует `com.google.ccc.abuse.droidguard` (the.apk). Одна такая строка приведена в `example_droidguard_str.php`, эта строка через некоторое время может перестать работать. При использовании второго способа делается дополнительный запрос и PHP должен быть настроен с поддержкой сокетов. Полученные данные GMS можно использовать лишь в течение какого-то периода времени.

Ещё можно перехватить Checkin запрос, который делает устройство при первом запуске, и посмотреть GMS ID и токен.

## Купить автору бутылку водки!

WMR — P778046516389

WMZ — Z828082159527

[Yandex Money](https://money.yandex.ru/to/41001864186137)