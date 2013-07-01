<?php
/**
* COPS (Calibre OPDS PHP Server) class file
*
* @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
* @author Sébastien Lucas <sebastien@slucas.fr>
*/

require_once('base.php');

class Author extends Base {
    const ALL_AUTHORS_ID = "calibre:authors";
    
    const AUTHOR_COLUMNS = "authors.id as id, authors.name as name, authors.sort as sort, count(*) as count";
    const SQL_AUTHORS_BY_FIRST_LETTER = "select {0} from authors, books_authors_link where author = authors.id and upper (authors.sort) like ? group by authors.id, authors.name, authors.sort order by sort";
    const SQL_AUTHORS_BY_FIRST_LETTER_UNREAD = "select {0} from authors, books_authors_link left outer join custom_column_1 on books_authors_link.book=custom_column_1.book where author = authors.id and (custom_column_1.value is null or custom_column_1.value=0) and upper (authors.sort) like ? group by authors.id, authors.name, authors.sort order by sort";
    const SQL_ALL_AUTHORS = "select {0} from authors, books_authors_link where author = authors.id group by authors.id, authors.name, authors.sort order by sort";
    const SQL_ALL_AUTHORS_UNREAD = "select {0} from authors, books_authors_link left outer join custom_column_1 on books_authors_link.book=custom_column_1.book where author = authors.id and (custom_column_1.value is null or custom_column_1.value=0) group by authors.id, authors.name, authors.sort order by sort";
    
    public $id;
    public $name;
    public $sort;
    
    public function __construct($pid, $pname) {
        $this->id = $pid;
        $this->name = $pname;
    }
    
    public function getUri ($unread) {
    	if ($unread)
    		return "?page=".parent::PAGE_AUTHOR_DETAIL_UNREAD."&id=$this->id";
        return "?page=".parent::PAGE_AUTHOR_DETAIL."&id=$this->id";
    }
    
    public function getEntryId () {
        return self::ALL_AUTHORS_ID.":".$this->id;
    }
    
    public static function getEntryIdByLetter ($startingLetter) {
        return self::ALL_AUTHORS_ID.":letter:".$startingLetter;
    }

    public static function getCount() {
        $nAuthors = parent::getDb ()->query('select count(*) from authors')->fetchColumn();
        $entry = new Entry (localize("authors.title"), self::ALL_AUTHORS_ID,
            str_format (localize("authors.alphabetical", $nAuthors), $nAuthors), "text",
            array ( new LinkNavigation ("?page=".parent::PAGE_ALL_AUTHORS)));
        return $entry;
    }
    
    public static function getAllAuthorsByFirstLetter($unread) {
    	if ($unread) {
        	$result = parent::getDb ()->query("select substr (upper (sort), 1, 1) as title, count(*) as count from authors inner join books_authors_link on author = authors.id left outer join custom_column_1 on books_authors_link.book=custom_column_1.book where custom_column_1.value is null or custom_column_1.value=0 group by substr (upper (sort), 1, 1) order by substr (upper (sort), 1, 1)");
    	} else {
        	$result = parent::getDb ()->query("select substr (upper (sort), 1, 1) as title, count(*) as count from authors group by substr (upper (sort), 1, 1) order by substr (upper (sort), 1, 1)");
    	}
        $entryArray = array();
        while ($post = $result->fetchObject ())
        {
	    	if ($unread) {
	        	$page = parent::PAGE_AUTHORS_FIRST_LETTER_UNREAD;
	    	} else {
	        	$page = parent::PAGE_AUTHORS_FIRST_LETTER;
	    	}
            array_push ($entryArray, new Entry ($post->title, Author::getEntryIdByLetter ($post->title),
                str_format (localize("authorword", $post->count), $post->count), "text",
                array ( new LinkNavigation ("?page=".$page."&id=". rawurlencode ($post->title)))));
        }
        return $entryArray;
    }
    
    public static function getAuthorsByStartingLetter($letter, $unread) {
    	if ($unread)
	        return self::getEntryArray (self::SQL_AUTHORS_BY_FIRST_LETTER_UNREAD, array ($letter . "%"), false);
        return self::getEntryArray (self::SQL_AUTHORS_BY_FIRST_LETTER, array ($letter . "%"), false);
    }
    
    public static function getAllAuthors($unread) {
    	if ($unread)
	        return self::getEntryArray (self::SQL_ALL_AUTHORS_UNREAD, array (), false);
        return self::getEntryArray (self::SQL_ALL_AUTHORS, array (), false);
    }
    
    public static function getEntryArray ($query, $params, $unread) {
        list ($totalNumber, $result) = parent::executeQuery ($query, self::AUTHOR_COLUMNS, "", $params, -1);
        $entryArray = array();
        while ($post = $result->fetchObject ())
        {
            $author = new Author ($post->id, $post->sort);
            array_push ($entryArray, new Entry ($post->sort, $author->getEntryId (),
                str_format (localize("bookword", $post->count), $post->count), "text",
                array ( new LinkNavigation ($author->getUri ($unread)))));
        }
        return $entryArray;
    }
        
    public static function getAuthorById ($authorId) {
        $result = parent::getDb ()->prepare('select sort from authors where id = ?');
        $result->execute (array ($authorId));
        return new Author ($authorId, $result->fetchColumn ());
    }
    
    public static function getAuthorByBookId ($bookId) {
        $result = parent::getDb ()->prepare('select authors.id as id, authors.sort as sort
from authors, books_authors_link
where author = authors.id
and book = ?');
        $result->execute (array ($bookId));
        $authorArray = array ();
        while ($post = $result->fetchObject ()) {
            array_push ($authorArray, new Author ($post->id, $post->sort));
        }
        return $authorArray;
    }
}
?>