<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */


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
    
    public function render ($xml) {
        $xml->startElement ("link");
            $xml->writeAttribute ("href", $this->href);
            $xml->writeAttribute ("type", $this->type);
            if (!is_null ($this->rel)) {
                $xml->writeAttribute ("rel", $this->rel);
            }
            if (!is_null ($this->title)) {
                $xml->writeAttribute ("title", $this->title);
            }
        $xml->endElement ();
    }
}

class LinkNavigation extends Link
{
    const OPDS_NAVIGATION_TYPE = "application/atom+xml;profile=opds-catalog;kind=navigation";

    public function __construct($phref, $prel = NULL, $ptitle = NULL) {
        parent::__construct ($phref, self::OPDS_NAVIGATION_TYPE, $prel, $ptitle);
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
    
    public function renderContent ($xml) {
        $xml->startElement ("title");
            $xml->text ($this->title);
        $xml->endElement ();
        $xml->startElement ("updated");
            $xml->text (self::getUpdatedTime ());
        $xml->endElement ();
        $xml->startElement ("id");
            $xml->text ($this->id);
        $xml->endElement ();
        $xml->startElement ("content");
            $xml->writeAttribute ("type", $this->contentType);
            if ($this->contentType == "text") {
                $xml->text ($this->content);
            } else {
                $xml->writeRaw ($this->content);
            }
        $xml->endElement ();
        foreach ($this->linkArray as $link) {
            $link->render ($xml);
        }
    }
    
    public function render ($xml) {
        $xml->startElement ("entry");
            self::renderContent ($xml);
        $xml->endElement ();
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
    
    public function renderContent ($xml) {
        parent::renderContent ($xml);
        foreach ($this->book->getAuthors () as $author) {
            $xml->startElement ("author");
                $xml->startElement ("name");
                    $xml->text ($author->name);
                $xml->endElement ();
                $xml->startElement ("uri");
                    $xml->text ($author->getUri ());
                $xml->endElement ();
            $xml->endElement ();
        }
        foreach ($this->book->getTags () as $category) {
            $xml->startElement ("category");
                $xml->writeAttribute ("term", $category);
                $xml->writeAttribute ("label", $category);
            $xml->endElement ();
        }
        if (!is_null ($this->book->pubdate)) {
            $xml->startElement ("dcterms:issued");
                $xml->text (date ("Y-m-d", $this->book->pubdate));
            $xml->endElement ();
        }
    }
    
    /* Polymorphism is strange with PHP */
    public function render ($xml) {
        $xml->startElement ("entry");
            self::renderContent ($xml);
        $xml->endElement ();
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
    private static $xmlStream = NULL;
    private static $updated = NULL;
    
    public static function getUpdatedTime () {
        if (is_null (self::$updated)) {
            self::$updated = time();
        }
        return date (DATE_ATOM, self::$updated);
    }
    
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
    
    public static function getXmlStream () {
        if (is_null (self::$xmlStream)) {
            self::$xmlStream = new XMLWriter();
            self::$xmlStream->openMemory();
            self::$xmlStream->setIndent (true);
        }
        return self::$xmlStream;
    }
    
    public static function getOpenSearch () {
        $xml = new XMLWriter ();
        $xml->openMemory ();
        $xml->setIndent (true);
        $xml->startDocument('1.0','UTF-8');
            $xml->startElement ("OpenSearchDescription");
                $xml->startElement ("ShortName");
                    $xml->text ("My catalog");
                $xml->endElement ();
                $xml->startElement ("InputEncoding");
                    $xml->text ("UTF-8");
                $xml->endElement ();
                $xml->startElement ("OutputEncoding");
                    $xml->text ("UTF-8");
                $xml->endElement ();
                $xml->startElement ("Image");
                    $xml->text ("favicon.ico");
                $xml->endElement ();
                $xml->startElement ("Url");
                    $xml->writeAttribute ("type", 'application/atom+xml');
                    $xml->writeAttribute ("template", 'feed.php?page=' . self::PAGE_OPENSEARCH_QUERY . '&query={searchTerms}');
                $xml->endElement ();
            $xml->endElement ();
        $xml->endDocument();
        return $xml->outputMemory(true);
    }
    
    public static function startXmlDocument ($title) {
        self::getXmlStream ()->startDocument('1.0','UTF-8');
        self::getXmlStream ()->startElement ("feed");
            self::getXmlStream ()->writeAttribute ("xmlns", "http://www.w3.org/2005/Atom");
            self::getXmlStream ()->writeAttribute ("xmlns:xhtml", "http://www.w3.org/1999/xhtml");
            self::getXmlStream ()->writeAttribute ("xmlns:opds", "http://opds-spec.org/2010/catalog");
            self::getXmlStream ()->writeAttribute ("xmlns:opensearch", "http://a9.com/-/spec/opensearch/1.1/");
            self::getXmlStream ()->writeAttribute ("xmlns:dcterms", "http://purl.org/dc/terms/");
            self::getXmlStream ()->startElement ("title");
                self::getXmlStream ()->text ($title);
            self::getXmlStream ()->endElement ();
            self::getXmlStream ()->startElement ("id");
                self::getXmlStream ()->text ($_SERVER['REQUEST_URI']);
            self::getXmlStream ()->endElement ();
            self::getXmlStream ()->startElement ("updated");
                self::getXmlStream ()->text (self::getUpdatedTime ());
            self::getXmlStream ()->endElement ();
            self::getXmlStream ()->startElement ("icon");
                self::getXmlStream ()->text ("favicon.ico");
            self::getXmlStream ()->endElement ();
            self::getXmlStream ()->startElement ("author");
                self::getXmlStream ()->startElement ("name");
                    self::getXmlStream ()->text (utf8_encode ("Sébastien Lucas"));
                self::getXmlStream ()->endElement ();
                self::getXmlStream ()->startElement ("uri");
                    self::getXmlStream ()->text ("http://blog.slucas.fr");
                self::getXmlStream ()->endElement ();
                self::getXmlStream ()->startElement ("email");
                    self::getXmlStream ()->text ("sebastien@slucas.fr");
                self::getXmlStream ()->endElement ();
            self::getXmlStream ()->endElement ();
            $link = new LinkNavigation ("feed.php", "start", "Home");
            $link->render (self::getXmlStream ());
            $link = new LinkNavigation ($_SERVER['REQUEST_URI'], "self");
            $link->render (self::getXmlStream ());
            $link = new Link ("feed.php?page=" . self::PAGE_OPENSEARCH, "application/opensearchdescription+xml", "search", "Search here");
            $link->render (self::getXmlStream ());
            $link = new LinkNavigation ("feed.php?page=7&id=9", "http://opds-spec.org/shelf", "Biblio");
            $link->render (self::getXmlStream ());
    }
    
    public static function addEntryClass ($entry) {
        $entry->render (self::getXmlStream ());
    }
    
    public static function endXmlDocument () {
        self::getXmlStream ()->endElement ();
        self::getXmlStream ()->endDocument ();
        return self::getXmlStream ()->outputMemory(true);
    }    
}
?>