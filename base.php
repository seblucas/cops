<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

function getURLParam ($name, $default = NULL) {
    if (!empty ($_GET) && isset($_GET[$name])) {
        return $_GET[$name];
    }
    return $default;
}

class Link
{
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
    const OPDS_NAVIGATION_TYPE = "application/atom+xml;profile=opds-catalog;kind=navigation";

    public function __construct($phref, $prel = NULL, $ptitle = NULL) {
        parent::__construct ($phref, self::OPDS_NAVIGATION_TYPE, $prel, $ptitle);
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
        $this->title = $ptitle;
        $this->id = $pid;
        $this->content = $pcontent;
        $this->contentType = $pcontentType;
        $this->linkArray = $plinkArray;
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
            if ($link->rel == "http://opds-spec.org/image/thumbnail")
                return $link->hrefXhtml ();
        }
        return null;
    }
}

class Page
{
    public $title;
    public $idPage;
    public $idGet;
    public $query;
    public $entryArray = array();
    
    public static function getPage ($pageId, $id, $query)
    {
        switch ($pageId) {
            case Base::PAGE_ALL_AUTHORS :
                return new PageAllAuthors ($id, $query);
            case Base::PAGE_AUTHOR_DETAIL :
                return new PageAuthorDetail ($id, $query);
            case Base::PAGE_ALL_SERIES :
                return new PageAllSeries ($id, $query);
            case Base::PAGE_ALL_BOOKS :
                return new PageAllBooks ($id, $query);
            case Base::PAGE_ALL_BOOKS_LETTER:
                return new PageAllBooksLetter ($id, $query);
            case Base::PAGE_ALL_RECENT_BOOKS :
                return new PageRecentBooks ($id, $query);
            case Base::PAGE_SERIE_DETAIL : 
                return new PageSerieDetail ($id, $query);
            case Base::PAGE_OPENSEARCH_QUERY :
                return new PageQueryResult ($id, $query);
                break;
            default:
                return new Page ($id, $query);
        }
    }
    
    public function __construct($pid, $pquery) {
        $this->idGet = $pid;
        $this->query = $pquery;
    }    
    
    public function InitializeContent () 
    {
        global $config;
        $this->title = $config['cops_title_default'];
        array_push ($this->entryArray, Author::getCount());
        array_push ($this->entryArray, Serie::getCount());
        $this->entryArray = array_merge ($this->entryArray, Book::getCount());
    }

}

class PageAllAuthors extends Page
{
    public function InitializeContent () 
    {
        $this->title = "All authors";
        $this->entryArray = Author::getAllAuthors();
    }
}

class PageAuthorDetail extends Page
{
    public function InitializeContent () 
    {
        $this->title = Author::getAuthorName ($this->idGet);
        $this->entryArray = Book::getBooksByAuthor ($this->idGet);
    }
}

class PageAllSeries extends Page
{
    public function InitializeContent () 
    {
        $this->title = "All series";
        $this->entryArray = Serie::getAllSeries();
    }
}

class PageSerieDetail extends Page
{
    public function InitializeContent () 
    {
        $this->title = "Series : " . Serie::getSerieById ($this->idGet)->name;
        $this->entryArray = Book::getBooksBySeries ($this->idGet);
    }
}

class PageAllBooks extends Page
{
    public function InitializeContent () 
    {
        $this->title = "All books by starting letter";
        $this->entryArray = Book::getAllBooks ();
    }
}

class PageAllBooksLetter extends Page
{
    public function InitializeContent () 
    {
        $this->title = "All books starting by " . $this->idGet;
        $this->entryArray = Book::getBooksByStartingLetter ($this->idGet);
    }
}

class PageRecentBooks extends Page
{
    public function InitializeContent () 
    {
        $this->title = "Most recent books";
        $this->entryArray = Book::getAllRecentBooks ();
    }
}

class PageQueryResult extends Page
{
    public function InitializeContent () 
    {
        $this->title = "Search result for query <" . $this->query . ">";
        $this->entryArray = Book::getBooksByQuery ($this->query);
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
    const COMPATIBILITY_XML_ALDIKO = "aldiko";
    
    private static $db = NULL;
    
    public static function getDb () {
        global $config;
        if (is_null (self::$db)) {
            try {
                self::$db = new PDO('sqlite:'. $config['calibre_directory'] .'metadata.db');
            } catch (Exception $e) {
                echo $e;
                die($e);
            }
        }
        return self::$db;
    }    
}
?>