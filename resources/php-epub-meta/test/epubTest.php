<?php
use PHPUnit\Framework\TestCase;

class EPubTest extends TestCase
{
    protected $epub;

    protected function setUp(): void
    {
        // sometime I might have accidentally broken the test file
        if (filesize(realpath(dirname(__FILE__)) . '/test.epub') != 768780) {
            die('test.epub has wrong size, make sure it\'s unmodified');
        }

        // we work on a copy to test saving
        if (!copy(realpath(dirname(__FILE__)) . '/test.epub', realpath(dirname(__FILE__)) . '/test.copy.epub')) {
            die('failed to create copy of the test book');
        }

        $this->epub = new EPub(realpath(dirname(__FILE__)) . '/test.copy.epub');
    }

    public static function tearDownAfterClass(): void
    {
        unlink(realpath(dirname(__FILE__)) . '/test.copy.epub');
    }

    public function testAuthors()
    {
        // read curent value
        $this->assertEquals(
            $this->epub->Authors(),
            ['Shakespeare, William' => 'William Shakespeare']
        );

        // remove value with string
        $this->assertEquals(
            $this->epub->Authors(''),
            []
        );

        // set single value by String

        $this->assertEquals(
            $this->epub->Authors('John Doe'),
            ['John Doe' => 'John Doe']
        );

        // set single value by indexed array
        $this->assertEquals(
            $this->epub->Authors(['John Doe']),
            ['John Doe' => 'John Doe']
        );

        // remove value with array
        $this->assertEquals(
            $this->epub->Authors([]),
            []
        );

        // set single value by associative array
        $this->assertEquals(
            $this->epub->Authors(['Doe, John' => 'John Doe']),
            ['Doe, John' => 'John Doe']
        );

        // set multi value by string
        $this->assertEquals(
            $this->epub->Authors('John Doe, Jane Smith'),
            ['John Doe' => 'John Doe', 'Jane Smith' => 'Jane Smith']
        );

        // set multi value by indexed array
        $this->assertEquals(
            $this->epub->Authors(['John Doe', 'Jane Smith']),
            ['John Doe' => 'John Doe', 'Jane Smith' => 'Jane Smith']
        );

        // set multi value by associative  array
        $this->assertEquals(
            $this->epub->Authors(['Doe, John' => 'John Doe', 'Smith, Jane' => 'Jane Smith']),
            ['Doe, John' => 'John Doe', 'Smith, Jane' => 'Jane Smith']
        );

        // check escaping
        $this->assertEquals(
            $this->epub->Authors(['Doe, John&nbsp;' => 'John Doe&nbsp;']),
            ['Doe, John&nbsp;' => 'John Doe&nbsp;']
        );
    }

    public function testTitle()
    {
        // get current value
        $this->assertEquals(
            $this->epub->Title(),
            'Romeo and Juliet'
        );

        // delete current value
        $this->assertEquals(
            $this->epub->Title(''),
            ''
        );

        // get current value
        $this->assertEquals(
            $this->epub->Title(),
            ''
        );

        // set new value
        $this->assertEquals(
            $this->epub->Title('Foo Bar'),
            'Foo Bar'
        );

        // check escaping
        $this->assertEquals(
            $this->epub->Title('Foo&nbsp;Bar'),
            'Foo&nbsp;Bar'
        );
    }

    public function testSubject()
    {
        // get current values
        $this->assertEquals(
            $this->epub->Subjects(),
            ['Fiction','Drama','Romance']
        );

        // delete current values with String
        $this->assertEquals(
            $this->epub->Subjects(''),
            []
        );

        // set new values with String
        $this->assertEquals(
            $this->epub->Subjects('Fiction, Drama, Romance'),
            ['Fiction','Drama','Romance']
        );

        // delete current values with Array
        $this->assertEquals(
            $this->epub->Subjects([]),
            []
        );

        // set new values with array
        $this->assertEquals(
            $this->epub->Subjects(['Fiction','Drama','Romance']),
            ['Fiction','Drama','Romance']
        );

        // check escaping
        $this->assertEquals(
            $this->epub->Subjects(['Fiction','Drama&nbsp;','Romance']),
            ['Fiction','Drama&nbsp;','Romance']
        );
    }


    /*public function testCover(){
        // read current cover
        $cover = $this->epub->Cover2();
        $this->assertEquals($cover['mime'],'image/png');
        $this->assertEquals($cover['found'],'OPS/images/cover.png');
        $this->assertEquals(strlen($cover['data']), 657911);

        // // delete cover // Don't work anymore
        // $cover = $this->epub->Cover('');
        // $this->assertEquals($cover['mime'],'image/gif');
        // $this->assertEquals($cover['found'],false);
        // $this->assertEquals(strlen($cover['data']), 42);

        // // set new cover (will return a not-found as it's not yet saved)
        $cover = $this->epub->Cover2(realpath( dirname( __FILE__ ) ) . '/test.jpg','image/jpeg');
        // $this->assertEquals($cover['mime'],'image/jpeg');
        // $this->assertEquals($cover['found'],'OPS/php-epub-meta-cover.img');
        // $this->assertEquals(strlen($cover['data']), 0);

        // save
        $this->epub->save();
        //$this->epub = new EPub(realpath( dirname( __FILE__ ) ) . '/test.copy.epub');

        // read now changed cover
        $cover = $this->epub->Cover2();
        $this->assertEquals($cover['mime'],'image/jpeg');
        $this->assertEquals($cover['found'],'OPS/images/cover.png');
        $this->assertEquals(strlen($cover['data']), filesize(realpath( dirname( __FILE__ ) ) . '/test.jpg'));
    }*/
}
