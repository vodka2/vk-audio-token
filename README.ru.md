# Код для получения токена VK по данным GMS

Код позволяет получить токен VK, который подходит для запрашивания URI аудиозаписей, по логину VK, паролю VK, GMS ID и GMS токену. Код получен в результате анализа приложения Kate Mobile. Никаких зависимостей устанавливать не требуется.

Примеры получения токена находится в файлах в директории `examples` Также в них находится код получения данных GMS (Checkin). Запустите какой-нибудь скрипт таким образом: `example_microg.php login pass`, и он выведет токен.

Также есть и специальный скрипт со следующими опциями:
```
Usage: src/cli/vk-audio-token.php [options] vk_login vk_pass
Options:
-s file             - save GMS ID and token to the file
-l file             - load GMS ID and token from file
-g gms_id:gms_token - use specified GMS ID and token
-d file             - use droidguard string from file
                      instead of hardcoded one
-m                  - make microG checkin
                      by default checkin
                      with droidguard string is made
-h                  - print this help
```

Вообще есть два способа получения данных GMS. 

Можно вытащить их из Android устройства с root, на котором установлены сервисы Google. Токен находится в `/data/data/com.google.android.gsf/shared_prefs/CheckinService.xml`, ID в `/data/data/com.google.android.gms/shared_prefs/Checkin.xml`. Можно установить на устройство приложение [GMS Credentials](https://github.com/vodka2/gms-credentials) посмотреть значения токена и ID.

Также можно самим получить эти данные GMS, сделав «правильный» Checkin. Для этого предназначен класс `AndroidCheckin`. Он предоставляет два способа: checkin с помощью строки droidguard и как в проекте [microG](https://github.com/microg). Для первого способа нужна строка, которую генерирует `com.google.ccc.abuse.droidguard` (the.apk). Одна такая строка приведена в `example_droidguard_str.php`, не знаю, сколько раз с её помощью можно сделать Checkin. При использовании второго способа делается дополнительный запрос и PHP должен быть настроен с поддержкой сокетов. Полученные данные GMS можно использовать лишь в течение какого-то периода времени.

Ещё можно перехватить Checkin запрос, который делает устройство при первом запуске, и посмотреть GMS ID и токен.