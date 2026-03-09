FROM php:8.2-apache

RUN apt-get update && apt-get install -y curl

RUN apt-get update && apt-get install -y cifs-utils

RUN docker-php-ext-install pdo pdo_mysql

COPY . /var/www/html/

EXPOSE 80