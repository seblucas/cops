#!/bin/sh
# https://getcomposer.org/
if [ -x `which wget` ]; then
    echo "wget found."
    wget -q https://getcomposer.org/installer -O - | php
elif [ -x `which curl` ]; then
    echo "curl found."
    curl -sS https://getcomposer.org/installer | php
else
    echo "Please install wget or curl to download Composer."
fi

if [ -f "./composer.phar" ]; then
    chmod a+x ./composer.phar

    # Install support for bower and NPM packages
    ./composer.phar global require "fxp/composer-asset-plugin:~1.1"
fi
