<?php
/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

require_once (dirname(__FILE__) . "/config_test.php");
require_once (dirname(__FILE__) . "/../book.php");
require_once (dirname(__FILE__) . "/../OPDS_renderer.php");

define ("OPDS_RELAX_NG", dirname(__FILE__) . "/opds-relax-ng/opds_catalog_1_1.rng");
define ("OPENSEARCHDESCRIPTION_RELAX_NG", dirname(__FILE__) . "/opds-relax-ng/opensearchdescription.rng");
define ("JING_JAR", dirname(__FILE__) . "/jing.jar");
define ("TEST_FEED", dirname(__FILE__) . "/text.atom");

class OpdsTest extends PHPUnit_Framework_TestCase
{
    public static function tearDownAfterClass()
    {
        if (!file_exists (TEST_FEED)) {
            return;
        }
        unlink (TEST_FEED);
    }

    function opdsValidateSchema($feed, $relax = OPDS_RELAX_NG) {
        $path = "";
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // huge hack, not proud about it
            $path = "c:\\Progra~1\\Java\\jre7\\bin\\";
        }
        $res = system($path . 'java -jar ' . JING_JAR . ' ' . $relax . ' ' . $feed);
        if ($res != '') {
            echo 'RelaxNG validation error: '.$res;
            return false;
        } else
            return true;
    }



    public function testPageIndex ()
    {
        global $config;
        $page = Base::PAGE_INDEX;
        $query = NULL;
        $qid = NULL;
        $n = "1";

        $_SERVER['QUERY_STRING'] = "";
        $config['cops_subtitle_default'] = "My subtitle";

        $currentPage = Page::getPage ($page, $qid, $query, $n);
        $currentPage->InitializeContent ();

        $OPDSRender = new OPDSRenderer ();

        file_put_contents (TEST_FEED, $OPDSRender->render ($currentPage));
        $this->AssertTrue ($this->opdsValidateSchema (TEST_FEED));
        file_put_contents (TEST_FEED, str_replace ("id>", "ido>", $OPDSRender->render ($currentPage)));
        $this->AssertFalse ($this->opdsValidateSchema (TEST_FEED));

        $_SERVER['QUERY_STRING'] = NULL;
    }
    
    public function testOpenSearchDescription ()
    {
        $_SERVER['QUERY_STRING'] = "";

        $OPDSRender = new OPDSRenderer ();

        file_put_contents (TEST_FEED, $OPDSRender->getOpenSearch ());
        $this->AssertTrue ($this->opdsValidateSchema (TEST_FEED, OPENSEARCHDESCRIPTION_RELAX_NG));

        $_SERVER['QUERY_STRING'] = NULL;
    }

    public function testPageIndexMultipleDatabase ()
    {
        global $config;
        $config['calibre_directory'] = array ("Some books" => dirname(__FILE__) . "/BaseWithSomeBooks/",
                                              "One book" => dirname(__FILE__) . "/BaseWithOneBook/");
        $page = Base::PAGE_AUTHOR_DETAIL;
        $query = NULL;
        $qid = "1";
        $n = "1";
        $_SERVER['QUERY_STRING'] = "page=" . Base::PAGE_AUTHOR_DETAIL . "&id=1";
        $_GET ["db"] = "0";

        $currentPage = Page::getPage ($page, $qid, $query, $n);
        $currentPage->InitializeContent ();

        $OPDSRender = new OPDSRenderer ();

        file_put_contents (TEST_FEED, $OPDSRender->render ($currentPage));
        $this->AssertTrue ($this->opdsValidateSchema (TEST_FEED));
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
        $config['cops_books_filter'] = array ("Only Short Stories" => "Short Stories", "No Short Stories" => "!Short Stories");

        // First page

        $currentPage = Page::getPage ($page, $qid, $query, $n);
        $currentPage->InitializeContent ();

        $OPDSRender = new OPDSRenderer ();

        file_put_contents (TEST_FEED, $OPDSRender->render ($currentPage));
        $this->AssertTrue ($this->opdsValidateSchema (TEST_FEED));

        // Second page

        $n = 2;
        $currentPage = Page::getPage ($page, $qid, $query, $n);
        $currentPage->InitializeContent ();

        $OPDSRender = new OPDSRenderer ();

        file_put_contents (TEST_FEED, $OPDSRender->render ($currentPage));
        $this->AssertTrue ($this->opdsValidateSchema (TEST_FEED));

        // No pagination
        $config['cops_max_item_per_page'] = -1;

    }
}