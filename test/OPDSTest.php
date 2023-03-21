<?php
/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

require_once(dirname(__FILE__) . "/config_test.php");
use PHPUnit\Framework\TestCase;

define("OPDS_RELAX_NG", dirname(__FILE__) . "/opds-relax-ng/opds_catalog_1_1.rng");
define("OPENSEARCHDESCRIPTION_RELAX_NG", dirname(__FILE__) . "/opds-relax-ng/opensearchdescription.rng");
define("JING_JAR", dirname(__FILE__) . "/jing.jar");
define("OPDSVALIDATOR_JAR", dirname(__FILE__) . "/OPDSValidator.jar");
define("TEST_FEED", dirname(__FILE__) . "/text.atom");

class OpdsTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        global $config;
        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithSomeBooks/";
        Base::clearDb();
    }

    public static function tearDownAfterClass(): void
    {
        if (!file_exists(TEST_FEED)) {
            return;
        }
        unlink(TEST_FEED);
    }

    public function jingValidateSchema($feed, $relax = OPDS_RELAX_NG)
    {
        $path = "";
        $res = system($path . 'java -jar "' . JING_JAR . '" "' . $relax . '" "' . $feed . '"');
        if ($res != '') {
            echo 'RelaxNG validation error: '.$res;
            return false;
        } else {
            return true;
        }
    }

    public function opdsValidator($feed)
    {
        $oldcwd = getcwd(); // Save the old working directory
        chdir("test");
        $path = "";
        $res = system($path . 'java -jar "' . OPDSVALIDATOR_JAR . '" "' . $feed . '"');
        chdir($oldcwd);
        if ($res != '') {
            echo 'OPDS validation error: '.$res;
            return false;
        } else {
            return true;
        }
    }

    public function opdsCompleteValidation($feed)
    {
        return $this->jingValidateSchema($feed) && $this->opdsValidator($feed);
    }

    public function testPageIndex()
    {
        global $config;
        $page = Base::PAGE_INDEX;
        $query = null;
        $qid = null;
        $n = "1";

        $_SERVER['QUERY_STRING'] = "";
        $config['cops_subtitle_default'] = "My subtitle";

        $currentPage = Page::getPage($page, $qid, $query, $n);
        $currentPage->InitializeContent();

        $OPDSRender = new OPDSRenderer();

        file_put_contents(TEST_FEED, $OPDSRender->render($currentPage));
        $this->AssertTrue($this->jingValidateSchema(TEST_FEED));
        $this->AssertTrue($this->opdsCompleteValidation(TEST_FEED));

        $_SERVER ["HTTP_USER_AGENT"] = "XXX";
        $config['cops_generate_invalid_opds_stream'] = "1";

        file_put_contents(TEST_FEED, $OPDSRender->render($currentPage));
        $this->AssertFalse($this->jingValidateSchema(TEST_FEED));
        $this->AssertFalse($this->opdsValidator(TEST_FEED));

        unset($_SERVER['QUERY_STRING']);
        unset($_SERVER['HTTP_USER_AGENT']);
        $config['cops_generate_invalid_opds_stream'] = "0";
    }

    /**
     * @dataProvider providerPage
     */
    public function testMostPages($page, $query)
    {
        $qid = null;
        $n = "1";
        $_SERVER['QUERY_STRING'] = "?page={$page}";
        if (!empty($query)) {
            $_SERVER['QUERY_STRING'] .= "&query={$query}";
        }
        $_SERVER['REQUEST_URI'] = "feed.php" . $_SERVER['QUERY_STRING'];

        $currentPage = Page::getPage($page, $qid, $query, $n);
        $currentPage->InitializeContent();

        $OPDSRender = new OPDSRenderer();

        file_put_contents(TEST_FEED, $OPDSRender->render($currentPage));
        $this->AssertTrue($this->opdsCompleteValidation(TEST_FEED));

        unset($_SERVER['QUERY_STRING']);
        unset($_SERVER['REQUEST_URI']);
    }

    public function providerPage()
    {
        return [
            [Base::PAGE_OPENSEARCH, "car"],
            [Base::PAGE_ALL_AUTHORS, null],
            [Base::PAGE_ALL_SERIES, null],
            [Base::PAGE_ALL_TAGS, null],
            [Base::PAGE_ALL_PUBLISHERS, null],
            [Base::PAGE_ALL_LANGUAGES, null],
            [Base::PAGE_ALL_RECENT_BOOKS, null],
            [Base::PAGE_ALL_BOOKS, null],
        ];
    }

    public function testPageIndexMultipleDatabase()
    {
        global $config;
        $config['calibre_directory'] = ["Some books" => dirname(__FILE__) . "/BaseWithSomeBooks/",
                                              "One book" => dirname(__FILE__) . "/BaseWithOneBook/"];
        Base::clearDb();
        $page = Base::PAGE_INDEX;
        $query = null;
        $qid = "1";
        $n = "1";
        $_SERVER['QUERY_STRING'] = "";

        $currentPage = Page::getPage($page, $qid, $query, $n);
        $currentPage->InitializeContent();

        $OPDSRender = new OPDSRenderer();

        file_put_contents(TEST_FEED, $OPDSRender->render($currentPage));
        $this->AssertTrue($this->opdsCompleteValidation(TEST_FEED));

        unset($_SERVER['QUERY_STRING']);
        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithSomeBooks/";
        Base::clearDb();
    }

    public function testOpenSearchDescription()
    {
        $_SERVER['QUERY_STRING'] = "";

        $OPDSRender = new OPDSRenderer();

        file_put_contents(TEST_FEED, $OPDSRender->getOpenSearch());
        $this->AssertTrue($this->jingValidateSchema(TEST_FEED, OPENSEARCHDESCRIPTION_RELAX_NG));

        unset($_SERVER['QUERY_STRING']);
    }

    public function testPageAuthorMultipleDatabase()
    {
        global $config;
        $config['calibre_directory'] = ["Some books" => dirname(__FILE__) . "/BaseWithSomeBooks/",
                                              "One book" => dirname(__FILE__) . "/BaseWithOneBook/"];
        Base::clearDb();
        $page = Base::PAGE_AUTHOR_DETAIL;
        $query = null;
        $qid = "1";
        $n = "1";
        $_SERVER['QUERY_STRING'] = "page=" . Base::PAGE_AUTHOR_DETAIL . "&id=1";
        $_GET ["db"] = "0";

        $currentPage = Page::getPage($page, $qid, $query, $n);
        $currentPage->InitializeContent();

        $OPDSRender = new OPDSRenderer();

        file_put_contents(TEST_FEED, $OPDSRender->render($currentPage));
        $this->AssertTrue($this->opdsCompleteValidation(TEST_FEED));

        unset($_SERVER['QUERY_STRING']);
        unset($_GET['db']);
        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithSomeBooks/";
        Base::clearDb();
    }

    public function testPageAuthorsDetail()
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

        $OPDSRender = new OPDSRenderer();

        file_put_contents(TEST_FEED, $OPDSRender->render($currentPage));
        $this->AssertTrue($this->opdsCompleteValidation(TEST_FEED));

        // Second page

        $n = 2;
        $currentPage = Page::getPage($page, $qid, $query, $n);
        $currentPage->InitializeContent();

        $OPDSRender = new OPDSRenderer();

        file_put_contents(TEST_FEED, $OPDSRender->render($currentPage));
        $this->AssertTrue($this->opdsCompleteValidation(TEST_FEED));

        unset($_SERVER['QUERY_STRING']);
        // No pagination
        $config['cops_max_item_per_page'] = -1;
    }

    public function testPageAuthorsDetail_WithFacets()
    {
        global $config;
        $page = Base::PAGE_AUTHOR_DETAIL;
        $query = null;
        $qid = "1";
        $n = "1";
        $_SERVER['QUERY_STRING'] = "page=" . Base::PAGE_AUTHOR_DETAIL . "&id=1&n=1";
        $_GET["tag"] = "Short Stories";

        $config['cops_books_filter'] = ["Only Short Stories" => "Short Stories", "No Short Stories" => "!Short Stories"];

        $currentPage = Page::getPage($page, $qid, $query, $n);
        $currentPage->InitializeContent();

        $OPDSRender = new OPDSRenderer();

        file_put_contents(TEST_FEED, $OPDSRender->render($currentPage));
        $this->AssertTrue($this->opdsCompleteValidation(TEST_FEED));

        unset($_SERVER['QUERY_STRING']);
        unset($_GET['tag']);
        $config['cops_books_filter'] = [];
    }

    public function testPageAuthorsDetail_WithoutAnyId()
    {
        global $config;
        $page = Base::PAGE_AUTHOR_DETAIL;
        $query = null;
        $qid = "1";
        $n = "1";
        $_SERVER['QUERY_STRING'] = "page=" . Base::PAGE_AUTHOR_DETAIL . "&id=1&n=1";
        $_SERVER['REQUEST_URI'] = "index.php?XXXX";


        $currentPage = Page::getPage($page, $qid, $query, $n);
        $currentPage->InitializeContent();
        $currentPage->idPage = null;

        $OPDSRender = new OPDSRenderer();

        file_put_contents(TEST_FEED, $OPDSRender->render($currentPage));
        $this->AssertTrue($this->opdsCompleteValidation(TEST_FEED));

        unset($_SERVER['QUERY_STRING']);
        unset($_SERVER['REQUEST_URI']);
    }
}
