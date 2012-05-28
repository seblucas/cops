<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

require_once('base.php');
require_once('serie.php');
require_once('author.php');

class Book extends Base {
    const ALL_BOOKS_ID = "calibre:books";
    const ALL_RECENT_BOOKS_ID = "calibre:recentbooks";
    
    public $id;
    public $title;
    public $timestamp;
    public $pubdate;
    public $path;
    public $relativePath;
    public $seriesIndex;
    public $comment;
    public $authors = NULL;
    public $serie = NULL;
    public $tags = NULL;
    public $format = array ();
    public static $mimetypes = array(
        'epub'   => 'application/epub+zip',
        'mobi'   => 'application/x-mobipocket-ebook',
        'pdf'    => 'application/pdf'
    );

        
    public function __construct($pid, $ptitle, $ptimestamp, $ppubdate, $ppath, $pseriesIndex, $pcomment) {
        global $config;
        $this->id = $pid;
        $this->title = $ptitle;
        $this->timestamp = strtotime ($ptimestamp);
        $this->pubdate = strtotime ($ppubdate);
        $this->path = $config['calibre_directory'] . $ppath;
        $this->relativePath = $ppath;
        $this->seriesIndex = $pseriesIndex;
        $this->comment = $pcomment;
    }
        
    public function getEntryId () {
        return self::ALL_BOOKS_ID.":".$this->id;
    }
    
    public function getTitle () {
        return $this->title;
    }
    
    public function getAuthors () {
        if (is_null ($this->authors)) {
            $this->authors = Author::getAuthorByBookId ($this->id);
        }
        return $this->authors;
    }
    
    public function getAuthorsName () {
        $authorList = null;
        foreach ($this->getAuthors () as $author) {
            if ($authorList) {
                $authorList = $authorList . ", " . $author->name;
            }
            else
            {
                $authorList = $author->name;
            }
        }
        return $authorList;
    }
    
    public function getSerie () {
        if (is_null ($this->serie)) {
            $this->serie = Serie::getSerieByBookId ($this->id);
        }
        return $this->serie;
    }
    
    public function getTags () {
        if (is_null ($this->tags)) {
            $this->tags = array ();
            
            $result = parent::getDb ()->prepare('select name
                from books_tags_link, tags
                where tag = tags.id
                and book = ?
                order by name');
            $result->execute (array ($this->id));
            while ($post = $result->fetchObject ())
            {
                array_push ($this->tags, $post->name);
            }
        }
        return $this->tags;
    }
    
    public function getTagsName () {
        $tagList = null;
        foreach ($this->getTags () as $tag) {
            if ($tagList) {
                $tagList = $tagList . ", " . $tag;
            }
            else
            {
                $tagList = $tag;
            }
        }
        return $tagList;
    }
    
    public function getComment () {
        $addition = "";
        $se = $this->getSerie ();
        if (!is_null ($se)) {
            $addition = $addition . "<strong>Series : </strong>Book $this->seriesIndex in $se->name<br />\n";
        }
        return $addition . strip_tags ($this->comment, '<div>');
    }
    
    public function getFilePath ($extension, $relative = false)
    {
        if ($handle = opendir($this->path)) {
            while (false !== ($file = readdir($handle))) {
                if (preg_match ('/' . $extension . '$/', $file)) {
                    if ($relative)
                    {
                        return $this->relativePath."/".$file;
                    }
                    else
                    {
                        return $this->path."/".$file;
                    }
                }
            }
        }
        return NULL;
    }
    
    public function getLinkArray ()
    {
        global $config;
        $linkArray = array();
        if ($handle = opendir($this->path)) {
            while (false !== ($file = readdir($handle))) {
                if (preg_match ('/jpg$/', $file)) {
                    if (preg_match ('/^\//', $config['calibre_directory']))
                    {
                        array_push ($linkArray, new Link ("fetch.php?id=$this->id", "image/jpeg", "http://opds-spec.org/image"));
                    }
                    else
                    {
                        array_push ($linkArray, new Link (str_replace('%2F','/',rawurlencode ($this->path."/".$file)), "image/jpeg", "http://opds-spec.org/image"));
                    }
                    array_push ($linkArray, new Link ("fetch.php?id=$this->id&height=70", "image/jpeg", "http://opds-spec.org/image/thumbnail"));
                }
                foreach (self::$mimetypes as $ext => $mime)
                {
                    if (preg_match ('/'. $ext .'$/', $file)) {
                        $this->format [$ext] = $file;
                        if (preg_match ('/^\//', $config['calibre_directory']))
                        {
                            array_push ($linkArray, new Link ("fetch.php?id=$this->id&type=" . $ext, $mime, "http://opds-spec.org/acquisition", "Download"));
                        }
                        else
                        {
                            array_push ($linkArray, new Link (str_replace('%2F','/',rawurlencode ($this->path."/".$file)), $mime, "http://opds-spec.org/acquisition", "Download"));
                        }
                    }
                }
            }
        }
        
        foreach ($this->getAuthors () as $author) {
            array_push ($linkArray, new LinkNavigation ($author->getUri (), "related", "Other books by $author->name"));
        }
        
        $serie = $this->getSerie ();
        if (!is_null ($serie)) {
            array_push ($linkArray, new LinkNavigation ($serie->getUri (), "related", "Other books by the serie $serie->name"));
        }
        
        return $linkArray;
    }

    
    public function getEntry () {    
        return new EntryBook ($this->getTitle (), $this->getEntryId (), 
            $this->getComment (), "text/html", 
            $this->getLinkArray (), $this);
    }

    public static function getCount() {
        global $config;
        $nBooks = parent::getDb ()->query('select count(*) from books')->fetchColumn();
        $result = array();
        $entry = new Entry ("Books", 
                          self::ALL_BOOKS_ID, 
                          "Alphabetical index of the $nBooks books", "text", 
                          array ( new LinkNavigation ("?page=".parent::PAGE_ALL_BOOKS)));
        array_push ($result, $entry);
        $entry = new Entry ("Recents books", 
                          self::ALL_RECENT_BOOKS_ID, 
                          "Alphabetical index of the " . $config['cops_recentbooks_limit'] . " most recent books", "text", 
                          array ( new LinkNavigation ("?page=".parent::PAGE_ALL_RECENT_BOOKS)));
        array_push ($result, $entry);
        return $result;
    }
        
    public static function getBooksByAuthor($authorId) {
        $result = parent::getDb ()->prepare('select books.id as id, books.title as title, text as comment, path, timestamp, pubdate, series_index
from books_authors_link, books left outer join comments on comments.book = books.id
where books_authors_link.book = books.id
and author = ?
order by pubdate');
        $entryArray = array();
        $result->execute (array ($authorId));
        while ($post = $result->fetchObject ())
        {
            $book = new Book ($post->id, $post->title,  $post->timestamp, $post->pubdate, $post->path, $post->series_index, $post->comment);
            array_push ($entryArray, $book->getEntry ());
        }
        return $entryArray;
    }

    
    public static function getBooksBySeries($serieId) {
        $result = parent::getDb ()->prepare('select books.id as id, books.title as title, text as comment, path, timestamp, pubdate, series_index
from books_series_link, books left outer join comments on comments.book = books.id
where books_series_link.book = books.id and series = ?
order by series_index');
        $entryArray = array();
        $result->execute (array ($serieId));
        while ($post = $result->fetchObject ())
        {
            $book = new Book ($post->id, $post->title,  $post->timestamp, $post->pubdate, $post->path, $post->series_index, $post->comment);
            array_push ($entryArray, $book->getEntry ());
        }
        return $entryArray;
    }
    
    public static function getBookById($bookId) {
        $result = parent::getDb ()->prepare('select books.id as id, books.title as title, text as comment, path, timestamp, pubdate, series_index
from books left outer join comments on book = books.id
where books.id = ?');
        $entryArray = array();
        $result->execute (array ($bookId));
        while ($post = $result->fetchObject ())
        {
            $book = new Book ($post->id, $post->title,  $post->timestamp, $post->pubdate, $post->path, $post->series_index, $post->comment);
            return $book;
        }
        return NULL;
    }
    
    public static function getBooksByQuery($query) {
        $result = parent::getDb ()->prepare("select books.id as id, books.title as title, text as comment, path, timestamp, pubdate, series_index
from books left outer join comments on book = books.id
where exists (select null from authors, books_authors_link where book = books.id and author = authors.id and authors.name like ?)
or title like ?");
        $entryArray = array();
        $queryLike = "%" . $query . "%";
        $result->execute (array ($queryLike, $queryLike));
        while ($post = $result->fetchObject ())
        {
            $book = new Book ($post->id, $post->title,  $post->timestamp, $post->pubdate, $post->path, $post->series_index, $post->comment);
            array_push ($entryArray, $book->getEntry ());
        }
        return $entryArray;
    }
    
    public static function getAllBooks() {
        $result = parent::getDb ()->query("select substr (upper (sort), 1, 1) as title, count(*) as count
from books
group by substr (upper (sort), 1, 1)
order by substr (upper (sort), 1, 1)");
        $entryArray = array();
        while ($post = $result->fetchObject ())
        {
            array_push ($entryArray, new Entry ($post->title, "allbooks_" . $post->title, 
                "$post->count books", "text", 
                array ( new LinkNavigation ("?page=".parent::PAGE_ALL_BOOKS_LETTER."&id=".$post->title))));
        }
        return $entryArray;
    }
    
    public static function getBooksByStartingLetter($letter) {
        $result = parent::getDb ()->prepare('select books.id as id, books.title as title, text as comment, path, timestamp, pubdate, series_index
from books left outer join comments on book = books.id
where upper (books.sort) like ?');
        $entryArray = array();
        $queryLike = $letter . "%";
        $result->execute (array ($queryLike));
        while ($post = $result->fetchObject ())
        {
            $book = new Book ($post->id, $post->title,  $post->timestamp, $post->pubdate, $post->path, $post->series_index, $post->comment);
            array_push ($entryArray, $book->getEntry ());
        }
        return $entryArray;
    }

    
    public static function getAllRecentBooks() {
        global $config;
        $result = parent::getDb ()->query("select books.id as id, books.title as title, text as comment, path, timestamp, pubdate, series_index
from books left outer join comments on book = books.id
order by timestamp desc limit " . $config['cops_recentbooks_limit']);
        $entryArray = array();
        while ($post = $result->fetchObject ())
        {
            $book = new Book ($post->id, $post->title,  $post->timestamp, $post->pubdate, $post->path, $post->series_index, $post->comment);
            array_push ($entryArray, $book->getEntry ());
        }
        return $entryArray;
    }

}
?>