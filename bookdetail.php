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
<article class="bookpopup">
            <span class="cover">
            <?php
                if ($book->hasCover) {
            ?>
            <a href="<?php echo Data::getLink ($book, "jpg", "image/jpeg", Link::OPDS_IMAGE_TYPE, "cover.jpg", NULL)->hrefXhtml () ?>">
              <img src="<?php echo Data::getLink ($book, "jpg", "image/jpeg", Link::OPDS_THUMBNAIL_TYPE, "cover.jpg", NULL, NULL, 150)->hrefXhtml () ?>" alt="<?php echo localize("i18n.coversection") ?>" />
            </a>
            <?php
                }
            ?>
            </span>
<?php
            foreach ($book->getDatas() as $data)
            {
?>    
                <h2 class="download"><a href="<?php echo $data->getHtmlLink () ?>"><?php echo $data->format ?></a></h2>
<?php
            }
?>
        <h1><a rel="bookmark" href="<?php echo $book->getDetailUrl (true) ?>"><img src="<?php echo getUrlWithVersion("images/Link.png") ?>" alt="<?php echo localize ("permalink.alternate") ?>" /></a><?php echo htmlspecialchars ($book->title) ?></h1>
            <p class="popupless">
            <h3><?php echo localize("authors.title") ?>: </h3>
            
<?php
            $i = 0;
            foreach ($authors as $author) {
                if ($i > 0) echo ", ";
?>
                <a href="<?php $link = new LinkNavigation ($author->getUri ()); echo $link->hrefXhtml () ?>"><?php echo htmlspecialchars ($author->name) ?></a>
<?php
            }
?>
</p>
<?php
        if (count ($tags) > 0) {
?>
            <p class="popupless">
            <h3><?php echo localize("tags.title") ?>: </h3>

<?php
            $i = 0;
            foreach ($tags as $tag) {
                if ($i > 0) echo ", ";
?>
                <a href="<?php $link = new LinkNavigation ($tag->getUri ()); echo $link->hrefXhtml () ?>"><?php echo htmlspecialchars ($tag->name) ?></a>
<?php
            }
?>
</p>
<?php
        }
        if (!is_null ($serie))
        {
?>
            <p class="popupless">
                <h3><a href="<?php $link = new LinkNavigation ($serie->getUri ()); echo $link->hrefXhtml () ?>"><?php echo localize("series.title") ?></a>: </h3>
            <?php echo str_format (localize ("content.series.data"), $book->seriesIndex, htmlspecialchars ($serie->name)) ?>
</p>
<?php
        }
        if ($book->getPubDate() != "")
        {
?>
            <p class="popupless">
<h3><?php echo localize("pubdate.title") ?>: </h3>
            <?php echo $book->getPubDate() ?>
</p>
<?php
        }
        if ($book->getLanguages () != "")
        {
?>
            <p class="popupless">
<h3><?php echo localize("language.title") ?>: </h3>
            <?php echo $book->getLanguages () ?>
</p>
 <?php
        }
?>
<br />
<h4><?php echo localize("content.summary") ?></h4>
<div <?php if (!isset ($page)) echo 'style="max-width:700px;"' ?> ><?php echo $book->getComment (false) ?></div>
</article>