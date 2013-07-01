<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     S�bastien Lucas <sebastien@slucas.fr>
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
        return "?page=".parent::PAGE_SERIE_DETAIL."&id=$this->id";
    }
    
    public function getEntryId () {
        return self::ALL_SERIES_ID.":".$this->id;
    }

    public static function getCount() {
        $nSeries = parent::getDb ()->query('select count(*) from series')->fetchColumn();
        if ($nSeries == 0) return NULL;
        $entry = new Entry (localize("series.title"), self::ALL_SERIES_ID, 
            str_format (localize("series.alphabetical", $nSeries), $nSeries), "text", 
            array ( new LinkNavigation ("?page=".parent::PAGE_ALL_SERIES)));
        return $entry;
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
        $entryArray = array();
        while ($post = $result->fetchObject ())
        {
            $serie = new Serie ($post->id, $post->sort);
            array_push ($entryArray, new Entry ($serie->name, $serie->getEntryId (), 
                str_format (localize("bookword", $post->count), $post->count), "text", 
                array ( new LinkNavigation ($serie->getUri ()))));
        }
        return $entryArray;
    }

    public static function getAllSeriesUnread() {
        $result = parent::getDb ()->query('select series.id as id, series.name as name, series.sort as sort, count(*) as count
from series, books_series_link left outer join custom_column_1 on books_authors_link.book=custom_column_1.book
where series.id = series and (custom_column_1.value is null or custom_column_1.value=false) 
group by series.id, series.name, series.sort
order by series.sort');
        $entryArray = array();
        while ($post = $result->fetchObject ())
        {
            $serie = new Serie ($post->id, $post->sort);
            array_push ($entryArray, new Entry ($serie->name, $serie->getEntryId (), 
                str_format (localize("bookword", $post->count), $post->count), "text", 
                array ( new LinkNavigation ($serie->getUri ()))));
        }
        return $entryArray;
    }
}
?>