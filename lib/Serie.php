<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

class Serie extends Base
{
    public const ALL_SERIES_ID = "cops:series";
    public const SERIES_COLUMNS = "series.id as id, series.name as name, series.sort as sort, count(*) as count";
    public const SQL_ALL_SERIES = "select {0} from series, books_series_link where series.id = series group by series.id, series.name, series.sort order by series.sort";
    public const SQL_SERIES_FOR_SEARCH = "select {0} from series, books_series_link where series.id = series and upper (series.name) like ? group by series.id, series.name, series.sort order by series.sort";

    public $id;
    public $name;

    public function __construct($post)
    {
        $this->id = $post->id;
        $this->name = $post->name;
    }

    public function getUri()
    {
        return "?page=".parent::PAGE_SERIE_DETAIL."&id=$this->id";
    }

    public function getEntryId()
    {
        return self::ALL_SERIES_ID.":".$this->id;
    }

    public static function getCount()
    {
        // str_format (localize("series.alphabetical", count(array))
        return parent::getCountGeneric("series", self::ALL_SERIES_ID, parent::PAGE_ALL_SERIES);
    }

    public static function getSerieByBookId($bookId)
    {
        $result = parent::getDb()->prepare('select  series.id as id, name
from books_series_link, series
where series.id = series and book = ?');
        $result->execute([$bookId]);
        if ($post = $result->fetchObject()) {
            return new Serie($post);
        }
        return null;
    }

    public static function getSerieById($serieId)
    {
        $result = parent::getDb()->prepare('select id, name  from series where id = ?');
        $result->execute([$serieId]);
        if ($post = $result->fetchObject()) {
            return new Serie($post);
        }
        return null;
    }

    public static function getAllSeries()
    {
        return Base::getEntryArrayWithBookNumber(self::SQL_ALL_SERIES, self::SERIES_COLUMNS, [], "Serie");
    }

    public static function getAllSeriesByQuery($query)
    {
        return Base::getEntryArrayWithBookNumber(self::SQL_SERIES_FOR_SEARCH, self::SERIES_COLUMNS, ['%' . $query . '%'], "Serie");
    }
}
