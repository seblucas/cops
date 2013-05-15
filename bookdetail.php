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
<div>
<?php
    } else {
?>
<article class="bookpopup">
<?php
    }
?>
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
		<h1><a rel="bookmark" href="<?php echo 'index.php' . $book->getUri () ?>"><img src="<?php echo getUrlWithVersion("images/Link.png") ?>" alt="permalink" /></a><?php echo htmlspecialchars ($book->title) ?></h1>
			
			<h3><?php echo localize("authors.title") ?>: </h3>
			<p class="popupless">
<?php
            $i = 0;
            foreach ($authors as $author) {
                if ($i > 0) echo ", ";
?>
                <a href="<?php $link = new LinkNavigation ($author->getUri ()); echo $link->hrefXhtml () ?>"><?php echo htmlspecialchars ($author->name) ?></a>
<?php
            }
?>
</p><br />
<?php
        if (count ($tags) > 0) {
?>
<h3><?php echo localize("tags.title") ?>: </h3>
<p class="popupless">
<?php
            $i = 0;
            foreach ($tags as $tag) {
                if ($i > 0) echo ", ";
?>
                <a href="<?php $link = new LinkNavigation ($tag->getUri ()); echo $link->hrefXhtml () ?>"><?php echo htmlspecialchars ($tag->name) ?></a>
<?php
            }
?>
</p><br />
<?php
        }
        if (!is_null ($serie))
        {
?>
				<h3><a href="index.php<?php $link = new LinkNavigation ($serie->getUri ()); echo $link->hrefXhtml () ?>"><?php echo localize("series.title") ?></a>: </h3>
            <?php echo str_format (localize ("content.series.data"), $book->seriesIndex, htmlspecialchars ($serie->name)) ?>
<br />
<?php
        }
        if ($book->getPubDate() != "")
        {
?>

<h3><?php echo localize("pubdate.title") ?>: </h3>
            <?php echo $book->getPubDate() ?>

<?php
        }
        if ($book->getLanguages () != "")
        {
?>
<br />
<h3><?php echo localize("config.Language.label") ?>: </h3>
            <?php echo $book->getLanguages () ?>
 <?php
        }
?>
<br />
<p><h4><?php echo localize("content.summary") ?></h4>
<?php if (!isset ($page)) ?><?php echo $book->getComment (false) ?></p>
</article>