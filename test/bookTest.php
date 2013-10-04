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
    
    public function testGetBooksBySeries ()
    {
        // All book from the Sherlock Holmes series
        list ($entryArray, $totalNumber) = Book::getBooksBySeries (1, -1);
        $this->assertEquals (7, count($entryArray));
        $this->assertEquals (-1, $totalNumber);
    }
    
    public function testGetBooksByTag ()
    {
        // All book with the Fiction tag
        list ($entryArray, $totalNumber) = Book::getBooksByTag (1, -1);
        $this->assertEquals (14, count($entryArray));
        $this->assertEquals (-1, $totalNumber);
    }
    
    public function testGetBooksByLanguage ()
    {
        // All english book (= all books)
        list ($entryArray, $totalNumber) = Book::getBooksByLanguage (1, -1);
        $this->assertEquals (14, count($entryArray));
        $this->assertEquals (-1, $totalNumber);
    }

    public function testGetBookById ()
    {
        // also check most of book's class methods
        $book = Book::getBookById(2);
        $this->assertEquals ("The Return of Sherlock Holmes", $book->getTitle ());
        $this->assertEquals ("urn:uuid:87ddbdeb-1e27-4d06-b79b-4b2a3bfc6a5f", $book->getEntryId ());
        $this->assertEquals ("index.php?page=13&id=2", $book->getDetailUrl ());
        $this->assertEquals ("Doyle, Arthur Conan", $book->getAuthorsName ());
        $this->assertEquals ("Fiction, Mystery & Detective, Short Stories", $book->getTagsName ());
        $this->assertEquals ('<p class="description">The Return of Sherlock Holmes is a collection of 13 Sherlock Holmes stories, originally published in 1903-1904, by Arthur Conan Doyle.<br />The book was first published on March 7, 1905 by Georges Newnes, Ltd and in a Colonial edition by Longmans. 30,000 copies were made of the initial print run. The US edition by McClure, Phillips &amp; Co. added another 28,000 to the run.<br />This was the first Holmes collection since 1893, when Holmes had "died" in "The Adventure of the Final Problem". Having published The Hound of the Baskervilles in 1901–1902 (although setting it before Holmes\' death) Doyle came under intense pressure to revive his famous character.</p>', $book->getComment (false));
        
    }
    
}