<?php
/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

require_once (dirname(__FILE__) . "/config_test.php");
require_once (dirname(__FILE__) . "/../book.php");

class PageTest extends PHPUnit_Framework_TestCase
{
    public function testPageIndex ()
    {
        global $config;
        $page = Base::PAGE_INDEX;
        $query = NULL;
        $qid = NULL;
        $n = "1";

        $currentPage = Page::getPage ($page, $qid, $query, $n);
        $currentPage->InitializeContent ();

        $this->assertEquals ($config['cops_title_default'], $currentPage->title);
        $this->assertCount (7, $currentPage->entryArray);
        $this->assertEquals ("Authors", $currentPage->entryArray [0]->title);
        $this->assertEquals ("Alphabetical index of the 5 authors", $currentPage->entryArray [0]->content);
        $this->assertEquals ("Series", $currentPage->entryArray [1]->title);
        $this->assertEquals ("Alphabetical index of the 3 series", $currentPage->entryArray [1]->content);
        $this->assertEquals ("Publishers", $currentPage->entryArray [2]->title);
        $this->assertEquals ("Alphabetical index of the 5 publishers", $currentPage->entryArray [2]->content);
        $this->assertEquals ("Tags", $currentPage->entryArray [3]->title);
        $this->assertEquals ("Alphabetical index of the 10 tags", $currentPage->entryArray [3]->content);
        $this->assertEquals ("Languages", $currentPage->entryArray [4]->title);
        $this->assertEquals ("Alphabetical index of the single language", $currentPage->entryArray [4]->content);
        $this->assertEquals ("All books", $currentPage->entryArray [5]->title);
        $this->assertEquals ("Alphabetical index of the 14 books", $currentPage->entryArray [5]->content);
        $this->assertEquals ("Recent additions", $currentPage->entryArray [6]->title);
        $this->assertEquals ("50 most recent books", $currentPage->entryArray [6]->content);
        $this->assertFalse ($currentPage->ContainsBook ());
    }

    public function testPageIndexWithIgnored ()
    {
        global $config;
        $page = Base::PAGE_INDEX;
        $query = NULL;
        $qid = NULL;
        $n = "1";
        
        $config ['cops_ignored_categories'] = array ("author", "series", "tag", "publisher", "language");

        $currentPage = Page::getPage ($page, $qid, $query, $n);
        $currentPage->InitializeContent ();

        $this->assertEquals ($config['cops_title_default'], $currentPage->title);
        $this->assertCount (2, $currentPage->entryArray);
        $this->assertEquals ("All books", $currentPage->entryArray [0]->title);
        $this->assertEquals ("Alphabetical index of the 14 books", $currentPage->entryArray [0]->content);
        $this->assertEquals ("Recent additions", $currentPage->entryArray [1]->title);
        $this->assertEquals ("50 most recent books", $currentPage->entryArray [1]->content);
        $this->assertFalse ($currentPage->ContainsBook ());
    }

    
    public function testPageIndexWithCustomColumn ()
    {
        global $config;
        $page = Base::PAGE_INDEX;
        $query = NULL;
        $qid = NULL;
        $n = "1";

        $config['cops_calibre_custom_column'] = array ("type1");

        $currentPage = Page::getPage ($page, $qid, $query, $n);
        $currentPage->InitializeContent ();

        $this->assertCount (8, $currentPage->entryArray);
        $this->assertEquals ("Type1", $currentPage->entryArray [5]->title);
        $this->assertEquals ("Alphabetical index of the 2 tags", $currentPage->entryArray [5]->content);

        $config['cops_calibre_custom_column'] = array ("type2");

        $currentPage = Page::getPage ($page, $qid, $query, $n);
        $currentPage->InitializeContent ();

        $this->assertCount (8, $currentPage->entryArray);
        $this->assertEquals ("Type2", $currentPage->entryArray [5]->title);
        $this->assertEquals ("Alphabetical index of the 3 tags", $currentPage->entryArray [5]->content);

        $config['cops_calibre_custom_column'] = array ("type4");

        $currentPage = Page::getPage ($page, $qid, $query, $n);
        $currentPage->InitializeContent ();

        $this->assertCount (8, $currentPage->entryArray);
        $this->assertEquals ("Type4", $currentPage->entryArray [5]->title);
        $this->assertEquals ("Alphabetical index of the 2 tags", $currentPage->entryArray [5]->content);

        $config['cops_calibre_custom_column'] = array ("type1", "type2", "type4");

        $currentPage = Page::getPage ($page, $qid, $query, $n);
        $currentPage->InitializeContent ();

        $this->assertCount (10, $currentPage->entryArray);

        $config['cops_calibre_custom_column'] = array ();
    }

    public function testPageAllCustom ()
    {
        $page = Base::PAGE_ALL_CUSTOMS;
        $query = NULL;
        $qid = NULL;
        $n = "1";

        $_GET ["custom"] = "1";

        $currentPage = Page::getPage ($page, $qid, $query, $n);
        $currentPage->InitializeContent ();

        $this->assertEquals ("Type4", $currentPage->title);
        $this->assertCount (2, $currentPage->entryArray);
        $this->assertEquals ("SeriesLike", $currentPage->entryArray [0]->title);
        $this->assertFalse ($currentPage->ContainsBook ());

        $_GET ["custom"] = "2";

        $currentPage = Page::getPage ($page, $qid, $query, $n);
        $currentPage->InitializeContent ();

        $this->assertEquals ("Type2", $currentPage->title);
        $this->assertCount (3, $currentPage->entryArray);
        $this->assertEquals ("tag1", $currentPage->entryArray [0]->title);
        $this->assertFalse ($currentPage->ContainsBook ());

        $_GET ["custom"] = "3";

        $currentPage = Page::getPage ($page, $qid, $query, $n);
        $currentPage->InitializeContent ();

        $this->assertEquals ("Type1", $currentPage->title);
        $this->assertCount (2, $currentPage->entryArray);
        $this->assertEquals ("other", $currentPage->entryArray [0]->title);
        $this->assertFalse ($currentPage->ContainsBook ());

        $_GET ["custom"] = NULL;
    }

    public function testPageCustomDetail ()
    {
        $page = Base::PAGE_CUSTOM_DETAIL;
        $query = NULL;
        $qid = "1";
        $n = "1";

        $_GET ["custom"] = "1";

        $currentPage = Page::getPage ($page, $qid, $query, $n);
        $currentPage->InitializeContent ();

        $this->assertEquals ("SeriesLike", $currentPage->title);
        $this->assertCount (2, $currentPage->entryArray);
        $this->assertEquals ("Alice's Adventures in Wonderland", $currentPage->entryArray [0]->title);
        $this->assertTrue ($currentPage->ContainsBook ());

        $_GET ["custom"] = "2";

        $currentPage = Page::getPage ($page, $qid, $query, $n);
        $currentPage->InitializeContent ();

        $this->assertEquals ("tag1", $currentPage->title);
        $this->assertCount (2, $currentPage->entryArray);
        $this->assertEquals ("Alice's Adventures in Wonderland", $currentPage->entryArray [0]->title);
        $this->assertTrue ($currentPage->ContainsBook ());

        $_GET ["custom"] = "3";
        $qid = "2";

        $currentPage = Page::getPage ($page, $qid, $query, $n);
        $currentPage->InitializeContent ();

        $this->assertEquals ("other", $currentPage->title);
        $this->assertCount (1, $currentPage->entryArray);
        $this->assertEquals ("A Study in Scarlet", $currentPage->entryArray [0]->title);
        $this->assertTrue ($currentPage->ContainsBook ());

        $_GET ["custom"] = NULL;
    }

    public function testPageAllAuthors ()
    {
        global $config;
        $page = Base::PAGE_ALL_AUTHORS;
        $query = NULL;
        $qid = NULL;
        $n = "1";

        $config['cops_author_split_first_letter'] = "0";

        $currentPage = Page::getPage ($page, $qid, $query, $n);
        $currentPage->InitializeContent ();

        $this->assertEquals ("Authors", $currentPage->title);
        $this->assertCount (5, $currentPage->entryArray);
        $this->assertEquals ("Carroll, Lewis", $currentPage->entryArray [0]->title);
        $this->assertFalse ($currentPage->ContainsBook ());

        $config['cops_author_split_first_letter'] = "1";

        $currentPage = Page::getPage ($page, $qid, $query, $n);
        $currentPage->InitializeContent ();

        $this->assertEquals ("Authors", $currentPage->title);
        $this->assertCount (4, $currentPage->entryArray);
        $this->assertEquals ("C", $currentPage->entryArray [0]->title);
        $this->assertFalse ($currentPage->ContainsBook ());
    }

    public function testPageAuthorsFirstLetter ()
    {
        $page = Base::PAGE_AUTHORS_FIRST_LETTER;
        $query = NULL;
        $qid = "C";
        $n = "1";

        // Author Lewis Carroll
        $currentPage = Page::getPage ($page, $qid, $query, $n);
        $currentPage->InitializeContent ();

        $this->assertEquals ("1 author starting with C", $currentPage->title);
        $this->assertCount (1, $currentPage->entryArray);
        $this->assertFalse ($currentPage->ContainsBook ());
    }

    public function testPageAuthorsDetail ()
    {
        global $config;
        $page = Base::PAGE_AUTHOR_DETAIL;
        $query = NULL;
        $qid = "1";
        $n = "1";
        $_SERVER['QUERY_STRING'] = "page=" . Base::PAGE_AUTHOR_DETAIL . "&id=1&n=1";

        $config['cops_max_item_per_page'] = 2;

        // First page

        $currentPage = Page::getPage ($page, $qid, $query, $n);
        $currentPage->InitializeContent ();

        $this->assertEquals ("Doyle, Arthur Conan", $currentPage->title);
        $this->assertEquals (4, $currentPage->getMaxPage ());
        $this->assertCount (2, $currentPage->entryArray);
        $this->assertTrue ($currentPage->ContainsBook ());
        $this->assertTrue ($currentPage->IsPaginated ());
        $this->assertNull ($currentPage->getPrevLink ());

        // Last page
        $config['cops_max_item_per_page'] = 5;
        $n = "2";

        $currentPage = Page::getPage ($page, $qid, $query, $n);
        $currentPage->InitializeContent ();

        $this->assertEquals ("Doyle, Arthur Conan", $currentPage->title);
        $this->assertEquals (2, $currentPage->getMaxPage ());
        $this->assertCount (3, $currentPage->entryArray);
        $this->assertTrue ($currentPage->ContainsBook ());
        $this->assertTrue ($currentPage->IsPaginated ());
        $this->assertNull ($currentPage->getNextLink ());

        // No pagination
        $config['cops_max_item_per_page'] = -1;

        $currentPage = Page::getPage ($page, $qid, $query, $n);
        $currentPage->InitializeContent ();

        $this->assertEquals ("Doyle, Arthur Conan", $currentPage->title);
        $this->assertCount (8, $currentPage->entryArray);
        $this->assertTrue ($currentPage->ContainsBook ());
        $this->assertFalse ($currentPage->IsPaginated ());
    }

    public function testPageAllBooks ()
    {
        global $config;
        $page = Base::PAGE_ALL_BOOKS;
        $query = NULL;
        $qid = NULL;
        $n = "1";

        $config['cops_titles_split_first_letter'] = 0;

        $currentPage = Page::getPage ($page, $qid, $query, $n);
        $currentPage->InitializeContent ();

        $this->assertEquals ("All books", $currentPage->title);
        $this->assertCount (14, $currentPage->entryArray);
        $this->assertEquals ("The Adventures of Sherlock Holmes", $currentPage->entryArray [0]->title);
        $this->assertEquals ("Alice's Adventures in Wonderland", $currentPage->entryArray [1]->title);
        $this->assertTrue ($currentPage->ContainsBook ());

        $config['cops_titles_split_first_letter'] = 1;

        $currentPage = Page::getPage ($page, $qid, $query, $n);
        $currentPage->InitializeContent ();

        $this->assertEquals ("All books", $currentPage->title);
        $this->assertCount (9, $currentPage->entryArray);
        $this->assertEquals ("A", $currentPage->entryArray [0]->title);
        $this->assertEquals ("C", $currentPage->entryArray [1]->title);
        $this->assertFalse ($currentPage->ContainsBook ());
    }

    public function testPageAllBooksByLetter ()
    {
        $page = Base::PAGE_ALL_BOOKS_LETTER;
        $query = NULL;
        $qid = "C";
        $n = "1";

        $currentPage = Page::getPage ($page, $qid, $query, $n);
        $currentPage->InitializeContent ();

        $this->assertEquals ("2 books starting with C", $currentPage->title);
        $this->assertCount (2, $currentPage->entryArray);
        $this->assertEquals ("The Call of the Wild", $currentPage->entryArray [0]->title);
        $this->assertTrue ($currentPage->ContainsBook ());
    }

    public function testPageAllSeries ()
    {
        $page = Base::PAGE_ALL_SERIES;
        $query = NULL;
        $qid = NULL;
        $n = "1";

        $currentPage = Page::getPage ($page, $qid, $query, $n);
        $currentPage->InitializeContent ();

        $this->assertEquals ("Series", $currentPage->title);
        $this->assertCount (3, $currentPage->entryArray);
        $this->assertEquals ("D'Artagnan Romances", $currentPage->entryArray [0]->title);
        $this->assertFalse ($currentPage->ContainsBook ());
    }

    public function testPageSeriesDetail ()
    {
        $page = Base::PAGE_SERIE_DETAIL;
        $query = NULL;
        $qid = "1";
        $n = "1";
        $currentPage = Page::getPage ($page, $qid, $query, $n);
        $currentPage->InitializeContent ();

        $this->assertEquals ("Sherlock Holmes", $currentPage->title);
        $this->assertCount (7, $currentPage->entryArray);
        $this->assertEquals ("A Study in Scarlet", $currentPage->entryArray [0]->title);
        $this->assertTrue ($currentPage->ContainsBook ());
    }

    public function testPageAllPublishers ()
    {
        $page = Base::PAGE_ALL_PUBLISHERS;
        $query = NULL;
        $qid = NULL;
        $n = "1";

        $currentPage = Page::getPage ($page, $qid, $query, $n);
        $currentPage->InitializeContent ();

        $this->assertEquals ("Publishers", $currentPage->title);
        $this->assertCount (5, $currentPage->entryArray);
        $this->assertEquals ("D. Appleton and Company", $currentPage->entryArray [0]->title);
        $this->assertFalse ($currentPage->ContainsBook ());
    }

    public function testPagePublishersDetail ()
    {
        $page = Base::PAGE_PUBLISHER_DETAIL;
        $query = NULL;
        $qid = "6";
        $n = "1";

        $currentPage = Page::getPage ($page, $qid, $query, $n);
        $currentPage->InitializeContent ();

        $this->assertEquals ("Strand Magazine", $currentPage->title);
        $this->assertCount (8, $currentPage->entryArray);
        $this->assertEquals ("The Return of Sherlock Holmes", $currentPage->entryArray [0]->title);
        $this->assertTrue ($currentPage->ContainsBook ());
    }


    public function testPageAllTags ()
    {
        $page = Base::PAGE_ALL_TAGS;
        $query = NULL;
        $qid = NULL;
        $n = "1";

        $currentPage = Page::getPage ($page, $qid, $query, $n);
        $currentPage->InitializeContent ();

        $this->assertEquals ("Tags", $currentPage->title);
        $this->assertCount (10, $currentPage->entryArray);
        $this->assertEquals ("Action & Adventure", $currentPage->entryArray [0]->title);
        $this->assertFalse ($currentPage->ContainsBook ());
    }

    public function testPageTagDetail ()
    {
        $page = Base::PAGE_TAG_DETAIL;
        $query = NULL;
        $qid = "1";
        $n = "1";

        $currentPage = Page::getPage ($page, $qid, $query, $n);
        $currentPage->InitializeContent ();

        $this->assertEquals ("Fiction", $currentPage->title);
        $this->assertCount (14, $currentPage->entryArray);
        $this->assertEquals ("The Adventures of Sherlock Holmes", $currentPage->entryArray [0]->title);
        $this->assertTrue ($currentPage->ContainsBook ());
    }

    public function testPageAllLanguages ()
    {
        $page = Base::PAGE_ALL_LANGUAGES;
        $query = NULL;
        $qid = NULL;
        $n = "1";

        $currentPage = Page::getPage ($page, $qid, $query, $n);
        $currentPage->InitializeContent ();

        $this->assertEquals ("Languages", $currentPage->title);
        $this->assertCount (1, $currentPage->entryArray);
        $this->assertEquals ("English", $currentPage->entryArray [0]->title);
        $this->assertFalse ($currentPage->ContainsBook ());
    }

    public function testPageLanguageDetail ()
    {
        $page = Base::PAGE_LANGUAGE_DETAIL;
        $query = NULL;
        $qid = "1";
        $n = "1";

        $currentPage = Page::getPage ($page, $qid, $query, $n);
        $currentPage->InitializeContent ();

        $this->assertEquals ("English", $currentPage->title);
        $this->assertCount (14, $currentPage->entryArray);
        $this->assertEquals ("The Adventures of Sherlock Holmes", $currentPage->entryArray [0]->title);
        $this->assertTrue ($currentPage->ContainsBook ());
    }

    public function testPageRecent ()
    {
        $page = Base::PAGE_ALL_RECENT_BOOKS;
        $query = NULL;
        $qid = NULL;
        $n = "1";

        $currentPage = Page::getPage ($page, $qid, $query, $n);
        $currentPage->InitializeContent ();

        $this->assertEquals ("Recent additions", $currentPage->title);
        $this->assertCount (14, $currentPage->entryArray);
        $this->assertEquals ("Alice's Adventures in Wonderland", $currentPage->entryArray [0]->title);
        $this->assertTrue ($currentPage->ContainsBook ());

        // Test facets

        $_GET["tag"] = "Historical";
        $currentPage = Page::getPage ($page, $qid, $query, $n);
        $currentPage->InitializeContent ();

        $this->assertEquals ("Recent additions", $currentPage->title);
        $this->assertCount (2, $currentPage->entryArray);
        $this->assertEquals ("Twenty Years After", $currentPage->entryArray [0]->title);
        $this->assertTrue ($currentPage->ContainsBook ());

        $_GET["tag"] = "!Romance";
        $currentPage = Page::getPage ($page, $qid, $query, $n);
        $currentPage->InitializeContent ();

        $this->assertEquals ("Recent additions", $currentPage->title);
        $this->assertCount (12, $currentPage->entryArray);
        $this->assertEquals ("Alice's Adventures in Wonderland", $currentPage->entryArray [0]->title);
        $this->assertTrue ($currentPage->ContainsBook ());

        $_GET["tag"] = NULL;
    }

    public function testPageBookDetail ()
    {
        $page = Base::PAGE_BOOK_DETAIL;
        $query = NULL;
        $qid = "2";
        $n = "1";

        $currentPage = Page::getPage ($page, $qid, $query, $n);
        $currentPage->InitializeContent ();

        $this->assertEquals ("The Return of Sherlock Holmes", $currentPage->title);
        $this->assertCount (0, $currentPage->entryArray);
        $this->assertFalse ($currentPage->ContainsBook ());
    }

    public function testPageSearch ()
    {
        global $config;
        $page = Base::PAGE_OPENSEARCH_QUERY;
        $query = "alice";
        $qid = NULL;
        $n = "1";

        // Only books returned
        $currentPage = Page::getPage ($page, $qid, $query, $n);
        $currentPage->InitializeContent ();

        $this->assertEquals ("Search result for *alice*", $currentPage->title);
        $this->assertCount (1, $currentPage->entryArray);
        $this->assertEquals ("Search result for *alice* in books", $currentPage->entryArray [0]->title);
        $this->assertEquals ("2 books", $currentPage->entryArray [0]->content);
        $this->assertFalse ($currentPage->ContainsBook ());

        // Match Lewis Caroll & Scarlet
        $query = "car";

        $config ['cops_ignored_categories'] = array ("author");
        $currentPage = Page::getPage ($page, $qid, $query, $n);
        $currentPage->InitializeContent ();

        $this->assertEquals ("Search result for *car*", $currentPage->title);
        $this->assertCount (1, $currentPage->entryArray);
        $this->assertEquals ("Search result for *car* in books", $currentPage->entryArray [0]->title);
        $this->assertEquals ("1 book", $currentPage->entryArray [0]->content);
        $this->assertFalse ($currentPage->ContainsBook ());

        $config ['cops_ignored_categories'] = array ();
        $currentPage = Page::getPage ($page, $qid, $query, $n);
        $currentPage->InitializeContent ();

        $this->assertEquals ("Search result for *car*", $currentPage->title);
        $this->assertCount (2, $currentPage->entryArray);
        $this->assertEquals ("Search result for *car* in books", $currentPage->entryArray [0]->title);
        $this->assertEquals ("1 book", $currentPage->entryArray [0]->content);
        $this->assertEquals ("Search result for *car* in authors", $currentPage->entryArray [1]->title);
        $this->assertEquals ("1 author", $currentPage->entryArray [1]->content);
        $this->assertFalse ($currentPage->ContainsBook ());
    }

    public function testPageSearchScopeAuthors ()
    {
        $page = Base::PAGE_OPENSEARCH_QUERY;
        $qid = NULL;
        $n = "1";
        $_GET ["scope"] = "author";

        // Match Lewis Carroll
        $query = "car";
        $currentPage = Page::getPage ($page, $qid, $query, $n);
        $currentPage->InitializeContent ();

        $this->assertEquals ("Search result for *car* in authors", $currentPage->title);
        $this->assertCount (1, $currentPage->entryArray);
        $this->assertEquals ("Carroll, Lewis", $currentPage->entryArray [0]->title);
        $this->assertFalse ($currentPage->ContainsBook ());

        $_GET ["scope"] = NULL;
    }

    public function testPageSearchScopeSeries ()
    {
        $page = Base::PAGE_OPENSEARCH_QUERY;
        $qid = NULL;
        $n = "1";
        $_GET ["scope"] = "series";

        // Match Holmes
        $query = "hol";
        $currentPage = Page::getPage ($page, $qid, $query, $n);
        $currentPage->InitializeContent ();

        $this->assertEquals ("Search result for *hol* in series", $currentPage->title);
        $this->assertCount (1, $currentPage->entryArray);
        $this->assertEquals ("Sherlock Holmes", $currentPage->entryArray [0]->title);
        $this->assertFalse ($currentPage->ContainsBook ());

        $_GET ["scope"] = NULL;
    }

    public function testPageSearchScopeBooks ()
    {
        $page = Base::PAGE_OPENSEARCH_QUERY;
        $qid = NULL;
        $n = "1";
        $_GET ["scope"] = "book";

        // Match Holmes
        $query = "hol";
        $currentPage = Page::getPage ($page, $qid, $query, $n);
        $currentPage->InitializeContent ();

        $this->assertEquals ("Search result for *hol* in books", $currentPage->title);
        $this->assertCount (4, $currentPage->entryArray);
        $this->assertTrue ($currentPage->ContainsBook ());

        $_GET ["scope"] = NULL;
    }

    public function testPageSearchScopePublishers ()
    {
        $page = Base::PAGE_OPENSEARCH_QUERY;
        $qid = NULL;
        $n = "1";
        $_GET ["scope"] = "publisher";

        // Match Holmes
        $query = "millan";
        $currentPage = Page::getPage ($page, $qid, $query, $n);
        $currentPage->InitializeContent ();

        $this->assertEquals ("Search result for *millan* in publishers", $currentPage->title);
        $this->assertCount (2, $currentPage->entryArray);
        $this->assertEquals ("Macmillan and Co. London", $currentPage->entryArray [0]->title);
        $this->assertFalse ($currentPage->ContainsBook ());

        $_GET ["scope"] = NULL;
    }

    public function testPageSearchScopeTags ()
    {
        $page = Base::PAGE_OPENSEARCH_QUERY;
        $qid = NULL;
        $n = "1";
        $_GET ["scope"] = "tag";

        // Match Holmes
        $query = "fic";
        $currentPage = Page::getPage ($page, $qid, $query, $n);
        $currentPage->InitializeContent ();

        $this->assertEquals ("Search result for *fic* in tags", $currentPage->title);
        $this->assertCount (2, $currentPage->entryArray);
        $this->assertFalse ($currentPage->ContainsBook ());

        $_GET ["scope"] = NULL;
    }
}