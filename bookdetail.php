<?php
/**
 * COPS (Calibre OPDS PHP Server) book detail script
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 *
 */

require_once ("config.php");
require_once ("book.php");

$book = Book::getBookById($_GET["id"]);
$authors = $book->getAuthors ();
$tags = $book->getTags ();
$serie = $book->getSerie ();
$book->getLinkArray ();
 
?>
<div class="bookpopup">
    <div class="booke">
        <div class="cover">
            <?php
                if ($book->hasCover) {
            ?>
            <a href="fetch.php?id=<?php echo $book->id ?>"><img src="fetch.php?id=<?php echo $book->id ?>&amp;height=150" alt="<?php echo localize("i18n.coversection") ?>" /></a>
            <?php
                }
            ?>
        </div>
        <div class="download">
<?php
            foreach ($book->getDatas() as $data)
            {
?>    
                <div class="button buttonEffect"><a href="<?php echo $data->getHtmlLink () ?>"><?php echo $data->format ?></a></div>
<?php
            }
?>
        </div>
        <div class="entryTitle"><a rel="bookmark" href="<?php echo 'index.php' . $book->getUri () ?>"><img src="<?php echo getUrlWithVersion("images/Link.png") ?>" alt="permalink" /></a><?php echo htmlspecialchars ($book->title) ?></div>
        <div class="entrySection">
            <span><?php echo localize("authors.title") ?></span>
            <div class="buttonEffect pad6">
<?php
            $i = 0;
            foreach ($authors as $author) {
                if ($i > 0) echo ", ";
?>
                <a href="index.php<?php echo str_replace ("&", "&amp;", $author->getUri ()) ?>"><?php echo htmlspecialchars ($author->name) ?></a>
<?php
            }
?>
            </div>
        </div>
        <div class="entrySection">
            <span><?php echo localize("tags.title") ?></span>
            <div class="buttonEffect pad6">
<?php
            $i = 0;
            foreach ($tags as $tag) {
                if ($i > 0) echo ", ";
?>
                <a href="index.php<?php echo str_replace ("&", "&amp;", $tag->getUri ()) ?>"><?php echo htmlspecialchars ($tag->name) ?></a>
<?php
            }
?>
            </div>
        </div>
<?php
        if (!is_null ($serie))
        {
?>
        <div class="entrySection">
            <div class="buttonEffect pad6">
                <a href="index.php<?php echo str_replace ("&", "&amp;", $serie->getUri ()) ?>"><?php echo localize("series.title") ?></a>
            </div>
            <?php echo str_format (localize ("content.series.data"), $book->seriesIndex, htmlspecialchars ($serie->name)) ?>
        </div>
<?php
        }
?>
        <div class="entrySection">
            <span><?php echo localize("config.Language.label") ?></span>
            <?php echo $book->getLanguages () ?>
        </div>
    </div>
    <div class="clearer" />
    <hr />
    <div><?php echo localize("content.summary") ?></div>
    <div class="content" style="max-width:700px;"><?php echo $book->getComment (false) ?></div>
    <hr />
</div>