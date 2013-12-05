<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

require_once('base.php');

class tag extends Base {
    const ALL_TAGS_ID = "cops:tags";

    public $id;
    public $name;

    public function __construct($pid, $pname) {
        $this->id = $pid;
        $this->name = $pname;
    }

    public function getUri () {
        return "?page=".parent::PAGE_TAG_DETAIL."&id=$this->id";
    }

    public function getEntryId () {
        return self::ALL_TAGS_ID.":".$this->id;
    }

    public static function getCount() {
        $nTags = parent::getDb ()->query('select count(*) from tags')->fetchColumn();
        if ($nTags == 0) return NULL;
        $entry = new Entry (localize("tags.title"), self::ALL_TAGS_ID,
            str_format (localize("tags.alphabetical", $nTags), $nTags), "text",
            array ( new LinkNavigation ("?page=".parent::PAGE_ALL_TAGS)));
        return $entry;
    }

    public static function getTagById ($tagId) {
        $result = parent::getDb ()->prepare('select id, name  from tags where id = ?');
        $result->execute (array ($tagId));
        if ($post = $result->fetchObject ()) {
            return new Tag ($post->id, $post->name);
        }
        return NULL;
    }

    public static function getAllTags() {
        $result = parent::getDb ()->query('select tags.id as id, tags.name as name, count(*) as count
from tags, books_tags_link
where tags.id = tag
group by tags.id, tags.name
order by tags.name');
        $entryArray = array();
        while ($post = $result->fetchObject ())
        {
            $tag = new Tag ($post->id, $post->name);
            array_push ($entryArray, new Entry ($tag->name, $tag->getEntryId (),
                str_format (localize("bookword", $post->count), $post->count), "text",
                array ( new LinkNavigation ($tag->getUri ()))));
        }
        return $entryArray;
    }

    public static function getAllTagsByQuery($query, $n, $database = NULL, $numberPerPage = NULL) {
        $columns  = "tags.id as id, tags.name as name, (select count(*) from books_tags_link where tags.id = tag) as count";
        $sql = 'select {0} from tags where tags.name like ? {1} order by tags.name';
        list ($totalNumber, $result) = parent::executeQuery ($sql, $columns, "", array ('%' . $query . '%'), $n, $database, $numberPerPage);
        $entryArray = array();
        while ($post = $result->fetchObject ())
        {
            $tag = new Tag ($post->id, $post->name);
            array_push ($entryArray, new Entry ($tag->name, $tag->getEntryId (),
                str_format (localize("bookword", $post->count), $post->count), "text",
                array ( new LinkNavigation ($tag->getUri ()))));
        }
        return array ($entryArray, $totalNumber);
    }
}
