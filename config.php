<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

require_once 'config_default.php';
if (file_exists(dirname(__FILE__) . '/config_local.php') && (php_sapi_name() !== 'cli')) {
    require_once 'config_local.php';
}

$remote_user = array_key_exists('PHP_AUTH_USER', $_SERVER) ? $_SERVER['PHP_AUTH_USER'] : '';
// Clean username, only allow a-z, A-Z, 0-9, -_ chars
$remote_user = preg_replace( "/[^a-zA-Z0-9_-]/", "", $remote_user);
$user_config_file = 'config_local.' . $remote_user . '.php';
if (file_exists(dirname(__FILE__) . '/' . $user_config_file) && (php_sapi_name() !== 'cli')) {
    require_once $user_config_file;
}

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
