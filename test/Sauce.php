<?php

require_once 'vendor/autoload.php';

class Cops extends Sauce\Sausage\WebDriverTestCase
{
    public static $browsers = array(
        // run FF15 on Windows 8 on Sauce
        array(
            'browserName' => 'firefox',
            'desiredCapabilities' => array(
                'version' => '28',
                'platform' => 'Windows 8.1',
            )
        ),
        // run IE11 on Windows 8 on Sauce
        array(
            'browserName' => 'internet explorer',
            'desiredCapabilities' => array(
                'version' => '11',
                'platform' => 'Windows 8.1',
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
            'browserName' => 'iphone',
            'desiredCapabilities' => array(
                'app' => 'safari',
                'device' => 'iPhone 6',
                'version' => '9.2',
                'platform' => 'OS X 10.10',
            )
        ),
        // run Mobile Browser on Android
        array(
            'browserName' => 'Android',
            'desiredCapabilities' => array(
                'version' => '5.1',
                'platform' => 'Linux',
            )
        ),
        // run Chrome on Linux on Sauce
        array(
            'browserName' => 'chrome',
            'desiredCapabilities' => array(
                'version' => '33',
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
            $caps['tunnel-identifier'] = getenv ("TRAVIS_JOB_NUMBER");
            $caps['idle-timeout'] = "180";
            $this->setDesiredCapabilities($caps);
        }
        parent::setUp ();
    }

    public function setUpPage()
    {
        if (isset ($_SERVER["TRAVIS_JOB_NUMBER"])) {
            $this->url('http://127.0.0.1:8080/index.php');
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

        $element_present = function ($using, $id) use ($driver) {
            $elements = $driver->elements ($driver->using($using)->value($id));
            return count($elements) == 1;
        };

        // Click on the wrench to enable tag filtering
        $this->spinWait ("", $element_present, [ "class name", 'icon-wrench']);
        $this->byClassName ("icon-wrench")->click ();

        $this->spinWait ("", $element_present, [ "id", "html_tag_filter"]);
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
        $this->assertEquals (14, count($filtered));
    }

    public function normalSearch ($src, $out)
    {
        $driver = $this;
        $title_test = function($value) use ($driver) {
            $text = $driver->byXPath('//h1')->text ();
            return $text == $value;
        };

        // Click on the cog to show the search
        $cog = $this->byId ("searchImage");
        $cog->click ();
        //sleep (1);

        // Focus the input and type
        $this->waitUntil(function () {
            if ($this->byName ("query")) {
                return true;
            }
            return null;
        }, 1000);
        $queryInput = $this->byName ("query");
        $queryInput->click ();
        $queryInput->value ($src);
        $queryInput->submit ();

        $this->spinAssert("Home Title", $title_test, [ "SEARCH RESULT FOR *" . $out . "*" ]);
    }

    public function testSearchWithoutAccentuatedCharacters()
    {
        $this->normalSearch ("ali", "ALI");
    }

    public function testSearchWithAccentuatedCharacters()
    {
        if ($this->getBrowser() == "Android") {
            $this->markTestIncomplete();
            return;
        }
        $this->normalSearch ("é", "É");
    }
}
