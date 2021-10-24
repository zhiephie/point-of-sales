FROM php:8.0.10-fpm-alpine
RUN docker-php-ext-install pdo pdo_mysql