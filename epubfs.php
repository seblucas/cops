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

if (empty ($component)) {
    notFound ();
}

try {
    $data = $book->component ($component);
    $directory = dirname ($component);
    
    $callback = function ($m) use ($book, $component) {
        $method = $m[1];
        $path = $m[2];
        if (preg_match ("/^#/", $path)) {
            return $path;
        }
        $comp = $book->getComponentName ($component, $path);
        return "$method'epubfs.php?comp=$comp'";
    };
    
    $data = preg_replace_callback ("/(src=)[\"']([^:]*?)[\"']/", $callback, $data);
    $data = preg_replace_callback ("/(href=)[\"']([^:]*?)[\"']/", $callback, $data);
    $data = preg_replace_callback ("/(\@import\s+)[\"'](.*?)[\"'];/", $callback, $data);
    
    header ("Content-Type: " . $book->componentContentType($component));
    echo $data;
}
catch (Exception $e) {
    error_log ($e);
    notFound ();
}


?>