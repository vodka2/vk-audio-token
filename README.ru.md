# Код для получения токена VK по данным GMS

Код позволяет получить токен VK, который подходит для запрашивания URI аудиозаписей, по логину VK, паролю VK, GMS ID и GMS токену (последние два нужны только для метода, основанного на Kate Mobile.) Код получен в результате анализа приложения Kate Mobile и официального клиента ВК. Никаких зависимостей устанавливать не требуется.

Примеры получения токена находится в файлах в директории `examples` Также в них находится код получения данных GMS (Checkin). Запустите какой-нибудь скрипт таким образом: `example_microg.php login pass`, и он выведет токен. Ещё есть примеры использования токена — вызовы различных методов API в подкаталоге `usage`.

Также есть и специальный скрипт со следующими опциями (эмулирующий Kate Mobile):
```
Usage: src/cli/vk-audio-token.php [options] vk_login vk_pass
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

Двухфакторная авторизация поддерживается, кроме того можно создать пароль для приложения в настройках ВК и использовать его вместо пароля аккаунта.

## Docker
```
docker build -t vk-audio-tokens src/
docker run -t vk-audio-tokens:latest php src/cli/vk-audio-token.php -m vk_login vk_pass
docker run -t vk-audio-tokens:latest php src/examples/usage/example_kate.php token
```

## Данные GMS

Вообще есть два способа получения данных GMS. 

Можно вытащить их из Android устройства с root, на котором установлены сервисы Google. Токен находится в `/data/data/com.google.android.gsf/shared_prefs/CheckinService.xml`, ID в `/data/data/com.google.android.gms/shared_prefs/Checkin.xml`. Можно установить на устройство приложение [GMS Credentials](https://github.com/vodka2/gms-credentials) посмотреть значения токена и ID.

Также можно самим получить эти данные GMS, сделав «правильный» Checkin. Для этого предназначен класс `AndroidCheckin`. Он предоставляет два способа: checkin с помощью строки droidguard и как в проекте [microG](https://github.com/microg). Для первого способа нужна строка, которую генерирует `com.google.ccc.abuse.droidguard` (the.apk). Одна такая строка приведена в `example_droidguard_str.php`, эта строка через некоторое время может перестать работать. При использовании второго способа делается дополнительный запрос и PHP должен быть настроен с поддержкой сокетов. Полученные данные GMS можно использовать лишь в течение какого-то периода времени.

Ещё можно перехватить Checkin запрос, который делает устройство при первом запуске, и посмотреть GMS ID и токен.

## Купить автору бутылку водки

Бесплатные напитки очень мотивируют к написанию кода.

WMR — P778046516389

WMZ — Z828082159527

[Yandex Money](https://money.yandex.ru/to/41001864186137)