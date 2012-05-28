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
    const OPDS_THUMBNAIL_TYPE = "http://opds-spec.org/image/thumbnail";
    const OPDS_IMAGE_TYPE = "http://opds-spec.org/image";
    const OPDS_ACQUISITION_TYPE = "http://opds-spec.org/acquisition";
    const OPDS_NAVIGATION_TYPE = "application/atom+xml;profile=opds-catalog;kind=navigation";
    
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
                    array_push ($this->linkArray, new Link ($image, "image/png", Link::OPDS_THUMBNAIL_TYPE));
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
            case Base::PAGE_ALL_TAGS :
                return new PageAllTags ($id, $query);
            case Base::PAGE_TAG_DETAIL :
                return new PageTagDetail ($id, $query);
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
                $page = new Page ($id, $query);
                $page->idPage = "cops:catalog";
                return $page;
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
        array_push ($this->entryArray, Tag::getCount());
        $this->entryArray = array_merge ($this->entryArray, Book::getCount());
    }

}

class PageAllAuthors extends Page
{
    public function InitializeContent () 
    {
        $this->title = "All authors";
        $this->entryArray = Author::getAllAuthors();
        $this->idPage = Author::ALL_AUTHORS_ID;
    }
}

class PageAuthorDetail extends Page
{
    public function InitializeContent () 
    {
        $author = Author::getAuthorById ($this->idGet);
        $this->idPage = $author->getEntryId ();
        $this->title = $author->name;
        $this->entryArray = Book::getBooksByAuthor ($this->idGet);
    }
}

class PageAllTags extends Page
{
    public function InitializeContent () 
    {
        $this->title = "All tags";
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
        $this->entryArray = Book::getBooksByTag ($this->idGet);
    }
}

class PageAllSeries extends Page
{
    public function InitializeContent () 
    {
        $this->title = "All series";
        $this->entryArray = Serie::getAllSeries();
        $this->idPage = Serie::ALL_SERIES_ID;
    }
}

class PageSerieDetail extends Page
{
    public function InitializeContent () 
    {
        $serie = Serie::getSerieById ($this->idGet);
        $this->title = "Series : " . $serie->name;
        $this->entryArray = Book::getBooksBySeries ($this->idGet);
        $this->idPage = $serie->getEntryId ();
    }
}

class PageAllBooks extends Page
{
    public function InitializeContent () 
    {
        $this->title = "All books by starting letter";
        $this->entryArray = Book::getAllBooks ();
        $this->idPage = Book::ALL_BOOKS_ID;
    }
}

class PageAllBooksLetter extends Page
{
    public function InitializeContent () 
    {
        $this->title = "All books starting by " . $this->idGet;
        $this->entryArray = Book::getBooksByStartingLetter ($this->idGet);
        $this->idPage = Book::getEntryIdByLetter ($this->idGet);
    }
}

class PageRecentBooks extends Page
{
    public function InitializeContent () 
    {
        $this->title = "Most recent books";
        $this->entryArray = Book::getAllRecentBooks ();
        $this->idPage = Book::ALL_RECENT_BOOKS_ID;
    }
}

class PageQueryResult extends Page
{
    public function InitializeContent () 
    {
        $this->title = "Search result for query *" . $this->query . "*";
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
    const PAGE_ALL_TAGS = "11";
    const PAGE_TAG_DETAIL = "12";

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