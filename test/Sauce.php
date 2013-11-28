<?php

require_once 'vendor/autoload.php';

class WebDriverDemo extends Sauce\Sausage\WebDriverTestCase
{
    public static $browsers = array(
        // // run FF15 on Windows 8 on Sauce
        // array(
            // 'browserName' => 'firefox',
            // 'desiredCapabilities' => array(
                // 'version' => '15',
                // 'platform' => 'Windows 2012',
            // )
        // ),
        // // run IE9 on Windows 7 on Sauce
        // array(
            // 'browserName' => 'internet explorer',
            // 'desiredCapabilities' => array(
                // 'version' => '9',
                // 'platform' => 'Windows 7',
            // )
        // ),
        // // run IE10 on Windows 8 on Sauce
        // array(
            // 'browserName' => 'internet explorer',
            // 'desiredCapabilities' => array(
                // 'version' => '10',
                // 'platform' => 'Windows 8',
            // )
        // ),
        // // run Opera 12 on Windows 7 on Sauce
        // array(
            // 'browserName' => 'opera',
            // 'desiredCapabilities' => array(
                // 'version' => '12',
                // 'platform' => 'Windows 7',
            // )
        // ),
        // // run Mobile Safari on iOS
        // array(
            // 'browserName' => '',
            // 'desiredCapabilities' => array(
                // 'app' => 'safari',
                // 'device' => 'iPhone Simulator',
                // 'version' => '6.1',
                // 'platform' => 'Mac 10.8',
            // )
        // ),
        // run Chrome on Linux on Sauce
        array(
            'browserName' => 'chrome',
            'desiredCapabilities' => array(
                'version' => '30',
                'platform' => 'Linux'
          )
        )
        // run Mobile Browser on Android 
        // array(
            // 'browserName' => 'Android',
            // 'desiredCapabilities' => array(
                // 'version' => '4.0',
                // 'platform' => 'Linux',
            // )
        // )
        
        // run Chrome locally
        //array(
            //'browserName' => 'chrome',
            //'local' => true,
            //'sessionStrategy' => 'shared'
        //)
    );

    public function setUp()
    {
         $caps = $this->getDesiredCapabilities();
         $caps['build'] = getenv ("TRAVIS_JOB_NUMBER");
         $caps['tunnel_id'] = getenv ("TRAVIS_JOB_NUMBER");
         $caps['name'] = "COPS ";
         $this->setDesiredCapabilities($caps);
         parent::setUp ();
    }
    
    public function setUpPage()
    {
        $this->url('http://cops-travis:8888/index.php');
    }
    
    public function string_to_ascii($string)
    {
        $ascii = NULL;
         
        for ($i = 0; $i < strlen($string); $i++)
        {
            $ascii += ord($string[$i]);
        }
         
        return mb_detect_encoding($string) . "X" . $ascii;
    }

    public function testTitle()
    {
        $driver = $this;
        $title_test = function($value) use ($driver) {
            $text = $driver->byXPath('//h1')->text ();
            return $text == $value;
        };

        $this->spinAssert("Home Title", $title_test, [ "COPS DEMO" ]);
        
        $author = $this->byXPath ('//h2[contains(text(), "Authors")]');
        $author->click ();
        
        $this->spinAssert("Author Title", $title_test, [ "AUTHORS" ]);
        
        $cog = $this->byId ("searchImage");
        // try {
            // $search = $this->byName ("query");
            // $this->fail ();
        // }
        // catch (Exception $e) {
        // }
        
        $search = $this->byName ("query");
        $this->assertFalse ($search->displayed ());
        
        $cog->click ();
        //$this->implicitWait(2000);
        
        $search = $this->byName ("query");
        $this->assertTrue ($search->displayed ());
    }
}
