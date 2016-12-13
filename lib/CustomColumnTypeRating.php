<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

class CustomColumnTypeRating extends CustomColumnType
{
    protected function __construct($pcustomId)
    {
        parent::__construct($pcustomId, self::CUSTOM_TYPE_RATING);
    }

    /**
     * Get the name of the sqlite table for this column
     *
     * @return string|null
     */
    private function getTableName()
    {
        return "custom_column_{$this->customId}";
    }

    /**
     * Get the name of the linking sqlite table for this column
     * (or NULL if there is no linktable)
     *
     * @return string|null
     */
    private function getTableLinkName()
    {
        return "books_custom_column_{$this->customId}_link";
    }

    /**
     * Get the name of the linking column in the linktable
     *
     * @return string|null
     */
    private function getTableLinkColumn()
    {
        return "value";
    }

    public function getQuery($id)
    {
        if ($id == 0) {
            $query = str_format(Book::SQL_BOOKS_BY_CUSTOM_RATING_NULL, "{0}", "{1}", $this->getTableLinkName(), $this->getTableName(), $this->getTableLinkColumn());
            return array($query, array());
        } else {
            $query = str_format(Book::SQL_BOOKS_BY_CUSTOM_RATING, "{0}", "{1}", $this->getTableLinkName(), $this->getTableName(), $this->getTableLinkColumn());
            return array($query, array($id));
        }
    }

    public function getCustom($id)
    {
        return new CustomColumn ($id, str_format(localize("customcolumn.stars", $id / 2), $id / 2), $this);
    }

    protected function getAllCustomValuesFromDatabase()
    {
        $queryFormat = "SELECT coalesce({0}.value, 0) AS value, count(*) AS count FROM books  LEFT JOIN {1} ON  books.id = {1}.book LEFT JOIN {0} ON {0}.id = {1}.value GROUP BY coalesce({0}.value, -1)";
        $query = str_format($queryFormat, $this->getTableName(), $this->getTableLinkName());
        $result = $this->getDb()->query($query);

        $countArray = array(0 => 0, 2 => 0, 4 => 0, 6 => 0, 8 => 0, 10 => 0);
        while ($row = $result->fetchObject()) {
            $countArray[$row->value] = $row->count;
        }

        $entryArray = array();

        for ($i = 0; $i <= 5; $i++) {
            $count = $countArray[$i * 2];
            $name = str_format(localize("customcolumn.stars", $i), $i);
            $entryid = $this->getEntryId($i * 2);
            $content = str_format(localize("bookword", $count), $count);
            $linkarray = array(new LinkNavigation($this->getUri($i * 2)));
            $entry = new Entry($name, $entryid, $content, $this->datatype, $linkarray, "", $count);
            array_push($entryArray, $entry);
        }

        return $entryArray;
    }

    public function getDescription()
    {
        return localize("customcolumn.description.rating");
    }

    public function getCustomByBook($book)
    {
        $queryFormat = "SELECT {0}.value AS value FROM {0}, {1} WHERE {0}.id = {1}.{2} AND {1}.book = {3}";
        $query = str_format($queryFormat, $this->getTableName(), $this->getTableLinkName(), $this->getTableLinkColumn(), $book->id);

        $result = $this->getDb()->query($query);
        if ($post = $result->fetchObject()) {
            return new CustomColumn($post->value, str_format(localize("customcolumn.stars", $post->value / 2), $post->value / 2), $this);
        }
        return new CustomColumn(NULL, localize("customcolumn.rating.unknown"), $this);
    }

    public function isSearchable()
    {
        return true;
    }
}
