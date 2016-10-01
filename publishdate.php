<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     <andreas.schulz@bilderbuffet.de>
 */
require_once('base.php');

class Publishdate extends Base {

    const ALL_PUBLISHDATES_ID = "cops:publishdates";
    const PUBLISHDATE_COLUMNS = "strftime('%Y', pubdate) as name, count(*) as count";
    const SQL_ALL_PUBLISHDATES = "select {0} from books group by name order by name DESC";
    const SQL_PUBLISHDATE_COUNT = "select count(DISTINCT strftime('%Y', pubdate)) from books";

    public $id;
    public $name;

    public function __construct($post) {
        $this->id = $post->name;
        $this->name = $post->name;
    }

    public function getUri() {
        return "?page=" . parent::PAGE_PUBLISHDATE_YEAR . "&id=$this->id";
    }

    public function getEntryId() {
        return self::ALL_PUBLISHDATES_ID . ":" . $this->id;
    }

    public static function getCount() {
        return parent::getCountGeneric("pubdate", self::ALL_PUBLISHDATES_ID, parent::PAGE_ALL_PUBLISHDATES, "pubdate", self::SQL_PUBLISHDATE_COUNT);
    }

    public static function getAllPublishdates() {
        return Base::getEntryArrayWithBookNumber(self::SQL_ALL_PUBLISHDATES, self::PUBLISHDATE_COLUMNS, array(), "Publishdate");
    }

}
