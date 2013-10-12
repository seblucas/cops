<?php
/**
 * Epub loader application action: export ebooks info in a csv files
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Didier Corbière <didier.corbiere@opale-concept.com>
 */

defined('DEF_AppName') or die('Restricted access');

// Init csv file
$fileName = $dbConfig['db_path'] . DIRECTORY_SEPARATOR . basename($dbConfig['db_path']) . '_metadata.csv';
try {
	// Open or create the export file
	$export = new BookExport($fileName, BookExport::eExportTypeCsv, true);
	// Add the epub files into the export file
	$nbOk = 0;
	if (!empty($dbConfig['epub_path'])) {
		$fileList = RecursiveGlob($dbConfig['epub_path'], '*.epub');
		foreach ($fileList as $file) {
			$error = $export->AddEpub($file);
			if (!empty($error)) {
				$gErrorArray[$file] = $error;
				continue;
			}
			$nbOk++;
		}
	}
	// Save export
	$export->SaveToFile();
	// Display info
	echo sprintf('Export ebooks to %s - %d files', $fileName, $nbOk) . '<br />';
}
catch (Exception $e) {
	$gErrorArray[$fileName] = $e->getMessage();
}

?>
