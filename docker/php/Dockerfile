FROM php:8.3-apache

RUN a2enmod rewrite \
    && docker-php-ext-install pdo pdo_mysql

WORKDIR /var/www/html
