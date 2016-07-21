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
    require_once ("rating.php");
    require_once ("publisher.php");
    require_once ("serie.php");
    require_once ("tag.php");
    require_once ("language.php");
    require_once ("customcolumn.php");
    require_once ("book.php");
    require_once ("resources/doT-php/doT.php");

    // If we detect that an OPDS reader try to connect try to redirect to feed.php
    if (preg_match("/(MantanoReader|FBReader|Stanza|Marvin|Aldiko|Moon\+ Reader|Chunky|AlReader|org\.ebookdroid)/", $_SERVER['HTTP_USER_AGENT'])) {
        header("location: feed.php");
        exit ();
    }

    $page = getURLParam ("page", Base::PAGE_INDEX);
    $query = getURLParam ("query");
    $qid = getURLParam ("id");
    $n = getURLParam ("n", "1");
    $database = GetUrlParam (DB);


    // Access the database ASAP to be sure it's readable, redirect if that's not the case.
    // It has to be done before any header is sent.
    Base::checkDatabaseAvailability ();

    if ($config ['cops_fetch_protect'] == "1") {
        session_start();
        if (!isset($_SESSION['connected'])) {
            $_SESSION['connected'] = 0;
        }
    }

    header ("Content-Type:text/html;charset=utf-8");

    $data = array("title"                 => $config['cops_title_default'],
                  "version"               => VERSION,
                  "opds_url"              => $config['cops_full_url'] . "feed.php",
                  "customHeader"          => "",
                  "template"              => getCurrentTemplate (),
                  "server_side_rendering" => useServerSideRendering (),
                  "current_css"           => getCurrentCss (),
                  "favico"                => $config['cops_icon'],
                  "getjson_url"           => "getJSON.php?" . addURLParameter (getQueryString (), "complete", 1));
    if (preg_match("/Kindle/", $_SERVER['HTTP_USER_AGENT'])) {
        $data ["customHeader"] = '<style media="screen" type="text/css"> html { font-size: 75%; -webkit-text-size-adjust: 75%; -ms-text-size-adjust: 75%; }</style>';
    }
    $headcontent = file_get_contents('templates/' . getCurrentTemplate () . '/file.html');
    $template = new doT ();
    $dot = $template->template ($headcontent, NULL);
    echo ($dot ($data));
?><body>
<?php
if (useServerSideRendering ()) {
    // Get the data
    require_once ("JSON_renderer.php");
    $data = JSONRenderer::getJson (true);

    echo serverSideRender ($data);
}
?>
</body>
</html>
