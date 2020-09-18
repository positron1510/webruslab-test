# webruslab-test

Отправить по почте не получилось, почему то gmail и mail ругаются на вирусы. Поэтому подтягиваем зависимости:

cd /var/www/webruslab-test

composer install


ОС: Ubuntu 18.04

php: 7.4 (на 7.2 тоже пойдет)

mysql: 5.5

memcached последняя версия из репозитория sudo apt-get install memcached

модули php: php7.4-mysql, php7.4-memcached

Фреймворк Laravel 5.5 решил использовать фреймворк чтобы не затягивать время и не изобретать велосипедов

Конфигурации laravel в файле .env здесь находятся конфиги подключения к базе и движок кэша memcached

Дамп с базой dump.sql в корне проекта

DB_CONNECTION=mysql

DB_HOST=127.0.0.1

DB_PORT=3406

DB_DATABASE=posts

DB_USERNAME=root

DB_PASSWORD=root


...

CACHE_DRIVER=memcached

...

База данных: база posts, пользователь root, пароль root

Web-сервер: nginx+php-fpm

Папка с проектом будем считать находится по адресу /var/www/webruslab-test

Роуты: routes/web.php

Контроллеры: app/Http/Controllers

Консольные команды: app/Console/Commands


Список постов находится по роуту /posts, дальше переходы по каждому посту по клику

Для того чтобы выполнялось cron-задание по сохранению просмотров в базе (периодичность 1 минута)

crontab -e

прописываем

* * * * * php /var/www/webruslab-test/artisan schedule:run >>/dev/null 2>&1

Консольная команда для выполнения сохранения просмотров и сброса кэша

cd /var/www/webruslab-test

php artisan views:save

Вроде бы всё. Описание методов классов постарался подробно закомментировать
