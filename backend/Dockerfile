FROM spiralscout/roadrunner:2024.2.0 AS roadrunner
FROM composer:2.7.7 AS composer
FROM mlocati/php-extension-installer:2.2.18 AS php-extension-installer
FROM php:8.3.9-cli-alpine AS php

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
    xhprof

RUN set -x \
    && apk add --no-cache \
        supervisor \
   		supercronic \
    	curl \
    	tzdata

COPY --from=roadrunner /usr/bin/rr /usr/local/bin/rr
COPY --from=composer /usr/bin/composer /usr/local/bin/composer

COPY ./docker/etc/supervisor/supervisor.conf /etc/supervisor/supervisor.conf

ENTRYPOINT /usr/bin/supervisord -c /etc/supervisor/supervisor.conf

COPY ./docker/etc/cron.d /etc/cron.d
RUN chmod 0600 /etc/cron.d/*

COPY ./docker/docker-php.ini /usr/local/etc/php/conf.d/docker-php.ini

COPY ./bin /app/bin
COPY ./migrations /app/migrations
COPY ./src /app/src
COPY ./.rr.yaml ./composer.json ./composer.lock /app/

WORKDIR /app/

RUN rm -rf /app/vendor \
    && COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --no-progress --classmap-authoritative -d /app/
