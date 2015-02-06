<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     S�bastien Lucas <sebastien@slucas.fr>
 */

define ("VERSION", "1.0.0RC4");
define ("DB", "db");
date_default_timezone_set($config['default_timezone']);


function useServerSideRendering () {
    global $config;
    return preg_match("/" . $config['cops_server_side_render'] . "/", $_SERVER['HTTP_USER_AGENT']);
}

function serverSideRender ($data) {
    // Get the templates
    $theme = getCurrentTemplate ();
    $header = file_get_contents('templates/' . $theme . '/header.html');
    $footer = file_get_contents('templates/' . $theme . '/footer.html');
    $main = file_get_contents('templates/' . $theme . '/main.html');
    $bookdetail = file_get_contents('templates/' . $theme . '/bookdetail.html');
    $page = file_get_contents('templates/' . $theme . '/page.html');

    // Generate the function for the template
    $template = new doT ();
    $dot = $template->template ($page, array ("bookdetail" => $bookdetail,
                                              "header" => $header,
                                              "footer" => $footer,
                                              "main" => $main));
    // If there is a syntax error in the function created
    // $dot will be equal to FALSE
    if (!$dot) {
        return FALSE;
    }
    // Execute the template
    if (!empty ($data)) {
        return $dot ($data);
    }

    return NULL;
}

function getQueryString () {
    if ( isset($_SERVER['QUERY_STRING']) ) {
        return $_SERVER['QUERY_STRING'];
    }
    return "";
}

function notFound () {
    header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
    header("Status: 404 Not Found");

    $_SERVER['REDIRECT_STATUS'] = 404;
}

function getURLParam ($name, $default = NULL) {
    if (!empty ($_GET) && isset($_GET[$name]) && $_GET[$name] != "") {
        return $_GET[$name];
    }
    return $default;
}

function getCurrentOption ($option) {
    global $config;
    if (isset($_COOKIE[$option])) {
        if (isset($config ["cops_" . $option]) && is_array ($config ["cops_" . $option])) {
            return explode (",", $_COOKIE[$option]);
        } else {
            return $_COOKIE[$option];
        }
    }
    if ($option == "style") {
        return "default";
    }

    if (isset($config ["cops_" . $option])) {
        return $config ["cops_" . $option];
    }

    return "";
}

function getCurrentCss () {
    return "templates/" . getCurrentTemplate () . "/styles/style-" . getCurrentOption ("style") . ".css";
}

function getCurrentTemplate () {
    return getCurrentOption ("template");
}

function getUrlWithVersion ($url) {
    return $url . "?v=" . VERSION;
}

function xml2xhtml($xml) {
    return preg_replace_callback('#<(\w+)([^>]*)\s*/>#s', create_function('$m', '
        $xhtml_tags = array("br", "hr", "input", "frame", "img", "area", "link", "col", "base", "basefont", "param");
        return in_array($m[1], $xhtml_tags) ? "<$m[1]$m[2] />" : "<$m[1]$m[2]></$m[1]>";
    '), $xml);
}

function display_xml_error($error)
{
    $return = "";
    $return .= str_repeat('-', $error->column) . "^\n";

    switch ($error->level) {
        case LIBXML_ERR_WARNING:
            $return .= "Warning $error->code: ";
            break;
         case LIBXML_ERR_ERROR:
            $return .= "Error $error->code: ";
            break;
        case LIBXML_ERR_FATAL:
            $return .= "Fatal Error $error->code: ";
            break;
    }

    $return .= trim($error->message) .
               "\n  Line: $error->line" .
               "\n  Column: $error->column";

    if ($error->file) {
        $return .= "\n  File: $error->file";
    }

    return "$return\n\n--------------------------------------------\n\n";
}

function are_libxml_errors_ok ()
{
    $errors = libxml_get_errors();

    foreach ($errors as $error) {
        if ($error->code == 801) return false;
    }
    return true;
}

function html2xhtml ($html) {
    $doc = new DOMDocument();
    libxml_use_internal_errors(true);

    $doc->loadHTML('<html><head><meta http-equiv="content-type" content="text/html; charset=utf-8"></head><body>' .
                        $html  . '</body></html>'); // Load the HTML
    $output = $doc->saveXML($doc->documentElement); // Transform to an Ansi xml stream
    $output = xml2xhtml($output);
    if (preg_match ('#<html><head><meta http-equiv="content-type" content="text/html; charset=utf-8"></meta></head><body>(.*)</body></html>#ms', $output, $matches)) {
        $output = $matches [1]; // Remove <html><body>
    }
    /*
    // In case of error with summary, use it to debug
    $errors = libxml_get_errors();

    foreach ($errors as $error) {
        $output .= display_xml_error($error);
    }
    */

    if (!are_libxml_errors_ok ()) $output = "HTML code not valid.";

    libxml_use_internal_errors(false);
    return $output;
}

/**
 * This method is a direct copy-paste from
 * http://tmont.com/blargh/2010/1/string-format-in-php
 */
function str_format($format) {
    $args = func_get_args();
    $format = array_shift($args);

    preg_match_all('/(?=\{)\{(\d+)\}(?!\})/', $format, $matches, PREG_OFFSET_CAPTURE);
    $offset = 0;
    foreach ($matches[1] as $data) {
        $i = $data[0];
        $format = substr_replace($format, @$args[$i], $offset + $data[1] - 1, 2 + strlen($i));
        $offset += strlen(@$args[$i]) - 2 - strlen($i);
    }

    return $format;
}

/**
 * Get all accepted languages from the browser and put them in a sorted array
 * languages id are normalized : fr-fr -> fr_FR
 * @return array of languages
 */
function getAcceptLanguages() {
    $langs = array();

    if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        // break up string into pieces (languages and q factors)
        $accept = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
        if (preg_match('/^(\w{2})-\w{2}$/', $accept, $matches)) {
            // Special fix for IE11 which send fr-FR and nothing else
            $accept = $accept . "," . $matches[1] . ";q=0.8";
        }
        preg_match_all('/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i', $accept, $lang_parse);

        if (count($lang_parse[1])) {
            $langs = array();
            foreach ($lang_parse[1] as $lang) {
                // Format the language code (not standard among browsers)
                if (strlen($lang) == 5) {
                    $lang = str_replace("-", "_", $lang);
                    $splitted = preg_split("/_/", $lang);
                    $lang = $splitted[0] . "_" . strtoupper($splitted[1]);
                }
                array_push($langs, $lang);
            }
            // create a list like "en" => 0.8
            $langs = array_combine($langs, $lang_parse[4]);

            // set default to 1 for any without q factor
            foreach ($langs as $lang => $val) {
                if ($val === '') $langs[$lang] = 1;
            }

            // sort list based on value
            arsort($langs, SORT_NUMERIC);
        }
    }

    return $langs;
}

/**
 * Find the best translation file possible based on the accepted languages
 * @return array of language and language file
 */
function getLangAndTranslationFile() {
    global $config;
    $langs = array();
    $lang = "en";
    if (!empty($config['cops_language'])) {
        $lang = $config['cops_language'];
    }
    elseif (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        $langs = getAcceptLanguages();
    }
    //echo var_dump($langs);
    $lang_file = NULL;
    foreach ($langs as $language => $val) {
        $temp_file = dirname(__FILE__). '/lang/Localization_' . $language . '.json';
        if (file_exists($temp_file)) {
            $lang = $language;
            $lang_file = $temp_file;
            break;
        }
    }
    if (empty ($lang_file)) {
        $lang_file = dirname(__FILE__). '/lang/Localization_' . $lang . '.json';
    }
    return array($lang, $lang_file);
}

/**
 * This method is based on this page
 * http://www.mind-it.info/2010/02/22/a-simple-approach-to-localization-in-php/
 */
function localize($phrase, $count=-1, $reset=false) {
    global $config;
    if ($count == 0)
        $phrase .= ".none";
    if ($count == 1)
        $phrase .= ".one";
    if ($count > 1)
        $phrase .= ".many";

    /* Static keyword is used to ensure the file is loaded only once */
    static $translations = NULL;
    if ($reset) {
        $translations = NULL;
    }
    /* If no instance of $translations has occured load the language file */
    if (is_null($translations)) {
        $lang_file_en = NULL;
        list ($lang, $lang_file) = getLangAndTranslationFile();
        if ($lang != "en") {
            $lang_file_en = dirname(__FILE__). '/lang/' . 'Localization_en.json';
        }

        $lang_file_content = file_get_contents($lang_file);
        /* Load the language file as a JSON object and transform it into an associative array */
        $translations = json_decode($lang_file_content, true);

        /* Clean the array of all unfinished translations */
        foreach (array_keys ($translations) as $key) {
            if (preg_match ("/^##TODO##/", $key)) {
                unset ($translations [$key]);
            }
        }
        if ($lang_file_en)
        {
            $lang_file_content = file_get_contents($lang_file_en);
            $translations_en = json_decode($lang_file_content, true);
            $translations = array_merge ($translations_en, $translations);
        }
    }
    if (array_key_exists ($phrase, $translations)) {
        return $translations[$phrase];
    }
    return $phrase;
}

function addURLParameter($urlParams, $paramName, $paramValue) {
    if (empty ($urlParams)) {
        $urlParams = "";
    }
    $start = "";
    if (preg_match ("#^\?(.*)#", $urlParams, $matches)) {
        $start = "?";
        $urlParams = $matches[1];
    }
    $params = array();
    parse_str($urlParams, $params);
    if (empty ($paramValue) && $paramValue != 0) {
        unset ($params[$paramName]);
    } else {
        $params[$paramName] = $paramValue;
    }
    return $start . http_build_query($params);
}

function useNormAndUp () {
    global $config;
    return extension_loaded('mbstring') &&
           extension_loaded('intl') &&
           class_exists("Normalizer", $autoload = false) &&
           $config ['cops_normalized_search'] == "1";
}

function normalizeUtf8String( $s)
{
    $original_string = $s;

    // maps German (umlauts) and other European characters onto two characters before just removing diacritics
    $s    = preg_replace( '@\x{00c4}@u'    , "AE",    $s );    // umlaut Ä => AE
    $s    = preg_replace( '@\x{00d6}@u'    , "OE",    $s );    // umlaut Ö => OE
    $s    = preg_replace( '@\x{00dc}@u'    , "UE",    $s );    // umlaut Ü => UE
    $s    = preg_replace( '@\x{00e4}@u'    , "ae",    $s );    // umlaut ä => ae
    $s    = preg_replace( '@\x{00f6}@u'    , "oe",    $s );    // umlaut ö => oe
    $s    = preg_replace( '@\x{00fc}@u'    , "ue",    $s );    // umlaut ü => ue
    $s    = preg_replace( '@\x{00f1}@u'    , "ny",    $s );    // ñ => ny
    $s    = preg_replace( '@\x{00ff}@u'    , "yu",    $s );    // ÿ => yu


    // maps special characters (characters with diacritics) on their base-character followed by the diacritical mark
        // exmaple:  Ú => U´,  á => a`
    $s    = Normalizer::normalize( $s, Normalizer::FORM_D );


    $s    = preg_replace( '@\pM@u'        , "",    $s );    // removes diacritics


    $s    = preg_replace( '@\x{00df}@u'    , "ss",    $s );    // maps German ß onto ss
    $s    = preg_replace( '@\x{00c6}@u'    , "AE",    $s );    // Æ => AE
    $s    = preg_replace( '@\x{00e6}@u'    , "ae",    $s );    // æ => ae
    $s    = preg_replace( '@\x{0132}@u'    , "IJ",    $s );    // ? => IJ
    $s    = preg_replace( '@\x{0133}@u'    , "ij",    $s );    // ? => ij
    $s    = preg_replace( '@\x{0152}@u'    , "OE",    $s );    // Œ => OE
    $s    = preg_replace( '@\x{0153}@u'    , "oe",    $s );    // œ => oe

    $s    = preg_replace( '@\x{00d0}@u'    , "D",    $s );    // Ð => D
    $s    = preg_replace( '@\x{0110}@u'    , "D",    $s );    // Ð => D
    $s    = preg_replace( '@\x{00f0}@u'    , "d",    $s );    // ð => d
    $s    = preg_replace( '@\x{0111}@u'    , "d",    $s );    // d => d
    $s    = preg_replace( '@\x{0126}@u'    , "H",    $s );    // H => H
    $s    = preg_replace( '@\x{0127}@u'    , "h",    $s );    // h => h
    $s    = preg_replace( '@\x{0131}@u'    , "i",    $s );    // i => i
    $s    = preg_replace( '@\x{0138}@u'    , "k",    $s );    // ? => k
    $s    = preg_replace( '@\x{013f}@u'    , "L",    $s );    // ? => L
    $s    = preg_replace( '@\x{0141}@u'    , "L",    $s );    // L => L
    $s    = preg_replace( '@\x{0140}@u'    , "l",    $s );    // ? => l
    $s    = preg_replace( '@\x{0142}@u'    , "l",    $s );    // l => l
    $s    = preg_replace( '@\x{014a}@u'    , "N",    $s );    // ? => N
    $s    = preg_replace( '@\x{0149}@u'    , "n",    $s );    // ? => n
    $s    = preg_replace( '@\x{014b}@u'    , "n",    $s );    // ? => n
    $s    = preg_replace( '@\x{00d8}@u'    , "O",    $s );    // Ø => O
    $s    = preg_replace( '@\x{00f8}@u'    , "o",    $s );    // ø => o
    $s    = preg_replace( '@\x{017f}@u'    , "s",    $s );    // ? => s
    $s    = preg_replace( '@\x{00de}@u'    , "T",    $s );    // Þ => T
    $s    = preg_replace( '@\x{0166}@u'    , "T",    $s );    // T => T
    $s    = preg_replace( '@\x{00fe}@u'    , "t",    $s );    // þ => t
    $s    = preg_replace( '@\x{0167}@u'    , "t",    $s );    // t => t

    // remove all non-ASCii characters
    $s    = preg_replace( '@[^\0-\x80]@u'    , "",    $s );


    // possible errors in UTF8-regular-expressions
    if (empty($s))
        return $original_string;
    else
        return $s;
}

function normAndUp ($a) {
    return mb_strtoupper (normalizeUtf8String($a), 'UTF-8');
}

class Link
{
    const OPDS_THUMBNAIL_TYPE = "http://opds-spec.org/image/thumbnail";
    const OPDS_IMAGE_TYPE = "http://opds-spec.org/image";
    const OPDS_ACQUISITION_TYPE = "http://opds-spec.org/acquisition";
    const OPDS_NAVIGATION_TYPE = "application/atom+xml;profile=opds-catalog;kind=navigation";
    const OPDS_PAGING_TYPE = "application/atom+xml;profile=opds-catalog;kind=acquisition";

    public $href;
    public $type;
    public $rel;
    public $title;
    public $facetGroup;
    public $activeFacet;

    public function __construct($phref, $ptype, $prel = NULL, $ptitle = NULL, $pfacetGroup = NULL, $pactiveFacet = FALSE) {
        $this->href = $phref;
        $this->type = $ptype;
        $this->rel = $prel;
        $this->title = $ptitle;
        $this->facetGroup = $pfacetGroup;
        $this->activeFacet = $pactiveFacet;
    }

    public function hrefXhtml () {
        return $this->href;
    }
}

class LinkNavigation extends Link
{
    public function __construct($phref, $prel = NULL, $ptitle = NULL) {
        parent::__construct ($phref, Link::OPDS_NAVIGATION_TYPE, $prel, $ptitle);
        if (!is_null (GetUrlParam (DB))) $this->href = addURLParameter ($this->href, DB, GetUrlParam (DB));
        if (!preg_match ("#^\?(.*)#", $this->href) && !empty ($this->href)) $this->href = "?" . $this->href;
        if (preg_match ("/(bookdetail|getJSON).php/", $_SERVER["SCRIPT_NAME"])) {
            $this->href = "index.php" . $this->href;
        } else {
            $this->href = $_SERVER["SCRIPT_NAME"] . $this->href;
        }
    }
}

class LinkFacet extends Link
{
    public function __construct($phref, $ptitle = NULL, $pfacetGroup = NULL, $pactiveFacet = FALSE) {
        parent::__construct ($phref, Link::OPDS_PAGING_TYPE, "http://opds-spec.org/facet", $ptitle, $pfacetGroup, $pactiveFacet);
        if (!is_null (GetUrlParam (DB))) $this->href = addURLParameter ($this->href, DB, GetUrlParam (DB));
        $this->href = $_SERVER["SCRIPT_NAME"] . $this->href;
    }
}

class Entry
{
    public $title;
    public $id;
    public $content;
    public $numberOfElement;
    public $contentType;
    public $linkArray;
    public $localUpdated;
    public $className;
    private static $updated = NULL;

    public static $icons = array(
        Author::ALL_AUTHORS_ID       => 'images/author.png',
        Serie::ALL_SERIES_ID         => 'images/serie.png',
        Book::ALL_RECENT_BOOKS_ID    => 'images/recent.png',
        Tag::ALL_TAGS_ID             => 'images/tag.png',
        Language::ALL_LANGUAGES_ID   => 'images/language.png',
        CustomColumn::ALL_CUSTOMS_ID => 'images/tag.png',
        "cops:books$"             => 'images/allbook.png',
        "cops:books:letter"       => 'images/allbook.png',
        Publisher::ALL_PUBLISHERS_ID => 'images/publisher.png'
    );

    public function getUpdatedTime () {
        if (!is_null ($this->localUpdated)) {
            return date (DATE_ATOM, $this->localUpdated);
        }
        if (is_null (self::$updated)) {
            self::$updated = time();
        }
        return date (DATE_ATOM, self::$updated);
    }

    public function getNavLink () {
        foreach ($this->linkArray as $link) {
            if ($link->type != Link::OPDS_NAVIGATION_TYPE) { continue; }

            return $link->hrefXhtml ();
        }
        return "#";
    }

    public function __construct($ptitle, $pid, $pcontent, $pcontentType, $plinkArray, $pclass = "", $pcount = 0) {
        global $config;
        $this->title = $ptitle;
        $this->id = $pid;
        $this->content = $pcontent;
        $this->contentType = $pcontentType;
        $this->linkArray = $plinkArray;
        $this->className = $pclass;
        $this->numberOfElement = $pcount;

        if ($config['cops_show_icons'] == 1)
        {
            foreach (self::$icons as $reg => $image)
            {
                if (preg_match ("/" . $reg . "/", $pid)) {
                    array_push ($this->linkArray, new Link (getUrlWithVersion ($image), "image/png", Link::OPDS_THUMBNAIL_TYPE));
                    break;
                }
            }
        }

        if (!is_null (GetUrlParam (DB))) $this->id = str_replace ("cops:", "cops:" . GetUrlParam (DB) . ":", $this->id);
    }
}

class EntryBook extends Entry
{
    public $book;

    public function __construct($ptitle, $pid, $pcontent, $pcontentType, $plinkArray, $pbook) {
        parent::__construct ($ptitle, $pid, $pcontent, $pcontentType, $plinkArray);
        $this->book = $pbook;
        $this->localUpdated = $pbook->timestamp;
    }

    public function getCoverThumbnail () {
        foreach ($this->linkArray as $link) {
            if ($link->rel == Link::OPDS_THUMBNAIL_TYPE)
                return $link->hrefXhtml ();
        }
        return null;
    }

    public function getCover () {
        foreach ($this->linkArray as $link) {
            if ($link->rel == Link::OPDS_IMAGE_TYPE)
                return $link->hrefXhtml ();
        }
        return null;
    }
}

class Page
{
    public $title;
    public $subtitle = "";
    public $authorName = "";
    public $authorUri = "";
    public $authorEmail = "";
    public $idPage;
    public $idGet;
    public $query;
    public $favicon;
    public $n;
    public $book;
    public $totalNumber = -1;
    public $entryArray = array();

    public static function getPage ($pageId, $id, $query, $n)
    {
        switch ($pageId) {
            case Base::PAGE_ALL_AUTHORS :
                return new PageAllAuthors ($id, $query, $n);
            case Base::PAGE_AUTHORS_FIRST_LETTER :
                return new PageAllAuthorsLetter ($id, $query, $n);
            case Base::PAGE_AUTHOR_DETAIL :
                return new PageAuthorDetail ($id, $query, $n);
            case Base::PAGE_ALL_TAGS :
                return new PageAllTags ($id, $query, $n);
            case Base::PAGE_TAG_DETAIL :
                return new PageTagDetail ($id, $query, $n);
            case Base::PAGE_ALL_LANGUAGES :
                return new PageAllLanguages ($id, $query, $n);
            case Base::PAGE_LANGUAGE_DETAIL :
                return new PageLanguageDetail ($id, $query, $n);
            case Base::PAGE_ALL_CUSTOMS :
                return new PageAllCustoms ($id, $query, $n);
            case Base::PAGE_CUSTOM_DETAIL :
                return new PageCustomDetail ($id, $query, $n);
            case Base::PAGE_ALL_RATINGS :
                return new PageAllRating ($id, $query, $n);
            case Base::PAGE_RATING_DETAIL :
                return new PageRatingDetail ($id, $query, $n);
            case Base::PAGE_ALL_SERIES :
                return new PageAllSeries ($id, $query, $n);
            case Base::PAGE_ALL_BOOKS :
                return new PageAllBooks ($id, $query, $n);
            case Base::PAGE_ALL_BOOKS_LETTER:
                return new PageAllBooksLetter ($id, $query, $n);
            case Base::PAGE_ALL_RECENT_BOOKS :
                return new PageRecentBooks ($id, $query, $n);
            case Base::PAGE_SERIE_DETAIL :
                return new PageSerieDetail ($id, $query, $n);
            case Base::PAGE_OPENSEARCH_QUERY :
                return new PageQueryResult ($id, $query, $n);
            case Base::PAGE_BOOK_DETAIL :
                return new PageBookDetail ($id, $query, $n);
            case Base::PAGE_ALL_PUBLISHERS:
                return new PageAllPublishers ($id, $query, $n);
            case Base::PAGE_PUBLISHER_DETAIL :
                return new PagePublisherDetail ($id, $query, $n);
            case Base::PAGE_ABOUT :
                return new PageAbout ($id, $query, $n);
            case Base::PAGE_CUSTOMIZE :
                return new PageCustomize ($id, $query, $n);
            default:
                $page = new Page ($id, $query, $n);
                $page->idPage = "cops:catalog";
                return $page;
        }
    }

    public function __construct($pid, $pquery, $pn) {
        global $config;

        $this->idGet = $pid;
        $this->query = $pquery;
        $this->n = $pn;
        $this->favicon = $config['cops_icon'];
        $this->authorName = empty($config['cops_author_name']) ? utf8_encode('S�bastien Lucas') : $config['cops_author_name'];
        $this->authorUri = empty($config['cops_author_uri']) ? 'http://blog.slucas.fr' : $config['cops_author_uri'];
        $this->authorEmail = empty($config['cops_author_email']) ? 'sebastien@slucas.fr' : $config['cops_author_email'];
    }

    public function InitializeContent ()
    {
        global $config;
        $this->title = $config['cops_title_default'];
        $this->subtitle = $config['cops_subtitle_default'];
        if (Base::noDatabaseSelected ()) {
            $i = 0;
            foreach (Base::getDbNameList () as $key) {
                $nBooks = Book::getBookCount ($i);
                array_push ($this->entryArray, new Entry ($key, "cops:{$i}:catalog",
                                        str_format (localize ("bookword", $nBooks), $nBooks), "text",
                                        array ( new LinkNavigation ("?" . DB . "={$i}")), "", $nBooks));
                $i++;
                Base::clearDb ();
            }
        } else {
            if (!in_array (PageQueryResult::SCOPE_AUTHOR, getCurrentOption ('ignored_categories'))) {
                array_push ($this->entryArray, Author::getCount());
            }
            if (!in_array (PageQueryResult::SCOPE_SERIES, getCurrentOption ('ignored_categories'))) {
                $series = Serie::getCount();
                if (!is_null ($series)) array_push ($this->entryArray, $series);
            }
            if (!in_array (PageQueryResult::SCOPE_PUBLISHER, getCurrentOption ('ignored_categories'))) {
                $publisher = Publisher::getCount();
                if (!is_null ($publisher)) array_push ($this->entryArray, $publisher);
            }
            if (!in_array (PageQueryResult::SCOPE_TAG, getCurrentOption ('ignored_categories'))) {
                $tags = Tag::getCount();
                if (!is_null ($tags)) array_push ($this->entryArray, $tags);
            }
            if (!in_array (PageQueryResult::SCOPE_RATING, getCurrentOption ('ignored_categories'))) {
                $rating = Rating::getCount();
                if (!is_null ($rating)) array_push ($this->entryArray, $rating);
            }
            if (!in_array ("language", getCurrentOption ('ignored_categories'))) {
                $languages = Language::getCount();
                if (!is_null ($languages)) array_push ($this->entryArray, $languages);
            }
            foreach ($config['cops_calibre_custom_column'] as $lookup) {
                $customId = CustomColumn::getCustomId ($lookup);
                if (!is_null ($customId)) {
                    array_push ($this->entryArray, CustomColumn::getCount($customId));
                }
            }
            $this->entryArray = array_merge ($this->entryArray, Book::getCount());

            if (Base::isMultipleDatabaseEnabled ()) $this->title =  Base::getDbName ();
        }
    }

    public function isPaginated ()
    {
        return (getCurrentOption ("max_item_per_page") != -1 &&
                $this->totalNumber != -1 &&
                $this->totalNumber > getCurrentOption ("max_item_per_page"));
    }

    public function getNextLink ()
    {
        $currentUrl = preg_replace ("/\&n=.*?$/", "", "?" . getQueryString ());
        if (($this->n) * getCurrentOption ("max_item_per_page") < $this->totalNumber) {
            return new LinkNavigation ($currentUrl . "&n=" . ($this->n + 1), "next", localize ("paging.next.alternate"));
        }
        return NULL;
    }

    public function getPrevLink ()
    {
        $currentUrl = preg_replace ("/\&n=.*?$/", "", "?" . getQueryString ());
        if ($this->n > 1) {
            return new LinkNavigation ($currentUrl . "&n=" . ($this->n - 1), "previous", localize ("paging.previous.alternate"));
        }
        return NULL;
    }

    public function getMaxPage ()
    {
        return ceil ($this->totalNumber / getCurrentOption ("max_item_per_page"));
    }

    public function containsBook ()
    {
        if (count ($this->entryArray) == 0) return false;
        if (get_class ($this->entryArray [0]) == "EntryBook") return true;
        return false;
    }
}

class PageAllAuthors extends Page
{
    public function InitializeContent ()
    {
        $this->title = localize("authors.title");
        if (getCurrentOption ("author_split_first_letter") == 1) {
            $this->entryArray = Author::getAllAuthorsByFirstLetter();
        }
        else {
            $this->entryArray = Author::getAllAuthors();
        }
        $this->idPage = Author::ALL_AUTHORS_ID;
    }
}

class PageAllAuthorsLetter extends Page
{
    public function InitializeContent ()
    {
        $this->idPage = Author::getEntryIdByLetter ($this->idGet);
        $this->entryArray = Author::getAuthorsByStartingLetter ($this->idGet);
        $this->title = str_format (localize ("splitByLetter.letter"), str_format (localize ("authorword", count ($this->entryArray)), count ($this->entryArray)), $this->idGet);
    }
}

class PageAuthorDetail extends Page
{
    public function InitializeContent ()
    {
        $author = Author::getAuthorById ($this->idGet);
        $this->idPage = $author->getEntryId ();
        $this->title = $author->name;
        list ($this->entryArray, $this->totalNumber) = Book::getBooksByAuthor ($this->idGet, $this->n);
    }
}

class PageAllPublishers extends Page
{
    public function InitializeContent ()
    {
        $this->title = localize("publishers.title");
        $this->entryArray = Publisher::getAllPublishers();
        $this->idPage = Publisher::ALL_PUBLISHERS_ID;
    }
}

class PagePublisherDetail extends Page
{
    public function InitializeContent ()
    {
        $publisher = Publisher::getPublisherById ($this->idGet);
        $this->title = $publisher->name;
        list ($this->entryArray, $this->totalNumber) = Book::getBooksByPublisher ($this->idGet, $this->n);
        $this->idPage = $publisher->getEntryId ();
    }
}

class PageAllTags extends Page
{
    public function InitializeContent ()
    {
        $this->title = localize("tags.title");
        $this->entryArray = Tag::getAllTags();
        $this->idPage = Tag::ALL_TAGS_ID;
    }
}

class PageAllLanguages extends Page
{
    public function InitializeContent ()
    {
        $this->title = localize("languages.title");
        $this->entryArray = Language::getAllLanguages();
        $this->idPage = Language::ALL_LANGUAGES_ID;
    }
}

class PageCustomDetail extends Page
{
    public function InitializeContent ()
    {
        $customId = getURLParam ("custom", NULL);
        $custom = CustomColumn::getCustomById ($customId, $this->idGet);
        $this->idPage = $custom->getEntryId ();
        $this->title = $custom->name;
        list ($this->entryArray, $this->totalNumber) = Book::getBooksByCustom ($customId, $this->idGet, $this->n);
    }
}

class PageAllCustoms extends Page
{
    public function InitializeContent ()
    {
        $customId = getURLParam ("custom", NULL);
        $this->title = CustomColumn::getAllTitle ($customId);
        $this->entryArray = CustomColumn::getAllCustoms($customId);
        $this->idPage = CustomColumn::getAllCustomsId ($customId);
    }
}

class PageTagDetail extends Page
{
    public function InitializeContent ()
    {
        $tag = Tag::getTagById ($this->idGet);
        $this->idPage = $tag->getEntryId ();
        $this->title = $tag->name;
        list ($this->entryArray, $this->totalNumber) = Book::getBooksByTag ($this->idGet, $this->n);
    }
}

class PageLanguageDetail extends Page
{
    public function InitializeContent ()
    {
        $language = Language::getLanguageById ($this->idGet);
        $this->idPage = $language->getEntryId ();
        $this->title = $language->lang_code;
        list ($this->entryArray, $this->totalNumber) = Book::getBooksByLanguage ($this->idGet, $this->n);
    }
}

class PageAllSeries extends Page
{
    public function InitializeContent ()
    {
        $this->title = localize("series.title");
        $this->entryArray = Serie::getAllSeries();
        $this->idPage = Serie::ALL_SERIES_ID;
    }
}

class PageSerieDetail extends Page
{
    public function InitializeContent ()
    {
        $serie = Serie::getSerieById ($this->idGet);
        $this->title = $serie->name;
        list ($this->entryArray, $this->totalNumber) = Book::getBooksBySeries ($this->idGet, $this->n);
        $this->idPage = $serie->getEntryId ();
    }
}

class PageAllRating extends Page
{
    public function InitializeContent ()
    {
        $this->title = localize("ratings.title");
        $this->entryArray = Rating::getAllRatings();
        $this->idPage = Rating::ALL_RATING_ID;
    }
}

class PageRatingDetail extends Page
{
    public function InitializeContent ()
    {
        $rating = Rating::getRatingById ($this->idGet);
        $this->idPage = $rating->getEntryId ();
        $this->title =str_format (localize ("ratingword", $rating->name/2), $rating->name/2);
        list ($this->entryArray, $this->totalNumber) = Book::getBooksByRating ($this->idGet, $this->n);
    }
}

class PageAllBooks extends Page
{
    public function InitializeContent ()
    {
        $this->title = localize ("allbooks.title");
        if (getCurrentOption ("titles_split_first_letter") == 1) {
            $this->entryArray = Book::getAllBooks();
        }
        else {
            list ($this->entryArray, $this->totalNumber) = Book::getBooks ($this->n);
        }
        $this->idPage = Book::ALL_BOOKS_ID;
    }
}

class PageAllBooksLetter extends Page
{
    public function InitializeContent ()
    {
        list ($this->entryArray, $this->totalNumber) = Book::getBooksByStartingLetter ($this->idGet, $this->n);
        $this->idPage = Book::getEntryIdByLetter ($this->idGet);

        $count = $this->totalNumber;
        if ($count == -1)
            $count = count ($this->entryArray);

        $this->title = str_format (localize ("splitByLetter.letter"), str_format (localize ("bookword", $count), $count), $this->idGet);
    }
}

class PageRecentBooks extends Page
{
    public function InitializeContent ()
    {
        $this->title = localize ("recent.title");
        $this->entryArray = Book::getAllRecentBooks ();
        $this->idPage = Book::ALL_RECENT_BOOKS_ID;
    }
}

class PageQueryResult extends Page
{
    const SCOPE_TAG = "tag";
    const SCOPE_RATING = "rating";
    const SCOPE_SERIES = "series";
    const SCOPE_AUTHOR = "author";
    const SCOPE_BOOK = "book";
    const SCOPE_PUBLISHER = "publisher";

    private function useTypeahead () {
        return !is_null (getURLParam ("search"));
    }

    private function searchByScope ($scope, $limit = FALSE) {
        $n = $this->n;
        $numberPerPage = NULL;
        $queryNormedAndUp = $this->query;
        if (useNormAndUp ()) {
            $queryNormedAndUp = normAndUp ($this->query);
        }
        if ($limit) {
            $n = 1;
            $numberPerPage = 5;
        }
        switch ($scope) {
            case self::SCOPE_BOOK :
                $array = Book::getBooksByStartingLetter ('%' . $queryNormedAndUp, $n, NULL, $numberPerPage);
                break;
            case self::SCOPE_AUTHOR :
                $array = Author::getAuthorsForSearch ('%' . $queryNormedAndUp);
                break;
            case self::SCOPE_SERIES :
                $array = Serie::getAllSeriesByQuery ($queryNormedAndUp);
                break;
            case self::SCOPE_TAG :
                $array = Tag::getAllTagsByQuery ($queryNormedAndUp, $n, NULL, $numberPerPage);
                break;
            case self::SCOPE_PUBLISHER :
                $array = Publisher::getAllPublishersByQuery ($queryNormedAndUp);
                break;
            default:
                $array = Book::getBooksByQuery (
                    array ("all" => "%" . $queryNormedAndUp . "%"), $n);
        }

        return $array;
    }

    public function doSearchByCategory () {
        $database = GetUrlParam (DB);
        $out = array ();
        $pagequery = Base::PAGE_OPENSEARCH_QUERY;
        $dbArray = array ("");
        $d = $database;
        $query = $this->query;
        // Special case when no databases were chosen, we search on all databases
        if (Base::noDatabaseSelected ()) {
            $dbArray = Base::getDbNameList ();
            $d = 0;
        }
        foreach ($dbArray as $key) {
            if (Base::noDatabaseSelected ()) {
                array_push ($this->entryArray, new Entry ($key, DB . ":query:{$d}",
                                        " ", "text",
                                        array ( new LinkNavigation ("?" . DB . "={$d}")), "tt-header"));
                Base::getDb ($d);
            }
            foreach (array (PageQueryResult::SCOPE_BOOK,
                            PageQueryResult::SCOPE_AUTHOR,
                            PageQueryResult::SCOPE_SERIES,
                            PageQueryResult::SCOPE_TAG,
                            PageQueryResult::SCOPE_PUBLISHER) as $key) {
                if (in_array($key, getCurrentOption ('ignored_categories'))) {
                    continue;
                }
                $array = $this->searchByScope ($key, TRUE);

                $i = 0;
                if (count ($array) == 2 && is_array ($array [0])) {
                    $total = $array [1];
                    $array = $array [0];
                } else {
                    $total = count($array);
                }
                if ($total > 0) {
                    // Comment to help the perl i18n script
                    // str_format (localize("bookword", count($array))
                    // str_format (localize("authorword", count($array))
                    // str_format (localize("seriesword", count($array))
                    // str_format (localize("tagword", count($array))
                    // str_format (localize("publisherword", count($array))
                    array_push ($this->entryArray, new Entry (str_format (localize ("search.result.{$key}"), $this->query), DB . ":query:{$d}:{$key}",
                                        str_format (localize("{$key}word", $total), $total), "text",
                                        array ( new LinkNavigation ("?page={$pagequery}&query={$query}&db={$d}&scope={$key}")),
                                        Base::noDatabaseSelected () ? "" : "tt-header", $total));
                }
                if (!Base::noDatabaseSelected () && $this->useTypeahead ()) {
                    foreach ($array as $entry) {
                        array_push ($this->entryArray, $entry);
                        $i++;
                        if ($i > 4) { break; };
                    }
                }
            }
            $d++;
            if (Base::noDatabaseSelected ()) {
                Base::clearDb ();
            }
        }
        return $out;
    }

    public function InitializeContent ()
    {
        $scope = getURLParam ("scope");
        if (empty ($scope)) {
            $this->title = str_format (localize ("search.result"), $this->query);
        } else {
            // Comment to help the perl i18n script
            // str_format (localize ("search.result.author"), $this->query)
            // str_format (localize ("search.result.tag"), $this->query)
            // str_format (localize ("search.result.series"), $this->query)
            // str_format (localize ("search.result.book"), $this->query)
            // str_format (localize ("search.result.publisher"), $this->query)
            $this->title = str_format (localize ("search.result.{$scope}"), $this->query);
        }

        $crit = "%" . $this->query . "%";

        // Special case when we are doing a search and no database is selected
        if (Base::noDatabaseSelected () && !$this->useTypeahead ()) {
            $i = 0;
            foreach (Base::getDbNameList () as $key) {
                Base::clearDb ();
                list ($array, $totalNumber) = Book::getBooksByQuery (array ("all" => $crit), 1, $i, 1);
                array_push ($this->entryArray, new Entry ($key, DB . ":query:{$i}",
                                        str_format (localize ("bookword", $totalNumber), $totalNumber), "text",
                                        array ( new LinkNavigation ("?" . DB . "={$i}&page=9&query=" . $this->query)), "", $totalNumber));
                $i++;
            }
            return;
        }
        if (empty ($scope)) {
            $this->doSearchByCategory ();
            return;
        }

        $array = $this->searchByScope ($scope);
        if (count ($array) == 2 && is_array ($array [0])) {
            list ($this->entryArray, $this->totalNumber) = $array;
        } else {
            $this->entryArray = $array;
        }
    }
}

class PageBookDetail extends Page
{
    public function InitializeContent ()
    {
        $this->book = Book::getBookById ($this->idGet);
        $this->title = $this->book->title;
    }
}

class PageAbout extends Page
{
    public function InitializeContent ()
    {
        $this->title = localize ("about.title");
    }
}

class PageCustomize extends Page
{
    private function isChecked ($key, $testedValue = 1) {
        $value = getCurrentOption ($key);
        if (is_array ($value)) {
            if (in_array ($testedValue, $value)) {
                return "checked='checked'";
            }
        } else {
            if ($value == $testedValue) {
                return "checked='checked'";
            }
        }
        return "";
    }

    private function isSelected ($key, $value) {
        if (getCurrentOption ($key) == $value) {
            return "selected='selected'";
        }
        return "";
    }

    private function getStyleList () {
        $result = array ();
        foreach (glob ("templates/" . getCurrentTemplate () . "/styles/style-*.css") as $filename) {
            if (preg_match ('/styles\/style-(.*?)\.css/', $filename, $m)) {
                array_push ($result, $m [1]);
            }
        }
        return $result;
    }

    public function InitializeContent ()
    {
        $this->title = localize ("customize.title");
        $this->entryArray = array ();

        $ignoredBaseArray = array (PageQueryResult::SCOPE_AUTHOR,
                                   PageQueryResult::SCOPE_TAG,
                                   PageQueryResult::SCOPE_SERIES,
                                   PageQueryResult::SCOPE_PUBLISHER,
                                   PageQueryResult::SCOPE_RATING,
                                   "language");

        $content = "";
        array_push ($this->entryArray, new Entry ("Template", "",
                                        "<span style='cursor: pointer;' onclick='$.cookie(\"template\", \"bootstrap\", { expires: 365 });window.location=$(\".headleft\").attr(\"href\");'>Click to switch to Bootstrap</span>", "text",
                                        array ()));
        if (!preg_match("/(Kobo|Kindle\/3.0|EBRD1101)/", $_SERVER['HTTP_USER_AGENT'])) {
            $content .= '<select id="style" onchange="updateCookie (this);">';
            foreach ($this-> getStyleList () as $filename) {
                $content .= "<option value='{$filename}' " . $this->isSelected ("style", $filename) . ">{$filename}</option>";
            }
            $content .= '</select>';
        } else {
            foreach ($this-> getStyleList () as $filename) {
                $content .= "<input type='radio' onchange='updateCookieFromCheckbox (this);' id='style-{$filename}' name='style' value='{$filename}' " . $this->isChecked ("style", $filename) . " /><label for='style-{$filename}'> {$filename} </label>";
            }
        }
        array_push ($this->entryArray, new Entry (localize ("customize.style"), "",
                                        $content, "text",
                                        array ()));
        if (!useServerSideRendering ()) {
            $content = '<input type="checkbox" onchange="updateCookieFromCheckbox (this);" id="use_fancyapps" ' . $this->isChecked ("use_fancyapps") . ' />';
            array_push ($this->entryArray, new Entry (localize ("customize.fancybox"), "",
                                            $content, "text",
                                            array ()));
        }
        $content = '<input type="number" onchange="updateCookie (this);" id="max_item_per_page" value="' . getCurrentOption ("max_item_per_page") . '" min="-1" max="1200" pattern="^[-+]?[0-9]+$" />';
        array_push ($this->entryArray, new Entry (localize ("customize.paging"), "",
                                        $content, "text",
                                        array ()));
        $content = '<input type="text" onchange="updateCookie (this);" id="email" value="' . getCurrentOption ("email") . '" />';
        array_push ($this->entryArray, new Entry (localize ("customize.email"), "",
                                        $content, "text",
                                        array ()));
        $content = '<input type="checkbox" onchange="updateCookieFromCheckbox (this);" id="html_tag_filter" ' . $this->isChecked ("html_tag_filter") . ' />';
        array_push ($this->entryArray, new Entry (localize ("customize.filter"), "",
                                        $content, "text",
                                        array ()));
        $content = "";
        foreach ($ignoredBaseArray as $key) {
            $keyPlural = preg_replace ('/(ss)$/', 's', $key . "s");
            $content .=  '<input type="checkbox" name="ignored_categories[]" onchange="updateCookieFromCheckboxGroup (this);" id="ignored_categories_' . $key . '" ' . $this->isChecked ("ignored_categories", $key) . ' > ' . localize ("{$keyPlural}.title") . '</input> ';
        }

        array_push ($this->entryArray, new Entry (localize ("customize.ignored"), "",
                                        $content, "text",
                                        array ()));
    }
}


abstract class Base
{
    const PAGE_INDEX = "index";
    const PAGE_ALL_AUTHORS = "1";
    const PAGE_AUTHORS_FIRST_LETTER = "2";
    const PAGE_AUTHOR_DETAIL = "3";
    const PAGE_ALL_BOOKS = "4";
    const PAGE_ALL_BOOKS_LETTER = "5";
    const PAGE_ALL_SERIES = "6";
    const PAGE_SERIE_DETAIL = "7";
    const PAGE_OPENSEARCH = "8";
    const PAGE_OPENSEARCH_QUERY = "9";
    const PAGE_ALL_RECENT_BOOKS = "10";
    const PAGE_ALL_TAGS = "11";
    const PAGE_TAG_DETAIL = "12";
    const PAGE_BOOK_DETAIL = "13";
    const PAGE_ALL_CUSTOMS = "14";
    const PAGE_CUSTOM_DETAIL = "15";
    const PAGE_ABOUT = "16";
    const PAGE_ALL_LANGUAGES = "17";
    const PAGE_LANGUAGE_DETAIL = "18";
    const PAGE_CUSTOMIZE = "19";
    const PAGE_ALL_PUBLISHERS = "20";
    const PAGE_PUBLISHER_DETAIL = "21";
    const PAGE_ALL_RATINGS = "22";
    const PAGE_RATING_DETAIL = "23";

    const COMPATIBILITY_XML_ALDIKO = "aldiko";

    private static $db = NULL;

    public static function isMultipleDatabaseEnabled () {
        global $config;
        return is_array ($config['calibre_directory']);
    }

    public static function useAbsolutePath () {
        global $config;
        $path = self::getDbDirectory();
        return preg_match ('/^\//', $path) || // Linux /
               preg_match ('/^\w\:/', $path); // Windows X:
    }

    public static function noDatabaseSelected () {
        return self::isMultipleDatabaseEnabled () && is_null (GetUrlParam (DB));
    }

    public static function getDbList () {
        global $config;
        if (self::isMultipleDatabaseEnabled ()) {
            return $config['calibre_directory'];
        } else {
            return array ("" => $config['calibre_directory']);
        }
    }

    public static function getDbNameList () {
        global $config;
        if (self::isMultipleDatabaseEnabled ()) {
            return array_keys ($config['calibre_directory']);
        } else {
            return array ("");
        }
    }

    public static function getDbName ($database = NULL) {
        global $config;
        if (self::isMultipleDatabaseEnabled ()) {
            if (is_null ($database)) $database = GetUrlParam (DB, 0);
            $array = array_keys ($config['calibre_directory']);
            return  $array[$database];
        }
        return "";
    }

    public static function getDbDirectory ($database = NULL) {
        global $config;
        if (self::isMultipleDatabaseEnabled ()) {
            if (is_null ($database)) $database = GetUrlParam (DB, 0);
            $array = array_values ($config['calibre_directory']);
            return  $array[$database];
        }
        return $config['calibre_directory'];
    }


    public static function getDbFileName ($database = NULL) {
        return self::getDbDirectory ($database) .'metadata.db';
    }

    private static function error () {
        if (php_sapi_name() != "cli") {
            header("location: checkconfig.php?err=1");
        }
        throw new Exception('Database not found.');
    }

    public static function getDb ($database = NULL) {
        if (is_null (self::$db)) {
            try {
                if (is_readable (self::getDbFileName ($database))) {
                    self::$db = new PDO('sqlite:'. self::getDbFileName ($database));
                    if (useNormAndUp ()) {
                        self::$db->sqliteCreateFunction ('normAndUp', 'normAndUp', 1);
                    }
                } else {
                    self::error ();
                }
            } catch (Exception $e) {
                self::error ();
            }
        }
        return self::$db;
    }

    public static function checkDatabaseAvailability () {
        if (self::noDatabaseSelected ()) {
            for ($i = 0; $i < count (self::getDbList ()); $i++) {
                self::getDb ($i);
                self::clearDb ();
            }
        } else {
            self::getDb ();
        }
        return true;
    }

    public static function clearDb () {
        self::$db = NULL;
    }

    public static function executeQuerySingle ($query, $database = NULL) {
        return self::getDb ($database)->query($query)->fetchColumn();
    }

    public static function getCountGeneric($table, $id, $pageId, $numberOfString = NULL) {
        if (!$numberOfString) {
            $numberOfString = $table . ".alphabetical";
        }
        $count = self::executeQuerySingle ('select count(*) from ' . $table);
        if ($count == 0) return NULL;
        $entry = new Entry (localize($table . ".title"), $id,
            str_format (localize($numberOfString, $count), $count), "text",
            array ( new LinkNavigation ("?page=".$pageId)), "", $count);
        return $entry;
    }

    public static function getEntryArrayWithBookNumber ($query, $columns, $params, $category) {
        list (, $result) = self::executeQuery ($query, $columns, "", $params, -1);
        $entryArray = array();
        while ($post = $result->fetchObject ())
        {
            $instance = new $category ($post);
            if (property_exists($post, "sort")) {
                $title = $post->sort;
            } else {
                $title = $post->name;
            }
            array_push ($entryArray, new Entry ($title, $instance->getEntryId (),
                str_format (localize("bookword", $post->count), $post->count), "text",
                array ( new LinkNavigation ($instance->getUri ())), "", $post->count));
        }
        return $entryArray;
    }

    public static function executeQuery($query, $columns, $filter, $params, $n, $database = NULL, $numberPerPage = NULL) {
        $totalResult = -1;

        if (useNormAndUp ()) {
            $query = preg_replace("/upper/", "normAndUp", $query);
            $columns = preg_replace("/upper/", "normAndUp", $columns);
        }

        if (is_null ($numberPerPage)) {
            $numberPerPage = getCurrentOption ("max_item_per_page");
        }

        if ($numberPerPage != -1 && $n != -1)
        {
            // First check total number of results
            $result = self::getDb ($database)->prepare (str_format ($query, "count(*)", $filter));
            $result->execute ($params);
            $totalResult = $result->fetchColumn ();

            // Next modify the query and params
            $query .= " limit ?, ?";
            array_push ($params, ($n - 1) * $numberPerPage, $numberPerPage);
        }

        $result = self::getDb ($database)->prepare(str_format ($query, $columns, $filter));
        $result->execute ($params);
        return array ($totalResult, $result);
    }

}
