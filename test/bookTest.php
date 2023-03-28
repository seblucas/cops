<?php
/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

require_once(dirname(__FILE__) . "/config_test.php");
use PHPUnit\Framework\TestCase;

/*
Publishers:
id:2 (2 books)   Macmillan and Co. London:   Lewis Caroll
id:3 (2 books)   D. Appleton and Company     Alexander Dumas
id:4 (1 book)    Macmillan Publishers USA:   Jack London
id:5 (1 book)    Pierson's Magazine:         H. G. Wells
id:6 (8 books)   Strand Magazine:            Arthur Conan Doyle
*/

define("TEST_THUMBNAIL", dirname(__FILE__) . "/thumbnail.jpg");
define("COVER_WIDTH", 400);
define("COVER_HEIGHT", 600);

class BookTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        global $config;
        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithSomeBooks/";
        Base::clearDb();
        $book = Book::getBookById(2);
        if (!is_dir($book->path)) {
            mkdir($book->path, 0777, true);
        }
        $im = imagecreatetruecolor(COVER_WIDTH, COVER_HEIGHT);
        $text_color = imagecolorallocate($im, 255, 0, 0);
        imagestring($im, 1, 5, 5, 'Book cover', $text_color);
        imagejpeg($im, $book->path . "/cover.jpg", 80);
    }

    public static function tearDownAfterClass(): void
    {
        $book = Book::getBookById(2);
        if (!file_exists($book->path . "/cover.jpg")) {
            return;
        }
        unlink($book->path . "/cover.jpg");
        rmdir($book->path);
        rmdir(dirname($book->path));
    }

    public function testGetBookCount()
    {
        $this->assertEquals(15, Book::getBookCount());
    }

    public function testGetCount()
    {
        $entryArray = Book::getCount();
        $this->assertEquals(2, count($entryArray));

        $entryAllBooks = $entryArray [0];
        $this->assertEquals("Alphabetical index of the 15 books", $entryAllBooks->content);

        $entryRecentBooks = $entryArray [1];
        $this->assertEquals("50 most recent books", $entryRecentBooks->content);
    }

    public function testGetCountRecent()
    {
        global $config;
        $config['cops_recentbooks_limit'] = 0;
        $entryArray = Book::getCount();

        $this->assertEquals(1, count($entryArray));

        $config['cops_recentbooks_limit'] = 2;
        $entryArray = Book::getCount();

        $entryRecentBooks = $entryArray [1];
        $this->assertEquals("2 most recent books", $entryRecentBooks->content);

        $config['cops_recentbooks_limit'] = 50;
    }

    public function testGetBooksByAuthor()
    {
        // All book by Arthur Conan Doyle
        global $config;

        $config['cops_max_item_per_page'] = 5;
        [$entryArray, $totalNumber] = Book::getBooksByAuthor(1, 1);
        $this->assertEquals(5, count($entryArray));
        $this->assertEquals(8, $totalNumber);

        [$entryArray, $totalNumber] = Book::getBooksByAuthor(1, 2);
        $this->assertEquals(3, count($entryArray));
        $this->assertEquals(8, $totalNumber);

        $config['cops_max_item_per_page'] = -1;
        [$entryArray, $totalNumber] = Book::getBooksByAuthor(1, -1);
        $this->assertEquals(8, count($entryArray));
        $this->assertEquals(-1, $totalNumber);
    }

    public function testGetBooksBySeries()
    {
        // All book from the Sherlock Holmes series
        [$entryArray, $totalNumber] = Book::getBooksBySeries(1, -1);
        $this->assertEquals(7, count($entryArray));
        $this->assertEquals(-1, $totalNumber);
    }

    public function testGetBooksByPublisher()
    {
        // All books from Strand Magazine
        [$entryArray, $totalNumber] = Book::getBooksByPublisher(6, -1);
        $this->assertEquals(8, count($entryArray));
        $this->assertEquals(-1, $totalNumber);
    }

    public function testGetBooksByTag()
    {
        // All book with the Fiction tag
        [$entryArray, $totalNumber] = Book::getBooksByTag(1, -1);
        $this->assertEquals(14, count($entryArray));
        $this->assertEquals(-1, $totalNumber);
    }

    public function testGetBooksByLanguage()
    {
        // All english book (= all books)
        [$entryArray, $totalNumber] = Book::getBooksByLanguage(1, -1);
        $this->assertEquals(14, count($entryArray));
        $this->assertEquals(-1, $totalNumber);
    }

    public function testGetAllBooks()
    {
        // All books by first letter
        $entryArray = Book::getAllBooks();
        $this->assertCount(9, $entryArray);
    }

    public function testGetBooksByStartingLetter()
    {
        // All books by first letter
        [$entryArray, $totalNumber] = Book::getBooksByStartingLetter("T", -1);
        $this->assertEquals(-1, $totalNumber);
        $this->assertCount(3, $entryArray);
    }

    public function testGetBookByDataId()
    {
        $book = Book::getBookByDataId(17);

        $this->assertEquals("Alice's Adventures in Wonderland", $book->getTitle());
    }

    public function testGetAllRecentBooks()
    {
        // All recent books
        global $config;

        $config['cops_recentbooks_limit'] = 2;

        $entryArray = Book::getAllRecentBooks();
        $this->assertCount(2, $entryArray);

        $config['cops_recentbooks_limit'] = 50;

        $entryArray = Book::getAllRecentBooks();
        $this->assertCount(15, $entryArray);
    }

    /**
     * @dataProvider providerPublicationDate
     */
    public function testGetPubDate($pubdate, $expectedYear)
    {
        $book = Book::getBookById(2);
        $book->pubdate = $pubdate;
        $this->assertEquals($expectedYear, $book->getPubDate());
    }

    public function providerPublicationDate()
    {
        return [
            ['2010-10-05 22:00:00+00:00', '2010'],
            ['1982-11-15 13:05:29.908657+00:00', '1982'],
            ['1562-10-05 00:00:00+00:00', '1562'],
            ['0100-12-31 23:00:00+00:00', ''],
            ['', ''],
            [null, ''],
            ];
    }

    public function testGetBookById()
    {
        // also check most of book's class methods
        $book = Book::getBookById(2);

        $linkArray = $book->getLinkArray();
        $this->assertCount(5, $linkArray);

        $this->assertEquals("The Return of Sherlock Holmes", $book->getTitle());
        $this->assertEquals("urn:uuid:87ddbdeb-1e27-4d06-b79b-4b2a3bfc6a5f", $book->getEntryId());
        $this->assertEquals("index.php?page=13&id=2", $book->getDetailUrl());
        $this->assertEquals("Arthur Conan Doyle", $book->getAuthorsName());
        $this->assertEquals("Fiction, Mystery & Detective, Short Stories", $book->getTagsName());
        $this->assertEquals('<p class="description">The Return of Sherlock Holmes is a collection of 13 Sherlock Holmes stories, originally published in 1903-1904, by Arthur Conan Doyle.<br />The book was first published on March 7, 1905 by Georges Newnes, Ltd and in a Colonial edition by Longmans. 30,000 copies were made of the initial print run. The US edition by McClure, Phillips &amp; Co. added another 28,000 to the run.<br />This was the first Holmes collection since 1893, when Holmes had "died" in "The Adventure of the Final Problem". Having published The Hound of the Baskervilles in 1901–1902 (although setting it before Holmes\' death) Doyle came under intense pressure to revive his famous character.</p>', $book->getComment(false));
        $this->assertEquals("English", $book->getLanguages());
        $this->assertEquals("Strand Magazine", $book->getPublisher()->name);
    }

    public function testGetBookById_NotFound()
    {
        $book = Book::getBookById(666);

        $this->assertNull($book);
    }

    public function testGetRating_FiveStars()
    {
        $book = Book::getBookById(2);

        $this->assertEquals("&#9733;&#9733;&#9733;&#9733;&#9733;", $book->getRating());
    }

    public function testGetRating_FourStars()
    {
        $book = Book::getBookById(2);
        $book->rating = 8;

        // 4 filled stars and one empty
        $this->assertEquals("&#9733;&#9733;&#9733;&#9733;&#9734;", $book->getRating());
    }

    public function testGetRating_NoStars_Zero()
    {
        $book = Book::getBookById(2);
        $book->rating = 0;

        $this->assertEquals("", $book->getRating());
    }

    public function testGetRating_NoStars_Null()
    {
        $book = Book::getBookById(2);
        $book->rating = null;

        $this->assertEquals("", $book->getRating());
    }

    public function testBookGetLinkArrayWithUrlRewriting()
    {
        global $config;

        $book = Book::getBookById(2);
        $config['cops_use_url_rewriting'] = "1";

        $linkArray = $book->getLinkArray();
        foreach ($linkArray as $link) {
            if ($link->rel == Link::OPDS_ACQUISITION_TYPE && $link->title == "EPUB") {
                $this->assertEquals("download/1/The%20Return%20of%20Sherlock%20Holmes%20-%20Arthur%20Conan%20Doyle.epub", $link->href);
                return;
            }
        }
        $this->fail();
    }

    public function testBookGetLinkArrayWithoutUrlRewriting()
    {
        global $config;

        $book = Book::getBookById(2);
        $config['cops_use_url_rewriting'] = "0";

        $linkArray = $book->getLinkArray();
        foreach ($linkArray as $link) {
            if ($link->rel == Link::OPDS_ACQUISITION_TYPE && $link->title == "EPUB") {
                $this->assertEquals("fetch.php?data=1&type=epub&id=2", $link->href);
                return;
            }
        }
        $this->fail();
    }

    public function testGetThumbnailNotNeeded()
    {
        $book = Book::getBookById(2);

        $this->assertFalse($book->getThumbnail(null, null, null));

        // Current cover is 400*600
        $this->assertFalse($book->getThumbnail(COVER_WIDTH, null, null));
        $this->assertFalse($book->getThumbnail(COVER_WIDTH + 1, null, null));
        $this->assertFalse($book->getThumbnail(null, COVER_HEIGHT, null));
        $this->assertFalse($book->getThumbnail(null, COVER_HEIGHT + 1, null));
    }

    /**
     * @dataProvider providerThumbnail
     */
    public function testGetThumbnailByWidth($width, $height, $expectedWidth, $expectedHeight)
    {
        $book = Book::getBookById(2);

        $this->assertTrue($book->getThumbnail($width, $height, TEST_THUMBNAIL));

        $size = GetImageSize(TEST_THUMBNAIL);
        $this->assertEquals($expectedWidth, $size [0]);
        $this->assertEquals($expectedHeight, $size [1]);

        unlink(TEST_THUMBNAIL);
    }

    public function providerThumbnail()
    {
        return [
            [164, null, 164, 246],
            [null, 164, 109, 164],
        ];
    }

    public function testGetMostInterestingDataToSendToKindle_WithMOBI()
    {
        // Get Alice (available as MOBI, PDF, EPUB in that order)
        $book = Book::getBookById(17);
        $data = $book->GetMostInterestingDataToSendToKindle();
        $this->assertEquals("MOBI", $data->format);
    }

    public function testGetMostInterestingDataToSendToKindle_WithPdf()
    {
        // Get Alice (available as MOBI, PDF, EPUB in that order)
        $book = Book::getBookById(17);
        $book->GetMostInterestingDataToSendToKindle();
        array_shift($book->datas);
        $data = $book->GetMostInterestingDataToSendToKindle();
        $this->assertEquals("PDF", $data->format);
    }

    public function testGetMostInterestingDataToSendToKindle_WithEPUB()
    {
        // Get Alice (available as MOBI, PDF, EPUB in that order)
        $book = Book::getBookById(17);
        $book->GetMostInterestingDataToSendToKindle();
        array_shift($book->datas);
        array_shift($book->datas);
        $data = $book->GetMostInterestingDataToSendToKindle();
        $this->assertEquals("EPUB", $data->format);
    }

    public function testGetDataById()
    {
        global $config;

        // Get Alice MOBI=>17, PDF=>19, EPUB=>20
        $book = Book::getBookById(17);
        $mobi = $book->getDataById(17);
        $this->assertEquals("MOBI", $mobi->format);
        $epub = $book->getDataById(20);
        $this->assertEquals("EPUB", $epub->format);
        $this->assertEquals("Carroll, Lewis - Alice's Adventures in Wonderland.epub", $epub->getUpdatedFilenameEpub());
        $this->assertEquals("Carroll, Lewis - Alice's Adventures in Wonderland.kepub.epub", $epub->getUpdatedFilenameKepub());
        $this->assertEquals(dirname(__FILE__) . "/BaseWithSomeBooks/Lewis Carroll/Alice's Adventures in Wonderland (17)/Alice's Adventures in Wonderland - Lewis Carroll.epub", $epub->getLocalPath());

        $config['cops_use_url_rewriting'] = "1";
        $config['cops_provide_kepub'] = "1";
        $_SERVER["HTTP_USER_AGENT"] = "Kobo";
        $this->assertEquals("download/20/Carroll%2C%20Lewis%20-%20Alice%27s%20Adventures%20in%20Wonderland.kepub.epub", $epub->getHtmlLink());
        $this->assertEquals("download/17/Alice%27s%20Adventures%20in%20Wonderland%20-%20Lewis%20Carroll.mobi", $mobi->getHtmlLink());
        $config['cops_provide_kepub'] = "0";
        $_SERVER["HTTP_USER_AGENT"] = "Firefox";
        $this->assertEquals("download/20/Alice%27s%20Adventures%20in%20Wonderland%20-%20Lewis%20Carroll.epub", $epub->getHtmlLink());
        $config['cops_use_url_rewriting'] = "0";
        $this->assertEquals("fetch.php?data=20&type=epub&id=17", $epub->getHtmlLink());
    }

    public function testGetFilePath_Cover()
    {
        $book = Book::getBookById(17);

        $this->assertEquals(Base::getDbDirectory() . "Lewis Carroll/Alice's Adventures in Wonderland (17)/cover.jpg", $book->getFilePath("jpg", null, false));
    }

    public function testGetFilePath_Epub()
    {
        $book = Book::getBookById(17);

        $this->assertEquals(Base::getDbDirectory() . "Lewis Carroll/Alice's Adventures in Wonderland (17)/Alice's Adventures in Wonderland - Lewis Carroll.epub", $book->getFilePath("epub", 20, false));
    }

    public function testGetFilePath_Mobi()
    {
        $book = Book::getBookById(17);

        $this->assertEquals(Base::getDbDirectory() . "Lewis Carroll/Alice's Adventures in Wonderland (17)/Alice's Adventures in Wonderland - Lewis Carroll.mobi", $book->getFilePath("mobi", 17, false));
    }

    public function testGetDataFormat_EPUB()
    {
        $book = Book::getBookById(17);

        // Get Alice MOBI=>17, PDF=>19, EPUB=>20
        $data = $book->getDataFormat("EPUB");
        $this->assertEquals(20, $data->id);
    }

    public function testGetDataFormat_MOBI()
    {
        $book = Book::getBookById(17);

        // Get Alice MOBI=>17, PDF=>19, EPUB=>20
        $data = $book->getDataFormat("MOBI");
        $this->assertEquals(17, $data->id);
    }

    public function testGetDataFormat_PDF()
    {
        $book = Book::getBookById(17);

        // Get Alice MOBI=>17, PDF=>19, EPUB=>20
        $data = $book->getDataFormat("PDF");
        $this->assertEquals(19, $data->id);
    }

    public function testGetDataFormat_NonAvailable()
    {
        $book = Book::getBookById(17);

        // Get Alice MOBI=>17, PDF=>19, EPUB=>20
        $this->assertFalse($book->getDataFormat("FB2"));
    }

    public function testGetMimeType_EPUB()
    {
        $book = Book::getBookById(17);

        // Get Alice MOBI=>17, PDF=>19, EPUB=>20
        $data = $book->getDataFormat("EPUB");
        $this->assertEquals("application/epub+zip", $data->getMimeType());
    }

    public function testGetMimeType_MOBI()
    {
        $book = Book::getBookById(17);

        // Get Alice MOBI=>17, PDF=>19, EPUB=>20
        $data = $book->getDataFormat("MOBI");
        $this->assertEquals("application/x-mobipocket-ebook", $data->getMimeType());
    }

    public function testGetMimeType_PDF()
    {
        $book = Book::getBookById(17);

        // Get Alice MOBI=>17, PDF=>19, EPUB=>20
        $data = $book->getDataFormat("PDF");
        $this->assertEquals("application/pdf", $data->getMimeType());
    }

    public function testGetMimeType_Finfo()
    {
        $book = Book::getBookById(17);

        // Get Alice MOBI=>17, PDF=>19, EPUB=>20
        $data = $book->getDataFormat("PDF");
        $this->assertEquals("application/pdf", $data->getMimeType());

        // Alter a data to make a test for finfo_file if enabled
        $data->extension = "ico";
        $data->format = "ICO";
        $data->name = "favicon";
        $data->book->path = realpath(dirname(__FILE__) . "/../");
        if (function_exists('finfo_open') === true) {
            //$this->assertEquals("image/x-icon", $data->getMimeType());
            $this->assertEquals("image/vnd.microsoft.icon", $data->getMimeType());
        } else {
            $this->assertEquals("application/octet-stream", $data->getMimeType());
        }
    }

    public function testTypeaheadSearch_Tag()
    {
        $page = Base::PAGE_OPENSEARCH_QUERY;
        $qid = getURLParam("id");
        $query = "fic";
        $n = getURLParam("n", "1");
        setURLParam('search', 1);

        $currentPage = Page::getPage($page, $qid, $query, $n);
        $currentPage->InitializeContent();

        $this->assertCount(3, $currentPage->entryArray);
        $this->assertEquals("2 tags", $currentPage->entryArray[0]->content);
        $this->assertEquals("Fiction", $currentPage->entryArray[1]->title);
        $this->assertEquals("Science Fiction", $currentPage->entryArray[2]->title);

        setURLParam('search', null);
    }

    public function testTypeaheadSearch_BookAndAuthor()
    {
        $page = Base::PAGE_OPENSEARCH_QUERY;
        $qid = getURLParam("id");
        $query = "car";
        $n = getURLParam("n", "1");
        setURLParam('search', 1);

        $currentPage = Page::getPage($page, $qid, $query, $n);
        $currentPage->InitializeContent();

        $this->assertCount(4, $currentPage->entryArray);
        $this->assertEquals("1 book", $currentPage->entryArray[0]->content);
        $this->assertEquals("A Study in Scarlet", $currentPage->entryArray[1]->title);

        $this->assertEquals("1 author", $currentPage->entryArray[2]->content);
        $this->assertEquals("Carroll, Lewis", $currentPage->entryArray[3]->title);

        setURLParam('search', null);
    }

    public function testTypeaheadSearch_AuthorAndSeries()
    {
        $page = Base::PAGE_OPENSEARCH_QUERY;
        $qid = getURLParam("id");
        $query = "art";
        $n = getURLParam("n", "1");
        setURLParam('search', 1);

        $currentPage = Page::getPage($page, $qid, $query, $n);
        $currentPage->InitializeContent();

        $this->assertCount(5, $currentPage->entryArray);
        $this->assertEquals("1 author", $currentPage->entryArray[0]->content);
        $this->assertEquals("Doyle, Arthur Conan", $currentPage->entryArray[1]->title);

        $this->assertEquals("2 series", $currentPage->entryArray[2]->content);
        $this->assertEquals("D'Artagnan Romances", $currentPage->entryArray[3]->title);

        setURLParam('search', null);
    }

    public function testTypeaheadSearch_Publisher()
    {
        $page = Base::PAGE_OPENSEARCH_QUERY;
        $qid = getURLParam("id");
        $query = "Macmillan";
        $n = getURLParam("n", "1");
        setURLParam('search', 1);

        $currentPage = Page::getPage($page, $qid, $query, $n);
        $currentPage->InitializeContent();

        $this->assertCount(3, $currentPage->entryArray);
        $this->assertEquals("2 publishers", $currentPage->entryArray[0]->content);
        $this->assertEquals("Macmillan and Co. London", $currentPage->entryArray[1]->title);
        $this->assertEquals("Macmillan Publishers USA", $currentPage->entryArray[2]->title);

        setURLParam('search', null);
    }

    public function testTypeaheadSearchWithIgnored_SingleCategory()
    {
        global $config;
        $page = Base::PAGE_OPENSEARCH_QUERY;
        $qid = getURLParam("id");
        $query = "car";
        $n = getURLParam("n", "1");
        setURLParam('search', 1);

        $config ['cops_ignored_categories'] = ["author"];
        $currentPage = Page::getPage($page, $qid, $query, $n);
        $currentPage->InitializeContent();

        $this->assertCount(2, $currentPage->entryArray);
        $this->assertEquals("1 book", $currentPage->entryArray[0]->content);
        $this->assertEquals("A Study in Scarlet", $currentPage->entryArray[1]->title);

        setURLParam('search', null);
        $config ['cops_ignored_categories'] = [];
    }

    public function testTypeaheadSearchWithIgnored_MultipleCategory()
    {
        global $config;
        $page = Base::PAGE_OPENSEARCH_QUERY;
        $qid = getURLParam("id");
        $query = "art";
        $n = getURLParam("n", "1");
        setURLParam('search', 1);

        $config ['cops_ignored_categories'] = ["series"];
        $currentPage = Page::getPage($page, $qid, $query, $n);
        $currentPage->InitializeContent();

        $this->assertCount(2, $currentPage->entryArray);
        $this->assertEquals("1 author", $currentPage->entryArray[0]->content);
        $this->assertEquals("Doyle, Arthur Conan", $currentPage->entryArray[1]->title);

        setURLParam('search', null);
        $config ['cops_ignored_categories'] = [];
    }

    public function testTypeaheadSearchMultiDatabase()
    {
        global $config;
        $page = Base::PAGE_OPENSEARCH_QUERY;
        $qid = getURLParam("id");
        $query = "art";
        $n = getURLParam("n", "1");
        setURLParam('search', 1);
        setURLParam('multi', 1);

        $config['calibre_directory'] = ["Some books" => dirname(__FILE__) . "/BaseWithSomeBooks/",
            "One book" => dirname(__FILE__) . "/BaseWithOneBook/"];
        $currentPage = Page::getPage($page, $qid, $query, $n);
        $currentPage->InitializeContent();

        $this->assertCount(5, $currentPage->entryArray);
        $this->assertEquals("Some books", $currentPage->entryArray[0]->title);
        $this->assertEquals("1 author", $currentPage->entryArray[1]->content);
        $this->assertEquals("2 series", $currentPage->entryArray[2]->content);
        $this->assertEquals("One book", $currentPage->entryArray[3]->title);
        $this->assertEquals("1 book", $currentPage->entryArray[4]->content);

        setURLParam('search', null);
        setURLParam('multi', null);
        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithSomeBooks/";
        Base::clearDb();
    }

    public function tearDown(): void
    {
        Base::clearDb();
    }
}
