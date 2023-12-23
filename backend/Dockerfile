FROM spiralscout/roadrunner:2023.3.8 AS roadrunner
FROM composer:2.6.6 AS composer
FROM php:8.2.13-cli-alpine AS php

ENV DOCKER_PHP_EXT_INSTALLER_VERSION 2.1.60

ADD https://github.com/mlocati/docker-php-extension-installer/releases/download/$DOCKER_PHP_EXT_INSTALLER_VERSION/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions && install-php-extensions \
    pdo_mysql \
    zip \
    sockets \
    opcache \
    bcmath \
    decimal

COPY --from=roadrunner /usr/bin/rr /usr/local/bin/rr
COPY --from=composer /usr/bin/composer /usr/local/bin/composer

COPY ./docker/docker-php.ini /usr/local/etc/php/conf.d/docker-php.ini
COPY ./ /app/

WORKDIR /app/

RUN rm -rf /app/vendor \
    && COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --no-progress --classmap-authoritative -d /app/

CMD rr serve -w /app/ -c /app/.rr.yaml

HEALTHCHECK --interval=10s --timeout=5s --start-period=10s\
 	CMD curl -f http://localhost/api/health || exit 1
