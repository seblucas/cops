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
    
    header ("Content-Type:application/xhtml+xml;charset=utf-8");
    $page = getURLParam ("page", Base::PAGE_INDEX);
    $query = getURLParam ("query");
    $qid = getURLParam ("id");
    $n = getURLParam ("n", "1");
    $database = GetUrlParam (DB);
    
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
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo htmlspecialchars ($currentPage->title) ?></title>
    <script type="text/javascript" src="<?php echo getUrlWithVersion("js/jquery-1.9.1.min.js") ?>"></script>
    <script type="text/javascript" src="<?php echo getUrlWithVersion("js/jquery.cookies.js") ?>"></script>
<?php if ($config['cops_use_fancyapps'] == 1) { ?>
    <script type="text/javascript" src="<?php echo getUrlWithVersion("resources/fancybox/jquery.fancybox.pack.js") ?>"></script>
    <link rel="stylesheet" type="text/css" href="<?php echo getUrlWithVersion("resources/fancybox/jquery.fancybox.css") ?>" media="screen" />
<?php } ?>
    <script type="text/javascript" src="<?php echo getUrlWithVersion("js/jquery.sortElements.js") ?>"></script>
    <link rel="related" href="<?php echo $config['cops_full_url'] ?>feed.php" type="application/atom+xml;profile=opds-catalog" title="<?php echo $config['cops_title_default']; ?>" /> 
    <link rel="icon" type="image/vnd.microsoft.icon" href="<?php echo $currentPage->favicon ?>" />
    <link rel="stylesheet" type="text/css" href="<?php echo getUrlWithVersion("style.css") ?>" media="screen" />
    <link rel="stylesheet" href="//normalize-css.googlecode.com/svn/trunk/normalize.css" />
    <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,300italic,800,300,400italic,600,600italic,700,700italic,800italic' rel='stylesheet' type='text/css' />
    <script type="text/javascript">
        $(document).ready(function() {
            // Handler for .ready() called.
            $(".entry").click(function(){
                window.location=$(this).find("a").attr("href");
                return false;
            });
            
            $("#sort").click(function(){
                $('.books').sortElements(function(a, b){
                    var test = 1;
                    if ($("#sortorder").val() == "desc")
                    {
                        test = -1;
                    }
                    return $(a).find ("." + $("#sortchoice").val()).text() > $(b).find ("." + $("#sortchoice").val()).text() ? test : -test;
                });
            });
            
<?php if ($config['cops_use_fancyapps'] == 1) { ?>
            $(".fancycover").fancybox({
                'type' : 'image',
                prevEffect      : 'none',
                nextEffect      : 'none'
                <?php if ($isEink) echo ", openEffect : 'none', closeEffect : 'none', helpers : {overlay : null}"; ?>
            });
            
            $(".fancyabout").fancybox({
                'type' : 'ajax',
                title           : 'COPS <?php echo VERSION ?>',
                prevEffect      : 'none',
                nextEffect      : 'none'
                <?php if ($isEink) echo ", openEffect : 'none', closeEffect : 'none', helpers : {overlay : null}"; ?>
            });
<?php } ?>
            
            $(".headright").click(function(){
                if ($("#tool").is(":hidden")) {
                    $("#tool").slideDown("slow");
                    $.cookie('toolbar', '1');
                } else {
                    $("#tool").slideUp();
                    $.removeCookie('toolbar');
                }
            });
            
<?php if  ($page != Base::PAGE_BOOK_DETAIL) { ?>
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
<?php } ?>
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
<div class="container">
    <header>
        <a class="headleft" href="<?php echo $_SERVER["SCRIPT_NAME"]; if ($page != Base::PAGE_INDEX && !is_null ($database)) echo "?" . addURLParameter ("", DB, $database); ?>">
                <img src="<?php echo getUrlWithVersion("images/home.png") ?>" alt="<?php echo localize ("home.alternate") ?>" />
        </a>
        <img class="headright" id="searchImage" src="<?php echo getUrlWithVersion("images/setting64.png") ?>" alt="Settings and menu" />
        <div class="headcenter">
            <h1><?php echo htmlspecialchars ($currentPage->title) ?></h1>
        </div>
        <div id="tool" <?php if ($withToolbar) echo 'style="display: none"' ?>>
            <div style="float: left; width: 60%">
                <form action="index.php" method="get">
                    <div style="float: right">
                        <input type="image" src="images/search32.png" alt="<?php echo localize ("search.alternate") ?>" />
                    </div>
                    <div class="stop">
                        <input type="hidden" name="current" value="<?php echo $page ?>" />
                        <input type="hidden" name="page" value="9" />
                        <?php if (!is_null ($database)) { ?>
                            <input type="hidden" name="<?php echo DB ?>" value="<?php echo $database ?>" />
                        <?php } ?>
                        <input type="text" name="query" />
                    </div>
                </form>
            </div>
    <?php if ($currentPage->containsBook ()) { ?>
            <div style="float: right; width: 35%">
                <div style="float: right">
                    <img id="sort" src="images/sort32.png" alt="<?php echo localize ("sort.alternate") ?>" />
                </div>
                <div class="stop">
                    <select id="sortchoice">
                        <option value="st"><?php echo localize("bookword.title") ?></option>
                        <option value="sa"><?php echo localize("authors.title") ?></option>
                        <option value="ss"><?php echo localize("series.title") ?></option>
                        <option value="sp"><?php echo localize("content.published") ?></option>
                    </select>
                    <select id="sortorder">
                        <option value="asc"><?php echo localize("search.sortorder.asc") ?></option>
                        <option value="desc"><?php echo localize("search.sortorder.desc") ?></option>
                    </select> 
                </div>
            </div>
    <?php } ?>
        </div>
    </header>
    <div id="content" style="display: none;"></div>
    <section>
<?php
            if ($page == Base::PAGE_BOOK_DETAIL) {
                include ("bookdetail.php");
            } else if ($page == Base::PAGE_ABOUT) {
                readfile ("about.xml");
            }
            foreach ($currentPage->entryArray as $entry) {
                if (get_class ($entry) != "EntryBook") {
?>
        <article>
            <div class="frontpage">
            <?php foreach ($entry->linkArray as $link) { if ($link->type != Link::OPDS_NAVIGATION_TYPE) { continue; } ?> <a href="<?php echo $link->hrefXhtml () ?>">
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
        <article class="books">
            <span class="cover">
            <?php
                if ($entry->book->hasCover) {
            ?>
                <a data-fancybox-group="group" class="fancycover" href="<?php echo $entry->getCover () ?>"><img src="<?php echo $entry->getCoverThumbnail () ?>" alt="<?php echo localize("i18n.coversection") ?>" /></a>
            <?php
                }
            ?>
            </span>
            <h2 class="download">
            <?php
                $i = 0;
                foreach ($config['cops_prefered_format'] as $format)
                {
                    if ($i == 2) { break; }
                    if ($data = $entry->book->getDataFormat ($format)) {
                        $i++;
            ?>    
                <a href="<?php echo $data->getHtmlLink () ?>"><?php echo $format ?></a><br />
                <?php
                    }
                    
                }
            ?>
            </h2>
            <a class="fancyabout" href="<?php echo $entry->book->getDetailUrl () ?>">
            <div class="fullclickpopup">
                <h2><span class="st"><?php echo htmlspecialchars ($entry->title) ?></span>
            <?php
                if ($entry->book->getPubDate() != "")
                {
            ?>
                    <span class="sp">(<?php echo $entry->book->getPubDate() ?>)</span>
            <?php
                }
            ?>
                    <span class="sr"><?php echo $entry->book->getRating () ?></span></h2>
                <h4><?php echo localize("authors.title") . " : " ?></h4><span class="sa"><?php echo htmlspecialchars ($entry->book->getAuthorsName ()) ?></span><br />
                <h4><?php echo localize("tags.title") . " : </h4>" . htmlspecialchars ($entry->book->getTagsName ()) ?><br />
            <?php
                $serie = $entry->book->getSerie ();
                if (!is_null ($serie)) {
            ?>
                <h4><?php echo localize("series.title") . " : "  ?></h4><span class="ss"><?php echo htmlspecialchars ($serie->name) . " (" . $entry->book->seriesIndex . ")" ?></span><br />
            <?php
                }
            ?></div></a>
        </article>
        <?php
                }
        ?>
        <?php
            }
        ?>
    </section>
    <footer>
        <div class="footright">
            <a class="fancyabout" href="<?php if ($config['cops_use_fancyapps'] == 1) { echo "about.xml"; } else { echo $_SERVER["SCRIPT_NAME"] . str_replace ("&", "&amp;", addURLParameter ("?page=16", DB, $database)); } ?>"><img src="<?php echo getUrlWithVersion("images/info.png") ?>" alt="<?php echo localize ("about.title") ?>" /></a>
        </div>
<?php
    if ($currentPage->isPaginated ()) {
?> 

        <div class="footcenter">
        <?php
            if (!is_null ($prevLink)) {
        ?>
        <a href="<?php echo $prevLink->hrefXhtml () ?>" ><img src="<?php echo getUrlWithVersion("images/previous.png") ?>" alt="<?php echo localize ("paging.previous.alternate") ?>" /></a>
        <?php
            }
        ?>
        <p><?php echo " " . $currentPage->n . " / " . $currentPage->getMaxPage () . " " ?></p>
        <?php
            if (!is_null ($nextLink)) {
        ?>
        <a href="<?php echo $nextLink->hrefXhtml () ?>" ><img src="<?php echo getUrlWithVersion("images/next.png") ?>" alt="<?php echo localize ("paging.next.alternate") ?>" /></a>
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
