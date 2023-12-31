FROM spiralscout/roadrunner:2023.3.8 AS roadrunner
FROM composer:2.6.6 AS composer
FROM phusion/baseimage:jammy-1.0.1 AS php

RUN LC_ALL=C.UTF-8 add-apt-repository -y ppa:ondrej/php && \
    apt-get update && \
    apt-get upgrade -y && \
    apt-get install -y --no-install-recommends \
        php8.3 \
        php8.3-curl \
        php8.3-mysql \
        php8.3-opcache \
        php8.3-bcmath \
        php8.3-sockets \
        php8.3-mbstring \
        php8.3-zip \
        php8.3-decimal \
        curl \
        tzdata && \
    apt-get autoremove -y && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

COPY --from=roadrunner /usr/bin/rr /usr/local/bin/rr
COPY --from=composer /usr/bin/composer /usr/local/bin/composer

COPY ./docker/etc/cron.d /etc/cron.d
COPY ./docker/etc/service /etc/service
COPY ./docker/etc/php /etc/php

COPY ./bin /app/bin
COPY ./log /app/log
COPY ./migrations /app/migrations
COPY ./src /app/src
COPY ./.rr.yaml ./composer.json ./composer.lock /app/

WORKDIR /app/

RUN rm -rf /app/vendor \
    && COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --no-progress --classmap-authoritative -d /app/
