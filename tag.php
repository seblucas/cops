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
    const SQL_ALL_TAGS = 
    	"select tags.id as id, tags.name as name, count(*) as count 
    	 from tags 
    		inner join books_tags_link as link on tags.id = link.tag
    		inner join ({0}) as filter on filter.id = link.book
    	 group by tags.id, tags.name 
    	 order by tags.name";
    const SQL_ALL_TAGS_BY_QUERY = 
    	"select tags.id as id, tags.name as name, count(*) as count
         from tags 
    		inner join books_tags_link as link on tags.id = link.tag
    		inner join ({0}) as filter on filter.id = link.book
    	 where upper (tags.name) like ? 
    	 group by tags.id, tags.name 
    	 order by tags.name";
    	

    public $id;
    public $name;

    public function __construct($post) {
        $this->id = $post->id;
        $this->name = $post->name;
    }

    public function getUri () {
        return "?page=".parent::PAGE_TAG_DETAIL."&id=$this->id";
    }

    public function getEntryId () {
        return self::ALL_TAGS_ID.":".$this->id;
    }

    public static function getCount() {
        // str_format (localize("tags.alphabetical", count(array))
        return parent::getCountGeneric ("tags", self::ALL_TAGS_ID, parent::PAGE_ALL_TAGS);
    }

    public static function getTagById ($tagId) {
        $result = parent::getDb ()->prepare('select id, name  from tags where id = ?');
        $result->execute (array ($tagId));
        if ($post = $result->fetchObject ()) {
            return new Tag ($post);
        }
        return NULL;
    }

    public static function getAllTags() {
        return Base::getEntryArrayWithBookNumber (self::SQL_ALL_TAGS, array (), "Tag");
    }

    public static function getAllTagsByQuery($query, $n, $database = NULL, $virtualLib = NULL, $numberPerPage = NULL) {
        $columns  = "tags.id as id, tags.name as name, (select count(*) from books_tags_link where tags.id = tag) as count";
        $sql = 'select {0} from tags where upper (tags.name) like ? {1} order by tags.name';
        list ($totalNumber, $result) = parent::executeFilteredQuery (self::SQL_ALL_TAGS_BY_QUERY, array ('%' . $query . '%'), $n, $database, $virtualLib, $numberPerPage);
        $entryArray = array();
        while ($post = $result->fetchObject ())
        {
            $tag = new Tag ($post);
            array_push ($entryArray, new Entry ($tag->name, $tag->getEntryId (),
                str_format (localize("bookword", $post->count), $post->count), "text",
                array ( new LinkNavigation ($tag->getUri ()))));
        }
        return array ($entryArray, $totalNumber);
    }
}
