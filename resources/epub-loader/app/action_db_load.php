<?php
/**
 * Epub loader application action: load ebooks into calibre databases
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Didier Corbière <didier.corbiere@opale-concept.com>
 */

defined('DEF_AppName') or die('Restricted access');

// Init database file
$fileName = $dbConfig['db_path'] . DIRECTORY_SEPARATOR . 'metadata.db';
try {
	// Open or create the database
	$db = new CalibreDbLoader($fileName, $gConfig['create_db']);
	// Add the epub files into the database
	$nbOk = 0;
	if (!empty($dbConfig['epub_path'])) {
		$fileList = RecursiveGlob($dbConfig['epub_path'], '*.epub');
		foreach ($fileList as $file) {
			$error = $db->AddEpub($file);
			if (!empty($error)) {
				$gErrorArray[$file] = $error;
				continue;
			}
			$nbOk++;
		}
	}
	// Display info
	echo sprintf('Load database %s - %d files', $fileName, $nbOk) . '<br />';
}
catch (Exception $e) {
	$gErrorArray[$fileName] = $e->getMessage();
}

?>
