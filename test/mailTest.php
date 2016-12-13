<?php
/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

require_once (dirname(__FILE__) . "/config_test.php");
require_once (dirname(__FILE__) . "/../sendtomail.php");

class MailTest extends PHPUnit_Framework_TestCase
{
    public function testCheckConfigurationOk () {
        global $config;

        $this->assertFalse(checkConfiguration ());
    }

    public function testCheckConfigurationNull () {
        global $config;
        $config['cops_mail_configuration'] = NULL;

        $this->assertStringStartsWith("NOK", checkConfiguration ());
    }

    public function testCheckConfigurationNotArray () {
        global $config;
        $config['cops_mail_configuration'] = "Test";

        $this->assertStringStartsWith("NOK", checkConfiguration ());
    }

    public function testCheckConfigurationSmtpEmpty () {
        global $config;
        $config['cops_mail_configuration']["smtp.host"] = "";

        $this->assertStringStartsWith("NOK", checkConfiguration ());
    }

    public function testCheckConfigurationEmailEmpty () {
        global $config;
        $config['cops_mail_configuration']["address.from"] = "";

        $this->assertStringStartsWith("NOK", checkConfiguration ());
    }

    public function testCheckConfigurationEmailNotValid () {
        global $config;
        $config['cops_mail_configuration']["address.from"] = "a";

        $this->markTestIncomplete();
    }

    public function testCheckRequest () {
        $this->assertFalse (checkRequest (12, "a@a.com"));
    }

    public function testCheckRequestNoData () {
        $this->assertStringStartsWith ("No", checkRequest (NULL, "a@a.com"));
    }

    public function testCheckRequestNoEmail () {
        $this->assertStringStartsWith ("No", checkRequest (12, NULL));
    }

}