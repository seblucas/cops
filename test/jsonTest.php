<?php
/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

require_once (dirname(__FILE__) . "/config_test.php");

class JsonTest extends PHPUnit_Framework_TestCase
{
    public function testCompleteArray () {
        global $config;

        $_SERVER["HTTP_USER_AGENT"] = "Firefox";
        $test = array ();
        $test = JSONRenderer::addCompleteArray ($test);
        $this->assertArrayHasKey ("c", $test);
        $this->assertArrayHasKey ("version", $test ["c"]);
        $this->assertArrayHasKey ("i18n", $test ["c"]);
        $this->assertArrayHasKey ("url", $test ["c"]);
        $this->assertArrayHasKey ("config", $test ["c"]);

        $this->assertFalse ($test ["c"]["url"]["thumbnailUrl"] == $test ["c"]["url"]["coverUrl"]);

        // The thumbnails should be the same as the covers
        $config['cops_thumbnail_handling'] = "1";
        $test = array ();
        $test = JSONRenderer::addCompleteArray ($test);

        $this->assertTrue ($test ["c"]["url"]["thumbnailUrl"] == $test ["c"]["url"]["coverUrl"]);

        // The thumbnails should be the same as the covers
        $config['cops_thumbnail_handling'] = "/images.png";
        $test = array ();
        $test = JSONRenderer::addCompleteArray ($test);

        $this->assertEquals ("/images.png", $test ["c"]["url"]["thumbnailUrl"]);
    }

    public function testGetBookContentArrayWithoutSeries () {
        $book = Book::getBookById(17);
        $test = JSONRenderer::getBookContentArray($book);

        $this->assertEquals ("", $test ["seriesName"]);
        $this->assertEquals ("1.0", $test ["seriesIndex"]);
        $this->assertEquals ("", $test ["seriesCompleteName"]);
        $this->assertEquals ("", $test ["seriesurl"]);
    }

    public function testGetBookContentArrayWithSeries () {
        $book = Book::getBookById(2);

        $test = JSONRenderer::getBookContentArray($book);

        $this->assertEquals ("Sherlock Holmes", $test ["seriesName"]);
        $this->assertEquals ("6.0", $test ["seriesIndex"]);
        $this->assertEquals ("Book 6.0 in the Sherlock Holmes series", $test ["seriesCompleteName"]);
        $this->assertStringEndsWith ("?page=7&id=1", $test ["seriesurl"]);
    }

    public function testGetFullBookContentArray () {
        $book = Book::getBookById(17);

        $test = JSONRenderer::getFullBookContentArray($book);

        $this->assertCount (1, $test ["authors"]);
        $this->assertCount (3, $test ["tags"]);
        $this->assertCount (3, $test ["datas"]);
    }
}