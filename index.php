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
    
    // If we detect that an OPDS reader try to connect try to redirect to feed.php
    if (preg_match("/(MantanoReader|FBReader|Stanza|Aldiko|Moon+ Reader)/", $_SERVER['HTTP_USER_AGENT'])) {
        header("location: feed.php");
        exit ();
    }
    
    $withToolbar = false;
    if (!isset($_COOKIE['toolbar'])) $withToolbar = true;
    
    header ("Content-Type:application/xhtml+xml");
    $page = getURLParam ("page", Base::PAGE_INDEX);
    $query = getURLParam ("query");
    $qid = getURLParam ("id");
    $n = getURLParam ("n", "1");
    
    $currentPage = Page::getPage ($page, $qid, $query, $n);
    $currentPage->InitializeContent (); 

/* Test to see if pages are opened on an Eink screen 
 * test Kindle, Kobo Touch and Sony PRS-T1 Ereader. 
 * HTTP_USER_AGENT = "Mozilla/5.0 (Linux; U; en-us; EBRD1101; EXT) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1"
 */

	if (preg_match("/(Kobo|Kindle\/3.0|EBRD1101)/", $_SERVER['HTTP_USER_AGENT'])) {
		$isEink = 1;
	} else {
		$isEink = 0;
	}


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta http-equiv="imagetoolbar" content="no" />
    <meta name="viewport" content="width=device-width, height=device-height, user-scalable=no" />
    <title><?php echo htmlspecialchars ($currentPage->title) ?></title>
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
    <script type="text/javascript" src="fancybox/jquery.fancybox.pack.js?v=2.1.4"></script>
    <script type="text/javascript" src="<?php echo getUrlWithVersion("js/jquery.sortElements.js") ?>"></script>
    <script type="text/javascript" src="<?php echo getUrlWithVersion("js/jquery.cookies.js") ?>"></script>
    <link rel="related" href="feed.php" type="application/atom+xml;profile=opds-catalog" title="<?php echo $config['cops_title_default']; ?>" /> 
    <link rel="icon" type="image/vnd.microsoft.icon" href="<?php echo $currentPage->favicon ?>" />
    <link rel="stylesheet" type="text/css" href="fancybox/jquery.fancybox.css?v=2.1.4" media="screen" />
    <link rel="stylesheet" type="text/css" href="<?php echo getUrlWithVersion("style.css") ?>" media="screen" />
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
            
            $("#settingsImage").click(function(){
                if ($("#tool").is(":hidden")) {
                    $("#tool").slideDown("slow");
                    $.cookie('toolbar', '1');
                } else {
                    $("#tool").slideUp();
                    $.removeCookie('toolbar');
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
    <div class="head">
        <div class="headleft">
            <a href="<?php echo $_SERVER["SCRIPT_NAME"] ?>">
                <img src="<?php echo getUrlWithVersion("images/home.png") ?>" alt="Home" />
            </a>
        </div>
        <div class="headright">
            <img id="settingsImage" src="<?php echo getUrlWithVersion("images/setting64.png") ?>" alt="Settings and menu" />
        </div>
        <div class="headcenter">
            <p><?php echo htmlspecialchars ($currentPage->title) ?></p>
        </div>
    </div>
    <div class="clearer" />
    <div id="tool" <?php if ($withToolbar) echo 'style="display: none"' ?>>
        <div style="float: left; width: 60%">
            <form action="index.php" method="get">
                <div style="float: right">
                    <input type="image" src="images/search32.png" alt="Search" />
                </div>
                <div class="stop">
                    <input type="hidden" name="current" value="<?php echo $page ?>" />
                    <input type="hidden" name="page" value="9" />
                    <input type="text" name="query" />
                </div>
            </form>
        </div>
<?php if ($currentPage->containsBook ()) { ?>
        <div style="float: right; width: 35%">
            <div style="float: right">
                <img id="sort" src="images/sort32.png" alt="Sort" />
            </div>
            <div class="stop">
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
            </div>
        </div>
<?php } ?>
    </div>
    <div class="clearer" />
    <div id="content" style="display: none;"></div>
    <div class="entries">
        <?php
            if ($page == Base::PAGE_BOOK_DETAIL)
            {
                include ("bookdetail.php");
            }
            foreach ($currentPage->entryArray as $entry) {
                if (get_class ($entry) != "EntryBook") {
        ?>
        <div class="entry">
            <div class="entryTitle"><?php echo htmlspecialchars ($entry->title) ?></div>
            <div class="entryContent"><?php echo htmlspecialchars ($entry->content) ?></div>
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
            <?php
                if ($entry->book->hasCover) {
            ?>
                <a rel="group" class="fancycover" href="<?php echo $entry->getCover () ?>"><img src="<?php echo $entry->getCoverThumbnail () ?>" alt="<?php echo localize("i18n.coversection") ?>" /></a>
            <?php
                }
            ?>
            </div>
            <div class="download">
            <?php
                $i = 0;
                foreach ($config['cops_prefered_format'] as $format)
                {
                    if ($i == 2) { break; }
                    if ($data = $entry->book->getDataFormat ($format)) {
                        $i++;
            ?>    
                <div class="button buttonEffect"><a href="<?php echo $data->getHtmlLink () ?>"><?php echo $format ?></a></div>
            <?php
                    }
                }
            ?>
            </div>
            <div class="bookdetail">
                <a class="navigation" href="<?php echo $entry->book->getDetailUrl () ?>" />
                <div class="entryTitle st"><?php echo htmlspecialchars ($entry->title) ?>
            <?php
                if ($entry->book->getPubDate() != "")
                {
            ?>
                    <span class="sp">(<?php echo $entry->book->getPubDate() ?>)</span>
            <?php
                }
            ?>
                    <span class="sr"><?php echo $entry->book->getRating () ?></span>
                </div>
                <div class="entryContent sa"><?php echo localize("authors.title") . " : " . htmlspecialchars ($entry->book->getAuthorsName ()) ?></div>
                <div class="entryContent"><?php echo localize("tags.title") . " : " . htmlspecialchars ($entry->book->getTagsName ()) ?></div>
            <?php
                $serie = $entry->book->getSerie ();
                if (!is_null ($serie)) {
            ?>
                <div class="entryContent ss"><?php echo localize("series.title") . " : " . htmlspecialchars ($serie->name) . " (" . $entry->book->seriesIndex . ")" ?></div>
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
    <div class="foot">
        <div class="footright">
            <a class="fancyabout" href="about.xml"><img src="<?php echo getUrlWithVersion("images/info.png") ?>" alt="Home" /></a>
        </div>
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
    </div>
</div>
</body>
</html>
