<?php
/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

require_once(dirname(__FILE__) . "/config_test.php");

class CustomColumnTest extends PHPUnit_Framework_TestCase
{
    public function testColumnType01()
    {
        global $config;

        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithCustomColumns/";
        $config['cops_calibre_custom_column'] = array("custom_01");
        Base::clearDb();

        $coltype = CustomColumnType::createByCustomID(8);

        $this->assertEquals($coltype, CustomColumnType::createByLookup("custom_01"));

        $this->assertEquals(8, $coltype->customId);
        $this->assertEquals("custom_01", $coltype->columnTitle);
        $this->assertEquals("text", $coltype->datatype);
        $this->assertEquals("CustomColumnTypeText", get_class($coltype));

        $this->assertCount(3, $coltype->getAllCustomValues());
        $this->assertEquals("?page=14&custom=8", $coltype->getUriAllCustoms());
        $this->assertEquals("cops:custom:8", $coltype->getAllCustomsId());
        $this->assertEquals("custom_01", $coltype->getTitle());
        $this->assertEquals("Custom column example 01 (text)", $coltype->getDatabaseDescription());
        $this->assertEquals("Custom column example 01 (text)", $coltype->getDescription());
        $this->assertEquals(true, $coltype->isSearchable());

        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithSomeBooks/";
        $config['cops_calibre_custom_column'] = array();
        Base::clearDb();
    }

    public function testColumnType01b()
    {
        global $config;

        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithCustomColumns/";
        $config['cops_calibre_custom_column'] = array("custom_01b");
        Base::clearDb();

        $coltype = CustomColumnType::createByCustomID(16);

        $this->assertEquals($coltype, CustomColumnType::createByLookup("custom_01b"));

        $this->assertEquals(16, $coltype->customId);
        $this->assertEquals("custom_01b", $coltype->columnTitle);
        $this->assertEquals("text", $coltype->datatype);
        $this->assertEquals("CustomColumnTypeText", get_class($coltype));

        $this->assertCount(3, $coltype->getAllCustomValues());
        $this->assertEquals("?page=14&custom=16", $coltype->getUriAllCustoms());
        $this->assertEquals("cops:custom:16", $coltype->getAllCustomsId());
        $this->assertEquals("custom_01b", $coltype->getTitle());
        $this->assertEquals(NULL, $coltype->getDatabaseDescription());
        $this->assertEquals("Custom column 'custom_01b'", $coltype->getDescription());
        $this->assertEquals(true, $coltype->isSearchable());

        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithSomeBooks/";
        $config['cops_calibre_custom_column'] = array();
        Base::clearDb();
    }

    public function testColumnType02()
    {
        global $config;

        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithCustomColumns/";
        $config['cops_calibre_custom_column'] = array("custom_02");
        Base::clearDb();

        $coltype = CustomColumnType::createByCustomID(6);

        $this->assertEquals($coltype, CustomColumnType::createByLookup("custom_02"));

        $this->assertEquals(6, $coltype->customId);
        $this->assertEquals("custom_02", $coltype->columnTitle);
        $this->assertEquals("text", $coltype->datatype);
        $this->assertEquals("CustomColumnTypeText", get_class($coltype));

        $this->assertCount(3, $coltype->getAllCustomValues());
        $this->assertEquals("?page=14&custom=6", $coltype->getUriAllCustoms());
        $this->assertEquals("cops:custom:6", $coltype->getAllCustomsId());
        $this->assertEquals("custom_02", $coltype->getTitle());
        $this->assertEquals("Custom column example 02 (csv)", $coltype->getDatabaseDescription());
        $this->assertEquals("Custom column example 02 (csv)", $coltype->getDescription());
        $this->assertEquals(true, $coltype->isSearchable());

        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithSomeBooks/";
        $config['cops_calibre_custom_column'] = array();
        Base::clearDb();
    }

    public function testColumnType03()
    {
        global $config;

        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithCustomColumns/";
        $config['cops_calibre_custom_column'] = array("custom_03");
        Base::clearDb();

        $coltype = CustomColumnType::createByCustomID(7);

        $this->assertEquals($coltype, CustomColumnType::createByLookup("custom_03"));

        $this->assertEquals(7, $coltype->customId);
        $this->assertEquals("custom_03", $coltype->columnTitle);
        $this->assertEquals("comments", $coltype->datatype);
        $this->assertEquals("CustomColumnTypeComment", get_class($coltype));

        $this->assertEquals("?page=14&custom=7", $coltype->getUriAllCustoms());
        $this->assertEquals("cops:custom:7", $coltype->getAllCustomsId());
        $this->assertEquals("custom_03", $coltype->getTitle());
        $this->assertEquals("Custom column example 03 (long_text)", $coltype->getDatabaseDescription());
        $this->assertEquals("Custom column example 03 (long_text)", $coltype->getDescription());
        $this->assertEquals(false, $coltype->isSearchable());

        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithSomeBooks/";
        $config['cops_calibre_custom_column'] = array();
        Base::clearDb();
    }

    public function testColumnType04()
    {
        global $config;

        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithCustomColumns/";
        $config['cops_calibre_custom_column'] = array("custom_04");
        Base::clearDb();

        $coltype = CustomColumnType::createByCustomID(4);

        $this->assertEquals($coltype, CustomColumnType::createByLookup("custom_04"));

        $this->assertEquals(4, $coltype->customId);
        $this->assertEquals("custom_04", $coltype->columnTitle);
        $this->assertEquals("series", $coltype->datatype);
        $this->assertEquals("CustomColumnTypeSeries", get_class($coltype));

        $this->assertCount(3, $coltype->getAllCustomValues());
        $this->assertEquals("?page=14&custom=4", $coltype->getUriAllCustoms());
        $this->assertEquals("cops:custom:4", $coltype->getAllCustomsId());
        $this->assertEquals("custom_04", $coltype->getTitle());
        $this->assertEquals("Custom column example 04 (series_text)", $coltype->getDatabaseDescription());
        $this->assertEquals("Alphabetical index of the 3 series", $coltype->getDescription());
        $this->assertEquals(true, $coltype->isSearchable());

        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithSomeBooks/";
        $config['cops_calibre_custom_column'] = array();
        Base::clearDb();
    }

    public function testColumnType05()
    {
        global $config;

        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithCustomColumns/";
        $config['cops_calibre_custom_column'] = array("custom_05");
        Base::clearDb();

        $coltype = CustomColumnType::createByCustomID(5);

        $this->assertEquals($coltype, CustomColumnType::createByLookup("custom_05"));

        $this->assertEquals(5, $coltype->customId);
        $this->assertEquals("custom_05", $coltype->columnTitle);
        $this->assertEquals("enumeration", $coltype->datatype);
        $this->assertEquals("CustomColumnTypeEnumeration", get_class($coltype));

        $this->assertCount(4, $coltype->getAllCustomValues());
        $this->assertEquals("?page=14&custom=5", $coltype->getUriAllCustoms());
        $this->assertEquals("cops:custom:5", $coltype->getAllCustomsId());
        $this->assertEquals("custom_05", $coltype->getTitle());
        $this->assertEquals("Custom column example 05 (enum)", $coltype->getDatabaseDescription());
        $this->assertEquals("Alphabetical index of the 4 values", $coltype->getDescription());
        $this->assertEquals(true, $coltype->isSearchable());

        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithSomeBooks/";
        $config['cops_calibre_custom_column'] = array();
        Base::clearDb();
    }

    public function testColumnType06()
    {
        global $config;

        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithCustomColumns/";
        $config['cops_calibre_custom_column'] = array("custom_06");
        Base::clearDb();

        $coltype = CustomColumnType::createByCustomID(12);

        $this->assertEquals($coltype, CustomColumnType::createByLookup("custom_06"));

        $this->assertEquals(12, $coltype->customId);
        $this->assertEquals("custom_06", $coltype->columnTitle);
        $this->assertEquals("datetime", $coltype->datatype);
        $this->assertEquals("CustomColumnTypeDate", get_class($coltype));

        $this->assertCount(5, $coltype->getAllCustomValues());
        $this->assertEquals("?page=14&custom=12", $coltype->getUriAllCustoms());
        $this->assertEquals("cops:custom:12", $coltype->getAllCustomsId());
        $this->assertEquals("custom_06", $coltype->getTitle());
        $this->assertEquals("Custom column example 06 (date)", $coltype->getDatabaseDescription());
        $this->assertEquals("Custom column example 06 (date)", $coltype->getDescription());
        $this->assertEquals(true, $coltype->isSearchable());

        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithSomeBooks/";
        $config['cops_calibre_custom_column'] = array();
        Base::clearDb();
    }

    public function testColumnType07()
    {
        global $config;

        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithCustomColumns/";
        $config['cops_calibre_custom_column'] = array("custom_07");
        Base::clearDb();

        $coltype = CustomColumnType::createByCustomID(14);

        $this->assertEquals($coltype, CustomColumnType::createByLookup("custom_07"));

        $this->assertEquals(14, $coltype->customId);
        $this->assertEquals("custom_07", $coltype->columnTitle);
        $this->assertEquals("float", $coltype->datatype);
        $this->assertEquals("CustomColumnTypeFloat", get_class($coltype));

        $this->assertCount(6, $coltype->getAllCustomValues());
        $this->assertEquals("?page=14&custom=14", $coltype->getUriAllCustoms());
        $this->assertEquals("cops:custom:14", $coltype->getAllCustomsId());
        $this->assertEquals("custom_07", $coltype->getTitle());
        $this->assertEquals("Custom column example 07 (float)", $coltype->getDatabaseDescription());
        $this->assertEquals("Custom column example 07 (float)", $coltype->getDescription());
        $this->assertEquals(true, $coltype->isSearchable());

        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithSomeBooks/";
        $config['cops_calibre_custom_column'] = array();
        Base::clearDb();
    }

    public function testColumnType08()
    {
        global $config;

        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithCustomColumns/";
        $config['cops_calibre_custom_column'] = array("custom_08");
        Base::clearDb();

        $coltype = CustomColumnType::createByCustomID(10);

        $this->assertEquals($coltype, CustomColumnType::createByLookup("custom_08"));

        $this->assertEquals(10, $coltype->customId);
        $this->assertEquals("custom_08", $coltype->columnTitle);
        $this->assertEquals("int", $coltype->datatype);
        $this->assertEquals("CustomColumnTypeInteger", get_class($coltype));

        $this->assertCount(4, $coltype->getAllCustomValues());
        $this->assertEquals("?page=14&custom=10", $coltype->getUriAllCustoms());
        $this->assertEquals("cops:custom:10", $coltype->getAllCustomsId());
        $this->assertEquals("custom_08", $coltype->getTitle());
        $this->assertEquals("Custom column example 08 (int)", $coltype->getDatabaseDescription());
        $this->assertEquals("Custom column example 08 (int)", $coltype->getDescription());
        $this->assertEquals(true, $coltype->isSearchable());

        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithSomeBooks/";
        $config['cops_calibre_custom_column'] = array();
        Base::clearDb();
    }

    public function testColumnType09()
    {
        global $config;

        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithCustomColumns/";
        $config['cops_calibre_custom_column'] = array("custom_09");
        Base::clearDb();

        $coltype = CustomColumnType::createByCustomID(9);

        $this->assertEquals($coltype, CustomColumnType::createByLookup("custom_09"));

        $this->assertEquals(9, $coltype->customId);
        $this->assertEquals("custom_09", $coltype->columnTitle);
        $this->assertEquals("rating", $coltype->datatype);
        $this->assertEquals("CustomColumnTypeRating", get_class($coltype));

        $this->assertCount(6, $coltype->getAllCustomValues());
        $this->assertEquals("?page=14&custom=9", $coltype->getUriAllCustoms());
        $this->assertEquals("cops:custom:9", $coltype->getAllCustomsId());
        $this->assertEquals("custom_09", $coltype->getTitle());
        $this->assertEquals("Custom column example 09 (rating)", $coltype->getDatabaseDescription());
        $this->assertEquals("Index of ratings", $coltype->getDescription());
        $this->assertEquals(true, $coltype->isSearchable());

        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithSomeBooks/";
        $config['cops_calibre_custom_column'] = array();
        Base::clearDb();
    }

    public function testColumnType10()
    {
        global $config;

        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithCustomColumns/";
        $config['cops_calibre_custom_column'] = array("custom_10");
        Base::clearDb();

        $coltype = CustomColumnType::createByCustomID(11);

        $this->assertEquals($coltype, CustomColumnType::createByLookup("custom_10"));

        $this->assertEquals(11, $coltype->customId);
        $this->assertEquals("custom_10", $coltype->columnTitle);
        $this->assertEquals("bool", $coltype->datatype);
        $this->assertEquals("CustomColumnTypeBool", get_class($coltype));

        $this->assertCount(3, $coltype->getAllCustomValues());
        $this->assertEquals("?page=14&custom=11", $coltype->getUriAllCustoms());
        $this->assertEquals("cops:custom:11", $coltype->getAllCustomsId());
        $this->assertEquals("custom_10", $coltype->getTitle());
        $this->assertEquals("Custom column example 10 (bool)", $coltype->getDatabaseDescription());
        $this->assertEquals("Index of a boolean value", $coltype->getDescription());
        $this->assertEquals(true, $coltype->isSearchable());

        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithSomeBooks/";
        $config['cops_calibre_custom_column'] = array();
        Base::clearDb();
    }

    public function testColumnType11()
    {
        global $config;

        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithCustomColumns/";
        $config['cops_calibre_custom_column'] = array("custom_11");
        Base::clearDb();

        $coltype = CustomColumnType::createByCustomID(15);

        $this->assertEquals($coltype, CustomColumnType::createByLookup("custom_11"));

        $this->assertEquals(NULL, $coltype);

        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithSomeBooks/";
        $config['cops_calibre_custom_column'] = array();
        Base::clearDb();
    }

    public function testColumnType12()
    {
        global $config;

        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithCustomColumns/";
        $config['cops_calibre_custom_column'] = array("custom_12");
        Base::clearDb();

        $coltype = CustomColumnType::createByCustomID(13);

        $this->assertEquals($coltype, CustomColumnType::createByLookup("custom_12"));

        $this->assertEquals(NULL, $coltype);

        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithSomeBooks/";
        $config['cops_calibre_custom_column'] = array();
        Base::clearDb();
    }

    public function testInvalidColumn1()
    {
        global $config;

        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithCustomColumns/";
        $config['cops_calibre_custom_column'] = array("custom_12");
        Base::clearDb();

        $catch = false;
        try {
            CustomColumnType::createByCustomID(999);
        } catch (Exception $e) {
            $catch = true;
        }

        $this->assertTrue($catch);

        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithSomeBooks/";
        $config['cops_calibre_custom_column'] = array();
        Base::clearDb();
    }

    public function testInvalidColumn2()
    {
        global $config;

        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithCustomColumns/";
        $config['cops_calibre_custom_column'] = array("custom_12");
        Base::clearDb();

        $coltype = CustomColumnType::createByLookup("__ERR__");

        $this->assertEquals(NULL, $coltype);

        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithSomeBooks/";
        $config['cops_calibre_custom_column'] = array();
        Base::clearDb();
    }

    public function testIndexTypeAll()
    {
        global $config;

        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithCustomColumns/";
        $config['cops_calibre_custom_column'] = array("custom_01", "custom_02", "custom_03", "custom_04", "custom_05", "custom_06", "custom_07", "custom_08", "custom_09", "custom_10");
        Base::clearDb();

        $currentPage = Page::getPage(Base::PAGE_INDEX, NULL, NULL, "1");
        $currentPage->InitializeContent();

        $this->assertCount(15, $currentPage->entryArray); // Authors, Series, Publishers, Languages, custom, All, Recent
        $this->assertEquals("custom_01", $currentPage->entryArray[ 4]->title);
        $this->assertEquals("custom_02", $currentPage->entryArray[ 5]->title);
        $this->assertEquals("custom_04", $currentPage->entryArray[ 6]->title);
        $this->assertEquals("custom_05", $currentPage->entryArray[ 7]->title);
        $this->assertEquals("custom_06", $currentPage->entryArray[ 8]->title);
        $this->assertEquals("custom_07", $currentPage->entryArray[ 9]->title);
        $this->assertEquals("custom_08", $currentPage->entryArray[10]->title);
        $this->assertEquals("custom_09", $currentPage->entryArray[11]->title);
        $this->assertEquals("custom_10", $currentPage->entryArray[12]->title);

        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithSomeBooks/";
        $config['cops_calibre_custom_column'] = array();
        Base::clearDb();
    }

    public function testIndexType01()
    {
        global $config;

        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithCustomColumns/";
        $config['cops_calibre_custom_column'] = array("custom_01");
        Base::clearDb();

        $currentPage = Page::getPage(Base::PAGE_INDEX, NULL, NULL, "1");
        $currentPage->InitializeContent();

        $this->assertCount(7, $currentPage->entryArray); // Authors, Series, Publishers, Languages, custom, All, Recent
        $this->assertEquals("custom_01", $currentPage->entryArray[4]->title);
        $this->assertEquals("cops:custom:8", $currentPage->entryArray[4]->id);
        $this->assertEquals("Custom column example 01 (text)", $currentPage->entryArray[4]->content);
        $this->assertEquals(3, $currentPage->entryArray[4]->numberOfElement);
        $this->assertEquals("text", $currentPage->entryArray[4]->contentType);
        $this->assertEquals($currentPage->entryArray[4], CustomColumnType::createByCustomID(8)->getCount());

        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithSomeBooks/";
        $config['cops_calibre_custom_column'] = array();
        Base::clearDb();
    }

    public function testIndexType02()
    {
        global $config;

        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithCustomColumns/";
        $config['cops_calibre_custom_column'] = array("custom_02");
        Base::clearDb();

        $currentPage = Page::getPage(Base::PAGE_INDEX, NULL, NULL, "1");
        $currentPage->InitializeContent();

        $this->assertCount(7, $currentPage->entryArray); // Authors, Series, Publishers, Languages, custom, All, Recent
        $this->assertEquals("custom_02", $currentPage->entryArray[4]->title);
        $this->assertEquals("cops:custom:6", $currentPage->entryArray[4]->id);
        $this->assertEquals("Custom column example 02 (csv)", $currentPage->entryArray[4]->content);
        $this->assertEquals(3, $currentPage->entryArray[4]->numberOfElement);
        $this->assertEquals("text", $currentPage->entryArray[4]->contentType);
        $this->assertEquals($currentPage->entryArray[4], CustomColumnType::createByCustomID(6)->getCount());

        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithSomeBooks/";
        $config['cops_calibre_custom_column'] = array();
        Base::clearDb();
    }

    public function testIndexType03()
    {
        global $config;

        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithCustomColumns/";
        $config['cops_calibre_custom_column'] = array("custom_03");
        Base::clearDb();

        $currentPage = Page::getPage(Base::PAGE_INDEX, NULL, NULL, "1");
        $currentPage->InitializeContent();

        $this->assertCount(6, $currentPage->entryArray); // Authors, Series, Publishers, Languages, All, Recent

        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithSomeBooks/";
        $config['cops_calibre_custom_column'] = array();
        Base::clearDb();
    }

    public function testIndexType04()
    {
        global $config;

        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithCustomColumns/";
        $config['cops_calibre_custom_column'] = array("custom_04");
        Base::clearDb();

        $currentPage = Page::getPage(Base::PAGE_INDEX, NULL, NULL, "1");
        $currentPage->InitializeContent();

        $this->assertCount(7, $currentPage->entryArray); // Authors, Series, Publishers, Languages, custom, All, Recent
        $this->assertEquals("custom_04", $currentPage->entryArray[4]->title);
        $this->assertEquals("cops:custom:4", $currentPage->entryArray[4]->id);
        $this->assertEquals("Alphabetical index of the 3 series", $currentPage->entryArray[4]->content);
        $this->assertEquals(3, $currentPage->entryArray[4]->numberOfElement);
        $this->assertEquals("series", $currentPage->entryArray[4]->contentType);
        $this->assertEquals($currentPage->entryArray[4], CustomColumnType::createByCustomID(4)->getCount());

        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithSomeBooks/";
        $config['cops_calibre_custom_column'] = array();
        Base::clearDb();
    }

    public function testIndexType05()
    {
        global $config;

        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithCustomColumns/";
        $config['cops_calibre_custom_column'] = array("custom_05");
        Base::clearDb();

        $currentPage = Page::getPage(Base::PAGE_INDEX, NULL, NULL, "1");
        $currentPage->InitializeContent();

        $this->assertCount(7, $currentPage->entryArray); // Authors, Series, Publishers, Languages, custom, All, Recent
        $this->assertEquals("custom_05", $currentPage->entryArray[4]->title);
        $this->assertEquals("cops:custom:5", $currentPage->entryArray[4]->id);
        $this->assertEquals("Alphabetical index of the 4 values", $currentPage->entryArray[4]->content);
        $this->assertEquals(4, $currentPage->entryArray[4]->numberOfElement);
        $this->assertEquals("enumeration", $currentPage->entryArray[4]->contentType);
        $this->assertEquals($currentPage->entryArray[4], CustomColumnType::createByCustomID(5)->getCount());

        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithSomeBooks/";
        $config['cops_calibre_custom_column'] = array();
        Base::clearDb();
    }

    public function testIndexType06()
    {
        global $config;

        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithCustomColumns/";
        $config['cops_calibre_custom_column'] = array("custom_06");
        Base::clearDb();

        $currentPage = Page::getPage(Base::PAGE_INDEX, NULL, NULL, "1");
        $currentPage->InitializeContent();

        $this->assertCount(7, $currentPage->entryArray); // Authors, Series, Publishers, Languages, custom, All, Recent
        $this->assertEquals("custom_06", $currentPage->entryArray[4]->title);
        $this->assertEquals("cops:custom:12", $currentPage->entryArray[4]->id);
        $this->assertEquals("Custom column example 06 (date)", $currentPage->entryArray[4]->content);
        $this->assertEquals(5, $currentPage->entryArray[4]->numberOfElement);
        $this->assertEquals("datetime", $currentPage->entryArray[4]->contentType);
        $this->assertEquals($currentPage->entryArray[4], CustomColumnType::createByCustomID(12)->getCount());

        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithSomeBooks/";
        $config['cops_calibre_custom_column'] = array();
        Base::clearDb();
    }

    public function testIndexType07()
    {
        global $config;

        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithCustomColumns/";
        $config['cops_calibre_custom_column'] = array("custom_07");
        Base::clearDb();

        $currentPage = Page::getPage(Base::PAGE_INDEX, NULL, NULL, "1");
        $currentPage->InitializeContent();

        $this->assertCount(7, $currentPage->entryArray); // Authors, Series, Publishers, Languages, custom, All, Recent
        $this->assertEquals("custom_07", $currentPage->entryArray[4]->title);
        $this->assertEquals("cops:custom:14", $currentPage->entryArray[4]->id);
        $this->assertEquals("Custom column example 07 (float)", $currentPage->entryArray[4]->content);
        $this->assertEquals(6, $currentPage->entryArray[4]->numberOfElement);
        $this->assertEquals("float", $currentPage->entryArray[4]->contentType);
        $this->assertEquals($currentPage->entryArray[4], CustomColumnType::createByCustomID(14)->getCount());

        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithSomeBooks/";
        $config['cops_calibre_custom_column'] = array();
        Base::clearDb();
    }

    public function testIndexType08()
    {
        global $config;

        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithCustomColumns/";
        $config['cops_calibre_custom_column'] = array("custom_08");
        Base::clearDb();

        $currentPage = Page::getPage(Base::PAGE_INDEX, NULL, NULL, "1");
        $currentPage->InitializeContent();

        $this->assertCount(7, $currentPage->entryArray); // Authors, Series, Publishers, Languages, custom, All, Recent
        $this->assertEquals("custom_08", $currentPage->entryArray[4]->title);
        $this->assertEquals("cops:custom:10", $currentPage->entryArray[4]->id);
        $this->assertEquals("Custom column example 08 (int)", $currentPage->entryArray[4]->content);
        $this->assertEquals(4, $currentPage->entryArray[4]->numberOfElement);
        $this->assertEquals("int", $currentPage->entryArray[4]->contentType);
        $this->assertEquals($currentPage->entryArray[4], CustomColumnType::createByCustomID(10)->getCount());

        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithSomeBooks/";
        $config['cops_calibre_custom_column'] = array();
        Base::clearDb();
    }

    public function testIndexType09()
    {
        global $config;

        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithCustomColumns/";
        $config['cops_calibre_custom_column'] = array("custom_09");
        Base::clearDb();

        $currentPage = Page::getPage(Base::PAGE_INDEX, NULL, NULL, "1");
        $currentPage->InitializeContent();

        $this->assertCount(7, $currentPage->entryArray); // Authors, Series, Publishers, Languages, custom, All, Recent
        $this->assertEquals("custom_09", $currentPage->entryArray[4]->title);
        $this->assertEquals("cops:custom:9", $currentPage->entryArray[4]->id);
        $this->assertEquals("Index of ratings", $currentPage->entryArray[4]->content);
        $this->assertEquals(6, $currentPage->entryArray[4]->numberOfElement);
        $this->assertEquals("rating", $currentPage->entryArray[4]->contentType);
        $this->assertEquals($currentPage->entryArray[4], CustomColumnType::createByCustomID(9)->getCount());

        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithSomeBooks/";
        $config['cops_calibre_custom_column'] = array();
        Base::clearDb();
    }

    public function testIndexType10()
    {
        global $config;

        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithCustomColumns/";
        $config['cops_calibre_custom_column'] = array("custom_10");
        Base::clearDb();

        $currentPage = Page::getPage(Base::PAGE_INDEX, NULL, NULL, "1");
        $currentPage->InitializeContent();

        $this->assertCount(7, $currentPage->entryArray); // Authors, Series, Publishers, Languages, custom, All, Recent
        $this->assertEquals("custom_10", $currentPage->entryArray[4]->title);
        $this->assertEquals("cops:custom:11", $currentPage->entryArray[4]->id);
        $this->assertEquals("Index of a boolean value", $currentPage->entryArray[4]->content);
        $this->assertEquals(3, $currentPage->entryArray[4]->numberOfElement);
        $this->assertEquals("bool", $currentPage->entryArray[4]->contentType);
        $this->assertEquals($currentPage->entryArray[4], CustomColumnType::createByCustomID(11)->getCount());

        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithSomeBooks/";
        $config['cops_calibre_custom_column'] = array();
        Base::clearDb();
    }

    public function testAllCustomsType01()
    {
        global $config;

        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithCustomColumns/";
        $_GET["custom"] = "8";
        Base::clearDb();
        $currentPage = Page::getPage(Base::PAGE_ALL_CUSTOMS, NULL, NULL, "1");
        $currentPage->InitializeContent();


        $this->assertEquals("custom_01", $currentPage->title);
        $this->assertCount(3, $currentPage->entryArray);
        $this->assertEquals("cops:custom:8:3", $currentPage->entryArray[0]->id);
        $this->assertEquals("cops:custom:8:1", $currentPage->entryArray[1]->id);
        $this->assertEquals("cops:custom:8:2", $currentPage->entryArray[2]->id);


        $_GET["custom"] = NULL;
        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithSomeBooks/";
        Base::clearDb();
    }

    public function testAllCustomsType02()
    {
        global $config;

        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithCustomColumns/";
        $_GET["custom"] = "6";
        Base::clearDb();
        $currentPage = Page::getPage(Base::PAGE_ALL_CUSTOMS, NULL, NULL, "1");
        $currentPage->InitializeContent();


        $this->assertEquals("custom_02", $currentPage->title);
        $this->assertCount(3, $currentPage->entryArray);
        $this->assertEquals("cops:custom:6:1", $currentPage->entryArray[0]->id);
        $this->assertEquals("cops:custom:6:2", $currentPage->entryArray[1]->id);
        $this->assertEquals("cops:custom:6:3", $currentPage->entryArray[2]->id);


        $_GET["custom"] = NULL;
        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithSomeBooks/";
        Base::clearDb();
    }

    public function testAllCustomsType04()
    {
        global $config;

        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithCustomColumns/";
        $_GET["custom"] = "4";
        Base::clearDb();
        $currentPage = Page::getPage(Base::PAGE_ALL_CUSTOMS, NULL, NULL, "1");
        $currentPage->InitializeContent();


        $this->assertEquals("custom_04", $currentPage->title);
        $this->assertCount(3, $currentPage->entryArray);
        $this->assertEquals("cops:custom:4:4", $currentPage->entryArray[0]->id);
        $this->assertEquals("cops:custom:4:5", $currentPage->entryArray[1]->id);
        $this->assertEquals("cops:custom:4:6", $currentPage->entryArray[2]->id);


        $_GET["custom"] = NULL;
        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithSomeBooks/";
        Base::clearDb();
    }

    public function testAllCustomsType05()
    {
        global $config;

        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithCustomColumns/";
        $_GET["custom"] = "5";
        Base::clearDb();
        $currentPage = Page::getPage(Base::PAGE_ALL_CUSTOMS, NULL, NULL, "1");
        $currentPage->InitializeContent();


        $this->assertEquals("custom_05", $currentPage->title);
        $this->assertCount(4, $currentPage->entryArray);
        $this->assertEquals("cops:custom:5:3", $currentPage->entryArray[0]->id);
        $this->assertEquals("cops:custom:5:4", $currentPage->entryArray[1]->id);
        $this->assertEquals("cops:custom:5:5", $currentPage->entryArray[2]->id);
        $this->assertEquals("cops:custom:5:6", $currentPage->entryArray[3]->id);


        $_GET["custom"] = NULL;
        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithSomeBooks/";
        Base::clearDb();
    }

    public function testAllCustomsType06()
    {
        global $config;

        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithCustomColumns/";
        $_GET["custom"] = "12";
        Base::clearDb();
        $currentPage = Page::getPage(Base::PAGE_ALL_CUSTOMS, NULL, NULL, "1");
        $currentPage->InitializeContent();


        $this->assertEquals("custom_06", $currentPage->title);
        $this->assertCount(5, $currentPage->entryArray);
        $this->assertEquals("cops:custom:12:2000-01-01", $currentPage->entryArray[0]->id);
        $this->assertEquals("cops:custom:12:2000-01-02", $currentPage->entryArray[1]->id);
        $this->assertEquals("cops:custom:12:2000-01-03", $currentPage->entryArray[2]->id);
        $this->assertEquals("cops:custom:12:2016-04-20", $currentPage->entryArray[3]->id);
        $this->assertEquals("cops:custom:12:2016-04-24", $currentPage->entryArray[4]->id);


        $_GET["custom"] = NULL;
        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithSomeBooks/";
        Base::clearDb();
    }

    public function testAllCustomsType07()
    {
        global $config;

        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithCustomColumns/";
        $_GET["custom"] = "14";
        Base::clearDb();
        $currentPage = Page::getPage(Base::PAGE_ALL_CUSTOMS, NULL, NULL, "1");
        $currentPage->InitializeContent();


        $this->assertEquals("custom_07", $currentPage->title);
        $this->assertCount(6, $currentPage->entryArray);
        $this->assertEquals("cops:custom:14:-99.0", $currentPage->entryArray[0]->id);
        $this->assertEquals("cops:custom:14:0.0", $currentPage->entryArray[1]->id);
        $this->assertEquals("cops:custom:14:0.1", $currentPage->entryArray[2]->id);
        $this->assertEquals("cops:custom:14:0.2", $currentPage->entryArray[3]->id);
        $this->assertEquals("cops:custom:14:11.0", $currentPage->entryArray[4]->id);
        $this->assertEquals("cops:custom:14:100000.0", $currentPage->entryArray[5]->id);


        $_GET["custom"] = NULL;
        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithSomeBooks/";
        Base::clearDb();
    }

    public function testAllCustomsType08()
    {
        global $config;

        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithCustomColumns/";
        $_GET["custom"] = "10";
        Base::clearDb();
        $currentPage = Page::getPage(Base::PAGE_ALL_CUSTOMS, NULL, NULL, "1");
        $currentPage->InitializeContent();


        $this->assertEquals("custom_08", $currentPage->title);
        $this->assertCount(4, $currentPage->entryArray);
        $this->assertEquals("cops:custom:10:-2", $currentPage->entryArray[0]->id);
        $this->assertEquals("cops:custom:10:-1", $currentPage->entryArray[1]->id);
        $this->assertEquals("cops:custom:10:1",  $currentPage->entryArray[2]->id);
        $this->assertEquals("cops:custom:10:2",  $currentPage->entryArray[3]->id);


        $_GET["custom"] = NULL;
        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithSomeBooks/";
        Base::clearDb();
    }

    public function testAllCustomsType09()
    {
        global $config;

        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithCustomColumns/";
        $_GET["custom"] = "9";
        Base::clearDb();
        $currentPage = Page::getPage(Base::PAGE_ALL_CUSTOMS, NULL, NULL, "1");
        $currentPage->InitializeContent();


        $this->assertEquals("custom_09", $currentPage->title);
        $this->assertCount(6, $currentPage->entryArray);
        $this->assertEquals("cops:custom:9:0",  $currentPage->entryArray[0]->id);
        $this->assertEquals("cops:custom:9:2",  $currentPage->entryArray[1]->id);
        $this->assertEquals("cops:custom:9:4",  $currentPage->entryArray[2]->id);
        $this->assertEquals("cops:custom:9:6",  $currentPage->entryArray[3]->id);
        $this->assertEquals("cops:custom:9:8",  $currentPage->entryArray[4]->id);
        $this->assertEquals("cops:custom:9:10", $currentPage->entryArray[5]->id);


        $_GET["custom"] = NULL;
        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithSomeBooks/";
        Base::clearDb();
    }

    public function testAllCustomsType10()
    {
        global $config;

        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithCustomColumns/";
        $_GET["custom"] = "11";
        Base::clearDb();
        $currentPage = Page::getPage(Base::PAGE_ALL_CUSTOMS, NULL, NULL, "1");
        $currentPage->InitializeContent();


        $this->assertEquals("custom_10", $currentPage->title);
        $this->assertCount(3, $currentPage->entryArray);
        $this->assertEquals("cops:custom:11:-1", $currentPage->entryArray[0]->id);
        $this->assertEquals("cops:custom:11:0",  $currentPage->entryArray[1]->id);
        $this->assertEquals("cops:custom:11:1",  $currentPage->entryArray[2]->id);


        $_GET["custom"] = NULL;
        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithSomeBooks/";
        Base::clearDb();
    }

    public function testDetailTypeAllEntryIDs()
    {
        global $config;

        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithCustomColumns/";
        $_GET["custom"] = "11";
        $config['cops_calibre_custom_column'] = array("custom_01", "custom_02", "custom_03", "custom_04", "custom_05", "custom_06", "custom_07", "custom_08", "custom_09", "custom_10", "custom_11");
        Base::clearDb();

        $currentPage = Page::getPage(Base::PAGE_CUSTOM_DETAIL, "0", NULL, "1");
        $currentPage->InitializeContent();

        /* @var EntryBook[] $entries */
        $entries = $currentPage->entryArray;

        $this->assertCount(6, $entries);

        $customcolumnValues = $entries[0]->book->getCustomColumnValues($config['cops_calibre_custom_column']);

        $this->assertCount(10, $customcolumnValues);

        $this->assertEquals("cops:custom:8:1", $customcolumnValues[0]->getEntryId());
        $this->assertEquals("cops:custom:6:3", $customcolumnValues[1]->getEntryId());
        $this->assertEquals("cops:custom:7:3", $customcolumnValues[2]->getEntryId());
        $this->assertEquals("cops:custom:4:4", $customcolumnValues[3]->getEntryId());
        $this->assertEquals("cops:custom:5:6", $customcolumnValues[4]->getEntryId());
        $this->assertEquals("cops:custom:12:2016-04-24", $customcolumnValues[5]->getEntryId());
        $this->assertEquals("cops:custom:14:11.0", $customcolumnValues[6]->getEntryId());
        $this->assertEquals("cops:custom:10:-2", $customcolumnValues[7]->getEntryId());
        $this->assertEquals("cops:custom:9:2", $customcolumnValues[8]->getEntryId());
        $this->assertEquals("cops:custom:11:0", $customcolumnValues[9]->getEntryId());

        $_GET["custom"] = NULL;
        $config['cops_calibre_custom_column'] = array();
        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithSomeBooks/";
        Base::clearDb();
    }

    public function testRenderCustomColumns()
    {
        global $config;

        $_SERVER["HTTP_USER_AGENT"] = "Firefox";
        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithCustomColumns/";
        $_GET["custom"] = "11";
        $config['cops_calibre_custom_column'] = array("custom_01", "custom_02", "custom_03", "custom_04", "custom_05", "custom_06", "custom_07", "custom_08", "custom_09", "custom_10", "custom_11");
        $config['cops_calibre_custom_column_list'] = array("custom_01", "custom_02", "custom_03", "custom_04", "custom_05", "custom_06", "custom_07", "custom_08", "custom_09", "custom_10", "custom_11");
        $config['cops_calibre_custom_column_preview'] = array("custom_01", "custom_02", "custom_03", "custom_04", "custom_05", "custom_06", "custom_07", "custom_08", "custom_09", "custom_10", "custom_11");
        Base::clearDb();


        $book = Book::getBookById(223);
        $json = JSONRenderer::getBookContentArray($book);

        /* @var CustomColumn[] $custom */
        $custom = $json["customcolumns_list"];

        $this->assertEquals("custom_01", $custom[0]['customColumnType']['columnTitle']);
        $this->assertEquals("text_2", $custom[0]['htmlvalue']);

        $this->assertEquals("custom_02", $custom[1]['customColumnType']['columnTitle']);
        $this->assertEquals("a", $custom[1]['htmlvalue']);

        $this->assertEquals("custom_03", $custom[2]['customColumnType']['columnTitle']);
        $this->assertEquals("<div>Not Set</div>", $custom[2]['htmlvalue']);

        $this->assertEquals("custom_04", $custom[3]['customColumnType']['columnTitle']);
        $this->assertEquals("", $custom[3]['htmlvalue']);

        $this->assertEquals("custom_05", $custom[4]['customColumnType']['columnTitle']);
        $this->assertEquals("val05", $custom[4]['htmlvalue']);

        $this->assertEquals("custom_06", $custom[5]['customColumnType']['columnTitle']);
        $this->assertEquals("Not Set", $custom[5]['htmlvalue']);

        $this->assertEquals("custom_07", $custom[6]['customColumnType']['columnTitle']);
        $this->assertEquals("100000.0", $custom[6]['htmlvalue']);

        $this->assertEquals("custom_08", $custom[7]['customColumnType']['columnTitle']);
        $this->assertEquals("Not Set", $custom[7]['htmlvalue']);

        $this->assertEquals("custom_09", $custom[8]['customColumnType']['columnTitle']);
        $this->assertEquals("Not Set", $custom[8]['htmlvalue']);

        $this->assertEquals("custom_10", $custom[9]['customColumnType']['columnTitle']);
        $this->assertEquals("No", $custom[9]['htmlvalue']);

        $_SERVER["HTTP_USER_AGENT"] = "";
        $_GET["custom"] = NULL;
        $config['cops_calibre_custom_column'] = array();
        $config['cops_calibre_custom_column_list'] = array();
        $config['cops_calibre_custom_column_preview'] = array();
        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithSomeBooks/";
        Base::clearDb();
    }

    public function testQueries()
    {
        global $config;

        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithCustomColumns/";
        $_GET["custom"] = "11";
        $config['cops_calibre_custom_column'] = array("custom_01", "custom_02", "custom_03", "custom_04", "custom_05", "custom_06", "custom_07", "custom_08", "custom_09", "custom_10", "custom_11");
        Base::clearDb();


        list($query, $params) = CustomColumnType::createByLookup("custom_01")->getCustom("1")->getQuery();
        Book::getEntryArray($query, $params, 1);

        list($query, $params) = CustomColumnType::createByLookup("custom_02")->getCustom("3")->getQuery();
        Book::getEntryArray($query, $params, 1);

        list($query, $params) = CustomColumnType::createByLookup("custom_03")->getCustom("3")->getQuery();
        Book::getEntryArray($query, $params, 1);

        list($query, $params) = CustomColumnType::createByLookup("custom_04")->getCustom("4")->getQuery();
        Book::getEntryArray($query, $params, 1);

        list($query, $params) = CustomColumnType::createByLookup("custom_05")->getCustom("6")->getQuery();
        Book::getEntryArray($query, $params, 1);

        list($query, $params) = CustomColumnType::createByLookup("custom_06")->getCustom("2016-04-24")->getQuery();
        Book::getEntryArray($query, $params, 1);

        list($query, $params) = CustomColumnType::createByLookup("custom_07")->getCustom("11.0")->getQuery();
        Book::getEntryArray($query, $params, 1);

        list($query, $params) = CustomColumnType::createByLookup("custom_08")->getCustom("-2")->getQuery();
        Book::getEntryArray($query, $params, 1);

        list($query, $params) = CustomColumnType::createByLookup("custom_09")->getCustom("0")->getQuery();
        Book::getEntryArray($query, $params, 1);

        list($query, $params) = CustomColumnType::createByLookup("custom_09")->getCustom("1")->getQuery();
        Book::getEntryArray($query, $params, 1);

        list($query, $params) = CustomColumnType::createByLookup("custom_10")->getCustom("-1")->getQuery();
        Book::getEntryArray($query, $params, 1);

        list($query, $params) = CustomColumnType::createByLookup("custom_10")->getCustom("0")->getQuery();
        Book::getEntryArray($query, $params, 1);

        list($query, $params) = CustomColumnType::createByLookup("custom_10")->getCustom("1")->getQuery();
        Book::getEntryArray($query, $params, 1);

        $_GET["custom"] = NULL;
        $config['cops_calibre_custom_column'] = array();
        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithSomeBooks/";
        Base::clearDb();
    }

    public function testGetURI()
    {
        global $config;

        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithCustomColumns/";
        $_GET["custom"] = "11";
        $config['cops_calibre_custom_column'] = array("custom_01", "custom_02", "custom_03", "custom_04", "custom_05", "custom_06", "custom_07", "custom_08", "custom_09", "custom_10", "custom_11");
        Base::clearDb();


        $custom = CustomColumnType::createByLookup("custom_01")->getCustom("1");
        $this->assertEquals($custom->customColumnType->getQuery("1"), $custom->getQuery());

        $custom = CustomColumnType::createByLookup("custom_02")->getCustom("3");
        $this->assertEquals($custom->customColumnType->getQuery("3"), $custom->getQuery());

        $custom = CustomColumnType::createByLookup("custom_03")->getCustom("3");
        $this->assertEquals($custom->customColumnType->getQuery("3"), $custom->getQuery());

        $custom = CustomColumnType::createByLookup("custom_04")->getCustom("4");
        $this->assertEquals($custom->customColumnType->getQuery("4"), $custom->getQuery());

        $custom = CustomColumnType::createByLookup("custom_05")->getCustom("6");
        $this->assertEquals($custom->customColumnType->getQuery("6"), $custom->getQuery());

        $custom = CustomColumnType::createByLookup("custom_06")->getCustom("2016-04-24");
        $this->assertEquals($custom->customColumnType->getQuery("2016-04-24"), $custom->getQuery());

        $custom = CustomColumnType::createByLookup("custom_07")->getCustom("11.0");
        $this->assertEquals($custom->customColumnType->getQuery("11.0"), $custom->getQuery());

        $custom = CustomColumnType::createByLookup("custom_08")->getCustom("-2");
        $this->assertEquals($custom->customColumnType->getQuery("-2"), $custom->getQuery());

        $custom = CustomColumnType::createByLookup("custom_09")->getCustom("0");
        $this->assertEquals($custom->customColumnType->getQuery("0"), $custom->getQuery());

        $custom = CustomColumnType::createByLookup("custom_09")->getCustom("1");
        $this->assertEquals($custom->customColumnType->getQuery("1"), $custom->getQuery());

        $custom = CustomColumnType::createByLookup("custom_10")->getCustom("-1");
        $this->assertEquals($custom->customColumnType->getQuery("-1"), $custom->getQuery());

        $custom = CustomColumnType::createByLookup("custom_10")->getCustom("0");
        $this->assertEquals($custom->customColumnType->getQuery("0"), $custom->getQuery());

        $custom = CustomColumnType::createByLookup("custom_10")->getCustom("1");
        $this->assertEquals($custom->customColumnType->getQuery("1"), $custom->getQuery());

        $_GET["custom"] = NULL;
        $config['cops_calibre_custom_column'] = array();
        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithSomeBooks/";
        Base::clearDb();
    }
}