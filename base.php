<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     S�bastien Lucas <sebastien@slucas.fr>
 */

define ("VERSION", "0.5.0");
define ("DB", "db");
date_default_timezone_set($config['default_timezone']);
 
function getURLParam ($name, $default = NULL) {
    if (!empty ($_GET) && isset($_GET[$name])) {
        return $_GET[$name];
    }
    return $default;
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
 * This method is based on this page
 * http://www.mind-it.info/2010/02/22/a-simple-approach-to-localization-in-php/
 */
function localize($phrase, $count=-1) {
    if ($count == 0)
        $phrase .= ".none";
    if ($count == 1)
        $phrase .= ".one";
    if ($count > 1)
        $phrase .= ".many";

    /* Static keyword is used to ensure the file is loaded only once */
    static $translations = NULL;
    /* If no instance of $translations has occured load the language file */
    if (is_null($translations)) {
        $lang = "en";
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
        {
            $lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
        }
        $lang_file_en = NULL;
        $lang_file = 'lang/Localization_' . $lang . '.json';
        if (!file_exists($lang_file)) {
            $lang_file = 'lang/' . 'Localization_en.json';
        }
        elseif ($lang != "en") {
            $lang_file_en = 'lang/' . 'Localization_en.json';
        }
        $lang_file_content = file_get_contents($lang_file);
        /* Load the language file as a JSON object and transform it into an associative array */
        $translations = json_decode($lang_file_content, true);
        if ($lang_file_en)
        {
            $lang_file_content = file_get_contents($lang_file_en);
            $translations_en = json_decode($lang_file_content, true);
            $translations = array_merge ($translations_en, $translations);
        }
    }
    return $translations[$phrase];
}

function addURLParameter($urlParams, $paramName, $paramValue) {
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
        return str_replace ("&", "&amp;", $this->href);
    }
}

class LinkNavigation extends Link
{
    public function __construct($phref, $prel = NULL, $ptitle = NULL) {
        parent::__construct ($phref, Link::OPDS_NAVIGATION_TYPE, $prel, $ptitle);
        if (!is_null (GetUrlParam (DB))) $this->href = addURLParameter ($this->href, DB, GetUrlParam (DB));
        if (!preg_match ("#^\?(.*)#", $this->href) && !empty ($this->href)) $this->href = "?" . $this->href;
        if (preg_match ("/bookdetail.php/", $_SERVER["SCRIPT_NAME"])) {
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
    public $contentType;
    public $linkArray;
    public $localUpdated;
    private static $updated = NULL;
    
    public static $icons = array(
        Author::ALL_AUTHORS_ID       => 'images/author.png',
        Serie::ALL_SERIES_ID         => 'images/serie.png',
        Book::ALL_RECENT_BOOKS_ID    => 'images/recent.png',
        Tag::ALL_TAGS_ID             => 'images/tag.png',
        Language::ALL_LANGUAGES_ID   => 'images/language.png',
        CustomColumn::ALL_CUSTOMS_ID => 'images/tag.png',
        "calibre:books$"             => 'images/allbook.png',
        "calibre:books:letter"       => 'images/allbook.png'
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
 
    public function __construct($ptitle, $pid, $pcontent, $pcontentType, $plinkArray) {
        global $config;
        $this->title = $ptitle;
        $this->id = $pid;
        $this->content = $pcontent;
        $this->contentType = $pcontentType;
        $this->linkArray = $plinkArray;
        
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
        
        if (!is_null (GetUrlParam (DB))) $this->id = GetUrlParam (DB) . ":" . $this->id;
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
    public $idPage;
    public $idGet;
    public $query;
    public $favicon;
    public $n;
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
            case Base::PAGE_ABOUT :
                return new PageAbout ($id, $query, $n);
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
    }
    
    public function InitializeContent () 
    {
        global $config;
        $this->title = $config['cops_title_default'];
        $this->subtitle = $config['cops_subtitle_default'];
        $database = GetUrlParam (DB);
        if (is_array ($config['calibre_directory']) && is_null ($database)) {
            $i = 0;
            foreach ($config['calibre_directory'] as $key => $value) {
                array_push ($this->entryArray, new Entry ($key, "{$i}:cops:catalog", 
                                        "", "text", 
                                        array ( new LinkNavigation ("?" . DB . "={$i}"))));
                $i++;
            }
        } else {
            array_push ($this->entryArray, Author::getCount());
            $series = Serie::getCount();
            if (!is_null ($series)) array_push ($this->entryArray, $series);
            $tags = Tag::getCount();
            if (!is_null ($tags)) array_push ($this->entryArray, $tags);
			$languages = Language::getCount();
            if (!is_null ($languages)) array_push ($this->entryArray, $languages);
            foreach ($config['cops_calibre_custom_column'] as $lookup) {
                $customId = CustomColumn::getCustomId ($lookup);
                if (!is_null ($customId)) {
                    array_push ($this->entryArray, CustomColumn::getCount($customId));
                }
            }
            $this->entryArray = array_merge ($this->entryArray, Book::getCount());
            
            if (!is_null ($database)) $this->title =  Base::getDbName ();
        }
    }
    
    public function isPaginated ()
    {
        global $config;
        return ($config['cops_max_item_per_page'] != -1 && 
                $this->totalNumber != -1 && 
                $this->totalNumber > $config['cops_max_item_per_page']);
    }
    
    public function getNextLink ()
    {
        global $config;
        $currentUrl = $_SERVER['QUERY_STRING'];
        $currentUrl = preg_replace ("/\&n=.*?$/", "", "?" . $_SERVER['QUERY_STRING']);
        if (($this->n) * $config['cops_max_item_per_page'] < $this->totalNumber) {
            return new LinkNavigation ($currentUrl . "&n=" . ($this->n + 1), "next", "Page suivante");
        }
        return NULL;
    }
    
    public function getPrevLink ()
    {
        global $config;
        $currentUrl = $_SERVER['QUERY_STRING'];
        $currentUrl = preg_replace ("/\&n=.*?$/", "", "?" . $_SERVER['QUERY_STRING']);
        if ($this->n > 1) {
            return new LinkNavigation ($currentUrl . "&n=" . ($this->n - 1), "previous", "Page precedente");
        }
        return NULL;
    }
    
    public function getMaxPage ()
    {
        global $config;
        return ceil ($this->totalNumber / $config['cops_max_item_per_page']);
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
        global $config;
        
        $this->title = localize("authors.title");
        if ($config['cops_author_split_first_letter'] == 1) {
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
        global $config;
        
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
        $this->title = $language->name;
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

class PageAllBooks extends Page
{
    public function InitializeContent () 
    {
        $this->title = localize ("allbooks.title");
        $this->entryArray = Book::getAllBooks ();
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
    public function InitializeContent () 
    {
        global $config;
        $this->title = str_format (localize ("search.result"), $this->query);
        $currentPage = getURLParam ("current", NULL);
        
        // Special case when we are doing a search and no database is selected
        if (is_array ($config['calibre_directory']) && is_null (GetUrlParam (DB))) {
            $i = 0;
            foreach ($config['calibre_directory'] as $key => $value) {
                Base::clearDb ();
                list ($array, $totalNumber) = Book::getBooksByQuery ($this->query, $this->n, $i);
                array_push ($this->entryArray, new Entry ($key, DB . ":query:{$i}", 
                                        str_format (localize ("bookword", count($array)), count($array)), "text", 
                                        array ( new LinkNavigation ("?" . DB . "={$i}&page=9&query=" . $this->query))));
                $i++;
            }
            return;
        }
        
        switch ($currentPage) {
            case Base::PAGE_ALL_AUTHORS :
            case Base::PAGE_AUTHORS_FIRST_LETTER :
                $this->entryArray = Author::getAuthorsByStartingLetter ('%' . $this->query);
                break;
            default:
                list ($this->entryArray, $this->totalNumber) = Book::getBooksByQuery ($this->query, $this->n);
        }
    }
}

class PageBookDetail extends Page
{
    public function InitializeContent () 
    {
        $book = Book::getBookById ($this->idGet);
        $this->title = $book->title;
    }
}

class PageAbout extends Page
{
    public function InitializeContent () 
    {
        $this->title = localize ("about.title");
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

    const COMPATIBILITY_XML_ALDIKO = "aldiko";
    
    private static $db = NULL;
    
    public static function getDbList () {
        global $config;
        if (is_array ($config['calibre_directory'])) {
            return $config['calibre_directory'];
        } else {
            return array ("" => $config['calibre_directory']);
        }
    }

    public static function getDbName ($database = NULL) {
        global $config;
        if (is_array ($config['calibre_directory'])) {
            if (is_null ($database)) $database = GetUrlParam (DB, 0);
            $array = array_keys ($config['calibre_directory']);
            return  $array[$database];
        }
        return "";
    }

    public static function getDbDirectory ($database = NULL) {
        global $config;
        if (is_array ($config['calibre_directory'])) {
            if (is_null ($database)) $database = GetUrlParam (DB, 0);
            $array = array_values ($config['calibre_directory']);
            return  $array[$database];
        }
        return $config['calibre_directory'];
    }

  
    public static function getDbFileName ($database = NULL) {
        return self::getDbDirectory ($database) .'metadata.db';
    }
    
    public static function getDb ($database = NULL) {
        global $config;
        if (is_null (self::$db)) {
            try {
                self::$db = new PDO('sqlite:'. self::getDbFileName ($database));
            } catch (Exception $e) {
                header("location: checkconfig.php?err=1");
                exit();
            }
        }
        return self::$db;
    }
    
    public static function clearDb () {
        self::$db = NULL;
    }
    
    public static function executeQuery($query, $columns, $filter, $params, $n, $database = NULL) {
        global $config;
        $totalResult = -1;
        
        if ($config['cops_max_item_per_page'] != -1 && $n != -1)
        {
            // First check total number of results
            $result = self::getDb ($database)->prepare (str_format ($query, "count(*)", $filter));
            $result->execute ($params);
            $totalResult = $result->fetchColumn ();
            
            // Next modify the query and params
            $query .= " limit ?, ?";
            array_push ($params, ($n - 1) * $config['cops_max_item_per_page'], $config['cops_max_item_per_page']);
        }
        
        $result = self::getDb ($database)->prepare(str_format ($query, $columns, $filter));
        $result->execute ($params);
        return array ($totalResult, $result);
    }

}
?>
