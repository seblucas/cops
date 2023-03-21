<?php
/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

require_once(dirname(__FILE__) . "/config_test.php");
use PHPUnit\Framework\TestCase;

class PageMultiDatabaseTest extends TestCase
{
    public function testPageIndex()
    {
        global $config;
        $config['calibre_directory'] = ["Some books" => dirname(__FILE__) . "/BaseWithSomeBooks/",
                                              "One book" => dirname(__FILE__) . "/BaseWithOneBook/"];
        Base::clearDb();
        $page = Base::PAGE_INDEX;
        $query = null;
        $qid = null;
        $n = "1";

        $currentPage = Page::getPage($page, $qid, $query, $n);
        $currentPage->InitializeContent();

        $this->assertEquals($config['cops_title_default'], $currentPage->title);
        $this->assertCount(2, $currentPage->entryArray);
        $this->assertEquals("Some books", $currentPage->entryArray [0]->title);
        $this->assertEquals("15 books", $currentPage->entryArray [0]->content);
        $this->assertEquals(15, $currentPage->entryArray [0]->numberOfElement);
        $this->assertEquals("One book", $currentPage->entryArray [1]->title);
        $this->assertEquals("1 book", $currentPage->entryArray [1]->content);
        $this->assertEquals(1, $currentPage->entryArray [1]->numberOfElement);
        $this->assertFalse($currentPage->ContainsBook());
    }

    /**
     * @dataProvider providerSearch
     */
    public function testPageSearchXXX($maxItem)
    {
        global $config;
        $config['calibre_directory'] = ["Some books" => dirname(__FILE__) . "/BaseWithSomeBooks/",
                                              "One book" => dirname(__FILE__) . "/BaseWithOneBook/"];
        Base::clearDb();
        $page = Base::PAGE_OPENSEARCH_QUERY;
        $query = "art";
        $qid = null;
        $n = "1";

        // Issue 124
        $config['cops_max_item_per_page'] = $maxItem;
        $currentPage = Page::getPage($page, $qid, $query, $n);
        $currentPage->InitializeContent();


        $this->assertEquals("Search result for *art*", $currentPage->title);
        $this->assertCount(2, $currentPage->entryArray);
        $this->assertEquals("Some books", $currentPage->entryArray [0]->title);
        $this->assertEquals("11 books", $currentPage->entryArray [0]->content);
        $this->assertEquals("One book", $currentPage->entryArray [1]->title);
        $this->assertEquals("1 book", $currentPage->entryArray [1]->content);
        $this->assertFalse($currentPage->ContainsBook());
    }

    public function providerSearch()
    {
        return [
            [2],
            [-1],
        ];
    }

    public static function tearDownAfterClass(): void
    {
        Base::clearDb();
    }
}
