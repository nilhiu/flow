FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
    sqlite-dev \
    && docker-php-ext-install pdo pdo_sqlite

WORKDIR /var/www/html

COPY assets/ ./assets

COPY public/ .

USER www-data
