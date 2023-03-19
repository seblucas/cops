#!/usr/bin/env bash

PHP_VERSION=`php -v|grep --only-matching --perl-regexp "PHP 5\.\\d+"`
echo $PHP_VERSION


if [[ $PHP_VERSION != "PHP 5.4" ]]
  then
    echo "Bad PHP version"
    exit
fi

echo "Good PHP version"

# Handle scrutinizer
wget https://scrutinizer-ci.com/ocular.phar
php ocular.phar code-coverage:upload --format=php-clover clover.xml
