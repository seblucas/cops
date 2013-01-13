<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     S�bastien Lucas <sebastien@slucas.fr>
 */

define ("VERSION", "0.3.0");
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
    
    public function __construct($phref, $ptype, $prel = NULL, $ptitle = NULL) {
        $this->href = $phref;
        $this->type = $ptype;
        $this->rel = $prel;
        $this->title = $ptitle;
    }
    
    public function hrefXhtml () {
        return str_replace ("&", "&amp;", $this->href);
    }
}

class LinkNavigation extends Link
{
    public function __construct($phref, $prel = NULL, $ptitle = NULL) {
        parent::__construct ($phref, Link::OPDS_NAVIGATION_TYPE, $prel, $ptitle);
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
        Author::ALL_AUTHORS_ID    => 'images/author.png',
        Serie::ALL_SERIES_ID      => 'images/serie.png',
	Genre::ALL_GENRES_ID      => 'images/serie.png',
        Book::ALL_RECENT_BOOKS_ID => 'images/recent.png',
        Tag::ALL_TAGS_ID          => 'images/tag.png',
        "calibre:books$"          => 'images/allbook.png',
        "calibre:books:letter"    => 'images/allbook.png'
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
            case Base::PAGE_ALL_SERIES :
                return new PageAllSeries ($id, $query, $n);
	    case Base::PAGE_ALL_GENRES :
                return new PageAllGenres ($id, $query, $n);
            case Base::PAGE_ALL_BOOKS :
                return new PageAllBooks ($id, $query, $n);
            case Base::PAGE_ALL_BOOKS_LETTER:
                return new PageAllBooksLetter ($id, $query, $n);
            case Base::PAGE_ALL_RECENT_BOOKS :
                return new PageRecentBooks ($id, $query, $n);
            case Base::PAGE_SERIE_DETAIL : 
                return new PageSerieDetail ($id, $query, $n);
	    case Base::PAGE_GENRE_DETAIL :
                return new PageGenreDetail ($id, $query, $n);
            case Base::PAGE_OPENSEARCH_QUERY :
                return new PageQueryResult ($id, $query, $n);
            case Base::PAGE_BOOK_DETAIL :
                return new PageBookDetail ($id, $query, $n);
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
        array_push ($this->entryArray, Author::getCount());
        array_push ($this->entryArray, Serie::getCount());
	array_push ($this->entryArray, Genre::getCount());
        array_push ($this->entryArray, Tag::getCount());
        $this->entryArray = array_merge ($this->entryArray, Book::getCount());
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

class PageAllGenres extends Page
{
    public function InitializeContent ()
    {
        $this->title = localize("genres.title");
        $this->entryArray = Genre::getAllGenres();
        $this->idPage = Genre::ALL_GENRES_ID;
    }
}

class PageGenreDetail extends Page
{
    public function InitializeContent ()
    {
        $genre = Genre::getGenreById ($this->idGet);
        $this->idPage = $genre->getEntryId ();
        $this->title = $genre->name;
        list ($this->entryArray, $this->totalNumber) = Book::getBooksByGenres ($this->idGet, $this->n);
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
        $this->title = "Search result for query *" . $this->query . "*"; // TODO I18N
        list ($this->entryArray, $this->totalNumber) = Book::getBooksByQuery ($this->query, $this->n);
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
    const PAGE_ALL_GENRES = "14";
    const PAGE_GENRE_DETAIL = "15";

    const COMPATIBILITY_XML_ALDIKO = "aldiko";
    
    private static $db = NULL;
    
    public static function getDbFileName () {
        global $config;
        return $config['calibre_directory'] .'metadata.db';
    }
    
    public static function getDb () {
        global $config;
        if (is_null (self::$db)) {
            try {
                self::$db = new PDO('sqlite:'. self::getDbFileName ());
            } catch (Exception $e) {
                header("location: checkconfig.php?err=1");
                exit();
            }
        }
        return self::$db;
    }
    
    public static function executeQuery($query, $columns, $params, $n) {
        global $config;
        $totalResult = -1;
        
        if ($config['cops_max_item_per_page'] != -1 && $n != -1)
        {
            // First check total number of results
            $result = self::getDb ()->prepare (str_format ($query, "count(*)"));
            $result->execute ($params);
            $totalResult = $result->fetchColumn ();
            
            // Next modify the query and params
            $query .= " limit ?, ?";
            array_push ($params, ($n - 1) * $config['cops_max_item_per_page'], $config['cops_max_item_per_page']);
        }
        
        $result = self::getDb ()->prepare(str_format ($query, $columns));
        $result->execute ($params);
        return array ($totalResult, $result);
    }

}
?>
