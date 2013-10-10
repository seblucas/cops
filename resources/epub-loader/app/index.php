<?php
/**
 * Epub loader application
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Didier Corbière <didier.corbiere@opale-concept.com>
 */

//------------------------------------------------------------------------------
// Include files
//------------------------------------------------------------------------------

// Include config file
$fileName = __DIR__ . DIRECTORY_SEPARATOR . 'epub-loader-config.php';
if (!file_exists($fileName)) {
	die ('Missing configuration file: ' . $fileName);
}
require_once($fileName);

// Include Calibre database loader class
$fileName = $gConfig['cops_directory'] . '/resources/epub-loader/CalibreDbLoader.class.php';
if (!file_exists($fileName)) {
	die ('Incorrect include file: ' . $fileName);
}
require_once($fileName);

// Include book export class
$fileName = $gConfig['cops_directory'] . '/resources/epub-loader/BookExport.class.php';
if (!file_exists($fileName)) {
	die ('Incorrect include file: ' . $fileName);
}
require_once($fileName);

//------------------------------------------------------------------------------
// Start application
//------------------------------------------------------------------------------

// Global vars
$gErrorArray = array();

// Get the url parameters
$action = isset($_GET['action']) ? $_GET['action'] : null;
$dbNum = isset($_GET['dbnum']) ? (int)$_GET['dbnum'] : null;

// Include html header
require_once(__DIR__ . DIRECTORY_SEPARATOR . 'header.php');

/**
 * Recursive get files
 *
 * @param string Base directory to search in
 * @param string Search pattern
 */
function RecursiveGlob($inPath = '', $inPattern = '*')
{
	$res = array();

	// Check path
	if (!is_dir($inPath)) {
		return $res;
	}

	// Get the list of directories
	if (substr($inPath, -1) != DIRECTORY_SEPARATOR) {
		$inPath .= DIRECTORY_SEPARATOR;
	}

	// Add files from the current directory
	$files = glob($inPath . $inPattern, GLOB_MARK | GLOB_NOSORT);
	foreach ($files as $item) {
		if (substr($item, -1) == DIRECTORY_SEPARATOR) {
			continue;
		}
		$res[] = $item;
	}

	// Scan sub directories
	$paths = glob($inPath . '*', GLOB_MARK | GLOB_ONLYDIR | GLOB_NOSORT);
	foreach ($paths as $path) {
		$res = array_merge($res, RecursiveGlob($path, $inPattern));
	}

	return $res;
}

// Html content
if (isset($action) && isset($dbNum)) {
	if (!isset($gConfig['databases'][$dbNum])) {
		die ('Incorrect database num: ' . $dbNum);
	}
	$dbConfig = $gConfig['databases'][$dbNum];
	$dbPath = $dbConfig['db_path'];
	if (!is_dir($dbPath)) {
		if (!mkdir($dbPath, 0755, true)) {
			die ('Cannot create directory: ' . $dbPath);
		}
	}
	$fileName = sprintf('%s%saction_%s.php', __DIR__, DIRECTORY_SEPARATOR, $action);
	if (!file_exists($fileName)) {
		die ('Incorrect action file: ' . $fileName);
	}
	require_once($fileName);
}
else {
	if (!isset($action)) {
		// Display the available actions
		$str = '';
		$str .= '<div><b>' . 'Select action' . '</b></div>' . "\n";
		$str .= '	<ul>' . "\n";
		foreach ($gConfig['actions'] as $action => $actionInfo) {
			$str .= '		<li>' . "\n";
			$str .= '			<a href="./index.php?action=' . $action . '">' . $actionInfo . '</a>' . "\n";
			$str .= '		</li>' . "\n";
		}
		$str .= '	</ul>' . "\n";
		echo $str;
	}
	else {
		// Display databases
		$str = '';
		$str .= '<table width="100%">' . "\n";
		$str .= '<tr>' . "\n";
		$str .= '<th>' . 'Db num' . '</th>' . "\n";
		$str .= '<th>' . 'Db name' . '</th>' . "\n";
		$str .= '<th>' . 'Action' . '</th>' . "\n";
		$str .= '<th>' . 'Db Path' . '</th>' . "\n";
		$str .= '<th>' . 'Epub path' . '</th>' . "\n";
		$str .= '<th>' . 'Nb Files' . '</th>' . "\n";
		$str .= '</tr>' . "\n";
		$actionTitle = $gConfig['actions'][$action];
		foreach ($gConfig['databases'] as $dbNum => $dbConfig) {
			$fileList = RecursiveGlob($dbConfig['epub_path'], '*.epub');
			$str .= '<tr>' . "\n";
			$str .= '<td>' . $dbNum . '</td>' . "\n";
			$str .= '<td>' . $dbConfig['name'] . '</td>' . "\n";
			$str .= '<td>' . '<a href="./index.php?action=' . $action . '&dbnum=' . $dbNum . '">' . $actionTitle . '</a>' . '</td>' . "\n";
			$str .= '<td>' . $dbConfig['db_path'] . '</td>' . "\n";
			$str .= '<td>' . $dbConfig['epub_path'] . '</td>' . "\n";
			$str .= '<td>' . count($fileList) . '</td>' . "\n";
			$str .= '</tr>' . "\n";
			$numWork++;
		}
		$str .= '</table>' . "\n";
		echo $str;
	}
}

// Include html footer
require_once(__DIR__ . DIRECTORY_SEPARATOR . 'footer.php');

?>
