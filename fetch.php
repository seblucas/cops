<?php
/**
 * COPS (Calibre OPDS PHP Server)
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */
require_once ("config.php");
require_once ("book.php");
require_once ("data.php");

global $config;

if ($config['cops_fetch_protect'] == "1") {
	session_start();
	if (!isset($_SESSION['connected'])) {
		notFound();
		return;
	}
}

$expires = 60 * 60 * 24 * 14;
header("Pragma: public");
header("Cache-Control: maxage=" . $expires);
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $expires) . ' GMT');
$bookId = getURLParam("id", NULL);
$type = getURLParam("type", "jpg");
$idData = getURLParam("data", NULL);
if (is_null($bookId)) {
	$book = Book::getBookByDataId($idData);
}
else {
	$book = Book::getBookById($bookId);
}

if (!$book) {
	notFound();
	return;
}

if ($type == "jpg" || $type == "png") {
	$file = $book->getFilePath($type);
}
else {
	$file = $book->getFilePath($type, $idData);
}
if (!$file || !file_exists($file)) {
	notFound();
	return;
}

switch ($type) {
case "jpg":
	header("Content-Type: image/jpeg");
	if ($book->getThumbnail(getURLParam("width"), getURLParam("height"))) {
		// The cover had to be resized
		return;
	}
	break;
case "png":
	header("Content-Type: image/png");
	if ($book->getThumbnail(getURLParam("width"), getURLParam("height"))) {
		// The cover had to be resized
		return;
	}
	break;
default:
	$data = $book->getDataById($idData);
	header("Content-Type: " . $data->getMimeType());
	break;
}
if ($type == "epub" && $config['cops_update_epub-metadata']) {
	$book->getUpdatedEpub($idData);
	return;
}
if ($type == "jpg" || $type == "png") {
	header('Content-Disposition: filename="' . basename($file) . '"');
}
else {
	header('Content-Disposition: attachment; filename="' . basename($file) . '"');
}

if (empty($config['cops_x_accel_redirect'])) {
	header("Content-Length: " . filesize($file));
	$fp = fopen($file, 'rb');
	fpassthru($fp);
	fclose($fp);
}
else {
	header($config['cops_x_accel_redirect'] . ": " . $file);
}
