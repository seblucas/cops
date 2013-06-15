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
    require_once ("language.php");
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
    <title>COPS</title>
    <script type="text/javascript" src="<?php echo getUrlWithVersion("js/jquery-1.9.1.min.js") ?>"></script>
    <script type="text/javascript" src="<?php echo getUrlWithVersion("js/jquery.cookies.js") ?>"></script>
<?php if (getCurrentOption ('use_fancyapps') == 1) { ?>
    <script type="text/javascript" src="<?php echo getUrlWithVersion("resources/fancybox/jquery.fancybox.pack.js") ?>"></script>
    <link rel="stylesheet" type="text/css" href="<?php echo getUrlWithVersion("resources/fancybox/jquery.fancybox.css") ?>" media="screen" />
<?php } ?>
    <script type="text/javascript" src="<?php echo getUrlWithVersion("js/jquery.sortElements.js") ?>"></script>
    <script type="text/javascript" src="<?php echo getUrlWithVersion("resources/doT/doT.min.js") ?>"></script>
    <script type="text/javascript" src="<?php echo getUrlWithVersion("util.js") ?>"></script>
    <link rel="related" href="<?php echo $config['cops_full_url'] ?>feed.php" type="application/atom+xml;profile=opds-catalog" title="<?php echo $config['cops_title_default']; ?>" /> 
    <link rel="icon" type="image/vnd.microsoft.icon" href="favicon.ico" />
    <link rel='stylesheet' type='text/css' href='http://fonts.googleapis.com/css?family=Open+Sans:400,300italic,800,300,400italic,600,600italic,700,700italic,800italic' />
    <link rel="stylesheet" type="text/css" href="<?php echo getUrlWithVersion(getCurrentCss ()) ?>" media="screen" />
    <link rel="stylesheet" type="text/css" href="<?php echo getUrlWithVersion("resources/normalize/normalize.css") ?>" />
    <script type="text/javascript">
    
        $(document).ready(function() {
            // Handler for .ready() called.
            
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
            
<?php if (getCurrentOption ('use_fancyapps') == 1) { ?>
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
            
            $(".fancydetail").fancybox({
                'type' : 'ajax',
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
            
            $.get('templates/default/frontpage.html', function(data){
                template = doT.template(data);
                $.getJSON('<?php echo "getJSON.php?" . str_replace ("&", "&amp;", $_SERVER["QUERY_STRING"]); ?>', function(data) {
                    result = template (data);
                    document.title = data.title;
                    $(".container").html (result);
                    
                    ajaxifyLinks ();
                });
            });
        });
        
        

    </script>
</head>
<body>
<div id="content" style="display: none;"></div>
<div class="container">    
</div>
</body>
</html>
