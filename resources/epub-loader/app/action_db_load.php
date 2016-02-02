<?php
/**
 * Epub loader application action: load ebooks into calibre databases
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Didier Corbière <contact@atoll-digital-library.org>
 */

defined('DEF_AppName') or die('Restricted access');

// Init database file
$dbPath = $dbConfig['db_path'];
$fileName = $dbPath . DIRECTORY_SEPARATOR . 'metadata.db';
try {
	// Open or create the database
	$db = new CalibreDbLoader($fileName, $gConfig['create_db']);
	// Add the epub files into the database
	$nbOk = 0;
	$epubPath = $dbConfig['epub_path'];
	if (!empty($epubPath)) {
		$fileList = RecursiveGlob($dbPath . DIRECTORY_SEPARATOR . $epubPath, '*.epub');
		foreach ($fileList as $file) {
			$filePath = substr($file, strlen($dbPath) + 1);
			$error = $db->AddEpub($dbPath, $filePath);
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
