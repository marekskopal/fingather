FROM composer:2.9.5 AS composer
FROM mlocati/php-extension-installer:2.9.30 AS php-extension-installer
FROM dunglas/frankenphp:1.11.3-php8.5.3-alpine AS php

COPY --from=php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

RUN install-php-extensions \
    curl \
    pdo_mysql \
    opcache \
    bcmath \
    sockets \
    mbstring \
    zip \
    decimal \
    simplexml \
    gd \
    protobuf \
    igbinary \
    memcached \
    redis \
    xhprof

RUN set -x \
    && apk add --no-cache \
        supervisor \
   		supercronic \
    	curl \
    	tzdata \
    	libreoffice-calc \
    	libreoffice-writer \
    	font-noto

COPY --from=composer /usr/bin/composer /usr/local/bin/composer

COPY ./docker/etc/supervisor/supervisor.conf /etc/supervisor/supervisor.conf

ENTRYPOINT /usr/bin/supervisord -c /etc/supervisor/supervisor.conf

COPY ./docker/etc/cron.d /etc/cron.d
RUN chmod 0600 /etc/cron.d/*

COPY ./docker/docker-php.ini /usr/local/etc/php/conf.d/docker-php.ini

COPY ./bin /app/bin
COPY ./migrations /app/migrations
COPY ./translations /app/translations
COPY ./src /app/src
COPY ./Caddyfile ./composer.json ./composer.lock /app/

WORKDIR /app/

RUN rm -rf /app/vendor \
    && COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --no-progress --classmap-authoritative -d /app/ \
    && composer clear-cache
