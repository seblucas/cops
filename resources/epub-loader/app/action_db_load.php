<?php
/**
 * Epub loader application action: load ebooks into calibre databases
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Didier Corbière <didier.corbiere@opale-concept.com>
 */

// Init database file
$fileName = $dbConfig['db_path'] . DIRECTORY_SEPARATOR . 'metadata.db';
try {
	// Open or create the database
	$db = new CalibreDbLoader($fileName, $gConfig['create_db']);
	echo sprintf('Load database %s', $fileName) . '<br />';
	// Add the epub files into the database
	if (!empty($dbConfig['epub_path'])) {
		$fileList = RecursiveGlob($dbConfig['epub_path'], '*.epub');
		foreach ($fileList as $file) {
			$error = $db->AddEpub($file);
			if (!empty($error)) {
				$gErrorArray[$file] = $error;
			}
		}
	}
}
catch (Exception $e) {
	$gErrorArray[$fileName] = $e->getMessage();
}

?>
