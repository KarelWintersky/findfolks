# findfolks

# Фронт

# Бэкэнд

Используем Arris microframework 

# DEPLOY

1. Создать БД и пользователя для неё.
2. Создать в БД таблицы
3. Написать конфиги:
    - `common.conf` создать по примеру `common.example`, заполнить данными для подключения к БД
4. Поставить пакет
5. Ready?
 

# Требуемая структура БД:

```
CREATE TABLE `tickets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dt_create` datetime DEFAULT current_timestamp(),
  `dt_update` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `city` varchar(100) DEFAULT '' COMMENT 'Город',
  `district` varchar(100) DEFAULT '' COMMENT 'Район/регион',
  `street` varchar(100) DEFAULT '' COMMENT 'улица',
  `address` varchar(100) DEFAULT '' COMMENT 'Адрес (номер дома итд)',
  `fio` varchar(100) DEFAULT '' COMMENT 'ФИО',
  `ticket` text DEFAULT '' COMMENT 'текст объявления',
  `ipv4` varchar(14) DEFAULT '127.0.0.1' COMMENT 'ipv4 в строковой форме',
  `is_verified` tinyint(4) DEFAULT 1 COMMENT 'подтверждено',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='таблица объявлений'
```

# Конфиг nginx

```

server {
    listen 80; 
    server_name <host>;

    root        /var/www/findfolks/public;

    index       index.php index.html;

    access_log  /var/log/nginx/findfolks.access.log;
    error_log   /var/log/nginx/findfolks.error.log;

    gzip             on;
    gzip_static      on;
    gzip_min_length  1000;
    gzip_proxied     expired no-cache no-store private auth;
    gzip_types       application/json;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_pass php-handler-7-4;
        fastcgi_index index.php;
    }

    location ~ favicon.* {
        access_log off;
        log_not_found off;
    }
}
```






