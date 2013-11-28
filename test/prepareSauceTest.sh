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