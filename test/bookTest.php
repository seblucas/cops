<?php
/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

require_once (dirname(__FILE__) . "/config_test.php");
require_once (dirname(__FILE__) . "/../book.php");

/*
Publishers:
id:2 (2 books)   Macmillan and Co. London:   Lewis Caroll 
id:3 (2 books)   D. Appleton and Company     Alexander Dumas 
id:4 (1 book)    Macmillan Publishers USA:   Jack London 
id:5 (1 book)    Pierson's Magazine:         H. G. Wells 
id:6 (8 books)   Strand Magazine:            Arthur Conan Doyle
*/

class BookTest extends PHPUnit_Framework_TestCase
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
    
    public function testGetBooksByPublisher ()
    {
        // All books from Strand Magazine 
        list ($entryArray, $totalNumber) = Book::getBooksByPublisher (6, -1);
        $this->assertEquals (8, count($entryArray));
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
    
    public function testGetAllBooks ()
    {
        // All books by first letter
        $entryArray = Book::getAllBooks ();
        $this->assertCount (9, $entryArray);
    }
    
    public function testGetBooksByStartingLetter ()
    {
        // All books by first letter
        list ($entryArray, $totalNumber) = Book::getBooksByStartingLetter ("T", -1);
        $this->assertEquals (-1, $totalNumber);
        $this->assertCount (3, $entryArray);
    }
    
    public function testGetAllRecentBooks ()
    {
        // All recent books
        global $config;
        
        $config['cops_recentbooks_limit'] = 2;
        
        $entryArray = Book::getAllRecentBooks ();
        $this->assertCount (2, $entryArray);
        
        $config['cops_recentbooks_limit'] = 50;
        
        $entryArray = Book::getAllRecentBooks ();
        $this->assertCount (14, $entryArray);
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
        $this->assertEquals ("English", $book->getLanguages ());
        $this->assertEquals ("", $book->getRating ());
        $this->assertEquals ("Strand Magazine", $book->getPublisher()->name);
    }
    
    public function testTypeaheadSearch ()
    {
        $_GET["query"] = "fic";
        $_GET["search"] = "1";
        
        $array = getJson ();
        
        $this->assertCount (3, $array);
        $this->assertEquals ("2 tags", $array[0]["title"]);
        $this->assertEquals ("Fiction", $array[1]["title"]);
        $this->assertEquals ("Science Fiction", $array[2]["title"]);
        
        $_GET["query"] = "car";
        $_GET["search"] = "1";
        
        $array = getJson ();
        
        $this->assertCount (4, $array);
        $this->assertEquals ("1 book", $array[0]["title"]);
        $this->assertEquals ("A Study in Scarlet", $array[1]["title"]);
        $this->assertEquals ("1 author", $array[2]["title"]);
        $this->assertEquals ("Carroll, Lewis", $array[3]["title"]);
        
        $_GET["query"] = "art";
        $_GET["search"] = "1";
        
        $array = getJson ();
        
        $this->assertCount (4, $array);
        $this->assertEquals ("1 author", $array[0]["title"]);
        $this->assertEquals ("Doyle, Arthur Conan", $array[1]["title"]);
        $this->assertEquals ("1 series", $array[2]["title"]);
        $this->assertEquals ("D'Artagnan Romances", $array[3]["title"]);

        $_GET["query"] = "Macmillan";
        $_GET["search"] = "1";

        $array = getJson ();

        $this->assertCount (3, $array);
        $this->assertEquals ("2 publishers", $array[0]["title"]);
        $this->assertEquals ("Macmillan and Co. London", $array[1]["title"]);
        $this->assertEquals ("Macmillan Publishers USA", $array[2]["title"]);
        
        $_GET["query"] = NULL;
        $_GET["search"] = NULL;
    }
    
    public function testTypeaheadSearchMultiDatabase ()
    {
        global $config;
        $_GET["query"] = "art";
        $_GET["search"] = "1";
        $_GET["multi"] = "1";
        
        $config['calibre_directory'] = array ("Some books" => dirname(__FILE__) . "/BaseWithSomeBooks/",
                                              "One book" => dirname(__FILE__) . "/BaseWithOneBook/");
        
        $array = getJson ();
        
        $this->assertCount (4, $array);
        $this->assertEquals ("Some books", $array[0]["title"]);
        $this->assertEquals ("No book", $array[1]["title"]);
        $this->assertEquals ("One book", $array[2]["title"]);
        $this->assertEquals ("1 book", $array[3]["title"]);

        $_GET["query"] = NULL;
        $_GET["search"] = NULL;
    }
    
    public function tearDown () {
        Base::clearDb ();
    }
    
}