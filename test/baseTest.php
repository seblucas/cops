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
}