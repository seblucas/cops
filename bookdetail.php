<?php
/**
 * COPS (Calibre OPDS PHP Server) main script
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 *
 */

require_once ("config.php");
require_once ("book.php");

$book = Book::getBookById($_GET["id"]);
$authors = $book->getAuthors ();
 
?>
<div class="bookpopup">
    <div class="booke">
        <div class="cover">
            <img src="fetch.php?id=<?php echo $book->id ?>&amp;height=150" alt="cover" />
        </div>
        <div class="entryTitle"><?php echo htmlspecialchars ($book->title) ?></div>
        <div class="authors">
<?php
        $i = 0;
        foreach ($authors as $author) {
            if ($i > 0) echo ", ";
?>
            <a href="kobo.php<?php echo str_replace ("&", "&amp;", $author->getUri ()) ?>"><?php echo $author->name ?></a>
<?php
        }
?>
        </div>
    </div>
    <div class="clearer" />
    <div><?php echo localize("content.summary") ?></div>
    <hr />
    <?php echo $book->getComment () ?>
    <hr />
</div>