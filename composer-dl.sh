#!/bin/sh
if [ -x `which wget` ]; then
    echo "wget found."
    wget -q https://getcomposer.org/installer -O - | php
elif [ -x `which curl` ]; then
    echo "curl found."
    curl -sS https://getcomposer.org/installer | php
else
    echo "Please install wget or curl to download Composer."
fi
