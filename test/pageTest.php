<?php

require_once (dirname(__FILE__) . "/config_test.php");
require_once (dirname(__FILE__) . "/../book.php");

class PageTest extends PHPUnit_Framework_TestCase
{   
    public function testPageIndex ()
    {
        global $config;
        $page = Base::PAGE_INDEX;
        $query = NULL;
        $search = NULL;
        $qid = NULL;
        $n = "1";
        $database = NULL;
        
        $currentPage = Page::getPage ($page, $qid, $query, $n);
        $currentPage->InitializeContent ();
        
        $this->assertEquals ($config['cops_title_default'], $currentPage->title);
        $this->assertCount (6, $currentPage->entryArray);
        $this->assertEquals ("Authors", $currentPage->entryArray [0]->title);
        $this->assertEquals ("Alphabetical index of the 5 authors", $currentPage->entryArray [0]->content);
        $this->assertEquals ("Series", $currentPage->entryArray [1]->title);
        $this->assertEquals ("Alphabetical index of the 3 series", $currentPage->entryArray [1]->content);
        $this->assertEquals ("Tags", $currentPage->entryArray [2]->title);
        $this->assertEquals ("Alphabetical index of the 10 tags", $currentPage->entryArray [2]->content);
        $this->assertEquals ("Languages", $currentPage->entryArray [3]->title);
        $this->assertEquals ("Alphabetical index of the single language", $currentPage->entryArray [3]->content);
        $this->assertEquals ("All books", $currentPage->entryArray [4]->title);
        $this->assertEquals ("Alphabetical index of the 14 books", $currentPage->entryArray [4]->content);
        $this->assertEquals ("Recent additions", $currentPage->entryArray [5]->title);
        $this->assertEquals ("50 most recent books", $currentPage->entryArray [5]->content);
        $this->assertFalse ($currentPage->ContainsBook ());
    }
    
    public function testPageAllAuthors ()
    {
        global $config;
        $page = Base::PAGE_ALL_AUTHORS;
        $query = NULL;
        $search = NULL;
        $qid = NULL;
        $n = "1";
        $database = NULL;
        
        $config['cops_author_split_first_letter'] = "0";
        
        $currentPage = Page::getPage ($page, $qid, $query, $n);
        $currentPage->InitializeContent ();
        
        $this->assertEquals ("Authors", $currentPage->title);
        $this->assertCount (5, $currentPage->entryArray);
        $this->assertEquals ("Carroll, Lewis", $currentPage->entryArray [0]->title);
        $this->assertFalse ($currentPage->ContainsBook ());
        
        $config['cops_author_split_first_letter'] = 1;
        
        $currentPage = Page::getPage ($page, $qid, $query, $n);
        $currentPage->InitializeContent ();
        
        $this->assertEquals ("Authors", $currentPage->title);
        $this->assertCount (4, $currentPage->entryArray);
        $this->assertEquals ("C", $currentPage->entryArray [0]->title);
        $this->assertFalse ($currentPage->ContainsBook ());
    }
    
    public function testPageAuthorsFirstLetter ()
    {
        global $config;
        $page = Base::PAGE_AUTHORS_FIRST_LETTER;
        $query = NULL;
        $search = NULL;
        $qid = "C";
        $n = "1";
        $database = NULL;
        
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
        $search = NULL;
        $qid = "1";
        $n = "1";
        $database = NULL;
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
        $search = NULL;
        $qid = NULL;
        $n = "1";
        $database = NULL;
        
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
        global $config;
        $page = Base::PAGE_ALL_BOOKS_LETTER;
        $query = NULL;
        $search = NULL;
        $qid = "C";
        $n = "1";
        $database = NULL;
        
        $currentPage = Page::getPage ($page, $qid, $query, $n);
        $currentPage->InitializeContent ();
        
        $this->assertEquals ("2 books starting with C", $currentPage->title);
        $this->assertCount (2, $currentPage->entryArray);
        $this->assertEquals ("The Call of the Wild", $currentPage->entryArray [0]->title);
        $this->assertTrue ($currentPage->ContainsBook ());
    }
    
    public function testPageAllSeries ()
    {
        global $config;
        $page = Base::PAGE_ALL_SERIES;
        $query = NULL;
        $search = NULL;
        $qid = NULL;
        $n = "1";
        $database = NULL;
        
        $currentPage = Page::getPage ($page, $qid, $query, $n);
        $currentPage->InitializeContent ();
        
        $this->assertEquals ("Series", $currentPage->title);
        $this->assertCount (3, $currentPage->entryArray);
        $this->assertEquals ("D'Artagnan Romances", $currentPage->entryArray [0]->title);
        $this->assertFalse ($currentPage->ContainsBook ());
    }
    
    public function testPageSeriesDetail ()
    {
        global $config;
        $page = Base::PAGE_SERIE_DETAIL;
        $query = NULL;
        $search = NULL;
        $qid = "1";
        $n = "1";
        $database = NULL;
        
        $currentPage = Page::getPage ($page, $qid, $query, $n);
        $currentPage->InitializeContent ();
        
        $this->assertEquals ("Sherlock Holmes", $currentPage->title);
        $this->assertCount (7, $currentPage->entryArray);
        $this->assertEquals ("A Study in Scarlet", $currentPage->entryArray [0]->title);
        $this->assertTrue ($currentPage->ContainsBook ());
    }

    public function testPageAllTags ()
    {
        global $config;
        $page = Base::PAGE_ALL_TAGS;
        $query = NULL;
        $search = NULL;
        $qid = NULL;
        $n = "1";
        $database = NULL;
        
        $currentPage = Page::getPage ($page, $qid, $query, $n);
        $currentPage->InitializeContent ();
        
        $this->assertEquals ("Tags", $currentPage->title);
        $this->assertCount (10, $currentPage->entryArray);
        $this->assertEquals ("Action & Adventure", $currentPage->entryArray [0]->title);
        $this->assertFalse ($currentPage->ContainsBook ());
    }
    
    public function testPageTagDetail ()
    {
        global $config;
        $page = Base::PAGE_TAG_DETAIL;
        $query = NULL;
        $search = NULL;
        $qid = "1";
        $n = "1";
        $database = NULL;
        
        $currentPage = Page::getPage ($page, $qid, $query, $n);
        $currentPage->InitializeContent ();
        
        $this->assertEquals ("Fiction", $currentPage->title);
        $this->assertCount (14, $currentPage->entryArray);
        $this->assertEquals ("The Adventures of Sherlock Holmes", $currentPage->entryArray [0]->title);
        $this->assertTrue ($currentPage->ContainsBook ());
    }

    public function testPageAllLanguages ()
    {
        global $config;
        $page = Base::PAGE_ALL_LANGUAGES;
        $query = NULL;
        $search = NULL;
        $qid = NULL;
        $n = "1";
        $database = NULL;
        
        $currentPage = Page::getPage ($page, $qid, $query, $n);
        $currentPage->InitializeContent ();
        
        $this->assertEquals ("Languages", $currentPage->title);
        $this->assertCount (1, $currentPage->entryArray);
        $this->assertEquals ("English", $currentPage->entryArray [0]->title);
        $this->assertFalse ($currentPage->ContainsBook ());
    }
    
    public function testPageLanguageDetail ()
    {
        global $config;
        $page = Base::PAGE_LANGUAGE_DETAIL;
        $query = NULL;
        $search = NULL;
        $qid = "1";
        $n = "1";
        $database = NULL;
        
        $currentPage = Page::getPage ($page, $qid, $query, $n);
        $currentPage->InitializeContent ();
        
        $this->assertEquals ("English", $currentPage->title);
        $this->assertCount (14, $currentPage->entryArray);
        $this->assertEquals ("The Adventures of Sherlock Holmes", $currentPage->entryArray [0]->title);
        $this->assertTrue ($currentPage->ContainsBook ());
    }
    
    public function testPageRecent ()
    {
        global $config;
        $page = Base::PAGE_ALL_RECENT_BOOKS;
        $query = NULL;
        $search = NULL;
        $qid = NULL;
        $n = "1";
        $database = NULL;
        
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
        global $config;
        $page = Base::PAGE_BOOK_DETAIL;
        $query = NULL;
        $search = NULL;
        $qid = "2";
        $n = "1";
        $database = NULL;
        
        $currentPage = Page::getPage ($page, $qid, $query, $n);
        $currentPage->InitializeContent ();
        
        $this->assertEquals ("The Return of Sherlock Holmes", $currentPage->title);
        $this->assertCount (0, $currentPage->entryArray);
        $this->assertFalse ($currentPage->ContainsBook ());
    }
    
}