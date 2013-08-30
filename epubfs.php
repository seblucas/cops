<?php

require_once ("config.php");
require_once ("base.php");
require_once ("book.php");
require_once ("resources/php-epub-meta/epub.php");

function notFound () {
    header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
    header("Status: 404 Not Found");

    $_SERVER['REDIRECT_STATUS'] = 404;
}

$idData = getURLParam ("data", NULL);
$add = "data=$idData&";
if (!is_null (GetUrlParam (DB))) $add .= DB . "=" . GetUrlParam (DB) . "&";
$myBook = Book::getBookByDataId($idData);

$book = new EPub ($myBook->getFilePath ("EPUB", $idData));

$book->initSpineComponent ();

$component = $_GET["comp"];

if (empty ($component)) {
    notFound ();
}

try {
    $data = $book->component ($component);
    $directory = dirname ($component);
    
    $callback = function ($m) use ($book, $component, $add) {
        $method = $m[1];
        $path = $m[2];
        if (preg_match ("/^#/", $path)) {
            return $path;
        }
        $hash = "";
        if (preg_match ("/^(.+)#(.+)$/", $path, $matches)) {
            $path = $matches [1];
            $hash = "#" . $matches [2];
        }
        $comp = $book->getComponentName ($component, $path);
        if (!$comp) return "#";
        return "$method'epubfs.php?{$add}comp={$comp}{$hash}'";
    };
    
    $data = preg_replace_callback ("/(src=)[\"']([^:]*?)[\"']/", $callback, $data);
    $data = preg_replace_callback ("/(href=)[\"']([^:]*?)[\"']/", $callback, $data);
    $data = preg_replace_callback ("/(\@import\s+)[\"'](.*?)[\"'];/", $callback, $data);
    
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