<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

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

    /* @var Entry[] */
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
        $this->authorName = empty($config['cops_author_name']) ? utf8_encode('Sébastien Lucas') : $config['cops_author_name'];
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
                $customColumn = CustomColumnType::createByLookup($lookup);
                if (!is_null ($customColumn) && $customColumn->isSearchable()) {
                    array_push ($this->entryArray, $customColumn->getCount());
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
