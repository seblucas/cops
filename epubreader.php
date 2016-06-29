<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<?php
/**
 * COPS (Calibre OPDS PHP Server) epub reader
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

require_once ("config.php");
require_once ("base.php");
require_once ("book.php");
require_once ("resources/php-epub-meta/epub.php");

header ("Content-Type: text/html;charset=utf-8");

$idData = getURLParam ("data", NULL);
$add = "data=$idData&";
if (!is_null (GetUrlParam (DB))) $add .= DB . "=" . GetUrlParam (DB) . "&";
$myBook = Book::getBookByDataId($idData);

$book = new EPub ($myBook->getFilePath ("EPUB", $idData));
$book->initSpineComponent ();

?>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta http-equiv="imagetoolbar" content="no" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title>COPS's Epub Reader</title>
    <script type="text/javascript" src="<?php echo getUrlWithVersion("resources/monocle/scripts/monocore.js") ?>"></script>
    <script type="text/javascript" src="<?php echo getUrlWithVersion("resources/monocle/scripts/monoctrl.js") ?>"></script>
    <link rel="stylesheet" type="text/css" href="<?php echo getUrlWithVersion("resources/monocle/styles/monocore.css") ?>" media="screen" />
    <link rel="stylesheet" type="text/css" href="<?php echo getUrlWithVersion("resources/monocle/styles/monoctrl.css") ?>" media="screen" />
    <script type="text/javascript">
        Monocle.DEBUG = true;
        var bookData = {
          getComponents: function () {
            <?php echo "return [" . implode (", ", array_map (function ($comp) { return "'" . $comp . "'"; }, $book->components ())) . "];"; ?>
          },
          getContents: function () {
            <?php echo "return [" . implode (", ", array_map (function ($content) { return "{title: '" . addslashes($content["title"]) . "', src: '". $content["src"] . "'}"; }, $book->contents ())) . "];"; ?>
          },
          getComponent: function (componentId) {
            return { url: "epubfs.php?<?php echo $add ?>comp="  + componentId };
          },
          getMetaData: function(key) {
            return {
              title: "<?php echo $myBook->title ?>",
              creator: "Inventive Labs"
            }[key];
          }
        }

    </script>
    <script type="text/javascript" src="<?php echo getUrlWithVersion("styles/cops-monocle.js") ?>"></script>
    <link rel="stylesheet" type="text/css" href="<?php echo getUrlWithVersion("styles/cops-monocle.css") ?>" media="screen" />
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