<?php

require_once ("config.php");
require_once ("base.php");
require_once ("resources/php-epub-meta/epub.php");

header ("Content-Type: text/html;charset=utf-8");

$book = new EPub ("c:/Temp/Alice.epub");
$book->initSpineComponent ();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta http-equiv="imagetoolbar" content="no" />
    <meta name="viewport" content="width=device-width, height=device-height, user-scalable=no" />
    <title>COPS's Epub Reader</title>
    <script type="text/javascript" src="<?php echo getUrlWithVersion("js/jquery-1.9.1.min.js") ?>"></script>
    <script type="text/javascript" src="<?php echo getUrlWithVersion("resources/monocle/scripts/monocore.js") ?>"></script>
    <link rel="stylesheet" type="text/css" href="<?php echo getUrlWithVersion("resources/monocle/styles/monocore.css") ?>" media="screen" />
    <script type="text/javascript">
        var bookData = {
          getComponents: function () {
            <?php echo "return [" . implode (", ", array_map (function ($comp) { return "'" . $comp . "'"; }, $book->components ())) . "];"; ?>
          },
          getContents: function () {
            <?php echo "return [" . implode (", ", array_map (function ($content) { return "{title: '" . $content["title"] . "', src: '". $content["src"] . "']"; }, $book->contents ())) . "];"; ?>
            return [
              {
                title: "Chapter 1",
                src: "component1.xhtml"
              },
              {
                title: "Chapter 2",
                src: "component3.xhtml#chapter-2"
              }
            ]
          },
          getComponent: function (componentId) {
            return {
              'component1.xhtml':
                '<h1>Chapter 1</h1><p>Hello world</p>',
              'component2.xhtml':
                '<p>Chapter 1 continued.</p>',
              'component3.xhtml':
                '<p>Chapter 1 continued again.</p>' +
                '<h1 id="chapter-2">Chapter 2</h1>' +
                '<p>Hello from the second chapter.</p>',
              'component4.xhtml':
                '<p>THE END.</p>'
            }[componentId];
          },
          getMetaData: function(key) {
            return {
              title: "A book",
              creator: "Inventive Labs"
            }[key];
          }
        }

    </script>
</head>
<body>
</body>
</html>