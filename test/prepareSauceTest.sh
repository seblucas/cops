#!/usr/bin/env bash

if [[ -z $SAUCE_API_KEY ]]
  then
    echo "No Sauce Api Key (Pull request)"
    exit
fi

PHP_VERSION=`php -v|grep --only-matching --perl-regexp "PHP 5\.\\d+"`
echo $PHP_VERSION


if [[ $PHP_VERSION != "PHP 5.4" ]]
  then
    echo "Bad PHP version"
    exit
fi

echo "Good PHP version"

curl https://gist.github.com/seblucas/7692094/raw/e2a090e6ea639a0d700e6d02cee048fa2f6c8617/sauce_connect_setup.sh | bash
curl -s https://raw.github.com/jlipps/sausage-bun/master/givememysausage.php | php
cp -v test/config_local.php.sauce config_local.php
php -S 127.0.0.1:8888 &
vendor/bin/phpunit --no-configuration test/Sauce.php


