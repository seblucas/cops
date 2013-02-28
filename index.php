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
    require_once ("customcolumn.php");
    require_once ("book.php");
    
    header ("Content-Type:application/xhtml+xml");
    $page = getURLParam ("page", Base::PAGE_INDEX);
    $query = getURLParam ("query");
    $qid = getURLParam ("id");
    $n = getURLParam ("n", "1");
    
    $currentPage = Page::getPage ($page, $qid, $query, $n);
    $currentPage->InitializeContent (); 

/* Test to see if pages are opened on an Eink screen 
 * First test Kindle or Kobo Touch */

	if (preg_match("/(Kobo|Kindle\/3.0)/", $_SERVER['HTTP_USER_AGENT'])) {
		$isEink = 1;

/* Test Sony PRS-T1 Ereader. 
   HTTP_USER_AGENT = "Mozilla/5.0 (Linux; U; en-us; EBRD1101; EXT) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1"

*/
	
	} else if (preg_match("/EBRD1101/i", $_SERVER['HTTP_USER_AGENT'])) {
		$isEink = 1;
	
/* No Eink screens found */
	} else {
		$isEink = 0;
	}


?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="imagetoolbar" content="no" />
    <meta name="viewport" content="width=device-width, height=device-height, user-scalable=no" />
    <title><?php echo htmlspecialchars ($currentPage->title) ?></title>
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.min.js"></script>
    <script type="text/javascript" src="fancybox/jquery.fancybox.pack.js?v=2.1.3"></script>
    <script type="text/javascript" src="<?php echo getUrlWithVersion("js/jquery.sortElements.js") ?>"></script>
    <link rel="related" href="feed.php" type="application/atom+xml;profile=opds-catalog" title="<?php echo $config['cops_title_default']; ?>" /> 
    <link rel="icon" type="image/vnd.microsoft.icon" href="<?php echo $currentPage->favicon ?>" />
    <link rel="stylesheet" type="text/css" href="fancybox/jquery.fancybox.css?v=2.1.3" media="screen" />
    <link rel="stylesheet" type="text/css" href="<?php echo getUrlWithVersion("style.css") ?>" media="screen" />
	<link rel="stylesheet" href="//normalize-css.googlecode.com/svn/trunk/normalize.css" />
    <script type="text/javascript">
        $(document).ready(function() {
            // Handler for .ready() called.
            $(".entry").click(function(){
                window.location=$(this).find("a").attr("href");
                return false;
            });
            
            $("#sort").click(function(){
                $('.book').sortElements(function(a, b){
                    var test = 1;
                    if ($("#sortorder").val() == "desc")
                    {
                        test = -1;
                    }
                    return $(a).find ("." + $("#sortchoice").val()).text() > $(b).find ("." + $("#sortchoice").val()).text() ? test : -test;
                });
                $("#search").slideUp();
            });
            
<?php if ($config['cops_use_fancyapps'] == 1) { ?>
            $(".fancycover").fancybox({
                'type' : 'image',
                prevEffect		: 'none',
                nextEffect		: 'none'
                <?php if ($isEink) echo ", openEffect : 'none', closeEffect : 'none', helpers : {overlay : null}"; ?>
            });
<?php } ?>
            
            $(".fancyabout").fancybox({
                'type' : 'ajax',
                title           : 'COPS <?php echo VERSION ?>',
                prevEffect		: 'none',
                nextEffect		: 'none'
                <?php if ($isEink) echo ", openEffect : 'none', closeEffect : 'none', helpers : {overlay : null}"; ?>
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
<?php if ($config['cops_use_fancyapps'] == 0) { ?>
                window.location = url;
<?php } else { ?>
                $('#content').load(url, function(data, stat, req){
                    $.fancybox( {
                        content: data,
                        autoSize: true
                        <?php if ($isEink) echo ", margin : [15, 35, 10, 10], openEffect : 'none', closeEffect : 'none', helpers : {overlay : null}"; ?>
                    } );
                });
<?php } ?>
                return false;
            });
        });

<?php
    if ($currentPage->isPaginated ()) {
        $prevLink = $currentPage->getPrevLink ();
        $nextLink = $currentPage->getNextLink ();
?>
        $(document).keydown(function(e){
<?php
        if (!is_null ($prevLink)) {
            echo "if (e.keyCode == 37) {\$(location).attr('href','" . $prevLink->hrefXhtml () . "');}"; 
        }
        if (!is_null ($nextLink)) {
            echo "if (e.keyCode == 39) {\$(location).attr('href','" . $nextLink->hrefXhtml () . "');}"; 
        }

?>
        });
<?php
    }
?> 
    </script>
</head>
<body>
<div id="loading">
  <p><img src="images/ajax-loader.gif" alt="waiting" /> Please Wait</p>
</div>
<div class="container">
    <header>
        <a class="headleft" href="<?php echo $_SERVER["SCRIPT_NAME"] ?>">
                <img src="<?php echo getUrlWithVersion("images/home.png") ?>" alt="Home" />
        </a>
        <img class="headright" id="searchImage" src="<?php echo getUrlWithVersion("images/setting64.png") ?>" alt="Settings and menu" />
        <h1><?php echo htmlspecialchars ($currentPage->title) ?></h1>
    </header>
    <aside>
        <div id="search" class="search">
            <form action="index.php?page=9" method="get">
                <input type="text" name="query" />
                <input type="hidden" name="page" value="9" />
                <input type="image" src="images/search32.png" alt="Search" />
            </form>
            <form action="index.php?page=9" method="get">
                <select id="sortchoice">
                    <option value="st"><?php echo localize("bookword.title") ?></option>
                    <option value="sa"><?php echo localize("authors.title") ?></option>
                    <option value="ss"><?php echo localize("series.title") ?></option>
                    <option value="sp"><?php echo localize("content.published") ?></option>
                </select>
                <select id="sortorder">
                    <option value="asc">Asc</option>
                    <option value="desc">Desc</option>
                </select> 
                <img id="sort" src="images/sort32.png" alt="Sort" />
            </form>
        </div>
    </aside>
	
	<div id="content" style="display: none;"></div>
    <section>
        <?php
            if ($page == Base::PAGE_BOOK_DETAIL)
            {
                include ("bookdetail.php");
            }
            foreach ($currentPage->entryArray as $entry) {
                if (get_class ($entry) != "EntryBook") {
        ?>
        <article>
			<div class="frontpage">
			<?php foreach ($entry->linkArray as $link) {?> <a href="<?php echo $link->hrefXhtml () ?>">
					<h2><?php echo htmlspecialchars ($entry->title) ?></h2>
					<?php } ?>
					<h4><?php echo htmlspecialchars ($entry->content) ?></h4> 
				</a>
			</div>
		</article>
        <?php
                }
                else
                {
        ?>
        <article>
			<div class="books">
            <?php
                if ($entry->book->hasCover) {
            ?>
                <span class="cover"><a rel="group" class="fancycover" href="<?php echo $entry->getCover () ?>"><img src="<?php echo $entry->getCoverThumbnail () ?>" alt="<?php echo localize("i18n.coversection") ?>" /></a></span>
            <?php
                }
            ?>
            <?php
                $i = 0;
                foreach ($config['cops_prefered_format'] as $format)
                {
                    if ($i == 2) { break; }
                    if ($data = $entry->book->getDataFormat ($format)) {
                        $i++;
            ?>    
                <h2 class="download"><a href="<?php echo $data->getHtmlLink () ?>"><?php echo $format ?></a></h2>
				<?php
                    }
                }
            ?>
            
            <a class="navigation" href="<?php echo $entry->book->getDetailUrl () ?>" />
                <h2><?php echo htmlspecialchars ($entry->title) ?>
            <?php
                if ($entry->book->getPubDate() != "")
                {
            ?>
                    (<?php echo $entry->book->getPubDate() ?>)
            <?php
                }
            ?>
                    <?php echo $entry->book->getRating () ?></h2>
                <h4><?php echo localize("authors.title") . " : </h4>" . htmlspecialchars ($entry->book->getAuthorsName ()) ?><br />
                <h4><?php echo localize("tags.title") . " : </h4>" . htmlspecialchars ($entry->book->getTagsName ()) ?><br />
            <?php
                $serie = $entry->book->getSerie ();
                if (!is_null ($serie)) {
            ?>
                <h4><?php echo localize("series.title") . " :</h4> " . htmlspecialchars ($serie->name) . " (" . $entry->book->seriesIndex . ")" ?><br />
            <?php
                }
            ?>
			</div>
        </article>
        <?php
                }
        ?>
        <?php
            }
        ?>
    </section>
    <footer>
            <a href="about.xml"><img src="<?php echo getUrlWithVersion("images/info.png") ?>" alt="Home" /></a>
<?php
    if ($currentPage->isPaginated ()) {
?> 

        <div class="footcenter">
        <?php
            if (!is_null ($prevLink)) {
        ?>
        <a href="<?php echo $prevLink->hrefXhtml () ?>" ><img src="<?php echo getUrlWithVersion("images/previous.png") ?>" alt="Previous" /></a>
        <?php
            }
        ?>
        <p><?php echo "&nbsp;" . $currentPage->n . " / " . $currentPage->getMaxPage () . "&nbsp;" ?></p>
        <?php
            if (!is_null ($nextLink)) {
        ?>
        <a href="<?php echo $nextLink->hrefXhtml () ?>" ><img src="<?php echo getUrlWithVersion("images/next.png") ?>" alt="Next" /></a>
        <?php
            }
        ?>
        </div>
<?php
    }
?>
    </footer>
</div>
</body>
</html>
