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
    const SQL_ALL_PUBLISHERS = 
    		"select publishers.id as id, publishers.name as name, count(*) as count 
    		 from publishers 
    			inner join books_publishers_link as link on publishers.id = link.publisher 
    			inner join ({0}) as filter on filter.id = link.book
    		 group by publishers.id, publishers.name 
    		 order by publishers.name";
    const SQL_PUBLISHERS_FOR_SEARCH = 
    		"select publishers.id as id, publishers.name as name, count(*) as count 
    		 from publishers 
    			inner join books_publishers_link as link on publishers.id = link.publisher 
    			inner join ({0}) as filter on filter.id = link.book 
    		 where upper (publishers.name) like ? 
    		 group by publishers.id, publishers.name 
    		 order by publishers.name";


    public $id;
    public $name;

    public function __construct($post) {
        $this->id = $post->id;
        $this->name = $post->name;
    }

    public function getUri () {
        return "?page=".parent::PAGE_PUBLISHER_DETAIL."&id=$this->id";
    }

    public function getEntryId () {
        return self::ALL_PUBLISHERS_ID.":".$this->id;
    }

    public static function getCount() {
        // str_format (localize("publishers.alphabetical", count(array))
        return parent::getCountGeneric ("publishers", self::ALL_PUBLISHERS_ID, parent::PAGE_ALL_PUBLISHERS);
    }

    public static function getPublisherByBookId ($bookId) {
        $result = parent::getDb ()->prepare('select publishers.id as id, name
from books_publishers_link, publishers
where publishers.id = publisher and book = ?');
        $result->execute (array ($bookId));
        if ($post = $result->fetchObject ()) {
            return new Publisher ($post);
        }
        return NULL;
    }

    public static function getPublisherById ($publisherId) {
        $result = parent::getDb ()->prepare('select id, name
from publishers where id = ?');
        $result->execute (array ($publisherId));
        if ($post = $result->fetchObject ()) {
            return new Publisher ($post);
        }
        return NULL;
    }

    public static function getAllPublishers() {
        return Base::getEntryArrayWithBookNumber (self::SQL_ALL_PUBLISHERS, array (), "Publisher");
    }

    public static function getAllPublishersByQuery($query) {
        return Base::getEntryArrayWithBookNumber (self::SQL_PUBLISHERS_FOR_SEARCH, array ('%' . $query . '%'), "Publisher");
    }
}
