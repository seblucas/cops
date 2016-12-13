<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

class CustomColumnTypeDate extends CustomColumnType
{
    protected function __construct($pcustomId)
    {
        parent::__construct($pcustomId, self::CUSTOM_TYPE_DATE);
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
        $date = new DateTime($id);
        $query = str_format(Book::SQL_BOOKS_BY_CUSTOM_DATE, "{0}", "{1}", $this->getTableName());
        return array($query, array($date->format("Y-m-d")));
    }

    public function getCustom($id)
    {
        $date = new DateTime($id);

        return new CustomColumn($id, $date->format(localize("customcolumn.date.format")), $this);
    }

    protected function getAllCustomValuesFromDatabase()
    {
        $queryFormat = "SELECT date(value) AS datevalue, count(*) AS count FROM {0} GROUP BY datevalue";
        $query = str_format($queryFormat, $this->getTableName());
        $result = $this->getDb()->query($query);

        $entryArray = array();
        while ($post = $result->fetchObject()) {
            $date = new DateTime($post->datevalue);
            $id = $date->format("Y-m-d");

            $entryPContent = str_format(localize("bookword", $post->count), $post->count);
            $entryPLinkArray = array(new LinkNavigation ($this->getUri($id)));

            $entry = new Entry($date->format(localize("customcolumn.date.format")), $this->getEntryId($id), $entryPContent, $this->datatype, $entryPLinkArray, "", $post->count);

            array_push($entryArray, $entry);
        }

        return $entryArray;
    }

    public function getDescription()
    {
        $desc = $this->getDatabaseDescription();
        if ($desc === NULL || empty($desc)) $desc = str_format(localize("customcolumn.description"), $this->getTitle());
        return $desc;
    }

    public function getCustomByBook($book)
    {
        $queryFormat = "SELECT date({0}.value) AS datevalue FROM {0} WHERE {0}.book = {1}";
        $query = str_format($queryFormat, $this->getTableName(), $book->id);

        $result = $this->getDb()->query($query);
        if ($post = $result->fetchObject()) {
            $date = new DateTime($post->datevalue);

            return new CustomColumn($date->format("Y-m-d"), $date->format(localize("customcolumn.date.format")), $this);
        }
        return new CustomColumn(NULL, localize("customcolumn.date.unknown"), $this);
    }

    public function isSearchable()
    {
        return true;
    }
}
