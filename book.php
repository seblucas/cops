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
require_once('rating.php');
require_once('publisher.php');
require_once('tag.php');
require_once('language.php');
require_once("customcolumn.php");
require_once('data.php');
require_once('resources/php-epub-meta/epub.php');

// Silly thing because PHP forbid string concatenation in class const
define ('SQL_BOOKS_LEFT_JOIN', "left outer join comments on comments.book = books.id
                                left outer join books_ratings_link on books_ratings_link.book = books.id
                                left outer join ratings on books_ratings_link.rating = ratings.id ");
define ('SQL_BOOKS_ALL', "select {0} from books " . SQL_BOOKS_LEFT_JOIN . " order by books.sort ");
define ('SQL_BOOKS_BY_PUBLISHER', "select {0} from books_publishers_link, books " . SQL_BOOKS_LEFT_JOIN . "
                                                    where books_publishers_link.book = books.id and publisher = ? {1} order by publisher");
define ('SQL_BOOKS_BY_FIRST_LETTER', "select {0} from books " . SQL_BOOKS_LEFT_JOIN . "
                                                    where upper (books.sort) like ? order by books.sort");
define ('SQL_BOOKS_BY_AUTHOR', "select {0} from books_authors_link, books " . SQL_BOOKS_LEFT_JOIN . "
                                                    left outer join books_series_link on books_series_link.book = books.id
                                                    where books_authors_link.book = books.id and author = ? {1} order by series desc, series_index asc, pubdate asc");
define ('SQL_BOOKS_BY_SERIE', "select {0} from books_series_link, books " . SQL_BOOKS_LEFT_JOIN . "
                                                    where books_series_link.book = books.id and series = ? {1} order by series_index");
define ('SQL_BOOKS_BY_TAG', "select {0} from books_tags_link, books " . SQL_BOOKS_LEFT_JOIN . "
                                                    where books_tags_link.book = books.id and tag = ? {1} order by sort");
define ('SQL_BOOKS_BY_LANGUAGE', "select {0} from books_languages_link, books " . SQL_BOOKS_LEFT_JOIN . "
                                                    where books_languages_link.book = books.id and lang_code = ? {1} order by sort");
define ('SQL_BOOKS_BY_CUSTOM', "select {0} from {2}, books " . SQL_BOOKS_LEFT_JOIN . "
                                                    where {2}.book = books.id and {2}.{3} = ? {1} order by sort");
define ('SQL_BOOKS_BY_CUSTOM_BOOL_TRUE', "select {0} from {2}, books " . SQL_BOOKS_LEFT_JOIN . "
                                                    where {2}.book = books.id and {2}.value = 1 {1} order by sort");
define ('SQL_BOOKS_BY_CUSTOM_BOOL_FALSE', "select {0} from {2}, books " . SQL_BOOKS_LEFT_JOIN . "
                                                    where {2}.book = books.id and {2}.value = 0 {1} order by sort");
define ('SQL_BOOKS_BY_CUSTOM_BOOL_NULL', "select {0} from books " . SQL_BOOKS_LEFT_JOIN . "
                                                    where books.id not in (select book from {2}) {1} order by sort");
define ('SQL_BOOKS_BY_CUSTOM_RATING', "select {0} from books " . SQL_BOOKS_LEFT_JOIN . "
                                                    left join {2} on {2}.book = books.id
                                                    left join {3} on {3}.id = {2}.{4}
                                                    where {3}.value = ?  order by sort");
define ('SQL_BOOKS_BY_CUSTOM_RATING_NULL', "select {0} from books " . SQL_BOOKS_LEFT_JOIN . "
								                    left join {2} on {2}.book = books.id
								                    left join {3} on {3}.id = {2}.{4}
                                                    where ((books.id not in (select {2}.book from {2})) or ({3}.value = 0)) {1} order by sort");
define ('SQL_BOOKS_BY_CUSTOM_DATE', "select {0} from {2}, books " . SQL_BOOKS_LEFT_JOIN . "
                                                    where {2}.book = books.id and date({2}.value) = ? {1} order by sort");
define ('SQL_BOOKS_BY_CUSTOM_DIRECT', "select {0} from {2}, books " . SQL_BOOKS_LEFT_JOIN . "
                                                    where {2}.book = books.id and {2}.value = ? {1} order by sort");
define ('SQL_BOOKS_BY_CUSTOM_DIRECT_ID', "select {0} from {2}, books " . SQL_BOOKS_LEFT_JOIN . "
                                                    where {2}.book = books.id and {2}.id = ? {1} order by sort");
define ('SQL_BOOKS_QUERY', "select {0} from books " . SQL_BOOKS_LEFT_JOIN . "
                                                    where (
                                                    exists (select null from authors, books_authors_link where book = books.id and author = authors.id and authors.name like ?) or
                                                    exists (select null from tags, books_tags_link where book = books.id and tag = tags.id and tags.name like ?) or
                                                    exists (select null from series, books_series_link on book = books.id and books_series_link.series = series.id and series.name like ?) or
                                                    exists (select null from publishers, books_publishers_link where book = books.id and books_publishers_link.publisher = publishers.id and publishers.name like ?) or
                                                    title like ?) {1} order by books.sort");
define ('SQL_BOOKS_RECENT', "select {0} from books " . SQL_BOOKS_LEFT_JOIN . "
                                                    where 1=1 {1} order by timestamp desc limit ");
define ('SQL_BOOKS_BY_RATING', "select {0} from books " . SQL_BOOKS_LEFT_JOIN . "
                                                    where books_ratings_link.book = books.id and ratings.id = ? {1} order by sort");

class Book extends Base {
    const ALL_BOOKS_UUID = "urn:uuid";
    const ALL_BOOKS_ID = "cops:books";
    const ALL_RECENT_BOOKS_ID = "cops:recentbooks";
    const BOOK_COLUMNS = "books.id as id, books.title as title, text as comment, path, timestamp, pubdate, series_index, uuid, has_cover, ratings.rating";

    const SQL_BOOKS_LEFT_JOIN = SQL_BOOKS_LEFT_JOIN;
    const SQL_BOOKS_ALL = SQL_BOOKS_ALL;
    const SQL_BOOKS_BY_PUBLISHER = SQL_BOOKS_BY_PUBLISHER;
    const SQL_BOOKS_BY_FIRST_LETTER = SQL_BOOKS_BY_FIRST_LETTER;
    const SQL_BOOKS_BY_AUTHOR = SQL_BOOKS_BY_AUTHOR;
    const SQL_BOOKS_BY_SERIE = SQL_BOOKS_BY_SERIE;
    const SQL_BOOKS_BY_TAG = SQL_BOOKS_BY_TAG;
    const SQL_BOOKS_BY_LANGUAGE = SQL_BOOKS_BY_LANGUAGE;
    const SQL_BOOKS_BY_CUSTOM = SQL_BOOKS_BY_CUSTOM;
    const SQL_BOOKS_BY_CUSTOM_BOOL_TRUE = SQL_BOOKS_BY_CUSTOM_BOOL_TRUE;
    const SQL_BOOKS_BY_CUSTOM_BOOL_FALSE = SQL_BOOKS_BY_CUSTOM_BOOL_FALSE;
    const SQL_BOOKS_BY_CUSTOM_BOOL_NULL = SQL_BOOKS_BY_CUSTOM_BOOL_NULL;
    const SQL_BOOKS_BY_CUSTOM_RATING = SQL_BOOKS_BY_CUSTOM_RATING;
    const SQL_BOOKS_BY_CUSTOM_RATING_NULL = SQL_BOOKS_BY_CUSTOM_RATING_NULL;
    const SQL_BOOKS_BY_CUSTOM_DATE = SQL_BOOKS_BY_CUSTOM_DATE;
    const SQL_BOOKS_BY_CUSTOM_DIRECT = SQL_BOOKS_BY_CUSTOM_DIRECT;
    const SQL_BOOKS_BY_CUSTOM_DIRECT_ID = SQL_BOOKS_BY_CUSTOM_DIRECT_ID;
    const SQL_BOOKS_QUERY = SQL_BOOKS_QUERY;
    const SQL_BOOKS_RECENT = SQL_BOOKS_RECENT;
    const SQL_BOOKS_BY_RATING = SQL_BOOKS_BY_RATING;

    const BAD_SEARCH = "QQQQQ";

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
    public $publisher = NULL;
    public $serie = NULL;
    public $tags = NULL;
    public $languages = NULL;
    public $format = array ();


    public function __construct($line) {
        $this->id = $line->id;
        $this->title = $line->title;
        $this->timestamp = strtotime ($line->timestamp);
        $this->pubdate = $line->pubdate;
        $this->path = Base::getDbDirectory () . $line->path;
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
        return "?page=".parent::PAGE_BOOK_DETAIL."&id=$this->id";
    }

    public function getDetailUrl () {
        $urlParam = $this->getUri ();
        if (!is_null (GetUrlParam (DB))) $urlParam = addURLParameter ($urlParam, DB, GetUrlParam (DB));
        return 'index.php' . $urlParam;
    }

    public function getTitle () {
        return $this->title;
    }

    /* Other class (author, series, tag, ...) initialization and accessors */

    /**
     * @return Author[]
     */
    public function getAuthors () {
        if (is_null ($this->authors)) {
            $this->authors = Author::getAuthorByBookId ($this->id);
        }
        return $this->authors;
    }

    public function getAuthorsName () {
        return implode (", ", array_map (function ($author) { return $author->name; }, $this->getAuthors ()));
    }

    public function getAuthorsSort () {
        return implode (", ", array_map (function ($author) { return $author->sort; }, $this->getAuthors ()));
    }

    public function getPublisher () {
        if (is_null ($this->publisher)) {
            $this->publisher = Publisher::getPublisherByBookId ($this->id);
        }
        return $this->publisher;
    }

    /**
     * @return Serie
     */
    public function getSerie () {
        if (is_null ($this->serie)) {
            $this->serie = Serie::getSerieByBookId ($this->id);
        }
        return $this->serie;
    }

    /**
     * @return string
     */
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
            array_push ($lang, Language::getLanguageString($post->lang_code));
        }
        return implode (", ", $lang);
    }

    /**
     * @return Tag[]
     */
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
                array_push ($this->tags, new Tag ($post));
            }
        }
        return $this->tags;
    }

    public function getTagsName () {
        return implode (", ", array_map (function ($tag) { return $tag->name; }, $this->getTags ()));
    }

    /**
     * @return Data[]
     */
    public function getDatas ()
    {
        if (is_null ($this->datas)) {
            $this->datas = Data::getDataByBook ($this);
        }
        return $this->datas;
    }

    /* End of other class (author, series, tag, ...) initialization and accessors */

    public static function getFilterString () {
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

    public function GetMostInterestingDataToSendToKindle ()
    {
        $bestFormatForKindle = array ("EPUB", "PDF", "AZW3", "MOBI");
        $bestRank = -1;
        $bestData = NULL;
        foreach ($this->getDatas () as $data) {
            $key = array_search ($data->format, $bestFormatForKindle);
            if ($key !== false && $key > $bestRank) {
                $bestRank = $key;
                $bestData = $data;
            }
        }
        return $bestData;
    }

    public function getDataById ($idData)
    {
        $reduced = array_filter ($this->getDatas (), function ($data) use ($idData) {
            return $data->id == $idData;
        });
        return reset ($reduced);
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

    public function getPubDate () {
        if (empty ($this->pubdate)) {
            return "";
        }
        $dateY = (int) substr($this->pubdate, 0, 4);
        if ($dateY > 102) {
            return str_pad($dateY, 4, "0", STR_PAD_LEFT);
        }
        return "";
    }

    public function getComment ($withSerie = true) {
        $addition = "";
        $se = $this->getSerie ();
        if (!is_null ($se) && $withSerie) {
            $addition = $addition . "<strong>" . localize("content.series") . "</strong>" . str_format (localize ("content.series.data"), $this->seriesIndex, htmlspecialchars ($se->name)) . "<br />\n";
        }
        if (preg_match ("/<\/(div|p|a|span)>/", $this->comment))
        {
            return $addition . html2xhtml ($this->comment);
        }
        else
        {
            return $addition . htmlspecialchars ($this->comment);
        }
    }

    public function getDataFormat ($format) {
        $reduced = array_filter ($this->getDatas (), function ($data) use ($format) {
            return $data->format == $format;
        });
        return reset ($reduced);
    }

    public function getFilePath ($extension, $idData = NULL, $relative = false)
    {
        if ($extension == "jpg")
        {
            $file = "cover.jpg";
        }
        else
        {
            $data = $this->getDataById ($idData);
            if (!$data) return NULL;
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
        global $config;
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
            $epub->Cover2 ($this->getFilePath ("jpg"), "image/jpeg");
            $epub->Calibre ($this->uuid);
            $se = $this->getSerie ();
            if (!is_null ($se)) {
                $epub->Serie ($se->name);
                $epub->SerieIndex ($this->seriesIndex);
            }
            if ($config['cops_provide_kepub'] == "1"  && preg_match("/Kobo/", $_SERVER['HTTP_USER_AGENT'])) {
                $epub->updateForKepub ();
            }
            $epub->download ($data->getUpdatedFilenameEpub ());
        }
        catch (Exception $e)
        {
            echo "Exception : " . $e->getMessage();
        }
    }

    public function getThumbnail ($width, $height, $outputfile = NULL) {
        if (is_null ($width) && is_null ($height)) {
            return false;
        }

        $file = $this->getFilePath ("jpg");
        // get image size
        if ($size = GetImageSize($file)) {
            $w = $size[0];
            $h = $size[1];
            //set new size
            if (!is_null ($width)) {
                $nw = $width;
                if ($nw >= $w) { return false; }
                $nh = ($nw*$h)/$w;
            } else {
                $nh = $height;
                if ($nh >= $h) { return false; }
                $nw = ($nh*$w)/$h;
            }
        } else {
            return false;
        }

        //draw the image
        $src_img = imagecreatefromjpeg($file);
        $dst_img = imagecreatetruecolor($nw,$nh);
        imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $nw, $nh, $w, $h);//resizing the image
        imagejpeg($dst_img,$outputfile,80);
        imagedestroy($src_img);
        imagedestroy($dst_img);

        return true;
    }

    public function getLinkArray ()
    {
        $linkArray = array();

        if ($this->hasCover)
        {
            array_push ($linkArray, Data::getLink ($this, "jpg", "image/jpeg", Link::OPDS_IMAGE_TYPE, "cover.jpg", NULL));

            array_push ($linkArray, Data::getLink ($this, "jpg", "image/jpeg", Link::OPDS_THUMBNAIL_TYPE, "cover.jpg", NULL));
        }

        foreach ($this->getDatas () as $data)
        {
            if ($data->isKnownType ())
            {
                array_push ($linkArray, $data->getDataLink (Link::OPDS_ACQUISITION_TYPE, $data->format));
            }
        }

        foreach ($this->getAuthors () as $author) {
            /* @var $author Author */
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

    public static function getBookCount($database = NULL) {
        return parent::executeQuerySingle ('select count(*) from books', $database);
    }

    public static function getCount() {
        global $config;
        $nBooks = parent::executeQuerySingle ('select count(*) from books');
        $result = array();
        $entry = new Entry (localize ("allbooks.title"),
                          self::ALL_BOOKS_ID,
                          str_format (localize ("allbooks.alphabetical", $nBooks), $nBooks), "text",
                          array ( new LinkNavigation ("?page=".parent::PAGE_ALL_BOOKS)), "", $nBooks);
        array_push ($result, $entry);
        if ($config['cops_recentbooks_limit'] > 0) {
            $entry = new Entry (localize ("recent.title"),
                              self::ALL_RECENT_BOOKS_ID,
                              str_format (localize ("recent.list"), $config['cops_recentbooks_limit']), "text",
                              array ( new LinkNavigation ("?page=".parent::PAGE_ALL_RECENT_BOOKS)), "", $config['cops_recentbooks_limit']);
            array_push ($result, $entry);
        }
        return $result;
    }

    public static function getBooksByAuthor($authorId, $n) {
        return self::getEntryArray (self::SQL_BOOKS_BY_AUTHOR, array ($authorId), $n);
    }

    public static function getBooksByRating($ratingId, $n) {
        return self::getEntryArray (self::SQL_BOOKS_BY_RATING, array ($ratingId), $n);
    }

    public static function getBooksByPublisher($publisherId, $n) {
        return self::getEntryArray (self::SQL_BOOKS_BY_PUBLISHER, array ($publisherId), $n);
    }

    public static function getBooksBySeries($serieId, $n) {
        return self::getEntryArray (self::SQL_BOOKS_BY_SERIE, array ($serieId), $n);
    }

    public static function getBooksByTag($tagId, $n) {
        return self::getEntryArray (self::SQL_BOOKS_BY_TAG, array ($tagId), $n);
    }

    public static function getBooksByLanguage($languageId, $n) {
        return self::getEntryArray (self::SQL_BOOKS_BY_LANGUAGE, array ($languageId), $n);
    }

    /**
     * @param $customColumn CustomColumn
     * @param $id integer
     * @param $n integer
     * @return array
     */
    public static function getBooksByCustom($customColumn, $id, $n) {
        list($query, $params) = $customColumn->getQuery($id);

        return self::getEntryArray ($query, $params, $n);
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

    public static function getBooksByQuery($query, $n, $database = NULL, $numberPerPage = NULL) {
        $i = 0;
        $critArray = array ();
        foreach (array (PageQueryResult::SCOPE_AUTHOR,
                        PageQueryResult::SCOPE_TAG,
                        PageQueryResult::SCOPE_SERIES,
                        PageQueryResult::SCOPE_PUBLISHER,
                        PageQueryResult::SCOPE_BOOK) as $key) {
            if (in_array($key, getCurrentOption ('ignored_categories')) ||
                (!array_key_exists ($key, $query) && !array_key_exists ("all", $query))) {
                $critArray [$i] = self::BAD_SEARCH;
            }
            else {
                if (array_key_exists ($key, $query)) {
                    $critArray [$i] = $query [$key];
                } else {
                    $critArray [$i] = $query ["all"];
                }
            }
            $i++;
        }
        return self::getEntryArray (self::SQL_BOOKS_QUERY, $critArray, $n, $database, $numberPerPage);
    }

    public static function getBooks($n) {
        list ($entryArray, $totalNumber) = self::getEntryArray (self::SQL_BOOKS_ALL , array (), $n);
        return array ($entryArray, $totalNumber);
    }

    public static function getAllBooks() {
        /* @var $result PDOStatement */

        list (, $result) = parent::executeQuery ("select {0}
from books
group by substr (upper (sort), 1, 1)
order by substr (upper (sort), 1, 1)", "substr (upper (sort), 1, 1) as title, count(*) as count", self::getFilterString (), array (), -1);

        $entryArray = array();
        while ($post = $result->fetchObject ())
        {
            array_push ($entryArray, new Entry ($post->title, Book::getEntryIdByLetter ($post->title),
                str_format (localize("bookword", $post->count), $post->count), "text",
                array ( new LinkNavigation ("?page=".parent::PAGE_ALL_BOOKS_LETTER."&id=". rawurlencode ($post->title))), "", $post->count));
        }
        return $entryArray;
    }

    public static function getBooksByStartingLetter($letter, $n, $database = NULL, $numberPerPage = NULL) {
        return self::getEntryArray (self::SQL_BOOKS_BY_FIRST_LETTER, array ($letter . "%"), $n, $database, $numberPerPage);
    }

    public static function getEntryArray ($query, $params, $n, $database = NULL, $numberPerPage = NULL) {
        /* @var $totalNumber integer */
        /* @var $result PDOStatement */
        list($totalNumber, $result) = parent::executeQuery($query, self::BOOK_COLUMNS, self::getFilterString (), $params, $n, $database, $numberPerPage);

        $entryArray = array();
        while ($post = $result->fetchObject())
        {
            $book = new Book ($post);
            array_push ($entryArray, $book->getEntry());
        }
        return array ($entryArray, $totalNumber);
    }

    public static function getAllRecentBooks() {
        global $config;
        list ($entryArray, ) = self::getEntryArray (self::SQL_BOOKS_RECENT . $config['cops_recentbooks_limit'], array (), -1);
        return $entryArray;
    }

    /**
     * The values of all the specified columns
     *
     * @param string[] $columns
     * @return CustomColumn[]
     */
    public function getCustomColumnValues($columns, $asArray = false) {
        $result = array();
        
        foreach ($columns as $lookup) {
            $col = CustomColumnType::createByLookup($lookup);
            if (! is_null($col)) {
                $cust = $col->getCustomByBook($this);
                if (! is_null($cust)) {
                    if ($asArray) {
                        array_push($result, $cust->toArray());
                    } else {
                        array_push($result, $cust);
                    }
                }
            }
        }

        return $result;
    }
}
