<?php
/**
 * Epub loader application
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Didier Corbière <didier.corbiere@opale-concept.com>
 */

//------------------------------------------------------------------------------
// Global defines
//------------------------------------------------------------------------------

// Application version
define('DEF_AppVersion', '1.0');
// Application name
define('DEF_AppName', 'epub loader');
// Admin email
define('DEF_AppAdminMail', 'didier.corbiere@opale-concept.com');


//------------------------------------------------------------------------------
// Include files
//------------------------------------------------------------------------------

require_once(__DIR__ . DIRECTORY_SEPARATOR . 'config.php');
require_once($gConfig['cops_directory'] . '/resources/epub-loader/CalibreDbLoader.class.php');
require_once($gConfig['cops_directory'] . '/resources/epub-loader/BookExport.class.php');

//------------------------------------------------------------------------------
// Start application
//------------------------------------------------------------------------------

// Global vars
$gErrorArray = array();

// Get the action to execute
$action = isset($_GET['action']) ? $_GET['action'] : null;

require_once(__DIR__ . DIRECTORY_SEPARATOR . 'header.php');
if (isset($action)) {
	$phpFile = sprintf('%s%saction_%s.php', realpath(dirname(__FILE__)), DIRECTORY_SEPARATOR, $action);
	require_once($phpFile);
}
else {
	// Display the available actions
	echo '	<ul>' . "\n";
	foreach ($gConfig['actions'] as $action => $actionInfo) {
		echo '		<li>' . "\n";
		echo '			<a href="./index.php?action=' . $action . '">' . $actionInfo . '</a>' . "\n";
		echo '		</li>' . "\n";
	}
	echo '	</ul>' . "\n";
}
require_once(__DIR__ . DIRECTORY_SEPARATOR . 'footer.php');

?>
