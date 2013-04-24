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
<?php
    if (isset ($page) &&  $page == Base::PAGE_BOOK_DETAIL) {
?>
<div class="bookdetail">
<?php
    } else {
?>
<div class="bookpopup">
<?php
    }
?>
    <div class="booke">
        <div class="cover">
            <?php
                if ($book->hasCover) {
            ?>
            <a href="<?php echo Data::getLink ($book, "jpg", "image/jpeg", Link::OPDS_IMAGE_TYPE, "cover.jpg", NULL)->hrefXhtml () ?>">
              <img src="<?php echo Data::getLink ($book, "jpg", "image/jpeg", Link::OPDS_THUMBNAIL_TYPE, "cover.jpg", NULL, NULL, 150)->hrefXhtml () ?>" alt="<?php echo localize("i18n.coversection") ?>" />
            </a>
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
        <div class="entryTitle"><a rel="bookmark" href="<?php echo $book->getDetailUrl (true) ?>"><img src="<?php echo getUrlWithVersion("images/Link.png") ?>" alt="<?php echo localize ("permalink.alternate") ?>" /></a><?php echo htmlspecialchars ($book->title) ?></div>
        <div class="entrySection">
            <span><?php echo localize("authors.title") ?></span>
            <div class="buttonEffect pad6">
<?php
            $i = 0;
            foreach ($authors as $author) {
                if ($i > 0) echo ", ";
?>
                <a href="<?php $link = new LinkNavigation ($author->getUri ()); echo $link->hrefXhtml () ?>"><?php echo htmlspecialchars ($author->name) ?></a>
<?php
            }
?>
            </div>
        </div>
<?php
        if (count ($tags) > 0) {
?>
        <div class="entrySection">
            <span><?php echo localize("tags.title") ?></span>
            <div class="buttonEffect pad6">
<?php
            $i = 0;
            foreach ($tags as $tag) {
                if ($i > 0) echo ", ";
?>
                <a href="<?php $link = new LinkNavigation ($tag->getUri ()); echo $link->hrefXhtml () ?>"><?php echo htmlspecialchars ($tag->name) ?></a>
<?php
            }
?>
            </div>
        </div>
<?php
        }
        if (!is_null ($serie))
        {
?>
        <div class="entrySection">
            <div class="buttonEffect pad6">
                <a href="<?php $link = new LinkNavigation ($serie->getUri ()); echo $link->hrefXhtml () ?>"><?php echo localize("series.title") ?></a>
            </div>
            <?php echo str_format (localize ("content.series.data"), $book->seriesIndex, htmlspecialchars ($serie->name)) ?>
        </div>
<?php
        }
        if ($book->getPubDate() != "")
        {
?>
        <div class="entrySection">
            <span><?php echo localize("pubdate.title") ?></span>
            <?php echo $book->getPubDate() ?>
        </div>
<?php
        }
        if ($book->getLanguages () != "")
        {
?>
        <div class="entrySection">
            <span><?php echo localize("config.Language.label") ?></span>
            <?php echo $book->getLanguages () ?>
        </div>
<?php
        }
?>
    </div>
    <div class="clearer" ></div>
    <hr />
    <div><?php echo localize("content.summary") ?></div>
    <div class="content" <?php if (!isset ($page)) echo 'style="max-width:700px;"' ?>><?php echo $book->getComment (false) ?></div>
    <hr />
</div>