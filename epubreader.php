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
    <script type="text/javascript" src="<?php echo getUrlWithVersion("resources/monocle320/scripts/monocore.js") ?>"></script>
    <script type="text/javascript" src="<?php echo getUrlWithVersion("resources/monocle320/scripts/monoctrl.js") ?>"></script>
    <link rel="stylesheet" type="text/css" href="<?php echo getUrlWithVersion("resources/monocle320/styles/monocore.css") ?>" media="screen" />
    <link rel="stylesheet" type="text/css" href="<?php echo getUrlWithVersion("resources/monocle320/styles/monoctrl.css") ?>" media="screen" />
    <script type="text/javascript">
        Monocle.DEBUG = true; 
        var bookData = {
          getComponents: function () {
            <?php echo "return [" . implode (", ", array_map (function ($comp) { return "'" . $comp . "'"; }, $book->components ())) . "];"; ?>
          },
          getContents: function () {
            <?php echo "return [" . implode (", ", array_map (function ($content) { return "{title: '" . $content["title"] . "', src: '". $content["src"] . "'}"; }, $book->contents ())) . "];"; ?>
          },
          getComponent: function (componentId, callback) {
            $.ajax({
                url: "epubfs.php?comp="  + componentId,
                type: 'get',
                dataType: 'text',
                error: function () {alert ("error");},
                success: callback
            });
          },
          getMetaData: function(key) {
            return {
              title: "A book",
              creator: "Inventive Labs"
            }[key];
          }
        }

    </script>
    <script type="text/javascript" src="<?php echo getUrlWithVersion("cops-monocle.js") ?>"></script>
    <link rel="stylesheet" type="text/css" href="<?php echo getUrlWithVersion("cops-monocle.css") ?>" media="screen" />
</head>
<body>
  <div id="readerBg">
      <div class="board"></div>
      <div class="jacket"></div>
      <div class="dummyPage"></div>
      <div class="dummyPage"></div>
      <div class="dummyPage"></div>
      <div class="dummyPage"></div>
      <div class="dummyPage"></div>
  </div>
  <div id="readerCntr">
      <div id="reader"></div>
  </div>
</body>
</html>