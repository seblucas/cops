<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     At Libitum <eljarec@yahoo.com>
 */

require_once('base.php');

class Publisher extends Base {
    const ALL_PUBLISHERS_ID = "cops:publishers";

    public $id;
    public $name;

    public function __construct($pid, $pname) {
        $this->id = $pid;
        $this->name = $pname;
    }

    public function getUri () {
        return "?page=".parent::PAGE_PUBLISHER_DETAIL."&id=$this->id";
    }

    public function getEntryId () {
        return self::ALL_PUBLISHERS_ID.":".$this->id;
    }

    public static function getCount() {
        $nPublishers = parent::getDb ()->query('select count(*) from publishers')->fetchColumn();
        if ($nPublishers == 0) return NULL;
        $entry = new Entry (localize("publishers.title"), self::ALL_PUBLISHERS_ID,
            str_format (localize("publishers.alphabetical", $nPublishers), $nPublishers), "text",
            array ( new LinkNavigation ("?page=".parent::PAGE_ALL_PUBLISHERS)));
        return $entry;
    }

    public static function getPublisherByBookId ($bookId) {
        $result = parent::getDb ()->prepare('select publishers.id as id, name
from books_publishers_link, publishers
where publishers.id = publisher and book = ?');
        $result->execute (array ($bookId));
        if ($post = $result->fetchObject ()) {
            return new Publisher ($post->id, $post->name);
        }
        return NULL;
    }

    public static function getPublisherById ($publisherId) {
        $result = parent::getDb ()->prepare('select id, name
from publishers where id = ?');
        $result->execute (array ($publisherId));
        if ($post = $result->fetchObject ()) {
            return new Publisher ($post->id, $post->name);
        }
        return NULL;
    }

    public static function getAllPublishers() {
        $result = parent::getDb ()->query('select publishers.id as id, publishers.name as name, count(*) as count
from publishers, books_publishers_link
where publishers.id = publisher
group by publishers.id, publishers.name
order by publishers.name');
        $entryArray = array();

        while ($post = $result->fetchObject ())
        {
            $publisher = new Publisher ($post->id, $post->name);
            array_push ($entryArray, new Entry ($publisher->name, $publisher->getEntryId (),
                str_format (localize("bookword", $post->count), $post->count), "text",
                array ( new LinkNavigation ($publisher->getUri ()))));
        }
        return $entryArray;
    }

    public static function getAllPublishersByQuery($query) {
        $result = parent::getDb ()->prepare('select publishers.id as id, publishers.name as name, count(*) as count
from publishers, books_publishers_link
where publishers.id = publisher and publishers.name like ?
group by publishers.id, publishers.name
order by publishers.name');
        $entryArray = array();
        $result->execute (array ('%' . $query . '%'));

        while ($post = $result->fetchObject ())
        {
            $publisher = new Publisher ($post->id, $post->name);
            array_push ($entryArray, new Entry ($publisher->name, $publisher->getEntryId (),
                str_format (localize("bookword", $post->count), $post->count), "text",
                array ( new LinkNavigation ($publisher->getUri ()))));
        }
        return $entryArray;
    }
}
