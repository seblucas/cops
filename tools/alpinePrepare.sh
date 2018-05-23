#!/bin/sh

# apk --no-cache add git
# git clone https://github.com/seblucas/cops.git
# cd cops

apk add --upgrade apk-tools

# Deployment dependencies
apk --no-cache add php7 php7-phar php7-json php7-openssl php7-gd php7-xml php7-intl php7-mbstring php7-pdo_sqlite php7-xmlwriter php7-zip php7-ctype

# Development dependencies
apk --no-cache add php7-tokenizer php7-simplexml php7-curl php7-dom openjdk7-jre

wget http://getcomposer.org/composer.phar
php composer.phar install --optimize-autoloader

./vendor/bin/phpunit