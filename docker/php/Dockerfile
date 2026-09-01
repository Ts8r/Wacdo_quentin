FROM php:8.3-apache

RUN docker-php-ext-install pdo pdo_mysql
RUN a2enmod rewrite

WORKDIR /var/www/html
FROM php:8.3-apache

RUN a2enmod rewrite \
    && docker-php-ext-install pdo pdo_mysql

WORKDIR /var/www/html
