# COPS

COPS stands for Calibre OPDS (and HTML) Php Server.

See : [COPS's home](http://blog.slucas.fr/en/oss/calibre-opds-php-server) for more details.

Don't forget to check the [Wiki](https://github.com/seblucas/cops/wiki).

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/seblucas/cops/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/seblucas/cops/?branch=master)

[![Code Coverage](https://scrutinizer-ci.com/g/seblucas/cops/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/seblucas/cops/?branch=master)

[![Build Status / Scrutinizer](https://scrutinizer-ci.com/g/seblucas/cops/badges/build.png?b=master)](https://scrutinizer-ci.com/g/seblucas/cops/build-status/master)

[![Build Status](https://travis-ci.org/seblucas/cops.svg?branch=master)](https://travis-ci.org/seblucas/cops)

[![Selenium Test Status](https://saucelabs.com/browser-matrix/seblucas.svg)](https://saucelabs.com/u/seblucas)

# Why ?

In my opinion Calibre is a marvelous tool but is too big and has too much
dependencies to be used for its content server.

That's the main reason why I coded this OPDS server. I needed a simple
tool to be installed on a small server (Seagate Dockstar in my case).

I initially thought of Calibre2OPDS but as it generate static file no
search was possible.

Later I added an simple HTML catalog that should be usable on my Kobo.

So COPS's main advantages are :
 * No need for many dependencies.
 * No need for a lot of CPU or RAM.
 * Not much code.
 * Search is available.
 * With Dropbox / owncloud it's very easy to have an up to date OPDS server.
 * It was fun to code.

If you want to use the OPDS feed don't forget to specify feed.php at the end of your URL.

# Prerequisites

1. 	PHP 5.3, 5.4, 5.5, 5.6, 7.X or hhvm with GD image processing, Libxml, Intl, Json & SQLite3 support (PHP 5.6 or later recommended).
2. 	A web server with PHP support. I tested with various version of Nginx and Apache.
    Other people reported it working with Apache and Cherokee. You can also use PHP embedded server (https://github.com/seblucas/cops/wiki/Howto---PhpEmbeddedServer)
3.  The path to a calibre library (metadata.db, format, & cover files).

On any Debian based Linux you can use :
 `apt-get install php5-gd php5-sqlite php5-json php5-intl`

If you use Debian Stretch :
 `apt-get install php7.0-gd php7.0-sqlite3 php7.0-json php7.0-intl php7.0-xml php7.0-mbstring php7.0-zip`

On Centos you may have to add :
 yum install php-xml

# Install a release (Easiest way)

1.  Extract the zip file you got from [the release page](https://github.com/seblucas/cops/releases) to a folder in web space (visible to the web server).
2.  If you're doing a first-time install, copy config_local.php.example to config_local.php
3.  Edit config_local.php to match your config.
4.  If needed add other configuration item from config_default.php

If you like Docker, you can also try these docker containers.
[x64](https://hub.docker.com/r/linuxserver/cops/)
[armhf](https://hub.docker.com/r/lsioarmhf/cops)
[arm64](https://hub.docker.com/r/lsioarmhf/cops-aarch64/)

# Install from sources

```bash
git clone https://github.com/seblucas/cops.git # or download lastest zip see below
cd cops
wget https://getcomposer.org/composer.phar
php composer.phar global require "fxp/composer-asset-plugin:~1.1"
php composer.phar install --no-dev --optimize-autoloader
```

After that you can use the previous how-to starting at the second step.

Note that instead of cloning you can also get [latest master as zip](https://github.com/seblucas/cops/archive/master.zip)

Note that if your PHP version is lower that 5.6, then you may have to remove `composer.lock` before starting the last line.

# Where to put my Calibre directory ?

Long story short : ALWAYS outside of COPS's directory especially if COPS is installed on a VPS / Server. If you follow my advice then your data will be safe.

If you choose to put your Calibre directory inside your web directory and use Nginx then you will have to edit /etc/nginx/mime.types to add this line :
`application/epub+zip epub;`

# Known problems

Not a lot, except for the bad quality of the code (first PHP project ever) ;)

Please see https://github.com/seblucas/cops/issues for open issues

# Need help

Please read https://github.com/seblucas/cops/wiki and check the FAQ.

# Contributing

As you could see [here](https://github.com/seblucas/cops/graphs/contributors), I appreciate every contributions and there were a lot over time. So don't be shy and submit your Pull Requests.

Note to translators : please prefer using [Transifex](https://github.com/seblucas/cops/wiki/Update-translations) instead of doing a PR.

I only have one limit (I may have more but that one is the worse) : COPS' goal is to provide an alternative to Calibre's content server and not to replace Calibre entirely. So I will refuse any PR making changes to the database content.

# Credits

 * Locale message handling is inspired of http://www.mind-it.info/2010/02/22/a-simple-approach-to-localization-in-php/
 * str_format function come from http://tmont.com/blargh/2010/1/string-format-in-php
 * All icons come from Font Awesome : http://fontawesome.github.io/Font-Awesome/
 * The unofficial OPDS validator : http://opds-validator.appspot.com/
 * Thanks to all testers, translators and contributors.
 * Feed icons made by Freepik from Flaticon website licensed under Creative Commons BY 3.0 http://www.flaticon.com and http://www.freepik.com

External libraries used :
 * JQuery : http://jquery.com/
 * Magnific Popup : http://dimsemenov.com/plugins/magnific-popup/
 * Php-epub-meta : https://github.com/splitbrain/php-epub-meta with some modification by me (https://github.com/seblucas/php-epub-meta)
 * TbsZip : http://www.tinybutstrong.com/apps/tbszip/tbszip_help.html
 * DoT.js : http://olado.github.io/doT/index.html
 * PHPMailer : https://github.com/PHPMailer/PHPMailer
 * js-lru : https://github.com/rsms/js-lru

# Copyright & License

COPS - 2012-2017 (c) Sébastien Lucas

See COPYING and file headers for license info

