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
 
?>
<div class="bookpopup">
    <div class="booke">
        <div class="cover">
            <img src="fetch.php?id=<?php echo $book->id ?>&amp;height=150" alt="cover" />
        </div>
        <div class="entryTitle"><?php echo htmlspecialchars ($book->title) ?></div>
        <div class="entrySection">
            <span><?php echo localize("authors.title") ?></span>
            <div class="buttonEffect pad6">
<?php
            $i = 0;
            foreach ($authors as $author) {
                if ($i > 0) echo ", ";
?>
                <a href="index.php<?php echo str_replace ("&", "&amp;", $author->getUri ()) ?>"><?php echo $author->name ?></a>
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
                <a href="index.php<?php echo str_replace ("&", "&amp;", $tag->getUri ()) ?>"><?php echo $tag->name ?></a>
<?php
            }
?>
            </div>
        </div>
    </div>
    <div class="clearer" />
    <hr />
    <div><?php echo localize("content.summary") ?></div>
    <?php echo $book->getComment (false) ?>
    <hr />
</div>