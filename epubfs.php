<?php

require_once ("resources/php-epub-meta/epub.php");

function notFound () {
    header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
    header("Status: 404 Not Found");

    $_SERVER['REDIRECT_STATUS'] = 404;
}

$book = new EPub ("c:/Temp/Alice.epub");
$book->initSpineComponent ();

$component = $_GET["comp"];

if (empty ($component)) {
    notFound ();
}

try {
    $data = $book->component ($component);
    $directory = dirname ($component);
    
    $data = preg_replace ("/src=[\"']([^:]*?)[\"']/", "src='epubfs.php?comp=$1'", $data);
    $data = preg_replace ("/href=[\"']([^:]*?)[\"']/", "href='epubfs.php?comp=$1'", $data);
    $data = preg_replace ("/\@import\s+[\"'](.*?)[\"'];/", "@import 'epubfs.php?comp={$directory}/$1';", $data);
    
    header ("Content-Type: " . $book->componentContentType($component));
    echo $data;
}
catch (Exception $e) {
    notFound ();
}


?>