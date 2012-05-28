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
     * The internal directory set in nginx config file
     */
    $config['cops_title_default'] = "Sebastien's COPS"; 

    
    /*
     * Wich header to use when downloading books outside the web directory
     * Possible values are :
     *   X-Accel-Redirect : For Nginx
     *   X-Sendfile : For Lightttpd or Apache (with mod_xsendfile)
     */
    $config['cops_x_accel_redirect'] = "X-Accel-Redirect";
?>