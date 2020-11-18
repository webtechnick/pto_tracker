FROM php:7.4.11-fpm

RUN docker-php-ext-install pdo pdo_mysql