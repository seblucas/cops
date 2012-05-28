<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

require_once('base.php');

class Serie extends Base {
    const ALL_SERIES_ID = "calibre:series";
    
    public $id;
    public $name;
    
    public function __construct($pid, $pname) {
        $this->id = $pid;
        $this->name = $pname;
    }
    
    public function getUri () {
        return "feed.php?page=".parent::PAGE_SERIE_DETAIL."&id=$this->id";
    }

    public static function getCount() {
        $nSeries = parent::getDb ()->query('select count(*) from series')->fetchColumn();
        parent::addEntryClass (new Entry ("Series", self::ALL_SERIES_ID, 
            "Alphabetical index of the $nSeries series", "text", 
            array ( new LinkNavigation ("feed.php?page=".parent::PAGE_ALL_SERIES))));
    }
    
    public static function getSerieByBookId ($bookId) {
        $result = parent::getDb ()->prepare('select  series.id as id, name
from books_series_link, series
where series.id = series and book = ?');
        $result->execute (array ($bookId));
        if ($post = $result->fetchObject ()) {
            return new Serie ($post->id, $post->name);
        }
        return NULL;
    }
    
    public static function getSerieById ($serieId) {
        $result = parent::getDb ()->prepare('select id, name  from series where id = ?');
        $result->execute (array ($serieId));
        if ($post = $result->fetchObject ()) {
            return new Serie ($post->id, $post->name);
        }
        return NULL;
    }
    
    public static function getAllSeries() {
        $result = parent::getDb ()->query('select series.id as id, series.name as name, series.sort as sort, count(*) as count
from series, books_series_link
where series.id = series
group by series.id, series.name, series.sort
order by series.sort');
        while ($post = $result->fetchObject ())
        {
            parent::addEntryClass (new Entry ($post->sort, self::ALL_SERIES_ID.":".$post->id, 
                "$post->count books", "text", 
                array ( new LinkNavigation ("feed.php?page=".parent::PAGE_SERIE_DETAIL."&id=$post->id"))));
        }
    }
}
?>