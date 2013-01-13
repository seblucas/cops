<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

require_once('base.php');

class Genre extends Base {

    const ALL_GENRES_ID = "calibre:genre";
    
    public $id;
    public $name;
    
    public function __construct($pid, $pname) {
        $this->id = $pid;
        $this->name = $pname;
    }
    
    public function getUri () {
        return "?page=".parent::PAGE_GENRE_DETAIL."&id=$this->id";
    }
    
    public function getEntryId () {
        return self::ALL_GENRES_ID.":".$this->id;
    }

    public static function getCount() {
	global $config;
        $nGenres = parent::getDb ()->query('select count(*) from ' . $config['genre_table'])->fetchColumn();
        $entry = new Entry (localize("genres.title"), self::ALL_GENRES_ID, 
            str_format (localize("genres.alphabetical"), $nGenres), "text",
            array ( new LinkNavigation ("?page=".parent::PAGE_ALL_GENRES)));
        return $entry;
    }
    
    public static function getGenreByBookId ($bookId) {
	global $config;
        $result = parent::getDb ()->prepare('select ' . $config['genre_table'] . '.id as id, name
	from ' . $config['genre_table'] . '_link, ' . $config['genre_table'] . '
	where ' . $config['genre_table'] . '.id = ' . $config['genre_table'] . ' and book = ?');
        $result->execute (array ($bookId));
        if ($post = $result->fetchObject ()) {
            return new Genre ($post->id, $post->name);
        }
        return NULL;
    }
    
    public static function getGenreById ($genreId) {
	global $config;
        $result = parent::getDb ()->prepare('select id, value from ' . $config['genre_table'] . ' where id = ?');
        $result->execute (array ($genreId));
        if ($post = $result->fetchObject ()) {
            return new Genre ($post->id, $post->value);
        }
        return NULL;
    }
    
    public static function getAllGenres() {
	global $config;
        $result = parent::getDb ()->query('select ' . $config['genre_table'] . '.id as id, ' . $config['genre_table'] . '.value as value, count(*) as count
	from ' . $config['genre_table'] . ', books_' . $config['genre_table'] . '_link
	where ' . $config['genre_table'] . '.id = books_' . $config['genre_table'] . '_link.value
	group by books_' . $config['genre_table'] . '_link.value order by value');
        $entryArray = array();
        while ($post = $result->fetchObject ())
        {
            $genre = new Genre ($post->id,$post->value);
            array_push ($entryArray, new Entry ($genre->name, $genre->getEntryId (), 
                str_format (localize("bookword", $post->count), $post->count), "text", 
                array ( new LinkNavigation ($genre->getUri ()))));
        }
        return $entryArray;
    }
}
?>
