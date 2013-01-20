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
require_once('tag.php');
require_once ("customcolumn.php");
require_once('data.php');
require_once('php-epub-meta/epub.php');

// Silly thing because PHP forbid string concatenation in class const
define ('SQL_BOOKS_LEFT_JOIN', "left outer join comments on comments.book = books.id 
                                left outer join books_ratings_link on books_ratings_link.book = books.id 
                                left outer join ratings on books_ratings_link.rating = ratings.id ");
define ('SQL_BOOKS_BY_FIRST_LETTER', "select {0} from books " . SQL_BOOKS_LEFT_JOIN . "
                                                    where upper (books.sort) like ?");
define ('SQL_BOOKS_BY_AUTHOR', "select {0} from books_authors_link, books " . SQL_BOOKS_LEFT_JOIN . "
                                                    where books_authors_link.book = books.id and author = ? {1} order by pubdate");
define ('SQL_BOOKS_BY_SERIE', "select {0} from books_series_link, books " . SQL_BOOKS_LEFT_JOIN . "
                                                    where books_series_link.book = books.id and series = ? {1} order by series_index");
define ('SQL_BOOKS_BY_TAG', "select {0} from books_tags_link, books " . SQL_BOOKS_LEFT_JOIN . "
                                                    where books_tags_link.book = books.id and tag = ? {1} order by sort");
define ('SQL_BOOKS_BY_CUSTOM', "select {0} from {2}, books " . SQL_BOOKS_LEFT_JOIN . "
                                                    where {2}.book = books.id and {2}.{3} = ? {1} order by sort");
define ('SQL_BOOKS_QUERY', "select {0} from books " . SQL_BOOKS_LEFT_JOIN . "
                                                    where (exists (select null from authors, books_authors_link where book = books.id and author = authors.id and authors.name like ?) or title like ?) {1}");
define ('SQL_BOOKS_RECENT', "select {0} from books " . SQL_BOOKS_LEFT_JOIN . "
                                                    where 1=1 {1} order by timestamp desc limit ");

class Book extends Base {
    const ALL_BOOKS_UUID = "urn:uuid";
    const ALL_BOOKS_ID = "calibre:books";
    const ALL_RECENT_BOOKS_ID = "calibre:recentbooks";
    const BOOK_COLUMNS = "books.id as id, books.title as title, text as comment, path, timestamp, pubdate, series_index, uuid, has_cover, ratings.rating";
    
    const SQL_BOOKS_LEFT_JOIN = SQL_BOOKS_LEFT_JOIN;
    const SQL_BOOKS_BY_FIRST_LETTER = SQL_BOOKS_BY_FIRST_LETTER;
    const SQL_BOOKS_BY_AUTHOR = SQL_BOOKS_BY_AUTHOR;
    const SQL_BOOKS_BY_SERIE = SQL_BOOKS_BY_SERIE;
    const SQL_BOOKS_BY_TAG = SQL_BOOKS_BY_TAG;
    const SQL_BOOKS_BY_CUSTOM = SQL_BOOKS_BY_CUSTOM;
    const SQL_BOOKS_QUERY = SQL_BOOKS_QUERY;
    const SQL_BOOKS_RECENT = SQL_BOOKS_RECENT;
    
    public $id;
    public $title;
    public $timestamp;
    public $pubdate;
    public $path;
    public $uuid;
    public $hasCover;
    public $relativePath;
    public $seriesIndex;
    public $comment;
    public $rating;
    public $datas = NULL;
    public $authors = NULL;
    public $serie = NULL;
    public $tags = NULL;
    public $format = array ();

    
    public function __construct($line) {
        global $config;
        $this->id = $line->id;
        $this->title = $line->title;
        $this->timestamp = strtotime ($line->timestamp);
        $this->pubdate = strtotime ($line->pubdate);
        $this->path = $config['calibre_directory'] . $line->path;
        $this->relativePath = $line->path;
        $this->seriesIndex = $line->series_index;
        $this->comment = $line->comment;
        $this->uuid = $line->uuid;
        $this->hasCover = $line->has_cover;
        if (!file_exists ($this->getFilePath ("jpg"))) {
            // double check
            $this->hasCover = 0;
        }
        $this->rating = $line->rating;
    }
        
    public function getEntryId () {
        return self::ALL_BOOKS_UUID.":".$this->uuid;
    }
    
    public static function getEntryIdByLetter ($startingLetter) {
        return self::ALL_BOOKS_ID.":letter:".$startingLetter;
    }
    
    public function getUri () {
        return "?page=".parent::PAGE_BOOK_DETAIL."&amp;id=$this->id";
    }
    
    public function getDetailUrl () {
        global $config;
        if ($config['cops_use_fancyapps'] == 0) { 
            return 'index.php' . $this->getUri (); 
        } else { 
            return 'bookdetail.php?id=' . $this->id; 
        }
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
    
    public function getFilterString () {
        $filter = getURLParam ("tag", NULL);
        if (empty ($filter)) return "";
        
        $exists = true;
        if (preg_match ("/^!(.*)$/", $filter, $matches)) {
            $exists = false;
            $filter = $matches[1];    
        }
        
        $result = "exists (select null from books_tags_link, tags where books_tags_link.book = books.id and books_tags_link.tag = tags.id and tags.name = '" . $filter . "')";
        
        if (!$exists) {
            $result = "not " . $result;
        }
    
        return "and " . $result;
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
    
    public function getLanguages () {
        $lang = array ();
        $result = parent::getDb ()->prepare('select languages.lang_code
                from books_languages_link, languages
                where books_languages_link.lang_code = languages.id
                and book = ?
                order by item_order');
        $result->execute (array ($this->id));
        while ($post = $result->fetchObject ())
        {
            array_push ($lang, $post->lang_code);
        }
        return implode (", ", $lang);
    }
    
    public function getTags () {
        if (is_null ($this->tags)) {
            $this->tags = array ();
            
            $result = parent::getDb ()->prepare('select tags.id as id, name
                from books_tags_link, tags
                where tag = tags.id
                and book = ?
                order by name');
            $result->execute (array ($this->id));
            while ($post = $result->fetchObject ())
            {
                array_push ($this->tags, new Tag ($post->id, $post->name));
            }
        }
        return $this->tags;
    }
    
    public function getDatas ()
    {
        if (is_null ($this->datas)) {
            $this->datas = array ();
        
            $result = parent::getDb ()->prepare('select id, format, name
    from data where book = ?');
            $result->execute (array ($this->id));
            
            while ($post = $result->fetchObject ())
            {
                array_push ($this->datas, new Data ($post, $this));
            }
        }
        return $this->datas;
    }
    
    public function getDataById ($idData)
    {
        foreach ($this->getDatas () as $data) {
            if ($data->id == $idData) {
                return $data;
            }
        }
        return NULL;
    }

    
    public function getTagsName () {
        $tagList = null;
        foreach ($this->getTags () as $tag) {
            if ($tagList) {
                $tagList = $tagList . ", " . $tag->name;
            }
            else
            {
                $tagList = $tag->name;
            }
        }
        return $tagList;
    }
    
    public function getRating () {
        if (is_null ($this->rating) || $this->rating == 0) {
            return "";
        }
        $retour = "";
        for ($i = 0; $i < $this->rating / 2; $i++) {
            $retour .= "&#9733;";
        }
        for ($i = 0; $i < 5 - $this->rating / 2; $i++) {
            $retour .= "&#9734;";
        }
        return $retour;
    }
    
    public function getComment ($withSerie = true) {
        $addition = "";
        $se = $this->getSerie ();
        if (!is_null ($se) && $withSerie) {
            $addition = $addition . "<strong>" . localize("content.series") . "</strong>" . str_format (localize ("content.series.data"), $this->seriesIndex, htmlspecialchars ($se->name)) . "<br />\n";
        }
        if (preg_match ("/<\/(div|p|a)>/", $this->comment))
        {
            return $addition . preg_replace ("/<(br|hr)>/", "<$1 />", $this->comment);
        }
        else
        {
            return $addition . htmlspecialchars ($this->comment);
        }
    }
    
    public function getDataFormat ($format) {
        foreach ($this->getDatas () as $data)
        {
            if ($data->format == $format)
            {
                return $data;
            }
        }
        return NULL;
    }
    
    public function getFilePath ($extension, $idData = NULL, $relative = false)
    {
        $file = NULL;
        if ($extension == "jpg")
        {
            $file = "cover.jpg";
        }
        else
        {
            $data = $this->getDataById ($idData);
            $file = $data->name . "." . strtolower ($data->format);
        }

        if ($relative)
        {
            return $this->relativePath."/".$file;
        }
        else
        {
            return $this->path."/".$file;
        }
    }
    
    public function getUpdatedEpub ($idData)
    {
        $data = $this->getDataById ($idData);
            
        try
        {
            $epub = new EPub ($data->getLocalPath ());
            
            $epub->Title ($this->title);
            $authorArray = array ();
            foreach ($this->getAuthors() as $author) {
                $authorArray [$author->sort] = $author->name;
            }
            $epub->Authors ($authorArray);
            $epub->Language ($this->getLanguages ());
            $epub->Description ($this->getComment (false));
            $epub->Subjects ($this->getTagsName ());
            $se = $this->getSerie ();
            if (!is_null ($se)) {
                $epub->Serie ($se->name);
                $epub->SerieIndex ($this->seriesIndex);
            }
            $epub->download ($data->getFilename ());
        }
        catch (Exception $e)
        {
            echo "Exception : " . $e->getMessage();
        }
    }
    
    public function getLinkArray ()
    {
        global $config;
        $linkArray = array();
        
        if ($this->hasCover)
        {
            array_push ($linkArray, Data::getLink ($this, "jpg", "image/jpeg", Link::OPDS_IMAGE_TYPE, "cover.jpg", NULL));
            $height = "50";
            if (preg_match ('/feed.php/', $_SERVER["SCRIPT_NAME"])) {
                $height = $config['cops_opds_thumbnail_height'];
            }
            else
            {
                $height = $config['cops_html_thumbnail_height'];
            }
            array_push ($linkArray, new Link ("fetch.php?id=$this->id&height=" . $height, "image/jpeg", Link::OPDS_THUMBNAIL_TYPE));
        }
        
        foreach ($this->getDatas () as $data)
        {
            if ($data->isKnownType ())
            {
                array_push ($linkArray, $data->getDataLink (Link::OPDS_ACQUISITION_TYPE, "Download"));
            }
        }
                
        foreach ($this->getAuthors () as $author) {
            array_push ($linkArray, new LinkNavigation ($author->getUri (), "related", str_format (localize ("bookentry.author"), localize ("splitByLetter.book.other"), $author->name)));
        }
        
        $serie = $this->getSerie ();
        if (!is_null ($serie)) {
            array_push ($linkArray, new LinkNavigation ($serie->getUri (), "related", str_format (localize ("content.series.data"), $this->seriesIndex, $serie->name)));
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
        $entry = new Entry (localize ("allbooks.title"), 
                          self::ALL_BOOKS_ID, 
                          str_format (localize ("allbooks.alphabetical"), $nBooks), "text", 
                          array ( new LinkNavigation ("?page=".parent::PAGE_ALL_BOOKS)));
        array_push ($result, $entry);
        $entry = new Entry (localize ("recent.title"), 
                          self::ALL_RECENT_BOOKS_ID, 
                          str_format (localize ("recent.list"), $config['cops_recentbooks_limit']), "text", 
                          array ( new LinkNavigation ("?page=".parent::PAGE_ALL_RECENT_BOOKS)));
        array_push ($result, $entry);
        return $result;
    }
        
    public static function getBooksByAuthor($authorId, $n) {
        return self::getEntryArray (self::SQL_BOOKS_BY_AUTHOR, array ($authorId), $n);
    }

    
    public static function getBooksBySeries($serieId, $n) {
        return self::getEntryArray (self::SQL_BOOKS_BY_SERIE, array ($serieId), $n);
    }
    
    public static function getBooksByTag($tagId, $n) {
        return self::getEntryArray (self::SQL_BOOKS_BY_TAG, array ($tagId), $n);
    }
    
    public static function getBooksByCustom($customId, $id, $n) {
        $query = str_format (self::SQL_BOOKS_BY_CUSTOM, "{0}", "{1}", CustomColumn::getTableLinkName ($customId), CustomColumn::getTableLinkColumn ($customId));
        return self::getEntryArray ($query, array ($id), $n);
    }
    
    public static function getBookById($bookId) {
        $result = parent::getDb ()->prepare('select ' . self::BOOK_COLUMNS . '
from books ' . self::SQL_BOOKS_LEFT_JOIN . '
where books.id = ?');
        $result->execute (array ($bookId));
        while ($post = $result->fetchObject ())
        {
            $book = new Book ($post);
            return $book;
        }
        return NULL;
    }
    
    public static function getBookByDataId($dataId) {
        $result = parent::getDb ()->prepare('select ' . self::BOOK_COLUMNS . ', data.name, data.format
from data, books ' . self::SQL_BOOKS_LEFT_JOIN . '
where data.book = books.id and data.id = ?');
        $result->execute (array ($dataId));
        while ($post = $result->fetchObject ())
        {
            $book = new Book ($post);
            $data = new Data ($post, $book);
            $data->id = $dataId;
            $book->datas = array ($data);
            return $book;
        }
        return NULL;
    }
    
    public static function getBooksByQuery($query, $n) {
        return self::getEntryArray (self::SQL_BOOKS_QUERY, array ("%" . $query . "%", "%" . $query . "%"), $n);
    }
    
    public static function getAllBooks() {
        $result = parent::getDb ()->query("select substr (upper (sort), 1, 1) as title, count(*) as count
from books
group by substr (upper (sort), 1, 1)
order by substr (upper (sort), 1, 1)");
        $entryArray = array();
        while ($post = $result->fetchObject ())
        {
            array_push ($entryArray, new Entry ($post->title, Book::getEntryIdByLetter ($post->title), 
                str_format (localize("bookword", $post->count), $post->count), "text", 
                array ( new LinkNavigation ("?page=".parent::PAGE_ALL_BOOKS_LETTER."&id=". rawurlencode ($post->title)))));
        }
        return $entryArray;
    }
    
    public static function getBooksByStartingLetter($letter, $n) {
        return self::getEntryArray (self::SQL_BOOKS_BY_FIRST_LETTER, array ($letter . "%"), $n);
    }
    
    public static function getEntryArray ($query, $params, $n) {
        list ($totalNumber, $result) = parent::executeQuery ($query, self::BOOK_COLUMNS, self::getFilterString (), $params, $n);
        $entryArray = array();
        while ($post = $result->fetchObject ())
        {
            $book = new Book ($post);
            array_push ($entryArray, $book->getEntry ());
        }
        return array ($entryArray, $totalNumber);
    }

    
    public static function getAllRecentBooks() {
        global $config;
        list ($entryArray, $totalNumber) = self::getEntryArray (self::SQL_BOOKS_RECENT . $config['cops_recentbooks_limit'], array (), -1);
        return $entryArray;
    }

}
?>