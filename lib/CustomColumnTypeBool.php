<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

class CustomColumnTypeBool extends CustomColumnType
{
    // PHP pre 5.6 does not support const arrays
    private $BOOLEAN_NAMES = array(
        -1 => "customcolumn.boolean.unknown", // localize("customcolumn.boolean.unknown")
        00 => "customcolumn.boolean.no",      // localize("customcolumn.boolean.no")
        +1 => "customcolumn.boolean.yes",     // localize("customcolumn.boolean.yes")
    );

    protected function __construct($pcustomId)
    {
        parent::__construct($pcustomId, self::CUSTOM_TYPE_BOOL);
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

    public function getQuery($id)
    {
        if ($id == -1) {
            $query = str_format(Book::SQL_BOOKS_BY_CUSTOM_BOOL_NULL, "{0}", "{1}", $this->getTableName());
            return array($query, array());
        } else if ($id == 0) {
            $query = str_format(Book::SQL_BOOKS_BY_CUSTOM_BOOL_FALSE, "{0}", "{1}", $this->getTableName());
            return array($query, array());
        } else if ($id == 1) {
            $query = str_format(Book::SQL_BOOKS_BY_CUSTOM_BOOL_TRUE, "{0}", "{1}", $this->getTableName());
            return array($query, array());
        } else {
            return NULL;
        }
    }

    public function getCustom($id)
    {
        return new CustomColumn($id, localize($this->BOOLEAN_NAMES[$id]), $this);
    }

    protected function getAllCustomValuesFromDatabase()
    {
        $queryFormat = "SELECT coalesce({0}.value, -1) AS id, count(*) AS count FROM books LEFT JOIN {0} ON  books.id = {0}.book GROUP BY {0}.value ORDER BY {0}.value";
        $query = str_format($queryFormat, $this->getTableName());
        $result = $this->getDb()->query($query);

        $entryArray = array();
        while ($post = $result->fetchObject()) {
            $entryPContent = str_format(localize("bookword", $post->count), $post->count);
            $entryPLinkArray = array(new LinkNavigation ($this->getUri($post->id)));

            $entry = new Entry(localize($this->BOOLEAN_NAMES[$post->id]), $this->getEntryId($post->id), $entryPContent, $this->datatype, $entryPLinkArray, "", $post->count);

            array_push($entryArray, $entry);
        }
        return $entryArray;
    }

    public function getDescription()
    {
        return localize("customcolumn.description.bool");
    }

    public function getCustomByBook($book)
    {
        $queryFormat = "SELECT {0}.value AS boolvalue FROM {0} WHERE {0}.book = {1}";
        $query = str_format($queryFormat, $this->getTableName(), $book->id);

        $result = $this->getDb()->query($query);
        if ($post = $result->fetchObject()) {
            return new CustomColumn($post->boolvalue, localize($this->BOOLEAN_NAMES[$post->boolvalue]), $this);
        } else {
            return new CustomColumn(-1, localize($this->BOOLEAN_NAMES[-1]), $this);
        }
    }

    public function isSearchable()
    {
        return true;
    }
}
