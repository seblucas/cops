<?php
/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

require_once (dirname(__FILE__) . "/config_test.php");
require_once (dirname(__FILE__) . "/../book.php");

class PageMultiDatabaseTest extends PHPUnit_Framework_TestCase
{   
    public function testPageIndex ()
    {
        global $config;
        $config['calibre_directory'] = array ("Some books" => dirname(__FILE__) . "/BaseWithSomeBooks/",
                                              "One book" => dirname(__FILE__) . "/BaseWithOneBook/");
        $page = Base::PAGE_INDEX;
        $query = NULL;
        $search = NULL;
        $qid = NULL;
        $n = "1";
        $database = NULL;
        
        $currentPage = Page::getPage ($page, $qid, $query, $n);
        $currentPage->InitializeContent ();
        
        $this->assertEquals ($config['cops_title_default'], $currentPage->title);
        $this->assertCount (2, $currentPage->entryArray);
        $this->assertEquals ("Some books", $currentPage->entryArray [0]->title);
        $this->assertEquals ("14 books", $currentPage->entryArray [0]->content);
        $this->assertEquals ("One book", $currentPage->entryArray [1]->title);
        $this->assertEquals ("1 book", $currentPage->entryArray [1]->content);
        $this->assertFalse ($currentPage->ContainsBook ());
    }
   
    public function testPageSearchXXX ()
    {
        global $config;
        $config['calibre_directory'] = array ("Some books" => dirname(__FILE__) . "/BaseWithSomeBooks/",
                                              "One book" => dirname(__FILE__) . "/BaseWithOneBook/");
        $page = Base::PAGE_OPENSEARCH_QUERY;
        $query = "art";
        $search = NULL;
        $qid = NULL;
        $n = "1";
        $database = NULL;
        
        $currentPage = Page::getPage ($page, $qid, $query, $n);
        $currentPage->InitializeContent ();
        
        $this->assertEquals ("Search result for *art*", $currentPage->title);
        $this->assertCount (2, $currentPage->entryArray);
        $this->assertEquals ("Some books", $currentPage->entryArray [0]->title);
        $this->assertEquals ("10 books", $currentPage->entryArray [0]->content);
        $this->assertEquals ("One book", $currentPage->entryArray [1]->title);
        $this->assertEquals ("1 book", $currentPage->entryArray [1]->content);
        $this->assertFalse ($currentPage->ContainsBook ());
    }
    
    public static function tearDownAfterClass () {
        Base::clearDb ();
    }

}
