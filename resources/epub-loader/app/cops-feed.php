<?php
/**
 * Epub loader application: COPS feed wrapper
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Didier Corbière <contact@atoll-digital-library.org>
 */

/**
 * Feed validator
 *   http://opds-validator.appspot.com/
 */

// Application name
define('DEF_AppName', 'Cops feed');

// Include config file
$fileName = __DIR__ . DIRECTORY_SEPARATOR . 'epub-loader-config.php';
if (!file_exists($fileName)) {
	die ('Missing configuration file: ' . $fileName);
}
require_once($fileName);

// Add cops directory to include path
$includePath = ini_get('include_path');
ini_set('include_path', $includePath . PATH_SEPARATOR . $gConfig['cops_directory']);

// Include COPS feed
$fileName = $gConfig['cops_directory'] . '/feed.php';
if (!file_exists($fileName)) {
	die ('Incorrect include file: ' . $fileName);
}
require_once($fileName);

?>
