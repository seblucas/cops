<?php

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
}