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

    
    header ("Content-Type:application/xhtml+xml;charset=utf-8");
    
    $database = GetUrlParam (DB);
    $use_fancybox = "";
    if (getCurrentOption ("use_fancyapps") == 1) {
        $use_fancybox = "checked='checked'";
    }
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo localize ("customize.title") ?></title>
    <script type="text/javascript" src="<?php echo getUrlWithVersion("js/jquery-1.9.1.min.js") ?>"></script>
    <script type="text/javascript" src="<?php echo getUrlWithVersion("js/jquery.cookies.js") ?>"></script>
    <link rel='stylesheet' type='text/css' href='http://fonts.googleapis.com/css?family=Open+Sans:400,300italic,800,300,400italic,600,600italic,700,700italic,800italic' />
    <link rel="stylesheet" type="text/css" href="<?php echo getUrlWithVersion(getCurrentCss ()) ?>" media="screen" />
    <link rel="stylesheet" type="text/css" href="<?php echo getUrlWithVersion("resources/normalize/normalize.css") ?>" />
    <script type="text/javascript">
        function updateCookie (id) {
            var name = $(id).attr('id');
            var value = $(id).val ();
            $.cookie(name, value);
        }
        
        function updateCookieFromCheckbox (id) {
            var name = $(id).attr('id');
            if ((/^style/).test (name)) {
                name = "style";
            }
            if ($(id).is(":checked"))
            {
                if ($(id).is(':radio')) {
                    var toto = $(id).val ();
                    $.cookie(name, toto);
                } else {
                    $.cookie(name, '1');
                }
            }
            else
            {
                $.cookie(name, '0');
            }
        }
    </script>
</head>
<body>
<div class="container">
    <header>
        <a class="headleft" href="index.php">
                <img src="<?php echo getUrlWithVersion("images/home.png") ?>" alt="<?php echo localize ("home.alternate") ?>" />
        </a>
        <img class="headright" style="visibility: hidden;" id="searchImage" src="<?php echo getUrlWithVersion("images/setting64.png") ?>" alt="Settings and menu" />
        <div class="headcenter">
            <h1><?php echo localize ("customize.title") ?></h1>
        </div>
    </header>
    
    <section>
        <article class="frontpage">
            <h2><?php echo localize ("customize.style") ?></h2>
            <h4>
<?php
            if (!preg_match("/(Kobo|Kindle\/3.0|EBRD1101)/", $_SERVER['HTTP_USER_AGENT'])) {
                echo '<select id="style" onchange="updateCookie (this);">';
            }
                foreach (glob ("styles/style-*.css") as $filename) {
                    if (preg_match ('/styles\/style-(.*?)\.css/', $filename, $m)) {
                        $filename = $m [1];
                    }
                    $selected = "";
                    if (getCurrentOption ("style") == $filename) {
                        if (!preg_match("/(Kobo|Kindle\/3.0|EBRD1101)/", $_SERVER['HTTP_USER_AGENT'])) {
                            $selected = "selected='selected'";
                        } else {
                            $selected = "checked='checked'";
                        }
                    }
                    if (!preg_match("/(Kobo|Kindle\/3.0|EBRD1101)/", $_SERVER['HTTP_USER_AGENT'])) {
                        echo "<option value='{$filename}' {$selected}>{$filename}</option>";
                    } else {
                        echo "<input type='radio' onchange='updateCookieFromCheckbox (this);' id='style-{$filename}' name='style' value='{$filename}' {$selected} /><label for='style-{$filename}'> {$filename} </label>";
                    }
                }
            if (!preg_match("/(Kobo|Kindle\/3.0|EBRD1101)/", $_SERVER['HTTP_USER_AGENT'])) {
                echo '</select>';
            }

?>
            </h4>
        </article>
        <article class="frontpage">
            <h2><?php echo localize ("customize.fancybox") ?></h2>
            <h4><input type="checkbox" onchange="updateCookieFromCheckbox (this);" id="use_fancyapps" <?php echo $use_fancybox ?> /></h4>
        </article>
    </section>
    
    <footer>
    </footer>
</div>
</body>
</html>
