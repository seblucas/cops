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
    $n = getURLParam ("n", "1");
    
    $currentPage = Page::getPage ($page, $qid, $query, $n);
    $currentPage->InitializeContent (); 

/* Test to see if pages are opened on an Eink screen 
 * First test Kindle or Kobo Touch */

	if (preg_match("/(Kobo Touch|Kindle\/3.0)/", $_SERVER['HTTP_USER_AGENT'])) {
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
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta http-equiv="imagetoolbar" content="no" />
    <meta name="viewport" content="width=device-width, height=device-height, user-scalable=no" />
    <title><?php echo htmlspecialchars ($currentPage->title) ?></title>
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.min.js"></script>
    <script type="text/javascript" src="fancybox/jquery.fancybox.js?v=2.0.6"></script>
    <script type="text/javascript" src="<?php echo getUrlWithVersion("js/jquery.sortElements.js") ?>"></script>
    <link rel="stylesheet" type="text/css" href="fancybox/jquery.fancybox.css?v=2.0.6" media="screen" />
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
            
            $(".fancycover").fancybox({
                'type' : 'image',
                prevEffect		: 'none',
                nextEffect		: 'none',
                <?php if ($isEink) echo "openEffect : 'none', closeEffect : 'none', helpers : {overlay : null}"; ?>
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
                    $.fancybox( {
                        content: data, 
                        maxWidth : '700',
                        <?php if ($isEink) echo "margin : [15, 35, 10, 10], openEffect : 'none', closeEffect : 'none', helpers : {overlay : null}"; ?>
                    } );
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
            <img id="searchImage" src="<?php echo getUrlWithVersion("images/setting64.png") ?>" alt="Settings and menu" />
        </div>
        <div class="headcenter">
            <p><?php echo htmlspecialchars ($currentPage->title) ?></p>
        </div>
    </div>
    <div class="clearer" />
    <div class="menu">
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
    </div>
    <div class="clearer" />
    <div id="content" style="display: none;"></div>
    <div class="entries">
        <?php
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
                <a rel="group" class="fancycover" href="<?php echo $entry->getCover () ?>"><img src="<?php echo $entry->getCoverThumbnail () ?>" alt="cover" /></a>
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
                <a class="navigation" href="bookdetail.php?id=<?php echo $entry->book->id ?>" />
                <div class="entryTitle st"><?php echo htmlspecialchars ($entry->title) ?> <span class="sp">(<?php echo date ('Y', $entry->book->pubdate) ?>)</span></div>
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
</div>
</body>
</html>
