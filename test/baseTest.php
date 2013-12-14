<?php
/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

require_once (dirname(__FILE__) . "/config_test.php");
require_once (dirname(__FILE__) . "/../base.php");

class BaseTest extends PHPUnit_Framework_TestCase
{
    public function testAddURLParameter ()
    {
        $this->assertEquals ("?db=0", addURLParameter ("?", "db", "0"));
        $this->assertEquals ("?key=value&db=0", addURLParameter ("?key=value", "db", "0"));
        $this->assertEquals ("?key=value&otherKey=&db=0", addURLParameter ("?key=value&otherKey", "db", "0"));
    }

    public function testLocalize ()
    {
        $this->assertEquals ("Authors", localize ("authors.title"));

        $this->assertEquals ("unknow.key", localize ("unknow.key"));
    }

    public function testLocalizeFr ()
    {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = "fr,fr-fr;q=0.8,en-us;q=0.5,en;q=0.3";
        $this->assertEquals ("Auteurs", localize ("authors.title", -1, true));

        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = "en";
        localize ("authors.title", -1, true);
    }

    public function testLocalizeUnknown ()
    {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = "aa";
        $this->assertEquals ("Authors", localize ("authors.title", -1, true));

        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = "en";
        localize ("authors.title", -1, true);
    }

    public function testBaseFunction () {
        global $config;

        $this->assertFalse (Base::isMultipleDatabaseEnabled ());
        $this->assertEquals (array ("" => dirname(__FILE__) . "/BaseWithSomeBooks/"), Base::getDbList ());

        $config['calibre_directory'] = array ("Some books" => dirname(__FILE__) . "/BaseWithSomeBooks/",
                                              "One book" => dirname(__FILE__) . "/BaseWithOneBook/");

        $this->assertTrue (Base::isMultipleDatabaseEnabled ());
        $this->assertEquals ("Some books", Base::getDbName (0));
        $this->assertEquals ("One book", Base::getDbName (1));
        $this->assertEquals ($config['calibre_directory'], Base::getDbList ());
    }

    public function testCheckDatabaseAvailability_1 () {
        $this->assertTrue (Base::checkDatabaseAvailability ());
    }

    public function testCheckDatabaseAvailability_2 () {
        global $config;

        $config['calibre_directory'] = array ("Some books" => dirname(__FILE__) . "/BaseWithSomeBooks/",
                                              "One book" => dirname(__FILE__) . "/BaseWithOneBook/");

        $this->assertTrue (Base::checkDatabaseAvailability ());
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage not found
     */
    public function testCheckDatabaseAvailability_Exception1 () {
        global $config;

        $config['calibre_directory'] = array ("Some books" => dirname(__FILE__) . "/BaseWithSomeBooks/",
                                              "One book" => dirname(__FILE__) . "/OneBook/");

        $this->assertTrue (Base::checkDatabaseAvailability ());
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage not found
     */
    public function testCheckDatabaseAvailability_Exception2 () {
        global $config;

        $config['calibre_directory'] = array ("Some books" => dirname(__FILE__) . "/SomeBooks/",
                                              "One book" => dirname(__FILE__) . "/BaseWithOneBook/");

        $this->assertTrue (Base::checkDatabaseAvailability ());
    }
}