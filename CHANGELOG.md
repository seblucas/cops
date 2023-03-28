# Change Log for COPS

1.3.3 - 20230327 Update npm asset dependencies
  * Fix link to typeahead.css for bootstrap2 templates
  * Move simonpioli/sortelements dev-master to resources (last updated in 2012)
  * Switch from bower-asset/dot 1.1.3 to npm-asset/dot 1.1.3
  * Switch from bower-asset/jquery 1.12.4 to npm-asset/jquery 1.12.4
  * Switch from bower-asset/jquery-cookie 1.4.1 to npm-asset/js-cookie 2.2.1
  * Switch from bower-asset/normalize.css 7.0.0 to npm-asset/normalize.css 8.0.1
  * Switch from rsms/js-lru dev-v2 to npm-asset/lru-fast 0.2.2

1.3.2 - 20230325 Improve tests and security
  * Merge branch 'master' of https://github.com/peltos/cops - see @peltos

1.3.1 - 20230325 Update epub-loader resources
  * Merge commit 'refs/pull/424/head' of https://github.com/seblucas/cops - see seblucas/cops#424 from @marsender

1.3.0 - 20230324 Add bootstrap2 templates
  * Merge branch 'master' of https://github.com/SenorSmartyPants/cops - see seblucas/cops#497 and earlier from @SenorSmartyPants

1.2.3 - 20230324 Add fixes for PHP 8.2

1.2.2 - 20230324 Update fetch.php to lower memory consumption
  * Merge commit 'refs/pull/518/head' of https://github.com/seblucas/cops - see seblucas/cops#518 from @allandanton

1.2.1 - 20230321 Add phpstan baseline + fixes

1.2.0 - 20230319 Migration to PHP 8.x

1.1.3 - 20190624
 * Fixed an error with PHP > 7.2.X where create_function is deprecated, also fixed another error with PHP 7.3.X. Thanks to Turkish for the report.
 * Fixed the view button when URL Rewriting is enabled.
 * Fixed an error in epubreader with headers. thanks to worstje for the report. 
 * Added a real logo for COPS. Thanks to horus68 for doing all the work ;).
 * Added a proper translation for `_CLEAR_` text. Was reported for many year :(. 
 * Added Galician translation. Thanks to Sadrarin Highland.
 * Added Afrikaan translation. Thanks to PetrusVermaak.
 * Updated Spanish, Basque, Italian, Dutch, French, Portuguese, Romanian, Russian, Swedish, Turkish. Thanks to the translators and horus68.
 * Upgraded to latest phpmailer 5.2.27 and bootstrap 3.4.1.

1.1.2 - 20180626
 * Fixed the download of Kepub with recent firmware from Kobo. Thanks to ospring for the report.
 * Fixed the cache headers. Thanks to CgX for the fix.
 * Added Bulgarian, Indonesian, Chinese (China and Taywan) translation. Check Transifex for the authors.
 * Added an open button to use automatically your prefered reader. Thanks to ttan.
 * Updated Hungarian, Ukranian, Polish, Spanish and Swedish translations. Check Transifex for the authors.
 * Updated a lot of documentation and checkconfig.php to better help users.
 * Upgraded to latest jQuery 1.12.X, Normalise 7.0, PHP Mailer 5.2.26, Font Awesome Free 5.0.13.

1.1.1 - 20170502
 * Fixed the handling of user specific configuration files. Thanks to marioscube, chadberg for the diagnostic / fix and Neil for the PR.
 * Changed the cog on the upper right to a magnifying glass icon. Thanks to horus68.
 * Added test on travis on PHP 5.4.
 * Added a way to specify the secure SMTP port. Thanks to ubupl.

1.1.0 - 20170402
 * Upgraded to PHPMailer 5.2.21.
 * Merged a huge PR that clean most of COPS source code. Thanks to Markus Birth for his work and his patience.
 * Updated German, Greek, Italian, Polish, Romanian, Russian, Turkish translations. Check Transifex for the authors.
 * Fixed a bad external dependency in login.html causing problem with HTTPS. Thanks to polytan02.
 * Fixed minor gui nitpick.
 * Added automatic redirection to the OPDS feed for many new Android apps (see #309). Thanks to horus68.
 * Added a configuration item to set the mail subject.

1.0.1 - 20161015
 * Fixed some type of custom column showing id instead of text - Thanks to Mike Schwörer.
 * Fixed the redirection to the OPDS catalog for Moon+ Reader.
 * Fixed the mail character encoding, now in UTF-8.
 * Fixed checkconfig.php to avoid sending content before headers. Thanks to Luke Stevenson.
 * Fixed server side rendering with custom columns.
 * Moved /icons to /images (Apache issues). Thanks to CgX.

1.0.0 - 20160708
 * Updated the OPDS icons to better looking ones. Thanks to Horus68.
 * Updated the README.md.
 * Updated Brazillian, French, Hungarian, Portuguese, Russian translations.
 * Added support of language and country code. This allow to have proper Brazil Portuguese and Portugal Portuguese.
 * Added Korean translation. Thanks to Jin, Heonkyu.
 * Added Romanian translation. Thanks to mtzro2003.
 * Added Greek translation. Thanks to George Litos.
 * Added Turkish Translation. Thanks to Yunus Emre Deligöz.
 * Added Serbian Translation. Thanks to Dalibor Vinkić.
 * Added the transliteration of search text. You can enable it with $config ['cops_normalized_search']. Thanks to George Litos.
 * Added Ebookdroid, Chunky and AlReader in the know OPDS clients. Thanks to Mike Ferenduros and Horus68.
 * Added some mime types for audio books.
 * Added the rewrite rule for IIS.
 * Added a now parameter to set the style ($config['cops_style']). Thanks to Pablo Santiago Blum de Aguiar.
 * Added a directory cache ($config['cops_thumbnail_cache_directory']) to store the resized thumbnails (should help on slow NAS). Thanks to O2 Graphics.
 * Added support of all kind of custom columns (see configuration file). Thanks to Mike Schwörer.
 * Fixed COPS so that it's completely embedded (no external resources to download needed anymore).
 * Fixed a Reflected XSS vulnerability.
 * Fixed the tag filters with Bootstrap. Thanks to Klaus Broelemann.
 * Fixed some COPS path errors with reverse proxy. Thanks to Benjamin Kitt.
 * Fixed the publication date (wasn't working for date before 1901).
 * Fixed the download file name (replace + by %20 to be RFC compliant).


1.0.0RC3 - 20141229
 * Fixed server side render with Bootstrap template (a proper unit test was also added).
 * Upgraded to latest doT-php, Typeahead 0.10.5, jquery-cookie 1.4.1, JQuery 1.11.1
 * Fixed book count with custom columns.
 * Updated Catalan, Dutch, French and Russian translations.
 * Added AZW3 to the format that can be sent to Kindle (by mail).
 * Fixed $config['cops_thumbnail_handling'] with bootstrap template.
 * Added Hungarian translation. Thanks to harunibn.
 * Added Ukrainian translation. Thanks to Anatoliy Zavalinich
 * Added full PHP password check (without any need from specific webserver configuration). Thanks to Mark Bond.
 * Added new IOS7 style with default template. Thanks to an anonymous source ;).
 * Fixed display of authors names for books with more than one author.
 * Added PHP version to checkconfig.php (will help debugging for me).
 * Added a configuration item ($config['cops_template']) to change the default template. Thanks to Shin.
 * Added a configuration item ($config['cops_language']) to force COPS language. Thanks to Sandy Pleyte.
 * Added a trick to have user based configuration, check https://github.com/seblucas/cops/wiki/User-based-config for more information. Thanks to Sandy Pleyte.
 * Changed the default sort order on books by author page to show books in a series before all other books.


1.0.0RC2 - 20140731
 * Updated Italian, Spanish, Portuguese, Norwegian translations.
 * Added Polish translation. Thanks to macak_pl.
 * Added Haitian Creole translation. Thanks to Ian Macdonald & Jacinta.
 * Added Basque translation. Thanks to Turutarena.
 * Upgraded to JQuery 1.11.0, Magnific Popup 0.9.9, Normalize 3.0.1, Typeahead 0.10.2
 * Fixed search with accentuated characters on Internet Explorer.
 * Author can now be searched by sort or by name (Carroll, Lewis or Lewis Carroll will work).
 * Added a new bootstrap user interface.
 * Added correct mimetype for *.ibooks. Reported by Flowney.
 * Added an empty line at the end of .htaccess to make it easier to modify. Reported by Mariosipad.
 * Modified the README and checkconfig.php to check for php5-json. Reported by Mariosipad.
 * Handled properly the cancelling of a mail. Reported by coach0742.
 * Added an ugly hack to try to fix bad rendering with Kindle. Please report if it's better or not.

1.0.0RC1 - 20140404
  * Updated English, Spanish, German, Italian, Portuguese, Dutch translation files. Huge thanks to all to the translators.
  * Added Swedish translation. Thanks to Bo Rosén.
  * Added Czech translation. Thanks to Zdenek Hadrava.
  * Added a lot of refactoring to simplify the code.
  * Added a lot of new unit tests.
  * Fixed a caching bug causing problems with IE.
  * Added an embedded Epub Reader based on Monocle. Thanks to all the beta testers.
  * Cleaned up a lot of stuff to prepare for bootstrap template. Note to all CSS hackers, the stylesheets are now in templates/default/styles.
  * Fixed the charset of most of the pages. Thanks to edent.
  * Added a new category : ratings. Thanks to Michael.
  * Fixed the URL rewriting in the OPDS stream, should fix file naming with FBReader. Reported by Rassie.
  * Fixed a confusion between author's name and author's sort. Reported by At_Libitum.
  * Fixed the style of the tag filters to show that they're clickable. Thanks to cycojesus.
  * Replaced | by space in author name.

0.9.0 - 20131231
  * Add a lot of unit testing. I hope it will limit the risks of regression.
  * Added a "smart / autocomplete" search.
  * Updated the way locales are handled. Should be easier to add new languages.
  * Fixed display of Cyrillic characters.
  * Upgraded doT to version 1.0.1, Magnific-Popup to 0.9.8, Normalize.css to 2.1.3, Jquery-cookie to 1.4.0.
  * Fixed OPDS stream validity. Reported by Didier.
  * Added a new check in checkconfig.php to detect case problem between the actual path and the path stored in Calibre database. Try checkconfig.php?full=1. Reported by Ruud.
  * Fixed the display of the rating stars with Chrome. Thanks to At_Libitum.
  * Added a new parameter ($config['cops_titles_split_first_letter']) to avoid splitting the books by first letter. Thanks to At_Libitum.
  * Fixed non compliant OPDS search (for Stanza, Moon+ Reader, ...). Reported by At_Libitum.
  * Fixed the redirection in case the Calibre database is not found. Reported by At_Libitum
  * Changed .htaccess to allow the use of password protected catalogs with Sony's eReader (PRS-TX). Thanks to Ruud for the beta testing.
  * Updated Chinese, German, Norwegian, Portuguese, Russian translations. Huge thanks to all the translators.
  * Fixed a small problem : If a book had no summary the cover could be cut.
  * Fix COPS on Internet Explorer 9. Reported by At_Libitum.
  * Added publishers in home categories / search / autocomplete search.
  * Added a new configuration item ($config ['cops_ignored_categories']) to ignore some categories (author, tag, publisher, ...) in home screen and searches. It's also available in the "Customize UI" page.
  * Updated .htaccess to allow downloading books with a password protected COPS on a Sony PRS-TX. Reported by Ruud.
  * Changed the default search to search by categories also (should help with OPDS). Thanks to At_Libitum.
  * Fixed the tag filtering in the HTML catalog when two tags starts by the same word. Reported by Tyler.

0.6.2 - 20130913
  * Added server side rendering for devices like PRS-TX / Kindle / Cybook. Thanks to all the testers.
  * Added a configuration item to tweak how thumbnail are handled.
  * Fixed the click on cog on IOS. Thanks to sb domo.
  * Added dashboard icons / standalone mode for IOS. Thanks to sb domo.
  * Fixed a regression about custom favicon.ico. Thanks to Tyler.
  * Fixed another regression about COPS's version in the about box. Reported by Ian.
  * Upgraded Magnific Popup to v0.9.5.
  * Added a style for IPhone. Thanks to sb domo.
  * Added Portuguese translation. Thanks to Pablo Aguiar.
  * Fixed rendering on Internet Explorer < 9.0.

0.6.1 - 20130730
  * Properly close the lightbox when clicking in a link. Reported by le_.
  * Fix the book by languages list when the language is not found in the resources. Reported by le_.
  * Fix the string for Portuguese. Reported by le_.
  * Add again the series Index in the book list. Reported by fatzgenfatz.

0.6.0 - 20130724
  * COPS HTML catalog now use templated client side rendering. You can build your own template if you want. Should be a lot faster.
  * Fancybox has been replaced by Magnific Popup, it seems faster.
  * Added a way to send book by mail (to send to Kindle or to send to your friends).
  * Added expires instruction in .htaccess (won't crash if you haven't enabled mod_expires).
  * Upgrade to JQuery 1.10.2.
  * Changed the way thumbnails are handled to offer greater visual quality (especially on high pixel density devices : Retina, Nexus, ...).
  * Changed all icon by a vectorial font (again better visual quality).
  * Added a way to filter books by tags.
  * Added a login page (login.html) to allow access to a password protected COPS on a Kobo ereader (that does not support basic auth).
  * Fixed cookie expiry date.
  * Added a default web.config for IIS installation.
  * The eink style doesn't use shadow anymore.
  * Fixed the link to the series in book detail.

0.5.0 - 20130605
  * Upgrade COPS UI to HTML5 / CSS3 to hopefully make it prettier. Most of the code was contributed by Thomas Severinsen.
  * Add the number of books in each databases (when multiple database is enabled).
  * Add Norwegian Bokmål strings. Thanks to Rune Mathisen for the pull request.
  * Add a split by language of catalog. Thanks to Puiu Ionut for the pull request.
  * You can now change the theme and fancybox use on all your devices (You have to enable cookies).
  * Add an eink theme. Thanks to Gregory Bodin for the code.

0.4.0 - 20130507
  * Add multiple database support. Check the documentation of $config['calibre_directory'] in config-default.php to see how to enable it.
  * Include jquery library in COPS's repository to be sure that COPS will work on LAN (without Internet access).
  * Prepare the switch to HTML5. Thanks to Thomas Severinsen for most of the code.
  * Update the locale strings to be more strict with plurals. Thanks to Tobias Ausländer for the code.
  * If Fancybox is not enabled ($config['cops_use_fancyapps'] = "0") then it's not used at all (even in the about box).
  * Fix book comments if it contains UTF8 characters. Reported by Alain.
  * Link to the book permalink was not working correctly in some cases. Reported by celta.
  * Moved some external resources to a resources directory.
  * Add chinese translation. Thanks to wogong for the pull request.

0.3.4 - 20130327
  * Hopefully fix metadata update. Beware you should remove the directory php-epub-meta if you have one. Thanks to Mario for his time.
  * Fix two warnings. Reported by Goner and Mario.

0.3.3 - 20130323
  * Fix catalog if book summary contains bad HTML again :(.
  * Upgrade to Fancybox 2.4.0 and JQuery 1.9.1.
  * Search is now dependant on the page you're in. For now if you're on author page it'll look for author name.
  * Update checkconfig to check if the database provided comes from Calibre.
  * Update to latest php-epub-meta should fix the metadata update with Epub.
  * Fix OPDS catalog with Ibis Reader. It didn't like empty language.

0.3.2 - 20130303
  * Add dutch translation. Provided by Northguy.
  * Fix an ugly bug introduced in 0.3.1. Reported by mariosipad.
  * Small fixes/enhancement to the update metadata tools :
    * The book's name is Author - Title.epub
    * Add the Calibre uuid so that the book is automatically recognised by Calibre.
    * Update the cover
  * Fix display of the HTML catalog on Kobo's browser.
  * Enable kepub.epub download with cover fix (enable with $config['cops_provide_kepub']).
  * Hopefully fix browsing with PRS-T1. Thanks to Northguy.
  * Hopefully fix the OPDS catalog when the summary is full of HTML crap.
  * Merged 3 patches from Tyler J. Wagner :
    * Detect empty publication date set in Calibre to avoid having (0101) as publication year.
    * Don't print "Languages" if there are none defined.
    * Don't print the tag string if there's no tags.
  * If an OPDS client try to access index.php it will be automatically redirected to feed.php.
  * Move the search & sort tool box to a new line (also fix a w3c error).


0.3.1 - 20130127
  * Add Facets to the OPDS catalog (check config item cops_books_filter).
    So far the only OPDS client that support facets are Mantano Reader and Bluefire
  * Fix book sort in some list. Patch provided by Tyler J. Wagner.
  * Update .htaccess to check if Xsendfile is available. Thanks to Gaspine for the patch.
  * Add basic support of custom columns. Check the following config item : cops_calibre_custom_column
  * Usage of X-Accel-Redirect / X-Sendfile is not necessary anymore. Warning all Nginx users
    who wants to still use X-Accel-Redirect must add
    $config['cops_x_accel_redirect'] = "X-Accel-Redirect" in their config_local.php
  * Fix COPS on IIS / Windows. Reported by Kevnancy.
  * Simplified config_default.php
  * Add a new config_local.php.example with the minimal configuration item to change.


0.3.0 - 20130106
  * Add a config item to avoid using Fancyapps (pop-ups). Reported by mcister and Northguy.
  * Update documentation of .htaccess. Thanks to Stephane.
  * Add a config item to specify a custom icon. Based on a patch by Tyler J. Wagner.
  * Better handling of content type for book. Reported by Morg.
  * Upped the size of thumbnails for OPDS. They look way better with Mantano.
  * Add language in OPDS feed (shown in Mantano for example).
  * Update metadata on downloaded epub. Disabled by default (check config item cops_update_epub-metadata).
  * New Catalan translation provided by David Ciscar Presas.
  * Add a permalink to books, that way direct link to books can be shared. Reported by mcister and Tyler J. Wagner.
  * Add checkconfig.php that should allow to better detect the configuration problem (page in english only for now).
  * Fix some plural strings / some missing title. Reported by David Ciscar Presas.
  * Add an hint about the OPDS catalog in the HTML catalog.

0.2.3 - 20121205
  * Add a .htaccess to make it easier to use with Apache
  * Fix a typo in book download. Reported by jillmess
  * Update localization (thanks to Calibre2Opds)
  * Add some missing information from Calibre (language, rating for now). Reported by mcister
  * Upgrade Fancybox to 2.1.3


0.2.2 - 20121020
  * Changed JQuery URL to https (thanks to Dan Greve for the patch)
  * Added paging to both OPDS and HTML catalog (use new config item cops_max_item_per_page)
  * lots of code refactoring
  * Authors are now splitted by first letter, this is the new default. You can go back to the old way with the config item cops_author_split_first_letter (reported by Northguy)
  * Fix the link to books starting by special characters (reported by vinpel)
  * Upgrade to Fancyapps 2.1.0. I had to adapt the CSS so maybe it'll display better in PRS-T1
  * Add an about box on the HTML catalog which show the current version

0.2.1 - 20120916
  * Fix one last error (hopefully) in link generation (thanks to gaspine)
  * Add Sony PRS-T1 to the list of E-Ink device (thanks to Northguy)
  * Fix another HTML special characters problem (thanks to NeilBryant)
  * Add an ugly config parameter to allow search in non-compliant OPDS reader (thanks to Don Caruana and David Lee)

0.2.0 - 20120722
  * Fix all rewriting rule I forgot to change it in last release
  * Fix <hr> in book comment (thanks to jillmess)
  * Fix cover zoom in HTML catalog (you can also navigate through cover with keyboard)
  * Simplify Fancybox transition for e-Ink devices (for now Kobo and Kindle)

0.1.1 - 20120702
  * A lot of bug fixes in HTML catalog
  * Fixed the book comment in OPDS (broken in some rare case)
  * Fixed handling of HTML reserved characters
  * Changed book OPDS id to use an UUID (thanks to ilovejedd for the bug report)
  * Add new config item for the default timezone (thanks to gaspine)
  * Better handling of missing covers
  * Should support every book format supported by Calibre (thanks to Artem)
  * URL rewriting is off by default for the HTML catalog
  * Add some documentation about URL rewriting (thanks to gaspine and Christophe)
  * Tested and ready to use with PHP5.4

0.1.0 - 20120605
  * Add localization support (thanks to Calibre2Opds)
  * Hopefully fixed an issue with & in comment
  * HTML catalog is in the sources with no support (WIP)

0.0.4 - 20120523
  * More code refactoring to simplify code.
  * Changed OPDS Page id to match Calibre2Opds
  * Add icons to author, serie, tags and recent items (there is config item to disable it)
  * Fixed author URL
  * Added publishing date (works on Mantano)
  * Added Tags support

0.0.3 - 20120507
  * Fixed many things blocking opensearch from working
   * There was a bug introduced in 0.0.2
   * The URL can't be relative for Mantano reader, so I added a configuration item.
  * I continued the refactoring to bring HTML to COPS
  * Thumbnails have bigger size (I'll add a configuration item later)
  * Add headers to help caching image and thumbnail to the browser
  *

0.0.2 - 20120411
  * Add support for MOBI and PDF
  * Major refactoring to prepare something nice for the future ;)
  * Add a config item to make use of X-Sendfile instead of X-Accel-Redirect if needed

0.0.1 - 20120302
  * First public release
