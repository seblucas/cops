<?php
/**
 * COPS (Calibre OPDS PHP Server) HTML main script
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 *
 */
 
    require_once ("config.php");
    require_once ("base.php");
    require_once ("author.php");
    require_once ("serie.php");
    require_once ("tag.php");
    require_once ("book.php");
    
    header ("Content-Type:application/xhtml+xml");
    $page = getURLParam ("page", Base::PAGE_INDEX);
    $query = getURLParam ("query");
    $qid = getURLParam ("id");
    
    $currentPage = Page::getPage ($page, $qid, $query);
    $currentPage->InitializeContent (); 
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta http-equiv="imagetoolbar" content="no" />
    <meta name="viewport" content="width=device-width, height=device-height, user-scalable=no" />
    <title><?php echo $currentPage->title ?></title>
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.min.js"></script>
    <script type="text/javascript" src="fancybox/jquery.fancybox.js?v=2.0.6"></script>
    <link rel="stylesheet" type="text/css" href="fancybox/jquery.fancybox.css?v=2.0.6" media="screen" />
    <link rel="stylesheet" type="text/css" href="<?php echo getUrlWithVersion("style.css") ?>" media="screen" />
    <script type="text/javascript">
        $(document).ready(function() {
            // Handler for .ready() called.
            $(".entry").click(function(){
                $("#loading").show();
                window.location=$(this).find("a").attr("href");
                return false;
            });
            
            $(".fancycover").fancybox({
                'type' : 'image',
                openEffect  : 'none',
                closeEffect	: 'none',
                helpers : {
                    overlay : null
                }
            });
            
            $("#searchImage").click(function(){
                if ($("#search").is(":hidden")) {
                    $("#search").slideDown("slow");
                } else {
                    $("#search").slideUp();
                }
            });
            
            $(".bookdetail").click(function(){
                var url = $(this).find("a").attr("href");
                $('#content').load(url, function(data, stat, req){
                    $.fancybox( {content: data, maxWidth : '700' } );
                });

                return false;
            });
        });
    </script>
</head>
<body>
<div id="loading">
  <p><img src="images/ajax-loader.gif" alt="waiting" /> Please Wait</p>
</div>
<div class="container">
    <div class="head">
        <div class="headleft">
            <a href="<?php echo $_SERVER["SCRIPT_NAME"] ?>">
                <img src="<?php echo getUrlWithVersion("images/home.png") ?>" alt="Home" />
            </a>
        </div>
        <div class="headright">
            <img id="searchImage" src="<?php echo getUrlWithVersion("images/search.png") ?>" alt="Search" />
        </div>
        <div class="headcenter">
            <p><?php echo $currentPage->title ?></p>
        </div>
    </div>
    <div class="clearer" />
    <div id="search" class="search">
        <form action="index.php?page=9" method="get">
            <input type="text" style="width: 200px" name="query" />
            <input type="hidden" name="page" value="9" />
            <input type="submit" value="Search" />
        </form> 
    </div>
    <div class="clearer" />
    <div id="content" style="display: none;"></div>
    <div class="entries">
        <?php
            foreach ($currentPage->entryArray as $entry) {
                if (get_class ($entry) != "EntryBook") {
        ?>
        <div class="entry">
            <div class="entryTitle"><?php echo $entry->title ?></div>
            <div class="entryContent"><?php echo $entry->content ?></div>
        <?php
            foreach ($entry->linkArray as $link) {
        ?>
            <a href="<?php echo $link->hrefXhtml () ?>" class="navigation">nav</a>
        <?php
            }
        ?>
        </div>
        <?php
                }
                else
                {
        ?>
        <div class="book">
            <div class="cover">
                <a class="fancycover" href="<?php echo $entry->getCover () ?>"><img src="<?php echo $entry->getCoverThumbnail () ?>" alt="cover" /></a>
            </div>
            <div class="download">
            <?php
                if (array_key_exists("epub", $entry->book->format)) {
            ?>    
                <div class="button buttonEffect"><a href="<?php echo "download/" . $entry->book->id . "/" . urlencode ($entry->book->format ["epub"]) ?>">EPUB</a></div>
            <?php
                }
            ?>
            <?php
                if (array_key_exists("pdf", $entry->book->format)) {
            ?>    
                <div class="button buttonEffect"><a href="<?php echo "download/" . $entry->book->id . "/" . urlencode ($entry->book->format ["pdf"]) ?>">PDF</a></div>
            <?php
                }
            ?>
            </div>
            <div class="bookdetail">
                <a class="navigation" href="bookdetail.php?id=<?php echo $entry->book->id ?>" />
                <div class="entryTitle"><?php echo htmlspecialchars ($entry->title) ?></div>
                <div class="entryContent"><?php echo localize("authors.title") . " : " . $entry->book->getAuthorsName () ?></div>
                <div class="entryContent"><?php echo localize("tags.title") . " : " . htmlentities ($entry->book->getTagsName ()) ?></div>
            <?php
                $serie = $entry->book->getSerie ();
                if (!is_null ($serie)) {
            ?>
                <div class="entryContent"><?php echo localize("series.title") . " : " . $serie->name . " (" . $entry->book->seriesIndex . ")" ?></div>
            <?php
                }
            ?>
            </div>
        </div>
        <?php
                }
        ?>
        <div class="clearer" />
        <?php
            }
        ?>
    </div>
</div>
</body>
</html>