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
                                                    where books_authors_link.book = books.id and author = ? {1} order by pubdate");
define ('SQL_BOOKS_BY_SERIE', "select {0} from books_series_link, books " . SQL_BOOKS_LEFT_JOIN . "
                                                    where books_series_link.book = books.id and series = ? {1} order by series_index");
define ('SQL_BOOKS_BY_TAG', "select {0} from books_tags_link, books " . SQL_BOOKS_LEFT_JOIN . "
                                                    where books_tags_link.book = books.id and tag = ? {1} order by sort");
define ('SQL_BOOKS_BY_LANGUAGE', "select {0} from books_languages_link, books " . SQL_BOOKS_LEFT_JOIN . "
                                                    where books_languages_link.book = books.id and lang_code = ? {1} order by sort");
define ('SQL_BOOKS_BY_CUSTOM', "select {0} from {2}, books " . SQL_BOOKS_LEFT_JOIN . "
                                                    where {2}.book = books.id and {2}.{3} = ? {1} order by sort");
define ('SQL_BOOKS_QUERY', "select {0} from books " . SQL_BOOKS_LEFT_JOIN . "
                                                    where (
                                                    exists (select null from authors, books_authors_link where book = books.id and author = authors.id and authors.name like ?) or
                                                    exists (select null from tags, books_tags_link where book = books.id and tag = tags.id and tags.name like ?) or
                                                    exists (select null from series, books_series_link on book = books.id and books_series_link.series = series.id and series.name like ?) or
                                                    exists (select null from publishers, books_publishers_link where book = books.id and books_publishers_link.publisher = publishers.id and publishers.name like ?) or
                                                    title like ?) {1} order by books.sort");
define ('SQL_BOOKS_RECENT', "select {0} from books " . SQL_BOOKS_LEFT_JOIN . "
                                                    where 1=1 {1} order by timestamp desc limit ");

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
    const SQL_BOOKS_QUERY = SQL_BOOKS_QUERY;
    const SQL_BOOKS_RECENT = SQL_BOOKS_RECENT;

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
        $this->pubdate = strtotime ($line->pubdate);
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

    public function getContentArray () {
        global $config;
        $i = 0;
        $preferedData = array ();
        foreach ($config['cops_prefered_format'] as $format)
        {
            if ($i == 2) { break; }
            if ($data = $this->getDataFormat ($format)) {
                $i++;
                array_push ($preferedData, array ("url" => $data->getHtmlLink (), "name" => $format));
            }
        }

        $publisher = $this->getPublisher();
        if (is_null ($publisher)) {
            $pn = "";
            $pu = "";
        } else {
            $pn = $publisher->name;
            $link = new LinkNavigation ($publisher->getUri ());
            $pu = $link->hrefXhtml ();
        }

        $serie = $this->getSerie ();
        if (is_null ($serie)) {
            $sn = "";
            $scn = "";
            $su = "";
        } else {
            $sn = $serie->name;
            $scn = str_format (localize ("content.series.data"), $this->seriesIndex, $serie->name);
            $link = new LinkNavigation ($serie->getUri ());
            $su = $link->hrefXhtml ();
        }

        return array ("id" => $this->id,
                      "hasCover" => $this->hasCover,
                      "preferedData" => $preferedData,
                      "rating" => $this->getRating (),
                      "publisherName" => $pn,
                      "publisherurl" => $pu,
                      "pubDate" => $this->getPubDate (),
                      "languagesName" => $this->getLanguages (),
                      "authorsName" => $this->getAuthorsName (),
                      "tagsName" => $this->getTagsName (),
                      "seriesName" => $sn,
                      "seriesIndex" => $this->seriesIndex,
                      "seriesCompleteName" => $scn,
                      "seriesurl" => $su);

    }
    public function getFullContentArray () {
        global $config;
        $out = $this->getContentArray ();

        $out ["coverurl"] = Data::getLink ($this, "jpg", "image/jpeg", Link::OPDS_IMAGE_TYPE, "cover.jpg", NULL)->hrefXhtml ();
        $out ["thumbnailurl"] = Data::getLink ($this, "jpg", "image/jpeg", Link::OPDS_THUMBNAIL_TYPE, "cover.jpg", NULL, NULL, $config['cops_html_thumbnail_height'] * 2)->hrefXhtml ();
        $out ["content"] = $this->getComment (false);
        $out ["datas"] = array ();
        $dataKindle = $this->GetMostInterestingDataToSendToKindle ();
        foreach ($this->getDatas() as $data) {
            $tab = array ("id" => $data->id, "format" => $data->format, "url" => $data->getHtmlLink (), "mail" => 0);
            if (!empty ($config['cops_mail_configuration']) && !is_null ($dataKindle) && $data->id == $dataKindle->id) {
                $tab ["mail"] = 1;
            }
            array_push ($out ["datas"], $tab);
        }
        $out ["authors"] = array ();
        foreach ($this->getAuthors () as $author) {
            $link = new LinkNavigation ($author->getUri ());
            array_push ($out ["authors"], array ("name" => $author->name, "url" => $link->hrefXhtml ()));
        }
        $out ["tags"] = array ();
        foreach ($this->getTags () as $tag) {
            $link = new LinkNavigation ($tag->getUri ());
            array_push ($out ["tags"], array ("name" => $tag->name, "url" => $link->hrefXhtml ()));
        }
        ;
        return $out;
    }

    public function getDetailUrl ($permalink = false) {
        $urlParam = $this->getUri ();
        if (!is_null (GetUrlParam (DB))) $urlParam = addURLParameter ($urlParam, DB, GetUrlParam (DB));
        return 'index.php' . $urlParam;
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

    public function getAuthorsName () {
        return implode (", ", array_map (function ($author) { return $author->name; }, $this->getAuthors ()));
    }

    public function getPublisher () {
        if (is_null ($this->publisher)) {
            $this->publisher = Publisher::getPublisherByBookId ($this->id);
        }
        return $this->publisher;
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
            array_push ($lang, Language::getLanguageString($post->lang_code));
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

    public function GetMostInterestingDataToSendToKindle ()
    {
        $bestFormatForKindle = array ("EPUB", "PDF", "MOBI");
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
        foreach ($this->getDatas () as $data) {
            if ($data->id == $idData) {
                return $data;
            }
        }
        return NULL;
    }


    public function getTagsName () {
        return implode (", ", array_map (function ($tag) { return $tag->name; }, $this->getTags ()));
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
        if (is_null ($this->pubdate) || ($this->pubdate <= -58979923200)) {
            return "";
        }
        else {
            return date ("Y", $this->pubdate);
        }
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
        
        // In case something bad happen below set a default size
        $nw = "160";
        $nh = "120";
    
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
        $nBooks = parent::getDb ($database)->query('select count(*) from books')->fetchColumn();
        return $nBooks;
    }

    public static function getCount() {
        global $config;
        $nBooks = parent::getDb ()->query('select count(*) from books')->fetchColumn();
        $result = array();
        $entry = new Entry (localize ("allbooks.title"),
                          self::ALL_BOOKS_ID,
                          str_format (localize ("allbooks.alphabetical", $nBooks), $nBooks), "text",
                          array ( new LinkNavigation ("?page=".parent::PAGE_ALL_BOOKS)));
        array_push ($result, $entry);
        if ($config['cops_recentbooks_limit'] > 0) {
            $entry = new Entry (localize ("recent.title"),
                              self::ALL_RECENT_BOOKS_ID,
                              str_format (localize ("recent.list"), $config['cops_recentbooks_limit']), "text",
                              array ( new LinkNavigation ("?page=".parent::PAGE_ALL_RECENT_BOOKS)));
            array_push ($result, $entry);
        }
        return $result;
    }

    public static function getBooksByAuthor($authorId, $n) {
        return self::getEntryArray (self::SQL_BOOKS_BY_AUTHOR, array ($authorId), $n);
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

    public static function getBooksByQuery($query, $n, $database = NULL, $numberPerPage = NULL) {
        global $config;
        $i = 0;
        $critArray = array ();
        foreach (array (PageQueryResult::SCOPE_AUTHOR,
                        PageQueryResult::SCOPE_TAG,
                        PageQueryResult::SCOPE_SERIES,
                        PageQueryResult::SCOPE_PUBLISHER,
                        PageQueryResult::SCOPE_BOOK) as $key) {
            if (in_array($key, $config ['cops_ignored_search_scope']) ||
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

    public static function getBooksByStartingLetter($letter, $n, $database = NULL, $numberPerPage = NULL) {
        return self::getEntryArray (self::SQL_BOOKS_BY_FIRST_LETTER, array ($letter . "%"), $n, $database, $numberPerPage);
    }

    public static function getEntryArray ($query, $params, $n, $database = NULL, $numberPerPage = NULL) {
        list ($totalNumber, $result) = parent::executeQuery ($query, self::BOOK_COLUMNS, self::getFilterString (), $params, $n, $database, $numberPerPage);
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
        $entryArray = self::getEntryArray (self::SQL_BOOKS_RECENT . $config['cops_recentbooks_limit'], array (), -1);
        $entryArray = $entryArray [0];
        return $entryArray;
    }

}

function getJson ($complete = false) {
    global $config;
    $page = getURLParam ("page", Base::PAGE_INDEX);
    $query = getURLParam ("query");
    $search = getURLParam ("search");
    $qid = getURLParam ("id");
    $n = getURLParam ("n", "1");
    $database = GetUrlParam (DB);

    if ($search) {
        $out = array ();
        $pagequery = Base::PAGE_OPENSEARCH_QUERY;
        $dbArray = array ("");
        $d = $database; 
        // Special case when no databases were chosen, we search on all databases
        if (Base::noDatabaseSelected ()) {
            $dbArray = Base::getDbNameList ();
            $d = 0;
        }
        foreach ($dbArray as $key) {
            if (Base::noDatabaseSelected ()) {
                array_push ($out, array ("title" => $key,
                                         "class" => "tt-header",
                                         "navlink" => "index.php?db={$d}"));
                Base::getDb ($d);
            }
            foreach (array (PageQueryResult::SCOPE_BOOK,
                            PageQueryResult::SCOPE_AUTHOR,
                            PageQueryResult::SCOPE_SERIES,
                            PageQueryResult::SCOPE_TAG,
                            PageQueryResult::SCOPE_PUBLISHER) as $key) {
                if (in_array($key, $config ['cops_ignored_search_scope'])) {
                    continue;
                }
                switch ($key) {
                    case "book" :
                        $array = Book::getBooksByStartingLetter ('%' . $query, 1, NULL, 5);
                        break;
                    case "author" :
                        $array = Author::getAuthorsByStartingLetter ('%' . $query);
                        break;
                    case "series" :
                        $array = Serie::getAllSeriesByQuery ($query);
                        break;
                    case "tag" :
                        $array = Tag::getAllTagsByQuery ($query, 1, NULL, 5);
                        break;
                    case "publisher" :
                        $array = Publisher::getAllPublishersByQuery ($query);
                        break;
                }

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
                    array_push ($out, array ("title" => str_format (localize("{$key}word", $total), $total),
                                             "class" => Base::noDatabaseSelected () ? "" : "tt-header",
                                             "navlink" => "index.php?page={$pagequery}&query={$query}&db={$d}&scope={$key}"));
                }
                if (!Base::noDatabaseSelected ()) {
                    foreach ($array as $entry) {
                        if ($entry instanceof EntryBook) {
                            array_push ($out, array ("class" => "", "title" => $entry->title, "navlink" => $entry->book->getDetailUrl ()));
                        } else {
                            array_push ($out, array ("class" => "", "title" => $entry->title, "navlink" => $entry->getNavLink ()));
                        }
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

    $currentPage = Page::getPage ($page, $qid, $query, $n);
    $currentPage->InitializeContent ();

    $out = array ( "title" => $currentPage->title);
    $entries = array ();
    foreach ($currentPage->entryArray as $entry) {
        array_push ($entries, $entry->getContentArray ());
    }
    if (!is_null ($currentPage->book)) {
        $out ["book"] = $currentPage->book->getFullContentArray ();
    }
    $out ["databaseId"] = GetUrlParam (DB, "");
    $out ["databaseName"] = Base::getDbName ();
    $out ["page"] = $page;
    $out ["multipleDatabase"] = Base::isMultipleDatabaseEnabled () ? 1 : 0;
    $out ["entries"] = $entries;
    $out ["isPaginated"] = 0;
    if ($currentPage->isPaginated ()) {
        $prevLink = $currentPage->getPrevLink ();
        $nextLink = $currentPage->getNextLink ();
        $out ["isPaginated"] = 1;
        $out ["prevLink"] = "";
        if (!is_null ($prevLink)) {
            $out ["prevLink"] = $prevLink->hrefXhtml ();
        }
        $out ["nextLink"] = "";
        if (!is_null ($nextLink)) {
            $out ["nextLink"] = $nextLink->hrefXhtml ();
        }
        $out ["maxPage"] = $currentPage->getMaxPage ();
        $out ["currentPage"] = $currentPage->n;
    }
    if (!is_null (getURLParam ("complete")) || $complete) {
        $out ["c"] = array ("version" => VERSION, "i18n" => array (
                       "coverAlt" => localize("i18n.coversection"),
                       "authorsTitle" => localize("authors.title"),
                       "bookwordTitle" => localize("bookword.title"),
                       "tagsTitle" => localize("tags.title"),
                       "seriesTitle" => localize("series.title"),
                       "customizeTitle" => localize ("customize.title"),
                       "aboutTitle" => localize ("about.title"),
                       "previousAlt" => localize ("paging.previous.alternate"),
                       "nextAlt" => localize ("paging.next.alternate"),
                       "searchAlt" => localize ("search.alternate"),
                       "sortAlt" => localize ("sort.alternate"),
                       "homeAlt" => localize ("home.alternate"),
                       "cogAlt" => localize ("cog.alternate"),
                       "permalinkAlt" => localize ("permalink.alternate"),
                       "publisherName" => localize("publisher.name"),
                       "pubdateTitle" => localize("pubdate.title"),
                       "languagesTitle" => localize("language.title"),
                       "contentTitle" => localize("content.summary"),
                       "sortorderAsc" => localize("search.sortorder.asc"),
                       "sortorderDesc" => localize("search.sortorder.desc"),
                       "customizeEmail" => localize("customize.email")),
                   "url" => array (
                       "detailUrl" => "index.php?page=13&id={0}&db={1}",
                       "coverUrl" => "fetch.php?id={0}&db={1}",
                       "thumbnailUrl" => "fetch.php?height=" . $config['cops_html_thumbnail_height'] . "&id={0}&db={1}"),
                   "config" => array (
                       "use_fancyapps" => $config ["cops_use_fancyapps"],
                       "max_item_per_page" => $config['cops_max_item_per_page'],
                       "server_side_rendering" => useServerSideRendering (),
                       "html_tag_filter" => $config['cops_html_tag_filter']));
        if ($config['cops_thumbnail_handling'] == "1") {
            $out ["c"]["url"]["thumbnailUrl"] = $out ["c"]["url"]["coverUrl"];
        } else if (!empty ($config['cops_thumbnail_handling'])) {
            $out ["c"]["url"]["thumbnailUrl"] = $config['cops_thumbnail_handling'];
        }
   }

    $out ["containsBook"] = 0;
    if ($currentPage->containsBook ()) {
        $out ["containsBook"] = 1;
    }

    $out["abouturl"] = "index.php" . addURLParameter ("?page=16", DB, $database);

    if ($page == Base::PAGE_ABOUT) {
        $temp = preg_replace ("/\<h1\>About COPS\<\/h1\>/", "<h1>About COPS " . VERSION . "</h1>", file_get_contents('about.html'));
        $out ["fullhtml"] = $temp;
    }

    $out ["homeurl"] = "index.php";
    if ($page != Base::PAGE_INDEX && !is_null ($database)) $out ["homeurl"] = $out ["homeurl"] .  "?" . addURLParameter ("", DB, $database);

    return $out;
}

