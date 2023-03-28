<?php
/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

require_once(dirname(__FILE__) . "/config_test.php");
use PHPUnit\Framework\TestCase;

class PageTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        global $config;
        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithSomeBooks/";
        Base::clearDb();
    }

    public function testPageIndex()
    {
        global $config;
        $page = Base::PAGE_INDEX;
        $query = null;
        $qid = null;
        $n = "1";

        $currentPage = Page::getPage($page, $qid, $query, $n);
        $currentPage->InitializeContent();

        $this->assertEquals($config['cops_title_default'], $currentPage->title);
        $this->assertCount(8, $currentPage->entryArray);
        $this->assertEquals("Authors", $currentPage->entryArray [0]->title);
        $this->assertEquals("Alphabetical index of the 6 authors", $currentPage->entryArray [0]->content);
        $this->assertEquals(6, $currentPage->entryArray [0]->numberOfElement);
        $this->assertEquals("Series", $currentPage->entryArray [1]->title);
        $this->assertEquals("Alphabetical index of the 4 series", $currentPage->entryArray [1]->content);
        $this->assertEquals(4, $currentPage->entryArray [1]->numberOfElement);
        $this->assertEquals("Publishers", $currentPage->entryArray [2]->title);
        $this->assertEquals("Alphabetical index of the 6 publishers", $currentPage->entryArray [2]->content);
        $this->assertEquals(6, $currentPage->entryArray [2]->numberOfElement);
        $this->assertEquals("Tags", $currentPage->entryArray [3]->title);
        $this->assertEquals("Alphabetical index of the 11 tags", $currentPage->entryArray [3]->content);
        $this->assertEquals(11, $currentPage->entryArray [3]->numberOfElement);
        $this->assertEquals("Ratings", $currentPage->entryArray [4]->title);
        $this->assertEquals("3 ratings", $currentPage->entryArray [4]->content);
        $this->assertEquals(3, $currentPage->entryArray [4]->numberOfElement);
        $this->assertEquals("Languages", $currentPage->entryArray [5]->title);
        $this->assertEquals("Alphabetical index of the 2 languages", $currentPage->entryArray [5]->content);
        $this->assertEquals(2, $currentPage->entryArray [5]->numberOfElement);
        $this->assertEquals("All books", $currentPage->entryArray [6]->title);
        $this->assertEquals("Alphabetical index of the 15 books", $currentPage->entryArray [6]->content);
        $this->assertEquals(15, $currentPage->entryArray [6]->numberOfElement);
        $this->assertEquals("Recent additions", $currentPage->entryArray [7]->title);
        $this->assertEquals("50 most recent books", $currentPage->entryArray [7]->content);
        $this->assertEquals(50, $currentPage->entryArray [7]->numberOfElement);
        $this->assertFalse($currentPage->ContainsBook());
    }

    public function testPageIndexWithIgnored()
    {
        global $config;
        $page = Base::PAGE_INDEX;
        $query = null;
        $qid = null;
        $n = "1";

        $config ['cops_ignored_categories'] = ["author", "series", "tag", "publisher", "language"];

        $currentPage = Page::getPage($page, $qid, $query, $n);
        $currentPage->InitializeContent();

        $this->assertEquals($config['cops_title_default'], $currentPage->title);
        $this->assertCount(3, $currentPage->entryArray);
        $this->assertEquals("Ratings", $currentPage->entryArray [0]->title);
        $this->assertEquals("All books", $currentPage->entryArray [1]->title);
        $this->assertEquals("Alphabetical index of the 15 books", $currentPage->entryArray [1]->content);
        $this->assertEquals("Recent additions", $currentPage->entryArray [2]->title);
        $this->assertEquals("50 most recent books", $currentPage->entryArray [2]->content);
        $this->assertFalse($currentPage->ContainsBook());

        $config ['cops_ignored_categories'] = [];
    }

    public function testPageIndexWithCustomColumn_Type1()
    {
        global $config;
        $page = Base::PAGE_INDEX;
        $query = null;
        $qid = null;
        $n = "1";

        $config['cops_calibre_custom_column'] = ["type1"];

        $currentPage = Page::getPage($page, $qid, $query, $n);
        $currentPage->InitializeContent();

        $this->assertCount(9, $currentPage->entryArray);
        $this->assertEquals("Type1", $currentPage->entryArray [6]->title);
        $this->assertEquals("Custom column 'Type1'", $currentPage->entryArray [6]->content);
        $this->assertEquals(2, $currentPage->entryArray [6]->numberOfElement);

        $config['cops_calibre_custom_column'] = [];
    }

    public function testPageIndexWithCustomColumn_Type2()
    {
        global $config;
        $page = Base::PAGE_INDEX;
        $query = null;
        $qid = null;
        $n = "1";

        $config['cops_calibre_custom_column'] = ["type2"];

        $currentPage = Page::getPage($page, $qid, $query, $n);
        $currentPage->InitializeContent();

        $this->assertCount(9, $currentPage->entryArray);
        $this->assertEquals("Type2", $currentPage->entryArray [6]->title);
        $this->assertEquals("Custom column 'Type2'", $currentPage->entryArray [6]->content);
        $this->assertEquals(3, $currentPage->entryArray [6]->numberOfElement);

        $config['cops_calibre_custom_column'] = [];
    }

    public function testPageIndexWithCustomColumn_Type4()
    {
        global $config;
        $page = Base::PAGE_INDEX;
        $query = null;
        $qid = null;
        $n = "1";

        $config['cops_calibre_custom_column'] = ["type4"];

        $currentPage = Page::getPage($page, $qid, $query, $n);
        $currentPage->InitializeContent();

        $this->assertCount(9, $currentPage->entryArray);
        $this->assertEquals("Type4", $currentPage->entryArray [6]->title);
        $this->assertEquals("Alphabetical index of the 2 series", $currentPage->entryArray [6]->content);
        $this->assertEquals(2, $currentPage->entryArray [6]->numberOfElement);

        $config['cops_calibre_custom_column'] = [];
    }

    public function testPageIndexWithCustomColumn_ManyTypes()
    {
        global $config;
        $page = Base::PAGE_INDEX;
        $query = null;
        $qid = null;
        $n = "1";

        $config['cops_calibre_custom_column'] = ["type1", "type2", "type4"];

        $currentPage = Page::getPage($page, $qid, $query, $n);
        $currentPage->InitializeContent();

        $this->assertCount(11, $currentPage->entryArray);

        $config['cops_calibre_custom_column'] = [];
    }

    public function testPageAllCustom_Type4()
    {
        $page = Base::PAGE_ALL_CUSTOMS;
        $query = null;
        $qid = null;
        $n = "1";

        setURLParam('custom', 1);

        $currentPage = Page::getPage($page, $qid, $query, $n);
        $currentPage->InitializeContent();

        $this->assertEquals("Type4", $currentPage->title);
        $this->assertCount(2, $currentPage->entryArray);
        $this->assertEquals("SeriesLike", $currentPage->entryArray [0]->title);
        $this->assertEquals(2, $currentPage->entryArray [0]->numberOfElement);
        $this->assertFalse($currentPage->ContainsBook());

        setURLParam('custom', null);
    }

    public function testPageAllCustom_Type2()
    {
        $page = Base::PAGE_ALL_CUSTOMS;
        $query = null;
        $qid = null;
        $n = "1";

        setURLParam('custom', 2);

        $currentPage = Page::getPage($page, $qid, $query, $n);
        $currentPage->InitializeContent();

        $this->assertEquals("Type2", $currentPage->title);
        $this->assertCount(3, $currentPage->entryArray);
        $this->assertEquals("tag1", $currentPage->entryArray [0]->title);
        $this->assertEquals(2, $currentPage->entryArray [0]->numberOfElement);
        $this->assertFalse($currentPage->ContainsBook());

        setURLParam('custom', null);
    }

    public function testPageAllCustom_Type1()
    {
        $page = Base::PAGE_ALL_CUSTOMS;
        $query = null;
        $qid = null;
        $n = "1";

        setURLParam('custom', 3);

        $currentPage = Page::getPage($page, $qid, $query, $n);
        $currentPage->InitializeContent();

        $this->assertEquals("Type1", $currentPage->title);
        $this->assertCount(2, $currentPage->entryArray);
        $this->assertEquals("other", $currentPage->entryArray [0]->title);
        $this->assertEquals(1, $currentPage->entryArray [0]->numberOfElement);
        $this->assertFalse($currentPage->ContainsBook());

        setURLParam('custom', null);
    }

    public function testPageCustomDetail_Type4()
    {
        $page = Base::PAGE_CUSTOM_DETAIL;
        $query = null;
        $qid = "1";
        $n = "1";

        setURLParam('custom', 1);

        $currentPage = Page::getPage($page, $qid, $query, $n);
        $currentPage->InitializeContent();

        $this->assertEquals("SeriesLike", $currentPage->title);
        $this->assertCount(2, $currentPage->entryArray);
        $this->assertEquals("Alice's Adventures in Wonderland", $currentPage->entryArray [0]->title);
        $this->assertTrue($currentPage->ContainsBook());

        setURLParam('custom', null);
    }

    public function testPageCustomDetail_Type2()
    {
        $page = Base::PAGE_CUSTOM_DETAIL;
        $query = null;
        $qid = "1";
        $n = "1";

        setURLParam('custom', 2);

        $currentPage = Page::getPage($page, $qid, $query, $n);
        $currentPage->InitializeContent();

        $this->assertEquals("tag1", $currentPage->title);
        $this->assertCount(2, $currentPage->entryArray);
        $this->assertEquals("Alice's Adventures in Wonderland", $currentPage->entryArray [0]->title);
        $this->assertTrue($currentPage->ContainsBook());

        setURLParam('custom', null);
    }

    public function testPageCustomDetail_Type1()
    {
        $page = Base::PAGE_CUSTOM_DETAIL;
        $query = null;
        $qid = "1";
        $n = "1";

        setURLParam('custom', 3);
        $qid = "2";

        $currentPage = Page::getPage($page, $qid, $query, $n);
        $currentPage->InitializeContent();

        $this->assertEquals("other", $currentPage->title);
        $this->assertCount(1, $currentPage->entryArray);
        $this->assertEquals("A Study in Scarlet", $currentPage->entryArray [0]->title);
        $this->assertTrue($currentPage->ContainsBook());

        setURLParam('custom', null);
    }

    public function testPageAllAuthors_WithFullName()
    {
        global $config;
        $page = Base::PAGE_ALL_AUTHORS;
        $query = null;
        $qid = null;
        $n = "1";

        $config['cops_author_split_first_letter'] = "0";

        $currentPage = Page::getPage($page, $qid, $query, $n);
        $currentPage->InitializeContent();

        $this->assertEquals("Authors", $currentPage->title);
        $this->assertCount(6, $currentPage->entryArray);
        $this->assertEquals("Carroll, Lewis", $currentPage->entryArray [0]->title);
        $this->assertFalse($currentPage->ContainsBook());

        $config['cops_author_split_first_letter'] = "1";
    }

    public function testPageAllAuthors_SplittedByFirstLetter()
    {
        global $config;
        $page = Base::PAGE_ALL_AUTHORS;
        $query = null;
        $qid = null;
        $n = "1";

        $currentPage = Page::getPage($page, $qid, $query, $n);
        $currentPage->InitializeContent();

        $this->assertEquals("Authors", $currentPage->title);
        $this->assertCount(5, $currentPage->entryArray);
        $this->assertEquals("C", $currentPage->entryArray [0]->title);
        $this->assertEquals(1, $currentPage->entryArray [0]->numberOfElement);
        $this->assertFalse($currentPage->ContainsBook());
    }

    public function testPageAuthorsFirstLetter()
    {
        $page = Base::PAGE_AUTHORS_FIRST_LETTER;
        $query = null;
        $qid = "C";
        $n = "1";

        // Author Lewis Carroll
        $currentPage = Page::getPage($page, $qid, $query, $n);
        $currentPage->InitializeContent();

        $this->assertEquals("1 author starting with C", $currentPage->title);
        $this->assertCount(1, $currentPage->entryArray);
        $this->assertFalse($currentPage->ContainsBook());
    }

    public function testPageAuthorsDetail_FirstPage()
    {
        global $config;
        $page = Base::PAGE_AUTHOR_DETAIL;
        $query = null;
        $qid = "1";
        $n = "1";
        $_SERVER['QUERY_STRING'] = "page=" . Base::PAGE_AUTHOR_DETAIL . "&id=1&n=1";

        $config['cops_max_item_per_page'] = 2;

        // First page

        $currentPage = Page::getPage($page, $qid, $query, $n);
        $currentPage->InitializeContent();

        $this->assertEquals("Arthur Conan Doyle", $currentPage->title);
        $this->assertEquals(4, $currentPage->getMaxPage());
        $this->assertCount(2, $currentPage->entryArray);
        $this->assertTrue($currentPage->ContainsBook());
        $this->assertTrue($currentPage->IsPaginated());
        $this->assertNull($currentPage->getPrevLink());

        unset($_SERVER['QUERY_STRING']);
        $config['cops_max_item_per_page'] = -1;
    }

    public function testPageAuthorsDetail_LastPage()
    {
        global $config;
        $page = Base::PAGE_AUTHOR_DETAIL;
        $query = null;
        $qid = "1";
        $n = "1";
        $_SERVER['QUERY_STRING'] = "page=" . Base::PAGE_AUTHOR_DETAIL . "&id=1&n=1";

        // Last page
        $config['cops_max_item_per_page'] = 5;
        $n = "2";

        $currentPage = Page::getPage($page, $qid, $query, $n);
        $currentPage->InitializeContent();

        $this->assertEquals("Arthur Conan Doyle", $currentPage->title);
        $this->assertEquals(2, $currentPage->getMaxPage());
        $this->assertCount(3, $currentPage->entryArray);
        $this->assertTrue($currentPage->ContainsBook());
        $this->assertTrue($currentPage->IsPaginated());
        $this->assertNull($currentPage->getNextLink());

        unset($_SERVER['QUERY_STRING']);
        // No pagination
        $config['cops_max_item_per_page'] = -1;
    }

    public function testPageAuthorsDetail_NoPagination()
    {
        global $config;
        $page = Base::PAGE_AUTHOR_DETAIL;
        $query = null;
        $qid = "1";
        $n = "1";
        $_SERVER['QUERY_STRING'] = "page=" . Base::PAGE_AUTHOR_DETAIL . "&id=1&n=1";

        // No pagination
        $config['cops_max_item_per_page'] = -1;

        $currentPage = Page::getPage($page, $qid, $query, $n);
        $currentPage->InitializeContent();

        $this->assertEquals("Arthur Conan Doyle", $currentPage->title);
        $this->assertCount(8, $currentPage->entryArray);
        $this->assertTrue($currentPage->ContainsBook());
        $this->assertFalse($currentPage->IsPaginated());

        unset($_SERVER['QUERY_STRING']);
    }

    public function testPageAllBooks_WithFullName()
    {
        global $config;
        $page = Base::PAGE_ALL_BOOKS;
        $query = null;
        $qid = null;
        $n = "1";

        $config['cops_titles_split_first_letter'] = 0;

        $currentPage = Page::getPage($page, $qid, $query, $n);
        $currentPage->InitializeContent();

        $this->assertEquals("All books", $currentPage->title);
        $this->assertCount(15, $currentPage->entryArray);
        $this->assertEquals("The Adventures of Sherlock Holmes", $currentPage->entryArray [0]->title);
        $this->assertEquals("Alice's Adventures in Wonderland", $currentPage->entryArray [1]->title);
        $this->assertTrue($currentPage->ContainsBook());

        $config['cops_titles_split_first_letter'] = 1;
    }

    public function testPageAllBooks_SplittedByFirstLetter()
    {
        global $config;
        $page = Base::PAGE_ALL_BOOKS;
        $query = null;
        $qid = null;
        $n = "1";

        $currentPage = Page::getPage($page, $qid, $query, $n);
        $currentPage->InitializeContent();

        $this->assertEquals("All books", $currentPage->title);
        $this->assertCount(9, $currentPage->entryArray);
        $this->assertEquals("A", $currentPage->entryArray [0]->title);
        $this->assertEquals("C", $currentPage->entryArray [1]->title);
        $this->assertFalse($currentPage->ContainsBook());
    }

    public function testPageAllBooksByLetter()
    {
        $page = Base::PAGE_ALL_BOOKS_LETTER;
        $query = null;
        $qid = "C";
        $n = "1";

        $currentPage = Page::getPage($page, $qid, $query, $n);
        $currentPage->InitializeContent();

        $this->assertEquals("3 books starting with C", $currentPage->title);
        $this->assertCount(3, $currentPage->entryArray);
        $this->assertEquals("The Call of the Wild", $currentPage->entryArray [0]->title);
        $this->assertTrue($currentPage->ContainsBook());
    }

    public function testPageAllSeries()
    {
        $page = Base::PAGE_ALL_SERIES;
        $query = null;
        $qid = null;
        $n = "1";

        $currentPage = Page::getPage($page, $qid, $query, $n);
        $currentPage->InitializeContent();

        $this->assertEquals("Series", $currentPage->title);
        $this->assertCount(4, $currentPage->entryArray);
        $this->assertEquals("D'Artagnan Romances", $currentPage->entryArray [0]->title);
        $this->assertFalse($currentPage->ContainsBook());
    }

    public function testPageSeriesDetail()
    {
        $page = Base::PAGE_SERIE_DETAIL;
        $query = null;
        $qid = "1";
        $n = "1";
        $currentPage = Page::getPage($page, $qid, $query, $n);
        $currentPage->InitializeContent();

        $this->assertEquals("Sherlock Holmes", $currentPage->title);
        $this->assertCount(7, $currentPage->entryArray);
        $this->assertEquals("A Study in Scarlet", $currentPage->entryArray [0]->title);
        $this->assertTrue($currentPage->ContainsBook());
    }

    public function testPageAllPublishers()
    {
        $page = Base::PAGE_ALL_PUBLISHERS;
        $query = null;
        $qid = null;
        $n = "1";

        $currentPage = Page::getPage($page, $qid, $query, $n);
        $currentPage->InitializeContent();

        $this->assertEquals("Publishers", $currentPage->title);
        $this->assertCount(6, $currentPage->entryArray);
        $this->assertEquals("D. Appleton and Company", $currentPage->entryArray [0]->title);
        $this->assertFalse($currentPage->ContainsBook());
    }

    public function testPagePublishersDetail()
    {
        $page = Base::PAGE_PUBLISHER_DETAIL;
        $query = null;
        $qid = "6";
        $n = "1";

        $currentPage = Page::getPage($page, $qid, $query, $n);
        $currentPage->InitializeContent();

        $this->assertEquals("Strand Magazine", $currentPage->title);
        $this->assertCount(8, $currentPage->entryArray);
        $this->assertEquals("The Return of Sherlock Holmes", $currentPage->entryArray [0]->title);
        $this->assertTrue($currentPage->ContainsBook());
    }

    public function testPageAllTags()
    {
        $page = Base::PAGE_ALL_TAGS;
        $query = null;
        $qid = null;
        $n = "1";

        $currentPage = Page::getPage($page, $qid, $query, $n);
        $currentPage->InitializeContent();

        $this->assertEquals("Tags", $currentPage->title);
        $this->assertCount(11, $currentPage->entryArray);
        $this->assertEquals("Action & Adventure", $currentPage->entryArray [0]->title);
        $this->assertFalse($currentPage->ContainsBook());
    }

    public function testPageTagDetail()
    {
        $page = Base::PAGE_TAG_DETAIL;
        $query = null;
        $qid = "1";
        $n = "1";

        $currentPage = Page::getPage($page, $qid, $query, $n);
        $currentPage->InitializeContent();

        $this->assertEquals("Fiction", $currentPage->title);
        $this->assertCount(14, $currentPage->entryArray);
        $this->assertEquals("The Adventures of Sherlock Holmes", $currentPage->entryArray [0]->title);
        $this->assertTrue($currentPage->ContainsBook());
    }

    public function testPageAllLanguages()
    {
        $page = Base::PAGE_ALL_LANGUAGES;
        $query = null;
        $qid = null;
        $n = "1";

        $currentPage = Page::getPage($page, $qid, $query, $n);
        $currentPage->InitializeContent();

        $this->assertEquals("Languages", $currentPage->title);
        $this->assertCount(2, $currentPage->entryArray);
        $this->assertEquals("English", $currentPage->entryArray [0]->title);
        $this->assertEquals("French", $currentPage->entryArray [1]->title);
        $this->assertFalse($currentPage->ContainsBook());
    }

    public function testPageLanguageDetail()
    {
        $page = Base::PAGE_LANGUAGE_DETAIL;
        $query = null;
        $qid = "1";
        $n = "1";

        $currentPage = Page::getPage($page, $qid, $query, $n);
        $currentPage->InitializeContent();

        $this->assertEquals("English", $currentPage->title);
        $this->assertCount(14, $currentPage->entryArray);
        $this->assertEquals("The Adventures of Sherlock Holmes", $currentPage->entryArray [0]->title);
        $this->assertTrue($currentPage->ContainsBook());
    }

    public function testPageAllRatings()
    {
        $page = Base::PAGE_ALL_RATINGS;
        $query = null;
        $qid = null;
        $n = "1";

        $currentPage = Page::getPage($page, $qid, $query, $n);
        $currentPage->InitializeContent();

        $this->assertEquals("Ratings", $currentPage->title);
        $this->assertCount(3, $currentPage->entryArray);
        $this->assertEquals("2 stars", $currentPage->entryArray [0]->title);
        $this->assertFalse($currentPage->ContainsBook());
    }

    public function testPageRatingDetail()
    {
        $page = Base::PAGE_RATING_DETAIL;
        $query = null;
        $qid = "1";
        $n = "1";

        $currentPage = Page::getPage($page, $qid, $query, $n);
        $currentPage->InitializeContent();

        $this->assertEquals("5 stars", $currentPage->title);
        $this->assertCount(4, $currentPage->entryArray);
        $this->assertEquals("The Adventures of Sherlock Holmes", $currentPage->entryArray [0]->title);
        $this->assertTrue($currentPage->ContainsBook());
    }

    public function testPageRecent()
    {
        $page = Base::PAGE_ALL_RECENT_BOOKS;
        $query = null;
        $qid = null;
        $n = "1";

        $currentPage = Page::getPage($page, $qid, $query, $n);
        $currentPage->InitializeContent();

        $this->assertEquals("Recent additions", $currentPage->title);
        $this->assertCount(15, $currentPage->entryArray);
        $this->assertEquals("La curée", $currentPage->entryArray [0]->title);
        $this->assertTrue($currentPage->ContainsBook());
    }

    public function testPageRecent_WithFacets_IncludedTag()
    {
        $page = Base::PAGE_ALL_RECENT_BOOKS;
        $query = null;
        $qid = null;
        $n = "1";

        setURLParam('tag', "Historical");
        $currentPage = Page::getPage($page, $qid, $query, $n);
        $currentPage->InitializeContent();

        $this->assertEquals("Recent additions", $currentPage->title);
        $this->assertCount(2, $currentPage->entryArray);
        $this->assertEquals("Twenty Years After", $currentPage->entryArray [0]->title);
        $this->assertTrue($currentPage->ContainsBook());

        setURLParam('tag', null);
    }

    public function testPageRecent_WithFacets_ExcludedTag()
    {
        $page = Base::PAGE_ALL_RECENT_BOOKS;
        $query = null;
        $qid = null;
        $n = "1";

        setURLParam('tag', "!Romance");
        $currentPage = Page::getPage($page, $qid, $query, $n);
        $currentPage->InitializeContent();

        $this->assertEquals("Recent additions", $currentPage->title);
        $this->assertCount(13, $currentPage->entryArray);
        $this->assertEquals("La curée", $currentPage->entryArray [0]->title);
        $this->assertTrue($currentPage->ContainsBook());

        setURLParam('tag', null);
    }

    public function testPageBookDetail()
    {
        $page = Base::PAGE_BOOK_DETAIL;
        $query = null;
        $qid = "2";
        $n = "1";

        $currentPage = Page::getPage($page, $qid, $query, $n);
        $currentPage->InitializeContent();

        $this->assertEquals("The Return of Sherlock Holmes", $currentPage->title);
        $this->assertCount(0, $currentPage->entryArray);
        $this->assertFalse($currentPage->ContainsBook());
    }

    public function testPageSearch_WithOnlyBooksReturned()
    {
        global $config;
        $page = Base::PAGE_OPENSEARCH_QUERY;
        $query = "alice";
        $qid = null;
        $n = "1";

        // Only books returned
        $currentPage = Page::getPage($page, $qid, $query, $n);
        $currentPage->InitializeContent();

        $this->assertEquals("Search result for *alice*", $currentPage->title);
        $this->assertCount(1, $currentPage->entryArray);
        $this->assertEquals("Search result for *alice* in books", $currentPage->entryArray [0]->title);
        $this->assertEquals("2 books", $currentPage->entryArray [0]->content);
        $this->assertFalse($currentPage->ContainsBook());
    }

    public function testPageSearch_WithAuthorsIgnored()
    {
        global $config;
        $page = Base::PAGE_OPENSEARCH_QUERY;
        // Match Lewis Caroll & Scarlet
        $query = "car";
        $qid = null;
        $n = "1";

        $config ['cops_ignored_categories'] = ["author"];
        $currentPage = Page::getPage($page, $qid, $query, $n);
        $currentPage->InitializeContent();

        $this->assertEquals("Search result for *car*", $currentPage->title);
        $this->assertCount(1, $currentPage->entryArray);
        $this->assertEquals("Search result for *car* in books", $currentPage->entryArray [0]->title);
        $this->assertEquals("1 book", $currentPage->entryArray [0]->content);
        $this->assertFalse($currentPage->ContainsBook());

        $config ['cops_ignored_categories'] = [];
    }

    public function testPageSearch_WithTwoCategories()
    {
        global $config;
        $page = Base::PAGE_OPENSEARCH_QUERY;
        // Match Lewis Caroll & Scarlet
        $query = "car";
        $qid = null;
        $n = "1";

        $currentPage = Page::getPage($page, $qid, $query, $n);
        $currentPage->InitializeContent();

        $this->assertEquals("Search result for *car*", $currentPage->title);
        $this->assertCount(2, $currentPage->entryArray);
        $this->assertEquals("Search result for *car* in books", $currentPage->entryArray [0]->title);
        $this->assertEquals("1 book", $currentPage->entryArray [0]->content);
        $this->assertEquals("Search result for *car* in authors", $currentPage->entryArray [1]->title);
        $this->assertEquals("1 author", $currentPage->entryArray [1]->content);
        $this->assertFalse($currentPage->ContainsBook());
    }

    /**
     * @dataProvider providerAccentuatedCharacters
     */
    public function testPageSearch_WithAccentuatedCharacters($query, $count, $content)
    {
        global $config;
        $page = Base::PAGE_OPENSEARCH_QUERY;
        $qid = null;
        $n = "1";

        $currentPage = Page::getPage($page, $qid, $query, $n);
        $currentPage->InitializeContent();

        $this->assertEquals("Search result for *$query*", $currentPage->title);
        $this->assertCount($count, $currentPage->entryArray);
        if ($count > 0) {
            $this->assertEquals($content, $currentPage->entryArray [0]->content);
        }
        $this->assertFalse($currentPage->ContainsBook());
    }

    public function providerAccentuatedCharacters()
    {
        return [
            ["curée", 1, "1 book"],
            ["Émile zola", 1, "1 author"],
            ["émile zola", 0, null], // With standard search upper does not work with diacritics
            ["Littérature", 1, "1 tag"],
            ["Eugène Fasquelle", 1, "1 publisher"],
        ];
    }

    /**
     * @dataProvider providerNormalizedSearch
     */
    public function testPageSearch_WithNormalizedSearch_Book($query, $count, $content)
    {
        global $config;
        $page = Base::PAGE_OPENSEARCH_QUERY;
        $qid = null;
        $n = "1";
        $config ['cops_normalized_search'] = "1";
        Base::clearDb();
        if (!useNormAndUp()) {
            $this->markTestIncomplete();
        }

        $currentPage = Page::getPage($page, $qid, $query, $n);
        $currentPage->InitializeContent();

        $this->assertEquals("Search result for *$query*", $currentPage->title);
        $this->assertCount($count, $currentPage->entryArray);
        if ($count > 0) {
            $this->assertEquals($content, $currentPage->entryArray [0]->content);
        }
        $this->assertFalse($currentPage->ContainsBook());

        $config ['cops_normalized_search'] = "0";
        Base::clearDb();
    }

    public function providerNormalizedSearch()
    {
        return [
            ["curee", 1, "1 book"],
            ["emile zola", 1, "1 author"],
            ["émile zola", 1, "1 author"],
            ["Litterature", 1, "1 tag"],
            ["Litterâture", 1, "1 tag"],
            ["Serie des Rougon", 1, "1 series"],
            ["Eugene Fasquelle", 1, "1 publisher"],
        ];
    }

    public function testAuthorSearch_ByName()
    {
        global $config;
        $page = Base::PAGE_OPENSEARCH_QUERY;
        $query = "Lewis Carroll";
        setURLParam('scope', "author");
        $qid = null;
        $n = "1";

        $currentPage = Page::getPage($page, $qid, $query, $n);
        $currentPage->InitializeContent();

        $this->assertEquals("Search result for *Lewis Carroll* in authors", $currentPage->title);
        $this->assertCount(1, $currentPage->entryArray);
        $this->assertEquals("Carroll, Lewis", $currentPage->entryArray [0]->title);
        $this->assertFalse($currentPage->ContainsBook());

        setURLParam('scope', null);
    }

    public function testAuthorSearch_BySort()
    {
        global $config;
        $page = Base::PAGE_OPENSEARCH_QUERY;
        $query = "Carroll, Lewis";
        setURLParam('scope', "author");
        $qid = null;
        $n = "1";

        $currentPage = Page::getPage($page, $qid, $query, $n);
        $currentPage->InitializeContent();

        $this->assertEquals("Search result for *Carroll, Lewis* in authors", $currentPage->title);
        $this->assertCount(1, $currentPage->entryArray);
        $this->assertEquals("Carroll, Lewis", $currentPage->entryArray [0]->title);
        $this->assertFalse($currentPage->ContainsBook());

        setURLParam('scope', null);
    }

    public function testPageSearchScopeAuthors()
    {
        $page = Base::PAGE_OPENSEARCH_QUERY;
        $qid = null;
        $n = "1";
        setURLParam('scope', "author");

        // Match Lewis Carroll
        $query = "car";
        $currentPage = Page::getPage($page, $qid, $query, $n);
        $currentPage->InitializeContent();

        $this->assertEquals("Search result for *car* in authors", $currentPage->title);
        $this->assertCount(1, $currentPage->entryArray);
        $this->assertEquals("Carroll, Lewis", $currentPage->entryArray [0]->title);
        $this->assertFalse($currentPage->ContainsBook());

        setURLParam('scope', null);
    }

    public function testPageSearchScopeSeries()
    {
        $page = Base::PAGE_OPENSEARCH_QUERY;
        $qid = null;
        $n = "1";
        setURLParam('scope', "series");

        // Match Holmes
        $query = "hol";
        $currentPage = Page::getPage($page, $qid, $query, $n);
        $currentPage->InitializeContent();

        $this->assertEquals("Search result for *hol* in series", $currentPage->title);
        $this->assertCount(1, $currentPage->entryArray);
        $this->assertEquals("Sherlock Holmes", $currentPage->entryArray [0]->title);
        $this->assertFalse($currentPage->ContainsBook());

        setURLParam('scope', null);
    }

    public function testPageSearchScopeBooks()
    {
        $page = Base::PAGE_OPENSEARCH_QUERY;
        $qid = null;
        $n = "1";
        setURLParam('scope', "book");

        // Match Holmes
        $query = "hol";
        $currentPage = Page::getPage($page, $qid, $query, $n);
        $currentPage->InitializeContent();

        $this->assertEquals("Search result for *hol* in books", $currentPage->title);
        $this->assertCount(4, $currentPage->entryArray);
        $this->assertTrue($currentPage->ContainsBook());

        setURLParam('scope', null);
    }

    public function testPageSearchScopePublishers()
    {
        $page = Base::PAGE_OPENSEARCH_QUERY;
        $qid = null;
        $n = "1";
        setURLParam('scope', "publisher");

        // Match Holmes
        $query = "millan";
        $currentPage = Page::getPage($page, $qid, $query, $n);
        $currentPage->InitializeContent();

        $this->assertEquals("Search result for *millan* in publishers", $currentPage->title);
        $this->assertCount(2, $currentPage->entryArray);
        $this->assertEquals("Macmillan and Co. London", $currentPage->entryArray [0]->title);
        $this->assertFalse($currentPage->ContainsBook());

        setURLParam('scope', null);
    }

    public function testPageSearchScopeTags()
    {
        $page = Base::PAGE_OPENSEARCH_QUERY;
        $qid = null;
        $n = "1";
        setURLParam('scope', "tag");

        // Match Holmes
        $query = "fic";
        $currentPage = Page::getPage($page, $qid, $query, $n);
        $currentPage->InitializeContent();

        $this->assertEquals("Search result for *fic* in tags", $currentPage->title);
        $this->assertCount(2, $currentPage->entryArray);
        $this->assertFalse($currentPage->ContainsBook());

        setURLParam('scope', null);
    }
}
