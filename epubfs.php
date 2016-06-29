<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

require_once ("config.php");
require_once ("base.php");
require_once ("book.php");
require_once ("resources/php-epub-meta/epub.php");

function getComponentContent ($book, $component, $add) {
    $data = $book->component ($component);

    $callback = function ($m) use ($book, $component, $add) {
        $method = $m[1];
        $path = $m[2];
        $end = "";
        if (preg_match ("/^src\s*:/", $method)) {
            $end = ")";
        }
        if (preg_match ("/^#/", $path)) {
            return "{$method}'{$path}'{$end}";
        }
        $hash = "";
        if (preg_match ("/^(.+)#(.+)$/", $path, $matches)) {
            $path = $matches [1];
            $hash = "#" . $matches [2];
        }
        $comp = $book->getComponentName ($component, $path);
        if (!$comp) return "{$method}'#'{$end}";
        $out = "{$method}'epubfs.php?{$add}comp={$comp}{$hash}'{$end}";
        if ($end) {
            return $out;
        }
        return str_replace ("&", "&amp;", $out);
    };

    $data = preg_replace_callback ("/(src=)[\"']([^:]*?)[\"']/", $callback, $data);
    $data = preg_replace_callback ("/(href=)[\"']([^:]*?)[\"']/", $callback, $data);
    $data = preg_replace_callback ("/(\@import\s+)[\"'](.*?)[\"'];/", $callback, $data);
    $data = preg_replace_callback ("/(src\s*:\s*url\()(.*?)\)/", $callback, $data);

    return $data;
}

if (php_sapi_name() === 'cli') { return; }

$idData = getURLParam ("data", NULL);
$add = "data=$idData&";
if (!is_null (GetUrlParam (DB))) $add .= DB . "=" . GetUrlParam (DB) . "&";
$myBook = Book::getBookByDataId($idData);

$book = new EPub ($myBook->getFilePath ("EPUB", $idData));

$book->initSpineComponent ();

if (!isset ($_GET["comp"])) {
    notFound ();
    return;
}

$component = $_GET["comp"];

try {
    $data = getComponentContent ($book, $component, $add);

    $expires = 60*60*24*14;
    header("Pragma: public");
    header("Cache-Control: maxage=".$expires);
    header('Expires: ' . gmdate('D, d M Y H:i:s', time()+$expires) . ' GMT');
    header ("Content-Type: " . $book->componentContentType($component));
    echo $data;
}
catch (Exception $e) {
    error_log ($e);
    notFound ();
}