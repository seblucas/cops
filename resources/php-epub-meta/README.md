PHP EPub Meta
=============

This project aims to create a PHP class for reading and writing metadata
included in the EPub ebook format.

It also includes a very basic web interface to edit book metadata.

Please see the issue tracker for what's missing.

Forks and pull requests welcome.


About the EPub Manager Web Interface
------------------------------------

The manager expects your ebooks in a single flat directory (no subfolders). The
location of that directory has to be configured at the top of the index.php file.

All the epubs need to be read- and writable by the webserver.

The manager also makes some assumption on how the files should be named. The
format is: `<Author file-as>-<Title>.epub`. Commas will be replaced by `__` and
spaces are replaced by `_`.

Note that the manager will **RENAME** your files to that form when saving.

Using the "Lookup Book Data" link will open a dialog that searches the book at
Google Books you can use the found data using the "fill in" and "replace"
buttons. The former will only fill empty fields, while the latter will replace
all data. Author filling is missing currently.


Installing via Composer
=======================

You can use this package in your projects with [Composer](https://getcomposer.org/). Just
add these lines to your project's `composer.json`:

```
    "require": {
        "seblucas/php-epub-meta": "dev-master",
    }
```
