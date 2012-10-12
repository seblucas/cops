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
     * If this directory starts with a / EPUB download will only work with Nginx
     * and the calibre_internal_directory has to be set properly
     * BEWARE : it has to end with a /
     */
    $config['calibre_directory'] = './';
    
    /*
     * The internal directory set in nginx config file
     * or the same directory as calibre_directory with X-Sendfile
     */
    $config['calibre_internal_directory'] = '/Calibre/'; 

    /*
     * Full URL prefix (with trailing /)
     * usefull especially for Opensearch where a full URL is sometimes required
     * For example Mantano requires it.
     */
    $config['cops_full_url'] = ''; 
    
    /*
     * Number of recent books to show
     */
    $config['cops_recentbooks_limit'] = '50'; 
    
    /*
     * Catalog's title
     */
    $config['cops_title_default'] = "Sebastien's COPS"; 

    
    /*
     * Wich header to use when downloading books outside the web directory
     * Possible values are :
     *   X-Accel-Redirect : For Nginx
     *   X-Sendfile : For Lightttpd or Apache (with mod_xsendfile)
     */
    $config['cops_x_accel_redirect'] = "X-Accel-Redirect";
    
    /*
     * Height of thumbnail image for OPDS
     */
    $config['cops_opds_thumbnail_height'] = "40";
    
    /*
     * Height of thumbnail image for HTML
     */
    $config['cops_html_thumbnail_height'] = "70";
    
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
    $config['cops_prefered_format'] = array ("EPUB", "PDF", "MOBI", "CBR", "CBZ");
    
    /*
     * use URL rewriting for downloading of ebook in HTML catalog
     * See README for more information
     *  1 : enable
     *  0 : disable
     */
    $config['cops_use_url_rewriting'] = "0";
    
    /*
     * generate a invalid OPDS stream to allow bad OPDS client to use search
     * Example of non compliant OPDS client : FBReader (was working in May 2012), Moon+ Reader
     * Example of good OPDS client : Mantano
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
?>