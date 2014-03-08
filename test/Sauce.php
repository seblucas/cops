<?php

require_once 'vendor/autoload.php';

class Cops extends Sauce\Sausage\WebDriverTestCase
{
    public static $browsers = array(
        // run FF15 on Windows 8 on Sauce
        array(
            'browserName' => 'firefox',
            'desiredCapabilities' => array(
                'version' => '25',
                'platform' => 'Windows 8.1',
            )
        ),
        // run IE9 on Windows 7 on Sauce
        array(
            'browserName' => 'internet explorer',
            'desiredCapabilities' => array(
                'version' => '9',
                'platform' => 'Windows 7',
            )
        ),
        // run IE10 on Windows 8 on Sauce
        array(
            'browserName' => 'internet explorer',
            'desiredCapabilities' => array(
                'version' => '11',
                'platform' => 'Windows 8.1',
            )
        ),
        // run Opera 12 on Windows 7 on Sauce
        array(
            'browserName' => 'opera',
            'desiredCapabilities' => array(
                'version' => '12',
                'platform' => 'Windows 7',
            )
        ),
        // run Safari 7 on Maverick on Sauce
        array(
            'browserName' => 'safari',
            'desiredCapabilities' => array(
                'version' => '7',
                'platform' => 'OS X 10.9',
            )
        ),
        // run Mobile Safari on iOS
        array(
            'browserName' => '',
            'desiredCapabilities' => array(
                'app' => 'safari',
                'device' => 'iPhone Simulator',
                'version' => '6.1',
                'platform' => 'Mac 10.8',
            )
        ),
        // run Mobile Browser on Android
        array(
            'browserName' => 'Android',
            'desiredCapabilities' => array(
                'version' => '4.0',
                'platform' => 'Linux',
            )
        ),
        // run Chrome on Linux on Sauce
        array(
            'browserName' => 'chrome',
            'desiredCapabilities' => array(
                'version' => '30',
                'platform' => 'Linux'
          )
        )


        // run Chrome locally
        //array(
            //'browserName' => 'chrome',
            //'local' => true,
            //'sessionStrategy' => 'shared'
        //)
    );

    public function setUp()
    {
        if (isset ($_SERVER["TRAVIS_JOB_NUMBER"])) {
            $caps = $this->getDesiredCapabilities();
            $caps['build'] = getenv ("TRAVIS_JOB_NUMBER");
            $caps['idle-timeout'] = "180";
            $this->setDesiredCapabilities($caps);
        }
        parent::setUp ();
    }

    public function setUpPage()
    {
        if (isset ($_SERVER["TRAVIS_JOB_NUMBER"])) {
            $this->url('http://127.0.0.1:8888/index.php');
        } else {
            $this->url('http://cops-demo.slucas.fr/index.php');
        }

        $driver = $this;
        $title_test = function($value) use ($driver) {
            $text = $driver->byXPath('//h1')->text ();
            return $text == $value;
        };

        $this->spinAssert("Home Title", $title_test, [ "COPS DEMO" ]);
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

    // public function testTitle()
    // {
        // $driver = $this;
        // $title_test = function($value) use ($driver) {
            // $text = $driver->byXPath('//h1')->text ();
            // return $text == $value;
        // };

        // $author = $this->byXPath ('//h2[contains(text(), "Authors")]');
        // $author->click ();

        // $this->spinAssert("Author Title", $title_test, [ "AUTHORS" ]);
    // }

    // public function testCog()
    // {
        // $cog = $this->byId ("searchImage");

        // $search = $this->byName ("query");
        // $this->assertFalse ($search->displayed ());

        // $cog->click ();

        // $search = $this->byName ("query");
        // $this->assertTrue ($search->displayed ());
    // }

    public function testFilter()
    {
        $driver = $this;
        $title_test = function($value) use ($driver) {
            $text = $driver->byXPath('//h1')->text ();
            return $text == $value;
        };

        // Click on the wrench to enable tag filtering
        $this->byClassName ("icon-wrench")->click ();

        $this->byId ("html_tag_filter")->click ();

        // Go back to home screen
        $this->byClassName ("icon-home")->click ();

        $this->spinAssert("Home Title", $title_test, [ "COPS DEMO" ]);

        // Go on the recent page
        $author = $this->byXPath ('//h2[contains(text(), "Recent")]');
        $author->click ();

        $this->spinAssert("Recent book title", $title_test, [ "RECENT ADDITIONS" ]);

        // Click on the cog to show tag filters
        $cog = $this->byId ("searchImage");
        $cog->click ();
        sleep (1);
        // Filter on War & Military
        $filter = $this->byXPath ('//li[contains(text(), "War")]');
        $filter->click ();
        sleep (1);
        // Only one book
        $filtered = $this->elements ($this->using('css selector')->value('*[class="books"]'));
        $this->assertEquals (1, count($filtered));
        $filter->click ();
        sleep (1);
        // 13 book
        $filtered = $this->elements ($this->using('css selector')->value('*[class="books"]'));
        $this->assertEquals (13, count($filtered));
    }
}
