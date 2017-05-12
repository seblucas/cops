#!/usr/bin/env bash

PHP_VERSION=`php -v|grep --only-matching --perl-regexp "PHP 5\.\\d+"`
echo $PHP_VERSION


if [[ $PHP_VERSION != "PHP 5.6" ]]
  then
    echo "Bad PHP version"
    exit
fi

echo "Good PHP version"

# Handle scrutinizer
wget https://scrutinizer-ci.com/ocular.phar
php ocular.phar code-coverage:upload --format=php-clover clover.xml

if [[ -z $SAUCE_ACCESS_KEY ]]
  then
    echo "No Sauce Api Key (Pull request)"
    exit
fi

# Install dependencies
wget http://getcomposer.org/composer.phar
php composer.phar install

echo 'opcache.enable=1' >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
echo 'opcache.enable_cli=1' >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini

# Handle Sauce
# curl https://gist.githubusercontent.com/seblucas/7692094/raw/e2a090e6ea639a0d700e6d02cee048fa2f6c8617/sauce_connect_setup.sh | bash
cp -v test/config_local.php.sauce config_local.php
php -S 127.0.0.1:8080 &
vendor/bin/phpunit --no-configuration test/Sauce.php


