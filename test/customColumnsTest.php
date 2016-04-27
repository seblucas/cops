<?php
/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

require_once (dirname(__FILE__) . "/config_test.php");
require_once (dirname(__FILE__) . "/../book.php");

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
}