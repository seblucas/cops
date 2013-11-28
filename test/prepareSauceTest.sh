#!/usr/bin/env bash

PHP_VERSION=`php -v|grep --only-matching --perl-regexp "PHP 5\.\\d+"`
echo $PHP_VERSION


if [[ $PHP_VERSION != "PHP 5.5" ]]
  then
    echo "Bad PHP version"
    exit
fi

echo "Good PHP version"

curl -s https://raw.github.com/jlipps/sausage-bun/master/givememysausage.php | php
cp test/config_local.php.sauce config_local.php
php -S localhost:8888 2> /dev/null &
vendor/bin/phpunit test/Sauce.php