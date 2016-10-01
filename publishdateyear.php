<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     <andreas.schulz@bilderbuffet.de>
 */
require_once('base.php');

class PublishdateYear extends Base {

    const PUBLISHDATE_YEAR_ID = "cops:publishdatesyear";
    const PUBLISHDATE_YEAR_COLUMNS = "strftime('%Y-%m', pubdate) as name, count(*) as count";
    const SQL_PUBLISHDATE_YEAR = "select {0} from books where name LIKE :year group by name order by name ASC";
    const SQL_PUBLISHDATE_YEAR_COUNT = "select count(DISTINCT strftime('%Y-%m', pubdate)) from books";

    public $id;
    public $name;

    public function __construct($post) {
        $this->id = $post->name;
        $this->name = $post->name;
    }

    public function getUri() {
        return "?page=" . parent::PAGE_PUBLISHDATE_MONTH . "&id=$this->id";
    }

    public function getEntryId() {
        return self::PUBLISHDATE_YEAR_ID . ":" . $this->id;
    }

    public static function getCount() {
        return parent::getCountGeneric("pubdate", self::PUBLISHDATE_YEAR_ID, parent::PAGE_PUBLISHDATE_YEAR, "pubdate", self::SQL_PUBLISHDATE_YEAR_COUNT);
    }

    public static function getAllPublishdateMonth($id) {
        return Base::getEntryArrayWithBookNumber(self::SQL_PUBLISHDATE_YEAR, self::PUBLISHDATE_YEAR_COLUMNS, array('year' => $id."%"), "PublishdateYear");
    }

    public static function getPublishdateMonth ($id) {
        $post = array(
            'id' => $id, 
            'name' => $id
        );
        return new PublishdateYear ($post);
    }
}
