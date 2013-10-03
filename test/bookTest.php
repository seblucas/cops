<?php

require_once (dirname(__FILE__) . "/config_test.php");
require_once (dirname(__FILE__) . "/../book.php");

class StackTest extends PHPUnit_Framework_TestCase
{   
    public function testGetBookCount ()
    {
        $this->assertEquals (14, Book::getBookCount ());
    }
    
    public function testGetCount ()
    {
        $entryArray = Book::getCount ();
        $this->assertEquals (2, count($entryArray));
        
        $entryAllBooks = $entryArray [0];
        $this->assertEquals ("Alphabetical index of the 14 books", $entryAllBooks->content);
        
        $entryRecentBooks = $entryArray [1];
        $this->assertEquals ("50 most recent books", $entryRecentBooks->content);
        
    }
    
    public function testGetCountRecent ()
    {
        global $config;
        $config['cops_recentbooks_limit'] = 0; 
        $entryArray = Book::getCount ();
        
        $this->assertEquals (1, count($entryArray));
        
        $config['cops_recentbooks_limit'] = 2; 
        $entryArray = Book::getCount ();
        
        $entryRecentBooks = $entryArray [1];
        $this->assertEquals ("2 most recent books", $entryRecentBooks->content);
        
        $config['cops_recentbooks_limit'] = 50; 
    }
    
    public function testGetBooksByAuthor ()
    {
        // All book by Arthur Conan Doyle
        global $config;
        
        $config['cops_max_item_per_page'] = 5;
        list ($entryArray, $totalNumber) = Book::getBooksByAuthor (1, 1);
        $this->assertEquals (5, count($entryArray));
        $this->assertEquals (8, $totalNumber);
        
        list ($entryArray, $totalNumber) = Book::getBooksByAuthor (1, 2);
        $this->assertEquals (3, count($entryArray));
        $this->assertEquals (8, $totalNumber);
        
        $config['cops_max_item_per_page'] = -1;
        list ($entryArray, $totalNumber) = Book::getBooksByAuthor (1, -1);
        $this->assertEquals (8, count($entryArray));
        $this->assertEquals (-1, $totalNumber);
    }

}