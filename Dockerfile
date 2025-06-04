FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
    sqlite-dev \
    && docker-php-ext-install pdo pdo_sqlite

WORKDIR /var/www/html

COPY db-setup.sh /usr/bin/db-setup.sh

RUN chmod +x /usr/bin/db-setup.sh

COPY assets/ ./assets

COPY public/ .
