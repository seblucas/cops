<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

    require_once 'config_default.php';
    if (file_exists(dirname(__FILE__). '/config_local.php') && (php_sapi_name() !== 'cli'))
        require_once 'config_local.php';

if(!is_null($config['cops_basic_authentication']) &&
    is_array($config['cops_basic_authentication']))
{
    if (!isset($_SERVER['PHP_AUTH_USER']) ||
        (isset($_SERVER['PHP_AUTH_USER']) &&
        ($_SERVER['PHP_AUTH_USER']!=$config['cops_basic_authentication']['username'] ||
        $_SERVER['PHP_AUTH_PW'] != $config['cops_basic_authentication']['password'])))
    {
        header('WWW-Authenticate: Basic realm="COPS Authentication"');
        header('HTTP/1.0 401 Unauthorized');
        echo 'This site is password protected';
        exit;
    }
}
