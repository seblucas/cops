<?php

require_once ("resources/php-epub-meta/epub.php");

function notFound () {
    header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
    header("Status: 404 Not Found");

    $_SERVER['REDIRECT_STATUS'] = 404;
}

$book = new EPub ("c:/Temp/Phare.epub");
$book->initSpineComponent ();

$component = $_GET["comp"];
$elementPath = NULL;
if (!empty ($_GET) && isset($_GET["path"]) && $_GET["path"] != "") {
    $elementPath = $_GET["path"];
}

if (empty ($component)) {
    notFound ();
}

try {
    $data = $book->component ($component, $elementPath);
    $directory = dirname ($component);
    
    $data = preg_replace ("/src=[\"']([^:]*?)[\"']/", "src='epubfs.php?path=$1&comp=$component'", $data);
    $data = preg_replace ("/href=[\"']([^:]*?)[\"']/", "href='epubfs.php?path=$1&comp=$component'", $data);
    $data = preg_replace ("/\@import\s+[\"'](.*?)[\"'];/", "@import 'epubfs.php?comp={$directory}/$1';", $data);
    
    header ("Content-Type: " . $book->componentContentType($component));
    echo $data;
}
catch (Exception $e) {
    notFound ();
}


?>