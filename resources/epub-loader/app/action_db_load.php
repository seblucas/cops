<?php
/**
 * Epub loader application action: load ebooks into calibre databases
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Didier Corbière <contact@atoll-digital-library.org>
 */
/** @var array $dbConfig */
/** @var array $gConfig */

defined('DEF_AppName') or die('Restricted access');

global $gConfig;
global $dbConfig;
global $gErrorArray;

// Init database file
$dbPath = $dbConfig['db_path'];
$calibreFileName = $dbPath . DIRECTORY_SEPARATOR . 'metadata.db';
$bookIdsFileName = $dbPath . DIRECTORY_SEPARATOR . 'bookids.txt';
try {
    // Open or create the database
    $db = new CalibreDbLoader($calibreFileName, $gConfig['create_db'], $bookIdsFileName);
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
    echo sprintf('Load database %s - %d files', $calibreFileName, $nbOk) . '<br />';
} catch (Exception $e) {
    $gErrorArray[$calibreFileName] = $e->getMessage();
}
