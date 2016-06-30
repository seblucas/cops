<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

    if (!isset($config))
        $config = array();

    /*
     * The directory containing calibre's metadata.db file, with sub-directories
     * containing all the formats.
     * BEWARE : it has to end with a /
     * You can enable multiple database with this notation instead of a simple string :
     * $config['calibre_directory'] = array ("My database name" => "/home/directory/calibre1/", "My other database name" => "/home/directory/calibre2/");
     */
    $config['calibre_directory'] = './';

    /*
     * SPECIFIC TO NGINX
     * The internal directory set in nginx config file
     * Leave empty if you don't know what you're doing
     */
    $config['calibre_internal_directory'] = '';

    /*
     * Full URL prefix (with trailing /)
     * useful especially for Opensearch where a full URL is often required
     * For example Mantano, Aldiko and Marvin require it.
     */
    $config['cops_full_url'] = '';

    /*
     * Number of recent books to show
     */
    $config['cops_recentbooks_limit'] = '50';

    /*
     * Catalog's author name
     */
    $config['cops_author_name'] = "Sébastien Lucas";

    /*
     * Catalog's author uri
     */
    $config['cops_author_uri'] = "http://blog.slucas.fr";

    /*
     * Catalog's author email
     */
    $config['cops_author_email'] = "sebastien@slucas.fr";

    /*
     * Catalog's title
     */
    $config['cops_title_default'] = "COPS";

    /*
     * Catalog's subtitle
     */
    $config['cops_subtitle_default'] = "";

    /*
     * Wich header to use when downloading books outside the web directory
     * Possible values are :
     *   X-Accel-Redirect   : For Nginx
     *   X-Sendfile         : For Lightttpd or Apache (with mod_xsendfile)
     *   No value (default) : Let PHP handle the download
     */
    $config['cops_x_accel_redirect'] = "";

    /*
     * Height of thumbnail image for OPDS
     */
    $config['cops_opds_thumbnail_height'] = "164";

    /*
     * Height of thumbnail image for HTML
     */
    $config['cops_html_thumbnail_height'] = "164";

    /*
     * Icon for both OPDS and HTML catalog
     * Note that this has to be a real icon (.ico)
     */
    $config['cops_icon'] = "favicon.ico";

    /*
     * Show icon for authors, series, tags and books on OPDS feed
     *  1 : enable
     *  0 : disable
     */
    $config['cops_show_icons'] = "1";

    /*
     * Default timezone
     * Check following link for other timezones :
     * http://www.php.net/manual/en/timezones.php
     */
    $config['default_timezone'] = "Europe/Paris";

    /*
     * Prefered format for HTML catalog
     * The two first will be displayed in book entries
     * The other only appear in book detail
     */
    $config['cops_prefered_format'] = array ("EPUB", "PDF", "AZW3", "AZW", "MOBI", "CBR", "CBZ");

    /*
     * use URL rewriting for downloading of ebook in HTML catalog
     * See Github wiki for more information
     *  1 : enable
     *  0 : disable
     */
    $config['cops_use_url_rewriting'] = "0";

    /*
     * generate a invalid OPDS stream to allow bad OPDS client to use search
     * Example of non compliant OPDS client : Moon+ Reader
     * Example of good OPDS client : Mantano, FBReader
     *  1 : enable support for non compliant OPDS client
     *  0 : always generate valid OPDS code
     */
    $config['cops_generate_invalid_opds_stream'] = "0";

    /*
     * Max number of items per page
     * -1 unlimited
     */
    $config['cops_max_item_per_page'] = "-1";

    /*
     * split authors by first letter
     * 1 : Yes
     * 0 : No
     */
    $config['cops_author_split_first_letter'] = "1";

    /*
     * split titles by first letter
     * 1 : Yes
     * 0 : No
     */
    $config['cops_titles_split_first_letter'] = "1";

    /*
     * Enable the Lightboxes (for popups)
     * 1 : Yes (enable)
     * 0 : No
     */
    $config['cops_use_fancyapps'] = "1";

    /*
     * Update Epub metadata before download
     * 1 : Yes (enable)
     * 0 : No
     */
    $config['cops_update_epub-metadata'] = "0";

    /*
     * Filter on tags to book list
     * Only works with the OPDS catalog
     * Usage : array ("I only want to see books using the tag : Tag1"     => "Tag1",
     *                "I only want to see books not using the tag : Tag1" => "!Tag1",
     *                "I want to see every books"                         => "",
     *
     * Example : array ("All" => "", "Unread" => "!Read", "Read" => "Read")
     */
    $config['cops_books_filter'] = array ();

    /*
     * Custom Columns for the index page
     * to add as an array containing the lookup names configured in Calibre
     *
     * For example : array ("genre", "mycolumn");
     *
     * Note that the composite custom columns are not supported
     */
    $config['cops_calibre_custom_column'] = array ();

    /*
     * Custom Columns for the list representation
     * to add as an array containing the lookup names configured in Calibre
     *
     * For example : array ("genre", "mycolumn");
     *
     * Note that the composite custom columns are not supported
     */
    $config['cops_calibre_custom_column_list'] = array ();

    /*
     * Custom Columns for the book preview panel
     * to add as an array containing the lookup names configured in Calibre
     *
     * For example : array ("genre", "mycolumn");
     *
     * Note that the composite custom columns are not supported
     */
    $config['cops_calibre_custom_column_preview'] = array ();

    /*
     * Rename .epub to .kepub.epub if downloaded from a Kobo eReader
     * The ebook will then be recognized a Kepub so with chaptered paging, statistics, ...
     * You have to enable URL rewriting if you want to enable kepup.epub download
     * 1 : Yes (enable)
     * 0 : No
     */
    $config['cops_provide_kepub'] = "0";

    /*
     * Enable and configure Send To Kindle (or Email) feature.
     *
     * Don't forget to authorize the sender email you configured in your Kindle's  Approved Personal Document E-mail List.
     *
     * If you want to use a simple smtp server (provided by your ISP for example), you can configure it like that :
     * $config['cops_mail_configuration'] = array( "smtp.host"     => "smtp.free.fr",
     *                                           "smtp.username" => "",
     *                                           "smtp.password" => "",
     *                                           "smtp.secure"   => "",
     *                                           "address.from"  => "cops@slucas.fr"
     *                                           );
     *
     * For Gmail (ssl is mandatory) :
     * $config['cops_mail_configuration'] = array( "smtp.host"     => "smtp.gmail.com",
     *                                           "smtp.username" => "YOUR GMAIL ADRESS",
     *                                           "smtp.password" => "YOUR GMAIL PASSWORD",
     *                                           "smtp.secure"   => "ssl",
     *                                           "address.from"  => "cops@slucas.fr"
     *                                           );
     */
    $config['cops_mail_configuration'] = NULL;

    /*
     * Use filter in HTML catalog
     * 1 : Yes (enable)
     * 0 : No
     */
    $config['cops_html_tag_filter'] = "0";

    /*
     * Thumbnails are generated on-the-fly so it can be problematic on servers with slow CPU (Raspberry Pi, Dockstar, Piratebox, ...).
     * This configuration item allow to customize how thumbnail will be generated
     * "" : Generate thumbnail (CPU hungry)
     * "1" : always send the full size image (Network hungry)
     * any url : Send a constant image as the thumbnail (you can try "images/bookcover.png")
     */
    $config['cops_thumbnail_handling'] = "";

    /*
     * Directory to keep resized thumbnails: allow to resize thumbnails only on first access, then use this cache.
     * $config['cops_thumbnail_handling'] must be ""
     * "" : don't cache thumbnail
     * "/tmp/cache/" (example) : will generate thumbnails in /tmp/cache/
     * BEWARE : it has to end with a /
     */
    $config['cops_thumbnail_cache_directory'] = "";

    /*
     * Contains a list of user agent for browsers not compatible with client side rendering
     * For now : Kindle, Sony PRS-T1, Sony PRS-T2, All Cybook devices (maybe a little extreme).
     * This item is used as regular expression so "." will force server side rendering for all devices
     */
    $config['cops_server_side_render'] = "Kindle\/1\.0|Kindle\/2\.0|Kindle\/3\.0|EBRD1101|EBRD1201|cybook";

    /*
     * Specify the ignored categories for the home screen and with search
     * Meaning that if you don't want to search in publishers or tags just add them from the list
     * Only accepted values :
     * - author
     * - book
     * - series
     * - tag
     * - publisher
     * - rating
     * - language
     */
    $config ['cops_ignored_categories'] = array ();

    /*
     * If you use a Sony eReader or Aldiko you can't download ebooks if your catalog
     * is password protected. A simple workaround is to leave fetch.php not protected (see .htaccess).
     * But In that case your COPS installation is not completely safe.
     * Setting this parameter to "1" ensure that nobody can access fetch.php before accessing
     * index.php or feed.php first.
     * BEWARE : Do not touch this if you're not using password, not using PRS-TX or not using Aldiko.
     */
    $config ['cops_fetch_protect'] = "0";

    /*
     * WARNING NOT READY FOR PRODUCTION USE
     * Make the search better (don't care about diacritics, uppercase should work on Cyrillic) but slower.
     * 1 : Yes (enable)
     * 0 : No
     */
    $config ['cops_normalized_search'] = "0";

    /*
     * Enable PHP password protection (You can use if htpasswd is not possible for you)
     * If possible prefer htpasswd !
     * array( "username" => "xxx", "password" => "secret") : Enable PHP password protection
     * NULL : Disable PHP password protection (You can still use htpasswd)
     */
    $config['cops_basic_authentication'] = NULL;

    /*
     * Which template is used by default :
     * 'default'
     * 'bootstrap'
     */
    $config['cops_template'] = 'default';

    /*
     * Which style is used by default :
     * 'base'
     * 'default'
     * 'eink' (only available for the 'default' template)
     * 'iphone' (only available for the 'default' template)
     * 'iphone7' (only available for the 'default' template)
     */
    $config['cops_style'] = 'default';

    /*
     * Set language code to force a language (see lang/ directory for available languages).
     * When empty it will auto detect the language.
     */
    $config['cops_language'] = '';
