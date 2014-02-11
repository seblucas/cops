<?php
/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

require_once (dirname(__FILE__) . "/config_test.php");
require_once (dirname(__FILE__) . "/../book.php");
require_once (dirname(__FILE__) . "/../epubfs.php");

class EpubFsTest extends PHPUnit_Framework_TestCase
{
    public function testUrlImage () {
        $idData = 20;
        $add = "data=$idData&";
        $myBook = Book::getBookByDataId($idData);

        $this->assertNotNull($myBook);

        $book = new EPub ($myBook->getFilePath ("EPUB", $idData));

        $book->initSpineComponent ();

        $data = getComponentContent ($book, "cover.xml", $add);

        $src = "";
        if (preg_match("/src\=\'(.*?)\'/", $data, $matches)) {
            $src = $matches [1];
        }
        $this->assertEquals ('epubfs.php?data=20&amp;comp=images~SLASH~cover.png', $src);

    }

    public function testUrlHref () {
        $idData = 20;
        $add = "data=$idData&";
        $myBook = Book::getBookByDataId($idData);

        $this->assertNotNull($myBook);

        $book = new EPub ($myBook->getFilePath ("EPUB", $idData));

        $book->initSpineComponent ();

        $data = getComponentContent ($book, "title.xml", $add);

        $src = "";
        if (preg_match("/src\=\'(.*?)\'/", $data, $matches)) {
            $src = $matches [1];
        }
        $this->assertEquals ('epubfs.php?data=20&amp;comp=images~SLASH~logo~DASH~feedbooks~DASH~tiny.png', $src);

        $href = "";
        if (preg_match("/href\=\'(.*?)\'/", $data, $matches)) {
            $href = $matches [1];
        }
        $this->assertEquals ('epubfs.php?data=20&amp;comp=css~SLASH~title.css', $href);

    }

    public function testImportCss () {
        $idData = 20;
        $add = "data=$idData&";
        $myBook = Book::getBookByDataId($idData);

        $this->assertNotNull($myBook);

        $book = new EPub ($myBook->getFilePath ("EPUB", $idData));

        $book->initSpineComponent ();

        $data = getComponentContent ($book, "css~SLASH~title.css", $add);

        $import = "";
        if (preg_match("/import \'(.*?)\'/", $data, $matches)) {
            $import = $matches [1];
        }
        $this->assertEquals ('epubfs.php?data=20&amp;comp=css~SLASH~page.css', $import);
    }
}