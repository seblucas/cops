<?php
/**
 * Epub loader application action: export ebooks in a csv files
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Didier Corbière <contact@atoll-digital-library.org>
 */
/** @var array $dbConfig */

defined('DEF_AppName') or die('Restricted access');

// Init csv file
$dbPath = $dbConfig['db_path'];
$fileName = $dbPath . DIRECTORY_SEPARATOR . basename($dbPath) . '_metadata.csv';
try {
    // Open or create the export file
    $export = new BookExport($fileName, BookExport::eExportTypeCsv, true);
    // Add the epub files into the export file
    $nbOk = 0;
    $epubPath = $dbConfig['epub_path'];
    if (!empty($epubPath)) {
        $fileList = RecursiveGlob($dbPath . DIRECTORY_SEPARATOR . $epubPath, '*.epub');
        foreach ($fileList as $file) {
            $filePath = substr($file, strlen($dbPath) + 1);
            $error = $export->AddEpub($dbPath, $filePath);
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
} catch (Exception $e) {
    $gErrorArray[$fileName] = $e->getMessage();
}
